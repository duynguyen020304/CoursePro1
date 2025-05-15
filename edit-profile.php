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

// Kiểm tra đã login chưa
if (!isset($_SESSION['user']['userID'])) {
    header('Location: /login.php');
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
        'headline' => 'Học viên tại Ecourse',
        'bio' => 'Tôi là một người đam mê học hỏi và khám phá những điều mới mẻ thông qua các khóa học trực tuyến.',
        'email' => 'user@example.com',
        'profileImage' => null,
        'websiteLink' => 'https://example.com',
        'twitterLink' => 'https://twitter.com/ecourseUser',
        'facebookLink' => 'https://facebook.com/ecourseUser',
        'linkedinLink' => 'https://linkedin.com/in/ecourseUser',
        'youtubeLink' => 'https://youtube.com/ecourseUser'
    ];
} else {
    /** @var \App\DTO\UserDTO $user */
    $user = $response->data;
    if (!isset($user->firstName)) $user->firstName = strtok($user->name, ' ');
    if (!isset($user->lastName)) $user->lastName = substr(strstr($user->name, ' '), 1) ?: '';
    if (!isset($user->headline)) $user->headline = '';
    if (!isset($user->bio)) $user->bio = '';
    if (!isset($user->websiteLink)) $user->websiteLink = '';
    if (!isset($user->twitterLink)) $user->twitterLink = '';
    if (!isset($user->facebookLink)) $user->facebookLink = '';
    if (!isset($user->linkedinLink)) $user->linkedinLink = '';
    if (!isset($user->youtubeLink)) $user->youtubeLink = '';
}

