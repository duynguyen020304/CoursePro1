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
<link href="public/css/course.css" rel="stylesheet">
<?php include('template/header.php'); ?>

<!-- Banner -->
<header class="hero-section" style="background-image: url('media/course-bg.jpg'); background-size: cover; padding: 100px 0; color: #fff;">
    <div class="container text-center">
        <h1>Khám Phá Các Khóa Học</h1>
        <p>Học từ các chuyên gia và nâng cao kỹ năng nghề nghiệp của bạn ngay hôm nay!</p>
    </div>
</header>

<!-- Tìm kiếm và lọc khóa học -->
<section class="search-section py-5">
    <div class="container">
        <h2 class="text-center mb-4">Tìm Kiếm và Lọc Khóa Học</h2>
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="category">Danh Mục Khóa Học</label>
                    <select id="category" class="form-control">
                        <option>Chọn Danh Mục</option>
                        <option>Công nghệ thông tin</option>
                        <option>Thiết kế</option>
                        <option>Marketing</option>
                        <option>Kinh doanh</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="price">Khoảng Giá</label>
                    <select id="price" class="form-control">
                        <option>Chọn Khoảng Giá</option>
                        <option>Dưới 500.000 VNĐ</option>
                        <option>500.000 VNĐ - 1.000.000 VNĐ</option>
                        <option>Trên 1.000.000 VNĐ</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="search">Tìm Kiếm</label>
                    <input type="text" id="search" class="form-control" placeholder="Tìm khóa học...">
                </div>
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary btn-lg mt-4">Tìm Kiếm</button>
            </div>
        </div>
    </div>
</section>

<!-- Danh sách khóa học -->
<section class="course-list py-5">
    <div class="container">
        <h2 class="text-center mb-4">Danh Sách Các Khóa Học</h2>
        <div class="row">
            <div class="col-md-4">
                <div class="card course-card">
                    <img src="media/course1.jpg" class="card-img-top" alt="Khóa học 1">
                    <div class="card-body">
                        <h5 class="card-title">Lập trình Web</h5>
                        <p class="card-text">Giảng viên: John Doe</p>
                        <p class="card-price">Giá: 799.000 VNĐ</p>
                        <a href="course-detail.php" class="btn btn-primary">Xem Chi Tiết</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card course-card">
                    <img src="media/course2.jpg" class="card-img-top" alt="Khóa học 2">
                    <div class="card-body">
                        <h5 class="card-title">Thiết kế Đồ họa</h5>
                        <p class="card-text">Giảng viên: Jane Smith</p>
                        <p class="card-price">Giá: 650.000 VNĐ</p>
                        <a href="course-detail.php" class="btn btn-primary">Xem Chi Tiết</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card course-card">
                    <img src="media/course3.jpg" class="card-img-top" alt="Khóa học 3">
                    <div class="card-body">
                        <h5 class="card-title">Marketing Online</h5>
                        <p class="card-text">Giảng viên: Mark Lee</p>
                        <p class="card-price">Giá: 1.200.000 VNĐ</p>
                        <a href="course-detail.php" class="btn btn-primary">Xem Chi Tiết</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include('template/footer.php'); ?>
