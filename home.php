<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http');
$host = $_SERVER['HTTP_HOST'];
$script_path = $_SERVER['SCRIPT_NAME'];

$path_parts = explode('/', dirname($script_path));
if (count($path_parts) > 1 && $path_parts[1] !== '') {
    $app_root_path_relative = implode('/', array_slice($path_parts, 0, count($path_parts)));
    if ($app_root_path_relative === '/' || $app_root_path_relative === '\\') $app_root_path_relative = '';
} else {
    $app_root_path_relative = '';
}
$app_root_path_relative = rtrim($app_root_path_relative, '/');

define('APP_BASE_URL', $protocol . '://' . $host . $app_root_path_relative);
define('CONTROLLER_FILE_PATH', '/controller/c_file_loader.php');

function callApi(string $endpointUrl, string $method = 'GET', array $payload = []): array
{
    $url = $endpointUrl;
    $methodUpper = strtoupper($method);

    if ($methodUpper === 'GET' && !empty($payload) && strpos($url, '?') === false) {
        $url .= '?' . http_build_query($payload);
    }

    $headers = "Content-Type: application/json; charset=utf-8\r\n" .
        "Accept: application/json\r\n";

    $token = $_SESSION['user']['token'] ?? null;
    if ($token) {
        $headers .= "Authorization: Bearer " . $token . "\r\n";
    }

    $options = [
        'http' => [
            'method'        => $methodUpper,
            'header'        => $headers,
            'ignore_errors' => true,
            'timeout'       => 15
        ]
    ];

    if ($methodUpper !== 'GET' && $methodUpper !== 'HEAD') {
        if (!empty($payload)) {
            $options['http']['content'] = json_encode($payload);
        } else if (in_array($methodUpper, ['POST', 'PUT'])) {
            $options['http']['content'] = '{}';
        }
    }

    $context  = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);
    $responseHeaders = $http_response_header ?? [];

    $status_code = null;
    if (!empty($responseHeaders)) {
        foreach ($responseHeaders as $header) {
            if (preg_match('{HTTP/\S*\s(\d{3})}', $header, $match)) {
                $status_code = intval($match[1]);
                break;
            }
        }
    }

    if ($response === false) {
        return [
            'success' => false,
            'message' => 'Failed to connect to the API endpoint: ' . $url,
            'data' => null,
            'http_status_code' => $status_code ?? 0
        ];
    }

    $result = json_decode($response, true);
    $json_error = json_last_error();

    if ($result === null && $json_error !== JSON_ERROR_NONE) {
        return [
            'success' => false,
            'message' => 'Invalid API response or failed to decode JSON. Error: ' . json_last_error_msg(),
            'data' => null,
            'raw_response' => substr($response, 0, 500),
            'http_status_code' => $status_code
        ];
    }

    if (!is_array($result)) {
        if ($result === null && ($status_code >= 200 && $status_code < 300)) {
            return [
                'success' => true,
                'message' => 'Operation successful with empty response.',
                'data' => null,
                'http_status_code' => $status_code
            ];
        }
        return [
            'success' => false,
            'message' => 'API response was not in the expected array format.',
            'data' => $result,
            'raw_response' => substr($response, 0, 500),
            'http_status_code' => $status_code
        ];
    }

    if (!isset($result['http_status_code'])) {
        $result['http_status_code'] = $status_code;
    }
    if (!isset($result['success'])) {
        $result['success'] = ($status_code >= 200 && $status_code < 300);
    }
    if (!isset($result['data'])) {
        $result['data'] = null;
    }
    if (!isset($result['message'])) {
        $result['message'] = $result['success'] ? 'Request successful.' : 'Request failed.';
    }
    return $result;
}

$coursesApiUrl = APP_BASE_URL . CONTROLLER_FILE_PATH . '?act=home_page';
$coursesApiResponse = callApi($coursesApiUrl, 'GET');
$featured_courses = [];
$coursesErrorMessage = '';

