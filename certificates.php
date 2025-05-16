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

// Lấy danh sách chứng chỉ (Ví dụ dữ liệu mẫu)
$certificates = [
    ['CertificateID' => 'CERT001', 'CourseName' => 'Khóa Học Lập Trình Web Cơ Bản', 'CompletionDate' => '2025-02-28', 'CertificateImage' => 'https://via.placeholder.com/300x200/6f42c1/fff?text=Certificate+Web+Basics', 'DownloadLink' => '#link-to-download-cert001', 'ViewLink' => '#link-to-view-cert001'],
    ['CertificateID' => 'CERT002', 'CourseName' => 'Thiết Kế Giao Diện UI/UX Nâng Cao', 'CompletionDate' => '2025-04-10', 'CertificateImage' => 'https://via.placeholder.com/300x200/198754/fff?text=Certificate+UI/UX', 'DownloadLink' => '#link-to-download-cert002', 'ViewLink' => '#link-to-view-cert002'],
];

$user = (object) [ // Dữ liệu user mẫu
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
    <title>Chứng chỉ của tôi - Ecourse</title>
    <link href="public/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="public/css/certificates.css" rel="stylesheet">
</head>

<body>

    <?php include('template/user_sidebar.php'); // Include template sidebar người dùng ?>

    <div class="main-content">
        <div class="topbar-sm d-lg-none d-flex justify-content-between align-items-center mb-3">
            <button class="btn btn-dark" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
                <i class="bi bi-list"></i>
            </button>
            <h5 class="mb-0">Chứng chỉ của tôi</h5>
            <div></div>
        </div>

        <div class="container-fluid">
            <h3 class="mb-4 pt-2 pt-lg-0">Chứng chỉ của tôi</h3>

            <?php if (!empty($certificates)): ?>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    <?php foreach ($certificates as $cert): ?>
                    <div class="col">
                        <div class="card h-100 certificate-card shadow-sm">
                            <img src="<?= htmlspecialchars($cert['CertificateImage']) ?>" class="card-img-top" alt="Ảnh chứng chỉ <?= htmlspecialchars($cert['CourseName']) ?>">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title mb-2"><?= htmlspecialchars($cert['CourseName']) ?></h5>
                                <p class="card-text text-muted mb-3">
                                    <small>Hoàn thành ngày: <?= date('d/m/Y', strtotime($cert['CompletionDate'])) ?></small>
                                </p>
                                <div class="mt-auto d-grid gap-2 d-sm-flex">
                                     <a href="<?= htmlspecialchars($cert['ViewLink']) ?>" class="btn btn-sm btn-outline-primary flex-sm-fill" target="_blank" title="Xem chi tiết chứng chỉ">
                                        <i class="bi bi-eye-fill me-1"></i> Xem
                                    </a>
                                    <a href="<?= htmlspecialchars($cert['DownloadLink']) ?>" class="btn btn-sm btn-primary flex-sm-fill" download title="Tải xuống chứng chỉ">
                                        <i class="bi bi-download me-1"></i> Tải xuống
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-light text-center" role="alert">
                    <i class="bi bi-award fs-3 d-block mb-2"></i>
                    Bạn chưa có chứng chỉ nào. Hãy hoàn thành các khóa học để nhận chứng chỉ!
                     <br>
                    <a href="/my-courses.php" class="btn btn-info mt-3">Khóa học của tôi</a>
                </div>
            <?php endif; ?>

             <?php if (!empty($certificates) && count($certificates) > 6 ): ?>
            <nav aria-label="Certificates pagination" class="mt-4 d-flex justify-content-center">
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