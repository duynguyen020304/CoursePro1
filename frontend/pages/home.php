<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http');
$host = $_SERVER['HTTP_HOST'];
$script_path = $_SERVER['SCRIPT_NAME'];

$path_parts_original = explode('/', dirname($script_path));
if (count($path_parts_original) > 1 && $path_parts_original[1] !== '') {
    $app_root_path_relative_original = implode('/', array_slice($path_parts_original, 0, count($path_parts_original)));
    if ($app_root_path_relative_original === '/' || $app_root_path_relative_original === '\\') $app_root_path_relative_original = '';
} else {
    $app_root_path_relative_original = '';
}
$app_root_path_relative_original = rtrim($app_root_path_relative_original, '/');

define('APP_BASE_URL', $protocol . '://' . $host . $app_root_path_relative_original);
define('CONTROLLER_FILE_PATH',../../backend/Controller/Form/c_file_loader.php');

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
$featured_courses_data = [];
$coursesErrorMessage = '';

if (isset($coursesApiResponse['success']) && $coursesApiResponse['success'] === true && isset($coursesApiResponse['data']) && is_array($coursesApiResponse['data'])) {
    $featured_courses_data = $coursesApiResponse['data'];
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

$instructors_to_display_data = array_slice($all_instructors_data, 0, 3);

$defaultCourseImage = 'assets/img/no_image_600_400.svg';
$defaultInstructorImage = 'assets/img/no_image_300_300.svg';

include(__DIR__ . '/../templates/head.php');
?>

    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/font_awesome_all.min.css">
    <link href="../assets/css/aos.css" rel="stylesheet">
    <link href="../assets/css/swiper-bundle.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/home.css">
    <style>
        .big-img::before {
            content: "";
            display: flex;
            position: absolute;
            width: 100%;
            height: 100%;
            background: url("<?php echo APP_BASE_URL; ?>/frontend/assets/img/worker-man-young-mixed-team.webp");
            background-repeat: no-repeat;
            background-size: cover;
            z-index: -2;
        }
    </style>

<?php include(__DIR__ . '/../templates/header.php'); ?>

    <section class="big-img">
        <div class="big-img-content" data-aos="fade-up" data-aos-duration="1500">
            <h2>Khám phá các khóa học trực tuyến</h2>
            <p>Học từ các chuyên gia và nâng cao kỹ năng nghề nghiệp của bạn ngay hôm nay!</p>
            <a href="#featured-courses" class="btn btn-outline-warning" style="border-radius: 15px; width: 180px; padding: 10px 0;">Khám Phá Ngay</a>
        </div>
    </section>

    <section id="about" class="about section-padding">
        <div class="container">
            <div class="row">
                <div class="section-title">
                    <h2 data-title="Câu chuyện" style="color: var(--sub-color);" data-aos="fade-up" data-aos-duration="2000">Về Chúng Tôi</h2>
                </div>
            </div>
            <div class="row">
                <div class="about-item" data-aos="fade-right" data-aos-duration="1500">
                    <p>
                        Chúng tôi cung cấp các khóa học trực tuyến chất lượng cao giúp bạn nâng cao kỹ năng và phát triển sự nghiệp. Hãy tham gia cùng chúng tôi để học hỏi từ các chuyên gia trong nhiều lĩnh vực khác nhau! Với đội ngũ giảng viên giàu kinh nghiệm và chương trình học cập nhật, chúng tôi cam kết mang đến trải nghiệm học tập tốt nhất.
                    </p>
                    <a href="#featured-courses" type="button" class="btn btn-outline-warning" style="border-radius: 25px; width: 150px;">Xem Khóa Học</a>
                </div>
                <div class="about-item" data-aos="fade-left" data-aos-duration="1500">
                    <div class="about-item-img">
                        <img src="../assets/img/about_us.png" alt="Về chúng tôi">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="featured-courses" class="menu section-padding featured-courses-section">
        <div class="container">
            <div class="row">
                <div class="section-title" data-aos="fade-up" data-aos-duration="1500">
                    <h2 data-title="Đừng bỏ lỡ">Khóa học Nổi Bật</h2>
                </div>
            </div>
            <?php if (!empty($coursesErrorMessage)) : ?>
                <div class="alert alert-danger text-center"><?php echo $coursesErrorMessage; ?></div>
            <?php endif; ?>

            <?php if (!empty($featured_courses_data)) : ?>
                <div class="swiper featured-courses-slider">
                    <div class="swiper-wrapper">
                        <?php foreach ($featured_courses_data as $course) : ?>
                            <?php
                            $courseImageUrl = $defaultCourseImage;
                            if (!empty($course['images']) && isset($course['images'][0]['imagePath'])) {
                                $imageFileName = $course['images'][0]['imagePath'];
                                $courseImageUrl = APP_BASE_URL . CONTROLLER_FILE_PATH . '?act=serve_image&course_id=' . urlencode($course['courseID']) . '&image=' . urlencode($imageFileName);
                            } else if (!empty($course['image'])) {
                                $courseImageUrl = APP_BASE_URL . '/' . ltrim($course['image'], '/');
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
                                <div class="food-items course-card" data-aos="fade-up" data-aos-duration="1000">
                                    <div class="food-item">
                                        <img src="<?php echo htmlspecialchars($courseImageUrl); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" onerror="this.onerror=null;this.src='<?php echo $defaultCourseImage; ?>';">
                                        <a href="course-detail.php?course_id=<?php echo htmlspecialchars($course['courseID']); ?>"><?php echo htmlspecialchars($course['title']); ?></a>
                                    </div>
                                    <div class="course-instructor">
                                        <p><i class="fa fa-user"></i> <?php echo $instructorNames; ?></p>
                                    </div>
                                    <div class="food-price course-price-cta">
                                        <p><?php echo number_format($course['price'] ?? 0, 0, ',', '.'); ?> VNĐ</p>
                                        <a href="course-detail.php?course_id=<?php echo htmlspecialchars($course['courseID']); ?>" class="btn btn-sm btn-outline-warning">Xem Chi Tiết</a>
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

    <section id="course-categories" class="course-categories-section section-padding bg-light-custom">
        <div class="container">
            <div class="row">
                <div class="section-title" data-aos="fade-up" data-aos-duration="1500">
                    <h2 data-title="Lĩnh vực">Khám Phá Theo Chủ Đề</h2>
                </div>
            </div>
            <div class="row">
                <?php
                $categories = [
                    ['title' => 'Công nghệ Thông tin', 'description' => 'Khóa học về lập trình, web, an ninh mạng, và các công nghệ mới.', 'image' => 'assets/img/tech_category.jpg', 'link' => 'course-category.php?categoryID=2'],
                    ['title' => 'Kinh doanh & Marketing', 'description' => 'Các khóa học về marketing, quản lý, khởi nghiệp và phát triển doanh nghiệp.', 'image' => 'assets/img/bussiness_category.webp', 'link' => 'course-category.php?categoryID=33'],
                    ['title' => 'Thiết kế & Sáng tạo', 'description' => 'Khóa học về thiết kế đồ họa, UX/UI, nhiếp ảnh và sáng tạo nghệ thuật.', 'image' => 'assets/img/art_category.webp', 'link' => 'course-category.php?categoryID=49'],
                ];
                foreach ($categories as $category) {
                    ?>
                    <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-duration="1500">
                        <div class="category-card">
                            <img src="<?php echo $category['image']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($category['title']); ?>" onerror="this.onerror=null;this.src='<?php echo $defaultCourseImage; ?>';">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($category['title']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($category['description']); ?></p>
                                <a href="<?php echo $category['link']; ?>" class="btn btn-primary-custom">Xem Thêm</a>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </section>

    <section id="instructors" class="team section-padding">
        <div class="container">
            <div class="row">
                <div class="section-title" data-aos="fade-up" data-aos-duration="2000">
                    <h2 data-title="Chuyên gia">Giảng Viên Hàng Đầu</h2>
                </div>
            </div>
            <?php if (!empty($instructorsErrorMessage)) : ?>
                <div class="alert alert-danger text-center"><?php echo $instructorsErrorMessage; ?></div>
            <?php endif; ?>

            <?php if (!empty($instructors_to_display_data)) : ?>
                <div class="swiper instructors-slider">
                    <div class="swiper-wrapper">
                        <?php foreach ($instructors_to_display_data as $instructor) : ?>
                            <?php
                            $instructorImageUrl = $defaultInstructorImage;
                            if (!empty($instructor['profileImage'])) {
                                $instructorImageUrl = APP_BASE_URL . CONTROLLER_FILE_PATH . '?act=serve_user_image&user_id=' . urlencode($instructor['userID']) . '&image=' . urlencode($instructor['profileImage']);
                            } else if (!empty($instructor['image'])) {
                                $instructorImageUrl = APP_BASE_URL . '/' . ltrim($instructor['image'], '/');
                            }

                            $instructorFullName = htmlspecialchars(($instructor['firstName'] ?? '') . ' ' . ($instructor['lastName'] ?? 'N/A'));
                            $instructorTitle = (!empty($instructor['specialization']) ? htmlspecialchars($instructor['specialization']) : 'Chuyên gia');
                            if (empty($instructor['specialization']) && !empty($instructor['biography']) && strtoupper($instructor['biography']) !== 'NULL') {
                            }
                            ?>
                            <div class="swiper-slide">
                                <div class="team-items" data-aos="fade-up" data-aos-duration="2000">
                                    <img src="<?php echo htmlspecialchars($instructorImageUrl); ?>" alt="<?php echo $instructorFullName; ?>" onerror="this.onerror=null;this.src='<?php echo $defaultInstructorImage; ?>';">
                                    <div class="team-items-text">
                                        <h2><?php echo $instructorFullName; ?></h2>
                                        <span><?php echo $instructorTitle; ?></span>
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

    <section id="testimonials" class="comment section-padding">
        <div class="container">
            <div class="row">
                <div class="section-title">
                    <h2 data-title="Cảm nhận" data-aos="fade-up" data-aos-duration="1500">Học Viên Nói Gì Về Chúng Tôi</h2>
                </div>
            </div>
            <div class="swiper testimonials-slider">
                <div class="swiper-wrapper">
                    <?php
                    $testimonials_data = [
                        ['name' => 'Nguyễn Văn A', 'course' => 'Lập trình Web', 'image' => 'media/avatar1.png', 'text' => '"Khóa học về lập trình web thực sự giúp tôi cải thiện kỹ năng lập trình và có cơ hội xin việc tốt hơn. Giảng viên nhiệt tình, nội dung chi tiết."'],
                        ['name' => 'Trần Thị B', 'course' => 'Marketing Online', 'image' => 'media/avatar2.png', 'text' => '"Khóa học marketing online rất hữu ích, tôi đã áp dụng vào công việc và thấy rõ hiệu quả. Cảm ơn nền tảng đã cung cấp khóa học chất lượng!"'],
                        ['name' => 'Lê Văn C', 'course' => 'Thiết kế Đồ họa', 'image' => 'media/avatar3.png', 'text' => '"Giảng viên rất nhiệt tình, dễ hiểu, giúp tôi nắm bắt được các kỹ năng thiết kế đồ họa một cách nhanh chóng. Rất hài lòng! Sẽ giới thiệu người thân"'],
                    ];
                    foreach ($testimonials_data as $testimonial) {
                        ?>
                        <div class="swiper-slide">
                            <div class="comment-item" data-aos="flip-left" data-aos-duration="2000">
                                <div class="comment-item-content">
                                    <div class="comment-item-content-text">
                                        <h2><?php echo htmlspecialchars($testimonial['name']); ?></h2>
                                        <span>Học viên khóa <?php echo htmlspecialchars($testimonial['course']); ?></span>
                                    </div>
                                    <div class="comment-item-content-img">
                                        <img src="<?php echo $testimonial['image']; ?>" alt="Avatar <?php echo htmlspecialchars($testimonial['name']); ?>" onerror="this.style.display='none'">
                                    </div>
                                </div>
                                <p><?php echo htmlspecialchars($testimonial['text']); ?></p>
                                <div class="comment-item-start">
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star"></i>
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
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>AOS.init();</script>
    <script src="../assets/js/swiper-bundle.min.js"></script>
    <script>
        const menuBar = document.querySelector(".menu-bar");
        const menuItems = document.querySelector(".menu-items");

        if (menuBar && menuItems) {
            menuBar.addEventListener("click", function() {
                menuBar.classList.toggle("active");
                menuItems.classList.toggle("active");
            });
        }

        const toP = document.querySelector(".top");
        if (toP) {
            window.addEventListener("scroll", function() {
                const x = this.pageYOffset;
                if (x > 80) {
                    toP.classList.add("active");
                } else {
                    toP.classList.remove("active");
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            function initSwiperIfPresent(selector, options) {
                const swiperElement = document.querySelector(selector);
                if (swiperElement && swiperElement.querySelector('.swiper-wrapper') && swiperElement.querySelector('.swiper-wrapper').children.length > 0) {
                    new Swiper(selector, options);
                } else if (swiperElement) {
                    const navNext = swiperElement.querySelector('.swiper-button-next');
                    const navPrev = swiperElement.querySelector('.swiper-button-prev');
                    if (navNext) navNext.style.display = 'none';
                    if (navPrev) navPrev.style.display = 'none';
                }
            }

            initSwiperIfPresent('.featured-courses-slider', {
                slidesPerView: 3,
                spaceBetween: 30,
                loop: false,
                navigation: {
                    nextEl: '.featured-courses-slider .swiper-button-next',
                    prevEl: '.featured-courses-slider .swiper-button-prev',
                },
                breakpoints: {
                    320: { slidesPerView: 1, spaceBetween: 10 },
                    576: { slidesPerView: 1, spaceBetween: 20 },
                    768: { slidesPerView: 2, spaceBetween: 30 },
                    992: { slidesPerView: 3, spaceBetween: 30 },
                    1200: { slidesPerView: 3, spaceBetween: 30}
                }
            });

            initSwiperIfPresent('.instructors-slider', {
                slidesPerView: 3,
                spaceBetween: 30,
                loop: false,
                navigation: {
                    nextEl: '.instructors-slider .swiper-button-next',
                    prevEl: '.instructors-slider .swiper-button-prev',
                },
                breakpoints: {
                    320: { slidesPerView: 1, spaceBetween: 10 },
                    576: { slidesPerView: 1, spaceBetween: 20 },
                    768: { slidesPerView: 2, spaceBetween: 30 },
                    992: { slidesPerView: 3, spaceBetween: 30 },
                    1200: { slidesPerView: 3, spaceBetween: 30}
                }
            });

            initSwiperIfPresent('.testimonials-slider', {
                slidesPerView: 3,
                spaceBetween: 30,
                loop: false,
                navigation: {
                    nextEl: '.testimonials-slider .swiper-button-next',
                    prevEl: '.testimonials-slider .swiper-button-prev',
                },
                breakpoints: {
                    320: { slidesPerView: 1, spaceBetween: 10 },
                    576: { slidesPerView: 1, spaceBetween: 20 },
                    768: { slidesPerView: 2, spaceBetween: 30 },
                    992: { slidesPerView: 3, spaceBetween: 30 },
                    1200: { slidesPerView: 3, spaceBetween: 30}
                }
            });
        });
    </script>

<?php include(__DIR__ . '/../templates/footer.php'); ?>