if (isset($coursesApiResponse['success']) && $coursesApiResponse['success'] === true && isset($coursesApiResponse['data']) && is_array($coursesApiResponse['data'])) {
    $featured_courses = $coursesApiResponse['data'];
} elseif (isset($coursesApiResponse['message'])) {
    $coursesErrorMessage = "Lỗi khi tải khóa học: " . htmlspecialchars($coursesApiResponse['message']);
} else {
    $coursesErrorMessage = "Đã xảy ra lỗi không xác định khi tải dữ liệu khóa học.";
}

$instructorsApiUrl = APP_BASE_URL . CONTROLLER_FILE_PATH . '?act=get_instructors_home_page';
$instructorsApiResponse = callApi($instructorsApiUrl, 'GET');
$all_instructors_data = [];
$instructorsErrorMessage = '';

if (isset($instructorsApiResponse['success']) && $instructorsApiResponse['success'] === true && isset($instructorsApiResponse['data']) && is_array($instructorsApiResponse['data'])) {
    $all_instructors_data = $instructorsApiResponse['data'];
} elseif (isset($instructorsApiResponse['message'])) {
    $instructorsErrorMessage = "Lỗi khi tải giảng viên: " . htmlspecialchars($instructorsApiResponse['message']);
} else {
    $instructorsErrorMessage = "Đã xảy ra lỗi không xác định khi tải dữ liệu giảng viên.";
}

$instructors_to_display = array_slice($all_instructors_data, 0, 3);

$defaultCourseImage = 'https://placehold.co/600x400/EFEFEF/AAAAAA?text=No+Image';
$defaultInstructorImage = 'https://placehold.co/300x300/EFEFEF/AAAAAA?text=No+Image';

?>
<?php include('template/head.php'); ?>

<link href="public/css/bootstrap.min.css" rel="stylesheet">
<link href="public/css/home.css" rel="stylesheet">
<link href="public/css/swiper-bundle.min.css" rel="stylesheet">
<style>
    .instructor-card-img {
        width: 100%;
        height: 200px;
        object-fit: cover;
        border-radius: 50%;
        margin-bottom: 15px;
    }

    .card-text-instructor-bio {
        font-size: 0.9rem;
        color: #555;
        min-height: 60px;
    }
</style>

<?php include('template/header.php'); ?>

<header class="hero-section" style="background-image: url('public/img/worker-man-young-mixed-team.webp'); background-size: cover; background-position: center; padding: 80px 0; color: #fff;">
    <div class="container text-center">
        <h1>Khám phá các khóa học trực tuyến</h1>
        <p>Học từ các chuyên gia và nâng cao kỹ năng nghề nghiệp của bạn ngay hôm nay!</p>
        <a href="courses.php" class="btn btn-primary btn-lg">Khám Phá Ngay</a>
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
        <?php if (!empty($coursesErrorMessage)) : ?>
            <div class="alert alert-danger"><?php echo $coursesErrorMessage; ?></div>
        <?php endif; ?>

        <?php if (!empty($featured_courses)) : ?>
            <div class="swiper featured-courses-slider">
                <div class="swiper-wrapper">
                    <?php foreach ($featured_courses as $course) : ?>
                        <?php
                        $courseImageUrl = $defaultCourseImage;
                        if (!empty($course['images']) && isset($course['images'][0]['imagePath'])) {
                            $imageFileName = $course['images'][0]['imagePath'];
                            $courseImageUrl = APP_BASE_URL . CONTROLLER_FILE_PATH . '?act=serve_image&course_id=' . urlencode($course['courseID']) . '&image=' . urlencode($imageFileName);
                        }
                        $instructorNames = 'N/A';
                        if (!empty($course['instructors'])) {
                            $names = [];
                            foreach ($course['instructors'] as $instructor) {
                                $names[] = htmlspecialchars($instructor['firstName'] . ' ' . $instructor['lastName']);
                            }
                            $instructorNames = implode(', ', $names);
                        }
                        ?>
                        <div class="swiper-slide">
                            <div class="card h-100">
                                <img src="<?php echo htmlspecialchars($courseImageUrl); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($course['title']); ?>" style="height: 200px; object-fit: cover;" onerror="this.onerror=null;this.src='<?php echo $defaultCourseImage; ?>';">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h5>
                                    <p class="card-info">Giảng viên: <?php echo $instructorNames; ?></p>
                                    <p class="card-info">Giá: <?php echo number_format($course['price'] ?? 0, 0, ',', '.'); ?> VND</p>
                                    <p class="card-text flex-grow-1"><?php echo htmlspecialchars(mb_substr($course['description'] ?? '', 0, 100)); ?>...</p>
                                    <a href="course-detail.php?id=<?php echo htmlspecialchars($course['courseID']); ?>" class="btn btn-primary mt-auto">Xem Chi Tiết</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
        <?php elseif (empty($coursesErrorMessage)) : ?>
            <p class="text-center">Hiện tại không có khóa học nổi bật nào.</p>
        <?php endif; ?>
    </div>
