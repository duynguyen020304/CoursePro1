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

if (isset($_SESSION['user']) && isset($_SESSION['user']['userID'])) {
    $current_userID = $_SESSION['user']['userID'];

    echo "ID của người dùng đang đăng nhập là: " . htmlspecialchars($current_userID);
}
?>
<?php include('template/head.php'); ?>

<link href="public/css/bootstrap.min.css" rel="stylesheet">
<link href="public/css/home.css" rel="stylesheet">

<link href="public/css/swiper-bundle.min.css" rel="stylesheet">


<?php include('template/header.php'); ?>

<header class="hero-section" style="background-image: url('media/hero-bg.jpg'); background-size: cover; background-position: center; padding: 80px 0; color: #fff;">
    <div class="container text-center">
        <h1>Khám phá các khóa học trực tuyến</h1>
        <p>Học từ các chuyên gia và nâng cao kỹ năng nghề nghiệp của bạn ngay hôm nay!</p>
        <a href="#" class="btn btn-primary btn-lg">Khám Phá Ngay</a>
    </div>
</header>

<section class="about-us py-5">
    <div class="container">
        <h2 class="text-center mb-4">Về Chúng Tôi</h2>
        <p class="text-center">Chúng tôi cung cấp các khóa học trực tuyến chất lượng cao giúp bạn nâng cao kỹ năng và phát triển sự nghiệp. Hãy tham gia cùng chúng tôi để học hỏi từ các chuyên gia trong nhiều lĩnh vực khác nhau!</p>
    </div>
</section>

