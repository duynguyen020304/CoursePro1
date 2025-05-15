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

// Dữ liệu mẫu cho purchase history và user
$purchaseHistory = [
    ['OrderID' => 'DH001', 'CourseName' => 'Khóa Học Lập Trình Web Cơ Bản', 'PurchaseDate' => '2024-12-01', 'Price' => 599000, 'InvoiceLink' => '#link-to-invoice-001'],
    ['OrderID' => 'DH002', 'CourseName' => 'Thiết Kế Giao Diện UI/UX Nâng Cao', 'PurchaseDate' => '2025-01-15', 'Price' => 799000, 'InvoiceLink' => '#link-to-invoice-002'],
    ['OrderID' => 'DH003', 'CourseName' => 'Marketing Online Từ A đến Z', 'PurchaseDate' => '2025-03-20', 'Price' => 1299000, 'InvoiceLink' => '#link-to-invoice-003'],
    ['OrderID' => 'DH004', 'CourseName' => 'Nghệ Thuật Nhiếp Ảnh Cơ Bản', 'PurchaseDate' => '2025-04-05', 'Price' => 499000, 'InvoiceLink' => '#link-to-invoice-004'],
];
$user = (object) [
    'name' => isset($_SESSION['user']['name']) ? $_SESSION['user']['name'] : 'Người dùng',
];

// Xác định trang hiện tại để active menu
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử mua hàng - Ecourse</title>
    <link href="public/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* TOÀN BỘ CSS GỐC CỦA BẠN TỪ FILE purchase-history.php TRƯỚC ĐÓ SẼ NẰM Ở ĐÂY */
        body { background-color: #f8f9fa; }
        .sidebar { width: 260px; height: 100vh; position: fixed; top: 0; left: 0; background-color: #343a40; padding-top: 1rem; transition: all 0.3s; z-index: 1030; }
        .sidebar .nav-link { color: #adb5bd; padding: 0.75rem 1.5rem; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: #fff; background-color: #495057; }
        .sidebar .sidebar-header { color: #fff; padding: 0 1.5rem 1rem 1.5rem; font-size: 1.2rem; font-weight: bold; }
        .main-content { transition: margin-left 0.3s; padding: 1.5rem; overflow-x: hidden; }
        @media (min-width: 992px) { .main-content { margin-left: 260px; } .navbar-toggler-icon { display: none !important; } }
        .offcanvas-start { width: 260px; background-color: #343a40; }
        .offcanvas-header { border-bottom: 1px solid #495057; }
        .offcanvas-title { color: #fff; }
        .offcanvas-body .nav-link { color: #adb5bd; }
        .offcanvas-body .nav-link:hover, .offcanvas-body .nav-link.active { color: #fff; background-color: #495057; }
        .btn-close-white { filter: invert(1) grayscale(100%) brightness(200%); }
        .topbar-sm { background-color: #fff; border-bottom: 1px solid #dee2e6; padding: 0.5rem 1rem; position: sticky; top: 0; z-index: 1020; }
        .nav-link i { vertical-align: middle; }
        .table th { font-weight: 500; }
        .table td, .table th { vertical-align: middle; }
        .badge.bg-success-light { background-color: var(--bs-success-bg-subtle); color: var(--bs-success-text-emphasis); }
        .badge.bg-warning-light { background-color: var(--bs-warning-bg-subtle); color: var(--bs-warning-text-emphasis); }
        .card { margin-bottom: 1.5rem; } /* Thêm lại style card nếu cần */
    </style>
</head>
<body>

    <?php include('template/user_sidebar.php'); // Include template sidebar đã sửa ?>

    <div class="main-content">
        <div class="topbar-sm d-lg-none d-flex justify-content-between align-items-center mb-3">
            <button class="btn btn-dark" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
                <i class="bi bi-list"></i>
            </button>
            <h5 class="mb-0">Lịch sử mua hàng</h5>
            <div></div>
        </div>

        <div class="container-fluid">
            <h3 class="mb-4 pt-2 pt-lg-0">Lịch sử mua hàng</h3>

            <div class="card shadow-sm">
                <div class="card-body">
                    <?php if (!empty($purchaseHistory)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Mã đơn hàng</th>
                                        <th>Tên khóa học</th>
                                        <th>Ngày mua</th>
                                        <th class="text-end">Giá (₫)</th>
                                        <th class="text-center">Hóa đơn</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($purchaseHistory as $item): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item['OrderID']) ?></td>
                                        <td><?= htmlspecialchars($item['CourseName']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($item['PurchaseDate'])) ?></td>
                                        <td class="text-end"><?= number_format($item['Price'], 0, ',', '.') ?></td>
                                        <td class="text-center">
                                            <a href="<?= htmlspecialchars($item['InvoiceLink']) ?>" class="btn btn-sm btn-outline-primary" title="Xem hóa đơn">
                                                <i class="bi bi-receipt-cutoff"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-light text-center" role="alert">
                            <i class="bi bi-cart-x fs-3 d-block mb-2"></i>
                            Bạn chưa có lịch sử mua hàng nào.
                            <br>
                            <a href="/all-courses.php" class="btn btn-primary mt-3">Khám phá khóa học</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
             <?php if (!empty($purchaseHistory) && count($purchaseHistory) > 5 ): ?>
            <nav aria-label="Purchase history pagination" class="mt-4 d-flex justify-content-center">
                <ul class="pagination">
                    <li class="page-item disabled"><a class="page-link" href="#">Trước</a></li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">Sau</a></li>
                </ul>
            </nav>
            <?php endif; ?>
        </div>

        <footer class="text-center text-muted mt-4 py-3 border-top">
            <small>&copy; <?= date('Y') ?> Tên Website. All Rights Reserved.</small>
        </footer>
    </div>
    <script src="public/js/bootstrap.bundle.min.js"></script>
</body>
</html>