$updateMessage = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_profile'])) {
        $updateMessage = 'Chức năng cập nhật thông tin đang được phát triển.';
        $messageType = 'info';
    }
    if (isset($_POST['save_photo']) && isset($_FILES['profileImageFile']) && $_FILES['profileImageFile']['error'] == 0) {
         $updateMessage = 'Chức năng cập nhật ảnh đại diện đang được phát triển.';
        $messageType = 'info';
    }
    if (isset($_POST['save_password'])) {
        $updateMessage = 'Chức năng cập nhật mật khẩu đang được phát triển.';
        $messageType = 'info';
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

    <?php include('template/user_sidebar.php'); ?>
    <div class="main-content">
        <header class="profile-header">
            <div class="container-xl px-0 px-lg-3"> <h2 class="mb-3">Tài khoản</h2>
                <ul class="nav profile-nav">
                    <li class="nav-item">
                        <a class="nav-link active" id="profile-tab" data-bs-toggle="tab" href="#profileContent" role="tab" aria-controls="profileContent" aria-selected="true"><i class="bi bi-person-fill me-1"></i> Hồ sơ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="photo-tab" data-bs-toggle="tab" href="#photoContent" role="tab" aria-controls="photoContent" aria-selected="false"><i class="bi bi-image-fill me-1"></i> Ảnh đại diện</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="account-tab" data-bs-toggle="tab" href="#accountContent" role="tab" aria-controls="accountContent" aria-selected="false"><i class="bi bi-shield-lock-fill me-1"></i> Tài khoản</a>
                    </li>
                     <li class="nav-item">
                        <a class="nav-link" id="social-tab" data-bs-toggle="tab" href="#socialContent" role="tab" aria-controls="socialContent" aria-selected="false"><i class="bi bi-share-fill me-1"></i> Mạng xã hội</a>
                    </li>
                </ul>
            </div>
        </header>

        <div class="profile-content-wrapper container-xl px-0 px-lg-3">
            <?php if ($updateMessage): ?>
            <div class="alert alert-<?= $messageType ?> alert-dismissible fade show my-3" role="alert">
                <?= htmlspecialchars($updateMessage) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <div class="tab-content py-3" id="profileTabContent">
                <div class="tab-pane fade show active" id="profileContent" role="tabpanel" aria-labelledby="profile-tab">
                    <section class="form-section">
                        <h4 class="mb-4">Thông tin công khai</h4>
                        <form action="edit-profile.php" method="post">
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="firstName" class="form-label">Tên</label>
                                    <input type="text" class="form-control" id="firstName" name="firstName" value="<?= htmlspecialchars($user->firstName ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="lastName" class="form-label">Họ</label>
                                    <input type="text" class="form-control" id="lastName" name="lastName" value="<?= htmlspecialchars($user->lastName ?? '') ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="headline" class="form-label">Tiêu đề</label>
                                <input type="text" class="form-control" id="headline" name="headline" placeholder="Ví dụ: Nhà phát triển Web | Người đam mê học hỏi" value="<?= htmlspecialchars($user->headline ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label for="bio" class="form-label">Tiểu sử</label>
                                <textarea class="form-control" id="bio" name="bio" rows="5" placeholder="Giới thiệu ngắn gọn về bản thân..."><?= htmlspecialchars($user->bio ?? '') ?></textarea>
                            </div>
                            <button type="submit" name="save_profile" class="btn btn-primary"><i class="bi bi-save me-1"></i> Lưu hồ sơ</button>
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
                                <form action="edit-profile.php" method="post" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label for="profileImageFile" class="form-label">Tải ảnh mới</label>
                                        <input class="form-control" type="file" id="profileImageFile" name="profileImageFile" accept="image/jpeg, image/png, image/gif">
                                    </div>
                                    <button type="submit" name="save_photo" class="btn btn-primary"><i class="bi bi-upload me-1"></i> Tải lên</button>
                                </form>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="tab-pane fade" id="accountContent" role="tabpanel" aria-labelledby="account-tab">
                    <section class="form-section">
                        <h4 class="mb-4">Cài đặt tài khoản</h4>
                        <form action="edit-profile.php" method="post">
                             <div class="mb-3">
                                <label for="email" class="form-label">Địa chỉ email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user->email ?? '') ?>" readonly disabled>
                                <small class="form-text text-muted">Email không thể thay đổi.</small>
                            </div>
                            <hr class="my-4">
                            <h5 class="mb-3">Đổi mật khẩu</h5>
                            <div class="mb-3">
                                <label for="currentPassword" class="form-label">Mật khẩu hiện tại</label>
                                <input type="password" class="form-control" id="currentPassword" name="currentPassword">
                            </div>
                            <div class="mb-3">
                                <label for="newPassword" class="form-label">Mật khẩu mới</label>
                                <input type="password" class="form-control" id="newPassword" name="newPassword">
                            </div>
                            <div class="mb-3">
                                <label for="confirmPassword" class="form-label">Xác nhận mật khẩu mới</label>
                                <input type="password" class="form-control" id="confirmPassword" name="confirmPassword">
                            </div>
                            <button type="submit" name="save_password" class="btn btn-primary"><i class="bi bi-key-fill me-1"></i> Đổi mật khẩu</button>
                        </form>
                    </section>
                </div>

                <div class="tab-pane fade" id="socialContent" role="tabpanel" aria-labelledby="social-tab">
                    <section class="form-section">
                        <h4 class="mb-4">Liên kết mạng xã hội</h4>
                        <p class="text-muted">Thêm liên kết đến các trang mạng xã hội của bạn.</p>
                        <form action="edit-profile.php" method="post">
                             <div class="input-group mb-3">
                                <span class="input-group-text" style="width: 40px;"><i class="bi bi-globe"></i></span>
                                <input type="url" class="form-control" name="websiteLink" placeholder="Trang web (ví dụ: https://yourwebsite.com)" value="<?= htmlspecialchars($user->websiteLink ?? '') ?>">
                            </div>
                             <div class="input-group mb-3">
                                <span class="input-group-text" style="width: 40px;"><i class="bi bi-twitter-x"></i></span>
                                <input type="url" class="form-control" name="twitterLink" placeholder="Twitter (ví dụ: https://x.com/yourprofile)" value="<?= htmlspecialchars($user->twitterLink ?? '') ?>">
                            </div>
                             <div class="input-group mb-3">
                                <span class="input-group-text" style="width: 40px;"><i class="bi bi-facebook"></i></span>
                                <input type="url" class="form-control" name="facebookLink" placeholder="Facebook (ví dụ: https://facebook.com/yourprofile)" value="<?= htmlspecialchars($user->facebookLink ?? '') ?>">
                            </div>
                             <div class="input-group mb-3">
                                <span class="input-group-text" style="width: 40px;"><i class="bi bi-linkedin"></i></span>
                                <input type="url" class="form-control" name="linkedinLink" placeholder="LinkedIn (ví dụ: https://linkedin.com/in/yourprofile)" value="<?= htmlspecialchars($user->linkedinLink ?? '') ?>">
                            </div>
                             <div class="input-group mb-3">
                                <span class="input-group-text" style="width: 40px;"><i class="bi bi-youtube"></i></span>
                                <input type="url" class="form-control" name="youtubeLink" placeholder="YouTube (ví dụ: https://youtube.com/c/yourchannel)" value="<?= htmlspecialchars($user->youtubeLink ?? '') ?>">
                            </div>
                            <button type="submit" name="save_profile" class="btn btn-primary"><i class="bi bi-save me-1"></i> Lưu liên kết</button>
                        </form>
                    </section>
                </div>

            </div>
        </div>

        <footer class="text-center text-muted mt-4 py-3 border-top">
            <small>&copy; <?= date('Y') ?> Tên Website. All Rights Reserved.</small>
        </footer>
    </div>
    <script src="public/js/bootstrap.bundle.min.js"></script>
    <script>
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
            let activeTab = localStorage.getItem('activeProfileTab');
            if (activeTab) {
                let tabElement = document.querySelector('#profileTabContent .tab-pane' + activeTab);
                let navLink = document.querySelector('.profile-nav .nav-link[href="' + activeTab + '"]');
                if (tabElement && navLink) {
                    document.querySelector('.profile-nav .nav-link.active').classList.remove('active');
                    document.querySelector('#profileTabContent .tab-pane.active').classList.remove('show', 'active');
                    navLink.classList.add('active');
                    navLink.setAttribute('aria-selected', 'true');
                    tabElement.classList.add('show', 'active');
                } else {
                    document.querySelector('.profile-nav .nav-link#profile-tab').classList.add('active');
                    document.querySelector('#profileTabContent .tab-pane#profileContent').classList.add('show', 'active');
                }
            }
            var profileNavLinks = document.querySelectorAll('.profile-nav .nav-link');
            profileNavLinks.forEach(function(link) {
                link.addEventListener('click', function(event) {
                    localStorage.setItem('activeProfileTab', this.getAttribute('href'));
                });
            });
        });
    </script>
</body>
</html>