<section class="featured-courses py-5">
    <div class="container">
        <h2 class="text-center mb-4">Khóa học Nổi Bật</h2>
        <div class="swiper featured-courses-slider">
            <div class="swiper-wrapper">
                <?php
                // Dữ liệu khóa học mẫu (giữ nguyên hoặc lấy từ DB)
                $featured_courses = [
                    ['title' => 'Lập trình Web', 'instructor' => 'John Doe', 'image' => 'media/course1.jpg', 'id' => 1],
                    ['title' => 'Thiết kế Đồ họa', 'instructor' => 'Jane Smith', 'image' => 'media/course2.jpg', 'id' => 2],
                    ['title' => 'Marketing Online', 'instructor' => 'Mark Lee', 'image' => 'media/course3.jpg', 'id' => 3],
                    ['title' => 'Khoa học Dữ liệu', 'instructor' => 'Alice Brown', 'image' => 'media/course4.jpg', 'id' => 4],
                    ['title' => 'Phân tích Kinh doanh', 'instructor' => 'Linda Green', 'image' => 'media/course5.jpg', 'id' => 5],
                    ['title' => 'Quản lý Dự án', 'instructor' => 'Michael Johnson', 'image' => 'media/course6.jpg', 'id' => 6],
                    ['title' => 'Lập trình Mobile Swift', 'instructor' => 'Lisa Wong', 'image' => 'media/course1.jpg', 'id' => 7], // Thêm dữ liệu mẫu
                    ['title' => 'An Toàn Thông Tin', 'instructor' => 'David Kim', 'image' => 'media/course2.jpg', 'id' => 8], // Thêm dữ liệu mẫu
                ];

                foreach ($featured_courses as $course) {
                ?>
                    <div class="swiper-slide">
                        <div class="card">
                            <img src="<?php echo $course['image']; ?>" class="card-img-top" alt="<?php echo $course['title']; ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $course['title']; ?></h5>
                                <p class="card-text">Giảng viên: <?php echo $course['instructor']; ?></p>
                                <a href="course-detail.php?id=<?php echo $course['id']; ?>" class="btn btn-primary">Xem Chi Tiết</a>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
    </div>
</section>

<section class="course-categories py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-4">Khám Phá Các Khóa Học Theo Chủ Đề</h2>
        <div class="row">
            <div class="col-md-4">
                <div class="card text-center">
                    <img src="media/category1.jpg" class="card-img-top" alt="Category 1">
                    <div class="card-body">
                        <h5 class="card-title">Công nghệ</h5>
                        <p class="card-text">Khóa học về lập trình, web, và các công nghệ mới.</p>
                        <a href="category.php?cat=technology" class="btn btn-primary">Xem Thêm</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <img src="media/category2.jpg" class="card-img-top" alt="Category 2">
                    <div class="card-body">
                        <h5 class="card-title">Kinh doanh</h5>
                        <p class="card-text">Các khóa học về marketing, quản lý và phát triển doanh nghiệp.</p>
                        <a href="category.php?cat=business" class="btn btn-primary">Xem Thêm</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <img src="media/category3.jpg" class="card-img-top" alt="Category 3">
                    <div class="card-body">
                        <h5 class="card-title">Thiết kế</h5>
                        <p class="card-text">Khóa học về thiết kế đồ họa, UX/UI, và sáng tạo nghệ thuật.</p>
                        <a href="category.php?cat=design" class="btn btn-primary">Xem Thêm</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="instructors py-5">
    <div class="container">
        <h2 class="text-center mb-4">Giới Thiệu Về Các Giảng Viên</h2>
        <div class="swiper instructors-slider">
            <div class="swiper-wrapper">
                <?php
                // Dữ liệu giảng viên mẫu (giữ nguyên hoặc lấy từ DB)
                $instructors_data = [
                    ['name' => 'Giảng viên: John Doe', 'description' => 'Chuyên gia về lập trình web và phát triển phần mềm. John đã làm việc với nhiều công ty lớn và giúp họ phát triển các ứng dụng phức tạp.', 'image' => 'media/instructor1.jpg'],
                    ['name' => 'Giảng viên: Jane Smith', 'description' => 'Chuyên gia trong lĩnh vực thiết kế đồ họa và UX/UI. Jane có hơn 10 năm kinh nghiệm trong việc tạo ra các sản phẩm thiết kế nổi bật.', 'image' => 'media/instructor2.jpg'],
                    ['name' => 'Giảng viên: Mark Lee', 'description' => 'Chuyên gia trong lĩnh vực marketing online và quảng cáo kỹ thuật số. Mark giúp các công ty tối ưu hóa chiến lược marketing của họ.', 'image' => 'media/instructor3.jpg'],
                    ['name' => 'Giảng viên: Lisa Wong', 'description' => 'Chuyên gia về dữ liệu lớn và phân tích kinh doanh.', 'image' => 'media/instructor1.jpg'], // Thêm dữ liệu mẫu
                ];
                foreach ($instructors_data as $instructor) {
                ?>
                    <div class="swiper-slide">
                        <div class="card">
                            <img src="<?php echo $instructor['image']; ?>" class="card-img-top" alt="<?php echo $instructor['name']; ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $instructor['name']; ?></h5>
                                <p class="card-text"><?php echo $instructor['description']; ?></p>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
    </div>
</section>

<section class="testimonials py-5">
    <div class="container">
        <h2 class="text-center mb-4">Đánh Giá từ Học Viên</h2>
        <div class="swiper testimonials-slider">
            <div class="swiper-wrapper">
                <?php
                // Dữ liệu đánh giá mẫu (giữ nguyên hoặc lấy từ DB)
                $testimonials_data = [
                    ['text' => '"Khóa học về lập trình web thực sự giúp tôi cải thiện kỹ năng lập trình và có cơ hội xin việc." - Học viên A'],
                    ['text' => '"Khóa học marketing online rất hữu ích, tôi đã áp dụng vào công việc và thấy rõ hiệu quả." - Học viên B'],
                    ['text' => '"Giảng viên rất nhiệt tình, dễ hiểu, giúp tôi nắm bắt được các kỹ năng thiết kế đồ họa." - Học viên C'],
                    ['text' => '"Nội dung khóa học khoa học dữ liệu rất chuyên sâu và dễ tiếp cận." - Học viên D'], // Thêm dữ liệu mẫu
                    ['text' => '"Tôi đã tìm được công việc tốt hơn nhờ các khóa học trên nền tảng này." - Học viên E'], // Thêm dữ liệu mẫu
                ];
                foreach ($testimonials_data as $testimonial) {
                ?>
                    <div class="swiper-slide">
                        <div class="card">
                            <div class="card-body">
                                <p class="card-text"><?php echo $testimonial['text']; ?></p>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
    </div>
</section>

<script src="public/js/swiper-bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Khởi tạo Slider cho Khóa học Nổi bật (.featured-courses-slider)
        var featuredCoursesSwiper = new Swiper('.featured-courses-slider', {
            slidesPerView: 3, // Số slide hiển thị trên desktop (mặc định)
            spaceBetween: 30, // Khoảng cách giữa các slide
            loop: false,
            navigation: { // Cấu hình mũi tên điều hướng
                nextEl: '.featured-courses-slider .swiper-button-next',
                prevEl: '.featured-courses-slider .swiper-button-prev',
            },
            // Responsive breakpoints
            breakpoints: {
                // Khi chiều rộng màn hình >= 320px
                320: {
                    slidesPerView: 1,
                    spaceBetween: 10,
                },
                // Khi chiều rộng màn hình >= 576px (small devices)
                576: {
                    slidesPerView: 2,
                    spaceBetween: 20,
                },
                // Khi chiều rộng màn hình >= 768px (medium devices)
                768: {
                    slidesPerView: 3,
                    spaceBetween: 30,
                },
                // Khi chiều rộng màn hình >= 992px (large devices)
                992: {
                    slidesPerView: 3,
                    spaceBetween: 30,
                },
                // Khi chiều rộng màn hình >= 1200px (extra large devices)
                1200: {
                    slidesPerView: 4,
                    spaceBetween: 30,
                } // 4 cột trên màn hình rất lớn
            }
        });

        // Khởi tạo Slider cho Giới thiệu Giảng viên (.instructors-slider)
        var instructorsSwiper = new Swiper('.instructors-slider', {
            slidesPerView: 3, // Số slide hiển thị trên desktop (mặc định)
            spaceBetween: 30, // Khoảng cách giữa các slide
            loop: false,
            navigation: {
                nextEl: '.instructors-slider .swiper-button-next',
                prevEl: '.instructors-slider .swiper-button-prev',
            },
            breakpoints: {
                320: {
                    slidesPerView: 1,
                    spaceBetween: 10,
                },
                576: {
                    slidesPerView: 2,
                    spaceBetween: 20,
                },
                768: {
                    slidesPerView: 3,
                    spaceBetween: 30,
                },
                992: {
                    slidesPerView: 3,
                    spaceBetween: 30,
                },
                1200: {
                    slidesPerView: 4,
                    spaceBetween: 30,
                }
            }
        });

        // Khởi tạo Slider cho Đánh giá từ Học viên (.testimonials-slider)
        var testimonialsSwiper = new Swiper('.testimonials-slider', {
            slidesPerView: 3, // Số slide hiển thị trên desktop (mặc định)
            spaceBetween: 30, // Khoảng cách giữa các slide
            loop: false,
            navigation: {
                nextEl: '.testimonials-slider .swiper-button-next',
                prevEl: '.testimonials-slider .swiper-button-prev',
            },
            breakpoints: {
                320: {
                    slidesPerView: 1,
                    spaceBetween: 10,
                },
                576: {
                    slidesPerView: 2,
                    spaceBetween: 20,
                },
                768: {
                    slidesPerView: 3,
                    spaceBetween: 30,
                },
                992: {
                    slidesPerView: 3,
                    spaceBetween: 30,
                },
                1200: {
                    slidesPerView: 4,
                    spaceBetween: 30,
                }
            }
        });
    });
</script>

<?php include('template/footer.php'); ?>