<?php
// Bật session
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
if (!isset($_SESSION['user']['userID']) || !isset($_SESSION['user']['token'])) { // Thêm kiểm tra token
    // Bạn có thể muốn xử lý việc chuyển hướng hoặc báo lỗi nếu không có token
    // Ví dụ: nếu API yêu cầu token cho mọi request
    // header('Location: /login.php'); // Kiểm tra lại đường dẫn này
    // exit;
    // Hoặc nếu một số endpoint không cần token, bạn có thể bỏ qua
    // echo "Cảnh báo: Người dùng chưa đăng nhập hoặc không có token.";
    // Trong trường hợp này, hàm callApi sẽ không gửi header Authorization
}

$loggedInUserID = $_SESSION['user']['userID'] ?? null; // Đảm bảo userID tồn tại

// Gọi API, truyền ID qua mảng $payload
// Chỉ gọi API nếu loggedInUserID có giá trị
if ($loggedInUserID) {
    $userResp = callApi('user_api.php', 'GET', ['id' => $loggedInUserID]);
    $user = $userResp; // Gán toàn bộ response để có thể kiểm tra success và message
} else {
    // Xử lý trường hợp không có userID (ví dụ: nếu bạn không exit ở trên)
    $user = [
        'success' => false,
        'message' => 'UserID không hợp lệ hoặc người dùng chưa đăng nhập.',
        'data' => null,
        'http_status_code' => 401 // Unauthorized
    ];
}

// Ví dụ cách hiển thị thông tin người dùng hoặc lỗi

if (isset($userResp['success']) && $userResp['success'] === true && isset($userResp['data'])) {
    $user = $userResp['data'];
}

// Xác định trang hiện tại để active menu
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Học viên - <?= htmlspecialchars($user->name) ?></title>
    <link href="public/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* TOÀN BỘ CSS GỐC CỦA BẠN SẼ NẰM Ở ĐÂY */
        body {
            background-color: #f8f9fa;
        }

        .sidebar {
            width: 260px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #343a40;
            padding-top: 1rem;
            transition: all 0.3s;
            z-index: 1030;
        }

        .sidebar .nav-link {
            color: #adb5bd;
            padding: 0.75rem 1.5rem;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #fff;
            background-color: #495057;
        }

        .sidebar .sidebar-header {
            color: #fff;
            padding: 0 1.5rem 1rem 1.5rem;
            font-size: 1.2rem;
            font-weight: bold;
        }

        .main-content {
            transition: margin-left 0.3s;
            padding: 1.5rem;
            overflow-x: hidden;
        }

        @media (min-width: 992px) {
            .main-content {
                margin-left: 260px;
            }

            .navbar-toggler-icon {
                display: none !important;
            }
        }

        .offcanvas-start {
            width: 260px;
            background-color: #343a40;
        }

        .offcanvas-header {
            border-bottom: 1px solid #495057;
        }

        .offcanvas-title {
            color: #fff;
        }

        .offcanvas-body .nav-link {
            color: #adb5bd;
        }

        .offcanvas-body .nav-link:hover,
        .offcanvas-body .nav-link.active {
            color: #fff;
            background-color: #495057;
        }

        .btn-close-white {
            filter: invert(1) grayscale(100%) brightness(200%);
        }

        .topbar-sm {
            background-color: #fff;
            border-bottom: 1px solid #dee2e6;
            padding: 0.5rem 1rem;
            position: sticky;
            top: 0;
            z-index: 1020;
        }

        .card {
            margin-bottom: 1.5rem;
        }

        .profile-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 1rem;
            margin-bottom: 0.5rem;
        }

        .course-thumbnail {
            width: 80px;
            height: 50px;
            object-fit: cover;
            margin-right: 1rem;
            border-radius: 0.25rem;
        }

        .nav-link i {
            vertical-align: middle;
        }
    </style>
</head>