</section>

<section class="course-categories py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-4">Khám Phá Các Khóa Học Theo Chủ Đề</h2>
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="card text-center h-100">
                    <img src="public/img/tech_category.jpg" class="card-img-top" alt="Công nghệ" style="height: 200px; object-fit: cover;" onerror="this.onerror=null;this.src='<?php echo $defaultCourseImage; ?>';">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Công nghệ</h5>
                        <p class="card-text flex-grow-1">Khóa học về lập trình, web, và các công nghệ mới.</p>
                        <a href="category.php?cat=technology" class="btn btn-primary mt-auto">Xem Thêm</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card text-center h-100">
                    <img src="public/img/bussiness_category.webp" class="card-img-top" alt="Kinh doanh" style="height: 200px; object-fit: cover;" onerror="this.onerror=null;this.src='<?php echo $defaultCourseImage; ?>';">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Kinh doanh</h5>
                        <p class="card-text flex-grow-1">Các khóa học về marketing, quản lý và phát triển doanh nghiệp.</p>
                        <a href="category.php?cat=business" class="btn btn-primary mt-auto">Xem Thêm</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card text-center h-100">
                    <img src="public/img/art_category.webp" class="card-img-top" alt="Thiết kế" style="height: 200px; object-fit: cover;" onerror="this.onerror=null;this.src='<?php echo $defaultCourseImage; ?>';">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Thiết kế</h5>
                        <p class="card-text flex-grow-1">Khóa học về thiết kế đồ họa, UX/UI, và sáng tạo nghệ thuật.</p>
                        <a href="category.php?cat=design" class="btn btn-primary mt-auto">Xem Thêm</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="instructors py-5">
    <div class="container">
        <h2 class="text-center mb-4">Giới Thiệu Về Các Giảng Viên Nổi Bật</h2>
        <?php if (!empty($instructorsErrorMessage)) : ?>
            <div class="alert alert-danger"><?php echo $instructorsErrorMessage; ?></div>
        <?php endif; ?>

        <?php if (!empty($instructors_to_display)) : ?>
            <div class="swiper instructors-slider">
                <div class="swiper-wrapper">
                    <?php foreach ($instructors_to_display as $instructor) : ?>
                        <?php
                        $instructorImageUrl = $defaultInstructorImage;
                        if (!empty($instructor['profileImage'])) {
                            $instructorImageUrl = APP_BASE_URL . CONTROLLER_FILE_PATH . '?act=serve_user_image&user_id=' . urlencode($instructor['userID']) . '&image=' . urlencode($instructor['profileImage']);
                        }
                        $instructorFullName = htmlspecialchars(($instructor['firstName'] ?? '') . ' ' . ($instructor['lastName'] ?? ''));
                        $biography = (!empty($instructor['biography']) && strtoupper($instructor['biography']) !== 'NULL') ? htmlspecialchars($instructor['biography']) : 'Chưa có thông tin mô tả.';
                        ?>
                        <div class="swiper-slide">
                            <div class="card text-center h-100">
                                <img src="<?php echo htmlspecialchars($instructorImageUrl); ?>" class="card-img-top instructor-card-img mx-auto mt-3" alt="<?php echo $instructorFullName; ?>" onerror="this.onerror=null;this.src='<?php echo $defaultInstructorImage; ?>';">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?php echo $instructorFullName; ?></h5>
                                    <p class="card-text-instructor-bio flex-grow-1"><?php echo $biography; ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
        <?php elseif (empty($instructorsErrorMessage)) : ?>
            <p class="text-center">Hiện tại không có thông tin giảng viên nổi bật nào.</p>
        <?php endif; ?>
    </div>
