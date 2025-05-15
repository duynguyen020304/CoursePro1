<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http');
$host = $_SERVER['HTTP_HOST'];
$script_path = $_SERVER['SCRIPT_NAME'];
$path_parts = explode('/', ltrim($script_path, '/'));
$app_root_directory_name = $path_parts[0];
$app_root_path_relative = '/' . $app_root_directory_name;
$known_app_subdir_markers = ['/admin/', '/api/', '/includes/'];
$found_marker = false;

foreach ($known_app_subdir_markers as $marker) {
    $pos = strpos($script_path, $marker);
    if ($pos !== false) {
        $app_root_path_relative = substr($script_path, 0, $pos);
        $found_marker = true;
        break;
    }
}

if (!$found_marker) {
    $app_root_path_relative = dirname($script_path);
    if ($app_root_path_relative === '/' && $script_path !== '/') {
        $app_root_path_relative = '';
    } elseif ($app_root_path_relative === '/' && $script_path === '/') {
        $app_root_path_relative = '';
    }
}

if ($app_root_path_relative !== '/' && $app_root_path_relative !== '' && substr($app_root_path_relative, -1) === '/') {
    $app_root_path_relative = rtrim($app_root_path_relative, '/');
}

define('API_BASE', $protocol . '://' . $host . $app_root_path_relative . '/api');

function callApi(string $endpoint, string $method = 'GET', array $payload = []): array
{
    $url = API_BASE . '/' . ltrim($endpoint, '/');
    $methodUpper = strtoupper($method); // Chuyển method thành chữ hoa để xử lý nhất quán

    // Nếu là GET và có $payload, xây dựng query string
    if ($methodUpper === 'GET' && !empty($payload)) {
        $url .= '?' . http_build_query($payload);
    }

    // Khởi tạo chuỗi header
    $headers = "Content-Type: application/json; charset=utf-8\r\n" .
        "Accept: application/json\r\n";

    // Lấy token từ session nếu có
    $token = $_SESSION['user']['token'] ?? null;

    // Nếu có token, thêm header Authorization
    if ($token) {
        $headers .= "Authorization: Bearer " . $token . "\r\n";
    }

    $options = [
        'http' => [
            'method'        => $methodUpper,
            'header'        => $headers, // Sử dụng chuỗi headers đã được cập nhật
            'ignore_errors' => true,
        ]
    ];

    // Chỉ thêm 'content' (body) cho các method không phải GET và có $payload
    if ($methodUpper !== 'GET') {
        if (!empty($payload)) {
            $options['http']['content'] = json_encode($payload);
        } else if (in_array($methodUpper, ['POST', 'PUT'])) {
            // Gửi một đối tượng JSON rỗng nếu không có payload cho POST/PUT
            $options['http']['content'] = '{}';
        }
    }

    $context  = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);
    $result   = json_decode($response, true);

    $status_code = 500; // Mặc định là lỗi server nếu không lấy được header
    if (isset($http_response_header[0])) {
        preg_match('{HTTP\/\S*\s(\d{3})}', $http_response_header[0], $match);
        if (isset($match[1])) {
            $status_code = intval($match[1]);
        }
    }

    // Nếu $result không phải là mảng (ví dụ: lỗi decode JSON), trả về cấu trúc lỗi chuẩn
    if (!is_array($result)) {
        return [
            'success' => false,
            'message' => 'Invalid API response or failed to decode JSON.',
            'data' => null,
            'raw_response' => $response, // Giữ lại raw response để debug
            'http_status_code' => $status_code
        ];
    }

    // Đảm bảo có 'http_status_code' và 'success' trong kết quả trả về
    $result['http_status_code'] = $status_code;
    if (!isset($result['success'])) {
        $result['success'] = ($status_code >= 200 && $status_code < 300);
    }
    return $result;
}


if (!isset($_SESSION['user']['userID'])) {
    header('Location: /signin.php');
    exit;
}

// Nạp class service và DTO
require_once __DIR__ . '/service/service_user.php';
require_once __DIR__ . '/model/dto/user_dto.php';

// Khởi tạo service và gọi lấy user
$userService = new UserService();
$response    = $userService->get_user_by_id($_SESSION['user']['userID']);

if (!$response->success) {
    $user = (object) [
        'userID' => $_SESSION['user']['userID'],
        'name' => 'Người dùng Mẫu',
        'firstName' => 'Người',
        'lastName' => 'Dùng Mẫu',
        'email' => 'user@example.com',
        'profileImage' => null,
    ];
} else {
    /** @var \App\DTO\UserDTO $user */
    $user = $response->data;
    if (!isset($user->firstName)) $user->firstName = strtok($user->name, ' ');
    if (!isset($user->lastName)) $user->lastName = substr(strstr($user->name, ' '), 1) ?: '';
    // Đảm bảo các thuộc tính Email, ProfileImage, UserID tồn tại để tránh lỗi undefined property sau này
    if (!isset($user->Email)) $user->Email = 'user@example.com'; // Giá trị mặc định nếu thiếu
    if (!isset($user->ProfileImage)) $user->ProfileImage = null;
    if (!isset($user->userID)) $user->userID = $_SESSION['user']['userID'];
}