<body>

    <?php include('template/user_sidebar.php'); // Include template sidebar người dùng 
    ?>

    <div class="main-content">
        <div class="topbar-sm d-lg-none d-flex justify-content-between align-items-center mb-3">
            <button class="btn btn-dark" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
                <i class="bi bi-list"></i>
            </button>
            <h5 class="mb-0">Dashboard</h5>
            <div></div>
        </div>

        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="bi bi-bell-fill me-2"></i> Bạn có 2 thông báo mới!
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0"><i class="bi bi-journal-bookmark-fill me-2"></i> Các khóa học đã đăng ký</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-4 pb-2 border-bottom">
                                <img src="https://via.placeholder.com/80x50/dee2e6/6c757d.png?text=Course+1" alt="Course Thumbnail" class="course-thumbnail">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Tên Khóa Học Rất Dài Để Kiểm Tra Xuống Dòng</h6>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 75%;" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <small class="text-muted">Đã hoàn thành 75%</small>
                                </div>
                                <a href="#" class="btn btn-sm btn-primary ms-3">Tiếp tục học</a>
                            </div>
                            <div class="d-flex align-items-center mb-4 pb-2 border-bottom">
                                <img src="https://via.placeholder.com/80x50/dee2e6/6c757d.png?text=Course+2" alt="Course Thumbnail" class="course-thumbnail">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Khóa Học Lập Trình Web Cơ Bản</h6>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: 30%;" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <small class="text-muted">Đã hoàn thành 30%</small>
                                </div>
                                <a href="#" class="btn btn-sm btn-primary ms-3">Tiếp tục học</a>
                            </div>
                            <div class="d-flex align-items-center"> <img src="https://via.placeholder.com/80x50/dee2e6/6c757d.png?text=Course+3" alt="Course Thumbnail" class="course-thumbnail">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Thiết Kế Giao Diện UI/UX</h6>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: 10%;" aria-valuenow="10" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <small class="text-muted">Đã hoàn thành 10%</small>
                                </div>
                                <a href="#" class="btn btn-sm btn-primary ms-3">Tiếp tục học</a>
                            </div>
                            <div class="text-center mt-3">
                                <a href="#my-courses" class="btn btn-outline-secondary btn-sm">Xem tất cả khóa học</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body text-center">
                            <img
                                src="<?= htmlspecialchars(
                                            !empty($user->profileImage)
                                                ? '/media/' . $user->profileImage
                                                : '/public/img/avatar-user.png'
                                        ) ?>"
                                alt="Avatar"
                                class="profile-avatar mb-2">

                            <h5 class="card-title">
                                Chào <?= htmlspecialchars($user['firstName'] . " " . $user['lastName']) ?>!
                            </h5>

                            <p class="card-text text-muted mb-1">
                                Email: <?= htmlspecialchars($user['email']) ?>
                            </p>

                            <?php if (isset($user['created_at']) && !empty($user['created_at'])): ?>
                                <p class="card-text text-muted mb-3">
                                    Ngày tham gia:
                                    <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                                </p>
                            <?php endif; ?>

                            <a href="edit-profile.php" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-pencil-square me-1"></i> Chỉnh sửa Hồ sơ
                            </a>
                        </div>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0"><i class="bi bi-lightbulb-fill me-2"></i> Gợi ý cho bạn</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <img src="https://via.placeholder.com/80x50/dee2e6/6c757d.png?text=Suggest+1" alt="Suggested Course" class="course-thumbnail">
                                <div class="flex-grow-1">
                                    <h6 class="mb-0 fs-sm">Khóa Học ReactJS Nâng Cao</h6>
                                    <small class="text-muted">Lập trình viên</small>
                                </div>
                                <a href="#" class="btn btn-sm btn-outline-success ms-2" title="Xem chi tiết"><i class="bi bi-arrow-right-circle-fill"></i></a>
                            </div>
                            <div class="d-flex align-items-center"> <img src="https://via.placeholder.com/80x50/dee2e6/6c757d.png?text=Suggest+2" alt="Suggested Course" class="course-thumbnail">
                                <div class="flex-grow-1">
                                    <h6 class="mb-0 fs-sm">Nghệ Thuật Nhiếp Ảnh Cơ Bản</h6>
                                    <small class="text-muted">Nhiếp ảnh</small>
                                </div>
                                <a href="#" class="btn btn-sm btn-outline-success ms-2" title="Xem chi tiết"><i class="bi bi-arrow-right-circle-fill"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <footer class="text-center text-muted mt-4">
            <small>&copy; <?= date('Y') ?> Tên Website. All Rights Reserved.</small>
        </footer>

    </div>
    <script src="public/js/bootstrap.bundle.min.js"></script>
</body>

</html>