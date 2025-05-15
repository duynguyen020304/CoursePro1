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
// Giả sử các biến này đã được lấy từ database hoặc service
$totalCourses = 0; // Thay thế bằng số liệu thật
$totalUsers = 0;   // Thay thế bằng số liệu thật
$totalRevenueMonth = 0; // Thay thế bằng số liệu thật
$newOrdersToday = 0;  // Thay thế bằng số liệu thật
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/base_dashboard.css">
    <link rel="stylesheet" href="css/admin_style.css"> <style>
        .stat-card {
            border-left: 5px solid var(--bs-primary);
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
        }
        .stat-card .card-title {
            font-size: 1.1rem;
            font-weight: 500;
            color: #555;
        }
        .stat-card .card-text {
            font-size: 2rem;
            font-weight: bold;
        }
        .stat-card .bi {
            font-size: 2.5rem;
            opacity: 0.3;
        }
        .quick-links .list-group-item {
            font-size: 1.1rem;
        }
        .quick-links .list-group-item i {
            font-size: 1.3rem;
        }
    </style>
</head>

<body>
    <?php include('template/dashboard.php'); ?>

    <div class="main-content">
        <div class="container-fluid py-4">
            <h3 class="mb-4">Bảng điều khiển chung</h3>

            <div class="row g-4 mb-4">
                <div class="col-md-6 col-xl-3">
                    <div class="card shadow-sm stat-card border-primary">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-primary mb-1">Tổng số Khóa học</h6>
                                <p class="card-text text-primary mb-0"><?= htmlspecialchars($totalCourses) ?></p>
                            </div>
                            <i class="bi bi-journal-bookmark-fill text-primary"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card shadow-sm stat-card border-success">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-success mb-1">Tổng số Người dùng</h6>
                                <p class="card-text text-success mb-0"><?= htmlspecialchars($totalUsers) ?></p>
                            </div>
                            <i class="bi bi-people-fill text-success"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card shadow-sm stat-card border-warning">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-warning mb-1">Doanh thu tháng này</h6>
                                <p class="card-text text-warning mb-0"><?= number_format($totalRevenueMonth, 0, ',', '.') ?> ₫</p>
                            </div>
                            <i class="bi bi-cash-coin text-warning"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card shadow-sm stat-card border-info">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-info mb-1">Đơn hàng mới (hôm nay)</h6>
                                <p class="card-text text-info mb-0"><?= htmlspecialchars($newOrdersToday) ?></p>
                            </div>
                             <i class="bi bi-cart-check-fill text-info"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0"><i class="bi bi-lightning-charge-fill me-2"></i>Lối tắt nhanh</h5>
                        </div>
                        <div class="list-group list-group-flush quick-links">
                            <a href="course-management.php?action=add" class="list-group-item list-group-item-action d-flex align-items-center">
                                <i class="bi bi-plus-circle-fill text-success me-3"></i> Thêm khóa học mới
                            </a>
                            <a href="user-management.php" class="list-group-item list-group-item-action d-flex align-items-center">
                                <i class="bi bi-person-lines-fill text-info me-3"></i> Xem danh sách người dùng
                            </a>
                            <a href="revenue.php" class="list-group-item list-group-item-action d-flex align-items-center">
                                <i class="bi bi-graph-up-arrow text-warning me-3"></i> Xem báo cáo doanh thu
                            </a>
                            <a href="#" class="list-group-item list-group-item-action d-flex align-items-center">
                                <i class="bi bi-gear-fill text-secondary me-3"></i> Cài đặt chung
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                     <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0"><i class="bi bi-bell-fill me-2"></i>Thông báo gần đây</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-start mb-3">
                                <i class="bi bi-person-plus-fill text-primary fs-4 me-3"></i>
                                <div>
                                    <h6 class="mb-0">Người dùng mới đăng ký</h6>
                                    <small class="text-muted">user_abc@email.com vừa tham gia.</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-start mb-3">
                                <i class="bi bi-receipt text-success fs-4 me-3"></i>
                                <div>
                                    <h6 class="mb-0">Đơn hàng mới</h6>
                                    <small class="text-muted">Khóa học "Lập trình XYZ" vừa được mua.</small>
                                </div>
                            </div>
                             <div class="d-flex align-items-start">
                                <i class="bi bi-chat-left-text-fill text-info fs-4 me-3"></i>
                                <div>
                                    <h6 class="mb-0">Bình luận mới cần duyệt</h6>
                                    <small class="text-muted">Có bình luận mới trong khóa học "ABC".</small>
                                </div>
                            </div>
                            <div class="text-end mt-3">
                                <a href="#" class="btn btn-sm btn-outline-secondary">Xem tất cả</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            </div>
         <footer class="text-center text-muted mt-4 py-3 border-top">
            <small>&copy; <?= date('Y') ?> Course Online. All Rights Reserved.</small>
        </footer>
    </div>

    <script src="js/bootstrap.bundle.min.js"></script>