$updateMessage = '';
$messageType = '';

// Xử lý POST request (để hiển thị thông báo tạm thời, logic chính nằm ở controllers/c_edit-profile.php)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Giả sử controller sẽ redirect về với một param hoặc session message để hiển thị
    // Hoặc nếu bạn muốn xử lý trực tiếp ở đây (ít phổ biến hơn khi action trỏ đi nơi khác)
    // thì bạn cần submit form về chính trang này hoặc dùng AJAX.
    // Đoạn code hiện tại sẽ không chạy vì form action trỏ đến controllers/c_edit-profile.php
    // Tuy nhiên, tôi vẫn giữ lại cấu trúc này nếu bạn có ý định khác.
    if (isset($_POST['action'])) { // Thay vì check tên button, check giá trị của input hidden 'action'
        if ($_POST['action'] == 'save_profile') {
            $updateMessage = 'Chức năng cập nhật thông tin đang được phát triển.';
            $messageType = 'info';
        }
        if ($_POST['action'] == 'save_photo' && isset($_FILES['profileImageFile']) && $_FILES['profileImageFile']['error'] == 0) {
             $updateMessage = 'Chức năng cập nhật ảnh đại diện đang được phát triển.';
            $messageType = 'info';
        }
        if ($_POST['action'] == 'save_password') {
            $updateMessage = 'Chức năng cập nhật mật khẩu đang được phát triển.';
            $messageType = 'info';
        }
    }
}


// Xác định trang hiện tại để active menu
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa Hồ sơ - Ecourse</title>
    <link href="public/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="public/css/edit-profile.css" rel="stylesheet">