</section>

<section class="testimonials py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-4">Đánh Giá từ Học Viên</h2>
        <div class="swiper testimonials-slider">
            <div class="swiper-wrapper">
                <?php
                $testimonials_data = [
                    ['text' => '"Khóa học về lập trình web thực sự giúp tôi cải thiện kỹ năng lập trình..." - Học viên A', 'author_image' => 'public/img/avatar1.jpg'],
                    ['text' => '"Khóa học marketing online rất hữu ích..." - Học viên B', 'author_image' => 'public/img/avatar2.jpg'],
                    ['text' => '"Giảng viên rất nhiệt tình, dễ hiểu..." - Học viên C', 'author_image' => 'public/img/avatar3.jpg'],
                ];
                foreach ($testimonials_data as $testimonial) {
                ?>
                    <div class="swiper-slide">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <?php if (!empty($testimonial['author_image'])): ?>
                                    <img src="<?php echo htmlspecialchars($testimonial['author_image']); ?>" alt="Học viên" class="rounded-circle mb-3" style="width: 80px; height: 80px; object-fit: cover;" onerror="this.style.display='none'">
                                <?php endif; ?>
                                <p class="card-text fst-italic">"<?php echo htmlspecialchars(trim($testimonial['text'], '"')); ?>"</p>
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
        function initSwiper(selector, options) {
            const swiperElement = document.querySelector(selector);
            if (swiperElement && swiperElement.querySelector('.swiper-wrapper').children.length > 0) {
                return new Swiper(selector, options);
            } else if (swiperElement) {
                const navNext = swiperElement.querySelector('.swiper-button-next');
                const navPrev = swiperElement.querySelector('.swiper-button-prev');
                if (navNext) navNext.style.display = 'none';
                if (navPrev) navPrev.style.display = 'none';
            }
            return null;
        }

        initSwiper('.featured-courses-slider', {
            slidesPerView: 4,
            spaceBetween: 30,
            loop: false,
            navigation: {
                nextEl: '.featured-courses-slider .swiper-button-next',
                prevEl: '.featured-courses-slider .swiper-button-prev'
            },
            breakpoints: {
                320: {
                    slidesPerView: 1,
                    spaceBetween: 10
                },
                576: {
                    slidesPerView: 2,
                    spaceBetween: 20
                },
                768: {
                    slidesPerView: 3,
                    spaceBetween: 30
                },
                1200: {
                    slidesPerView: 4,
                    spaceBetween: 30
                }
            }
        });

        initSwiper('.instructors-slider', {
            slidesPerView: 3,
            spaceBetween: 30,
            loop: false,
            navigation: {
                nextEl: '.instructors-slider .swiper-button-next',
                prevEl: '.instructors-slider .swiper-button-prev'
            },
            breakpoints: {
                320: {
                    slidesPerView: 1,
                    spaceBetween: 10
                },
                576: {
                    slidesPerView: 2,
                    spaceBetween: 20
                },
                992: {
                    slidesPerView: 3,
                    spaceBetween: 30
                }
            }
        });

        initSwiper('.testimonials-slider', {
            slidesPerView: 3,
            spaceBetween: 30,
            loop: false,
            navigation: {
                nextEl: '.testimonials-slider .swiper-button-next',
                prevEl: '.testimonials-slider .swiper-button-prev'
            },
            breakpoints: {
                320: {
                    slidesPerView: 1,
                    spaceBetween: 10
                },
                576: {
                    slidesPerView: 2,
                    spaceBetween: 20
                },
                992: {
                    slidesPerView: 3,
                    spaceBetween: 30
                }
            }
        });
    });
</script>

<?php include('template/footer.php'); ?>