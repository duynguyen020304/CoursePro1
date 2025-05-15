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
?>

<?php include('template/head.php'); ?>
<link href="public/css/cart.css" rel="stylesheet">
<?php include('template/header.php'); ?>

<!-- Giỏ hàng -->
<section class="cart-section py-5">
    <div class="container">
        <h2 class="text-center mb-4">Các Khóa Học Trong Giỏ Hàng</h2>
        <div class="row">
            <div class="col-md-8">
                <div class="cart-items">
                    <!-- Khóa học 1 -->
                    <div class="cart-item">
                        <div class="row">
                            <div class="col-md-3">
                                <img src="media/course1.jpg" alt="Khóa học 1" class="img-fluid">
                            </div>
                            <div class="col-md-6">
                                <h5 class="cart-item-title">Lập trình Web</h5>
                                <p>Giảng viên: John Doe</p>
                            </div>
                            <div class="col-md-3">
                                <p class="cart-item-price">799.000 VNĐ</p>
                                <input type="number" class="form-control" value="1" min="1">
                                <button class="btn btn-danger btn-sm mt-2">Xóa</button>
                            </div>
                        </div>
                    </div>
                    <!-- Khóa học 2 -->
                    <div class="cart-item">
                        <div class="row">
                            <div class="col-md-3">
                                <img src="media/course2.jpg" alt="Khóa học 2" class="img-fluid">
                            </div>
                            <div class="col-md-6">
                                <h5 class="cart-item-title">Thiết kế Đồ họa</h5>
                                <p>Giảng viên: Jane Smith</p>
                            </div>
                            <div class="col-md-3">
                                <p class="cart-item-price">650.000 VNĐ</p>
                                <input type="number" class="form-control" value="1" min="1">
                                <button class="btn btn-danger btn-sm mt-2">Xóa</button>
                            </div>
                        </div>
                    </div>
                    <!-- Khóa học 3 -->
                    <div class="cart-item">
                        <div class="row">
                            <div class="col-md-3">
                                <img src="media/course3.jpg" alt="Khóa học 3" class="img-fluid">
                            </div>
                            <div class="col-md-6">
                                <h5 class="cart-item-title">Marketing Online</h5>
                                <p>Giảng viên: Mark Lee</p>
                            </div>
                            <div class="col-md-3">
                                <p class="cart-item-price">1.200.000 VNĐ</p>
                                <input type="number" class="form-control" value="1" min="1">
                                <button class="btn btn-danger btn-sm mt-2">Xóa</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="cart-summary">
                    <h4>Tổng Cộng</h4>
                    <ul class="list-unstyled">
                        <li><strong>Tổng Tiền:</strong> <span id="total-price">2.649.000 VNĐ</span></li>
                    </ul>
                    <a href="checkout.php" class="btn btn-success btn-lg btn-block">Tiến Hành Thanh Toán</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Phần Gợi Ý Khóa Học -->
<section class="recommended-courses py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-4">Khóa Học Gợi Ý Cho Bạn</h2>
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <img src="media/recommended1.jpg" class="card-img-top" alt="Khóa học Gợi Ý 1">
                    <div class="card-body">
                        <h5 class="card-title">Lập Trình Python</h5>
                        <p class="card-text">Khóa học Python cho người mới bắt đầu.</p>
                        <a href="#" class="btn btn-primary">Xem Chi Tiết</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <img src="media/recommended2.jpg" class="card-img-top" alt="Khóa học Gợi Ý 2">
                    <div class="card-body">
                        <h5 class="card-title">Thiết Kế Web</h5>
                        <p class="card-text">Khóa học thiết kế giao diện web với HTML, CSS, JavaScript.</p>
                        <a href="#" class="btn btn-primary">Xem Chi Tiết</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <img src="media/recommended3.jpg" class="card-img-top" alt="Khóa học Gợi Ý 3">
                    <div class="card-body">
                        <h5 class="card-title">Digital Marketing</h5>
                        <p class="card-text">Khóa học marketing trực tuyến cho doanh nghiệp.</p>
                        <a href="#" class="btn btn-primary">Xem Chi Tiết</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include('template/footer.php'); ?>