</head>
<body>

    <?php include('template/user_sidebar.php'); // Đường dẫn tới sidebar của bạn ?>
    <div class="main-content">
        <header class="profile-header">
            <div class="container-xl px-0 px-lg-3"> <h2 class="mb-3">Tài khoản của tôi</h2>
                <ul class="nav profile-nav">
                    <li class="nav-item">
                        <a class="nav-link active" id="profile-tab" data-bs-toggle="tab" href="#profileContent" role="tab" aria-controls="profileContent" aria-selected="true"><i class="bi bi-person-badge me-1"></i> Hồ sơ & Bảo mật</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="photo-tab" data-bs-toggle="tab" href="#photoContent" role="tab" aria-controls="photoContent" aria-selected="false"><i class="bi bi-image-fill me-1"></i> Ảnh đại diện</a>
                    </li>
                    </ul>
            </div>
        </header>

        <div class="profile-content-wrapper container-xl px-0 px-lg-3">
            <?php if ($updateMessage): ?>
            <div class="alert alert-<?= htmlspecialchars($messageType) ?> alert-dismissible fade show my-3" role="alert">
                <?= htmlspecialchars($updateMessage) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <div class="tab-content py-3" id="profileTabContent">
                <div class="tab-pane fade show active" id="profileContent" role="tabpanel" aria-labelledby="profile-tab">
                    <section class="form-section">
                        <h4 class="mb-4">Thông tin cá nhân</h4>
                        <form action="controllers/c_edit-profile.php" method="post">
                            <input type="hidden" name="action" value="save_profile"> <input type="hidden" name="userID" value="<?= htmlspecialchars($user->userID ?? '') ?>">

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="firstName" class="form-label">Tên</label>
                                    <input type="text" class="form-control" id="firstName" name="firstName" value="<?= htmlspecialchars($user->firstName ?? '') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="lastName" class="form-label">Họ</label>
                                    <input type="text" class="form-control" id="lastName" name="lastName" value="<?= htmlspecialchars($user->lastName ?? '') ?>" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Lưu thay đổi</button>
                        </form>
                    </section>

                    <hr class="my-4"> <section class="form-section">
                        <h4 class="mb-4">Cài đặt tài khoản & Bảo mật</h4>
                         <form action="controllers/c_edit-profile.php" method="post">
                            <input type="hidden" name="action" value="save_password"> <input type="hidden" name="userID" value="<?= htmlspecialchars($user->userID ?? '') ?>">

                            <div class="mb-3">
                                <label for="email" class="form-label">Địa chỉ email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user->email ?? '') ?>" readonly disabled>
                                <small class="form-text text-muted">Email không thể thay đổi.</small>
                            </div>
                            <hr class="my-4">
                            <h5 class="mb-3">Đổi mật khẩu</h5>
                            <div class="mb-3">
                                <label for="currentPassword" class="form-label">Mật khẩu hiện tại</label>
                                <input type="password" class="form-control" id="currentPassword" name="currentPassword" required>
                            </div>
                            <div class="mb-3">
                                <label for="newPassword" class="form-label">Mật khẩu mới</label>
                                <input type="password" class="form-control" id="newPassword" name="newPassword" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirmPassword" class="form-label">Xác nhận mật khẩu mới</label>
                                <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-key-fill me-1"></i> Đổi mật khẩu</button>
                        </form>
                    </section>
                </div>

                <div class="tab-pane fade" id="photoContent" role="tabpanel" aria-labelledby="photo-tab">
                    <section class="form-section">
                        <h4 class="mb-4">Ảnh đại diện</h4>
                        <div class="row align-items-center">
                            <div class="col-md-3 text-center mb-3 mb-md-0">
                                <img src="<?= htmlspecialchars(!empty($user->profileImage) ? '/media/' . $user->profileImage : '/public/img/avatar-user.png') ?>" alt="Ảnh đại diện hiện tại" class="profile-image-lg" id="currentProfileImage">
                            </div>
                            <div class="col-md-9">
                                <p class="text-muted">Để có kết quả tốt nhất, hãy tải lên ảnh vuông có kích thước ít nhất 200x200 pixel.</p>
                                <form action="controllers/c_edit-profile.php" method="post" enctype="multipart/form-data">
                                    <input type="hidden" name="action" value="save_photo"> <input type="hidden" name="userID" value="<?= htmlspecialchars($user->userID ?? '') ?>">
                                    <div class="mb-3">
                                        <label for="profileImageFile" class="form-label">Tải ảnh mới</label>
                                        <input class="form-control" type="file" id="profileImageFile" name="profileImageFile" accept="image/jpeg, image/png, image/gif" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-upload me-1"></i> Tải lên</button>
                                </form>
                            </div>
                        </div>
                    </section>
                </div>

                </div>
        </div>

        <footer class="text-center text-muted mt-4 py-3 border-top">
            <small>© <?= date('Y') ?> Tên Website Của Bạn. All Rights Reserved.</small>
        </footer>
    </div>
    <script src="public/js/bootstrap.bundle.min.js"></script>
    <script>
        // Giữ nguyên phần JavaScript xử lý active tab và preview ảnh
        const profileImageFile = document.getElementById('profileImageFile');
        const currentProfileImage = document.getElementById('currentProfileImage');
        if (profileImageFile && currentProfileImage) {
            profileImageFile.addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        currentProfileImage.src = e.target.result;
                    }
                    reader.readAsDataURL(file);
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            let activeTabId = localStorage.getItem('activeProfileTab');

            const defaultTabId = '#profileContent';
            // Cập nhật danh sách validTabIds, loại bỏ #accountContent
            const validTabIds = ['#profileContent', '#photoContent'];

            if (!activeTabId || !validTabIds.includes(activeTabId)) {
                activeTabId = defaultTabId;
            }
            
            // Deactivate all tabs and nav links first
            document.querySelectorAll('.profile-nav .nav-link.active').forEach(link => link.classList.remove('active'));
            document.querySelectorAll('#profileTabContent .tab-pane.active').forEach(pane => pane.classList.remove('show', 'active'));

            // Activate the stored/default tab
            // Đảm bảo selector đúng cho tab pane (nối activeTabId trực tiếp)
            const tabElement = document.querySelector('#profileTabContent .tab-pane' + activeTabId);
            const navLink = document.querySelector('.profile-nav .nav-link[href="' + activeTabId + '"]');

            if (tabElement && navLink) {
                navLink.classList.add('active');
                navLink.setAttribute('aria-selected', 'true'); // Cần thiết cho Bootstrap 5 tabs
                tabElement.classList.add('show', 'active');
            } else {
                // Fallback nếu có lỗi (dù đã kiểm tra ở trên)
                const defaultNavLink = document.querySelector('.profile-nav .nav-link[href="' + defaultTabId + '"]');
                const defaultTabElement = document.querySelector('#profileTabContent .tab-pane' + defaultTabId);
                if (defaultNavLink && defaultTabElement) {
                    defaultNavLink.classList.add('active');
                    defaultNavLink.setAttribute('aria-selected', 'true');
                    defaultTabElement.classList.add('show', 'active');
                }
            }

            var profileNavLinks = document.querySelectorAll('.profile-nav .nav-link');
            profileNavLinks.forEach(function(link) {
                link.addEventListener('click', function(event) {
                    // Bootstrap tự xử lý việc active tab khi click,
                    // chúng ta chỉ cần lưu lại href của tab vừa được click
                    localStorage.setItem('activeProfileTab', this.getAttribute('href'));
                });
            });
        });
    </script>
</body>
</html>