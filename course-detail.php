<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http');
$host = $_SERVER['HTTP_HOST'];
$script_path = $_SERVER['SCRIPT_NAME'];
// Logic để xác định $app_root_path_relative từ code gốc của bạn
$path_parts = explode('/', ltrim($script_path, '/'));

// Sử dụng logic đầy đủ từ code gốc của bạn để xác định $app_root_path_relative
$app_root_path_relative = ''; // Giá trị khởi tạo mặc định

// Logic ban đầu của bạn để tìm $app_root_path_relative
if (count($path_parts) > 0 && $path_parts[0] !== basename($script_path)) {
    $app_root_path_relative = '/' . $path_parts[0];
}


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
    } elseif ($app_root_path_relative === '\\') { // Dành cho Windows
        $app_root_path_relative = '';
    }
    elseif ($app_root_path_relative === '.' && ltrim($script_path, '/') !== basename($script_path) ) {
        $app_root_path_relative = '';
    } elseif ($app_root_path_relative === '.') {
        $app_root_path_relative = '';
    }


}

if ($app_root_path_relative !== '/' && $app_root_path_relative !== '' && substr($app_root_path_relative, -1) === '/') {
    $app_root_path_relative = rtrim($app_root_path_relative, '/');
}
if ($app_root_path_relative === '/' && $script_path === '/') {
    $app_root_path_relative = '';
}


define('API_BASE', $protocol . '://' . $host . $app_root_path_relative . '/api');

function callApi(string $endpoint, string $method = 'GET', array $payload = []): array
{
    $url = API_BASE . '/' . ltrim($endpoint, '/');
    $methodUpper = strtoupper($method);

    if ($methodUpper === 'GET' && !empty($payload)) {
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
        ]
    ];

    if ($methodUpper !== 'GET') {
        if (!empty($payload)) {
            $options['http']['content'] = json_encode($payload);
        } else if (in_array($methodUpper, ['POST', 'PUT'])) {
            $options['http']['content'] = '{}';
        }
    }

    $context  = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);

    $http_status_code = 0;
    if (isset($http_response_header[0])) {
        if (preg_match('{HTTP\/\S*\s(\d{3})}', $http_response_header[0], $match)) {
            $http_status_code = intval($match[1]);
        }
    }


    if ($response === false) {
        return [
            'success' => false,
            'message' => 'Lỗi kết nối đến API hoặc API không phản hồi. URL: ' . htmlspecialchars($url),
            'data' => null,
            'raw_response' => null,
            'http_status_code' => $http_status_code
        ];
    }

    $result   = json_decode($response, true);

    if ($result === null && json_last_error() !== JSON_ERROR_NONE) {
        return [
            'success' => false,
            'message' => 'Phản hồi API không hợp lệ hoặc không thể giải mã JSON. Lỗi JSON: ' . json_last_error_msg(),
            'data' => null,
            'raw_response' => $response,
            'http_status_code' => $http_status_code
        ];
    }

    if (!is_array($result)) {
        $result = [];
    }
    $result['http_status_code'] = $http_status_code;

    if (!isset($result['success'])) {
        $result['success'] = ($http_status_code >= 200 && $http_status_code < 300);
    }
    return $result;
}

$course_data = null;
$error_message = null;
$course_id = $_GET['course_id'] ?? null;

// URL cơ sở cho controller tải file
$file_loader_base_url = $app_root_path_relative . '/controller/c_file_loader.php';

function format_price($price) {
    if (!is_numeric($price)) return 'N/A';
    return '₫' . number_format($price, 0, ',', '.');
}

if ($course_id) {
    $api_response = callApi('course_api.php', 'GET', ['courseID' => $course_id]);

    if (isset($api_response['success']) && $api_response['success'] && isset($api_response['data'])) {
        $raw_data = $api_response['data'];

        if (is_array($raw_data) && !empty($raw_data)) {
            if (isset($raw_data['courseID'])) {
                $course_data = $raw_data;
            } elseif (isset($raw_data[0]['courseID'])) {
                $found_course = null;
                foreach ($raw_data as $c) {
                    if (isset($c['courseID']) && $c['courseID'] == $course_id) {
                        $found_course = $c;
                        break;
                    }
                }
                if ($found_course) {
                    $course_data = $found_course;
                } else {
                    $error_message = 'Không tìm thấy khóa học với ID (' . htmlspecialchars($course_id) . ') trong dữ liệu trả về.';
                }
            } else {
                $error_message = 'Dữ liệu khóa học nhận được không hợp lệ hoặc có cấu trúc không đúng.';
            }
        } else {
            $error_message = 'Dữ liệu khóa học trống hoặc không hợp lệ từ API.';
        }
        if (!$course_data && !$error_message) {
            $error_message = 'Không thể xử lý dữ liệu khóa học từ API.';
        }

    } else {
        $error_message = $api_response['message'] ?? 'Không thể tải dữ liệu khóa học hoặc khóa học không tồn tại.';
        if (isset($api_response['http_status_code']) && $api_response['http_status_code'] !== 200) {
            $error_message .= " (Mã lỗi HTTP: " . $api_response['http_status_code'] . ")";
        }
    }
} else {
    $error_message = 'Không có ID khóa học nào được cung cấp trên URL.';
}

$app_root_url_for_paths = htmlspecialchars($app_root_path_relative);

?>
<?php include('template/head.php'); ?>
<?php include('template/header.php'); ?>
<link href="<?php echo $app_root_url_for_paths; ?>/public/css/course-detail.css" rel="stylesheet">

<?php if ($error_message): ?>
    <div class="course-hero-bg">
        <div class="course-hero-container" style="text-align: center; padding: 50px; color: red;">
            <h1>Đã xảy ra lỗi</h1>
            <p><?php echo htmlspecialchars($error_message); ?></p>
            <p><a href="<?php echo $app_root_url_for_paths; ?>/">Quay lại trang chủ</a></p>
        </div>
    </div>
<?php elseif ($course_data): ?>
    <div class="course-hero-bg">
        <div class="course-hero-container">
            <div class="course-hero-main">
                <nav class="course-breadcrumbs" aria-label="Điều hướng phân cấp">
                    <?php if (!empty($course_data['categories']) && is_array($course_data['categories'])): ?>
                        <?php foreach ($course_data['categories'] as $index => $category): ?>
                            <a href="#" tabindex="0"><?php echo htmlspecialchars($category['categoryName']); ?></a>
                            <?php if ($index < count($course_data['categories']) - 1): ?>
                                <span aria-hidden="true">›</span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <a href="#" tabindex="0">Chưa phân loại</a>
                    <?php endif; ?>
                </nav>
                <h1 class="course-hero-title"><?php echo htmlspecialchars($course_data['title'] ?? 'N/A'); ?></h1>

                <div class="course-hero-meta" role="list">
                    <span class="course-badge" role="listitem" aria-label="Huy hiệu bán chạy nhất">Bán chạy nhất</span> <span class="course-rating" role="listitem" aria-label="Đánh giá khóa học 4.6 trên 5 sao"> <span class="course-rating-num">4.6</span>
                        <span class="course-stars" aria-hidden="true">
                            <?php for ($i = 0; $i < 5; $i++): ?>
                                <svg viewBox="0 0 20 20" width="14" height="14" fill="#f7b500" aria-hidden="true">
                                <path d="M10 15.27L16.18 19l-1.64-7.03L20 7.24l-7.19-.61L10 0 7.19 6.63 0 7.24l5.46 4.73L3.82 19z"></path>
                            </svg>
                            <?php endfor; ?>
                        </span>
                    </span>
                    <a href="#" class="course-link-reviews" role="listitem">(150,860 đánh giá)</a> <span class="course-students" role="listitem" aria-label="764,815 học viên đã đăng ký"> <svg width="16" height="16" fill="none" stroke="#f7b500" stroke-width="2" aria-hidden="true">
                            <circle cx="8" cy="8" r="7" />
                        </svg>764,815 học viên
                    </span>
                </div>
                <div class="course-meta-author">
                    Được dạy bởi
                    <?php if (!empty($course_data['instructors']) && is_array($course_data['instructors'])): ?>
                        <?php
                        $instructor_links = [];
                        foreach ($course_data['instructors'] as $instructor) {
                            $instructor_name = htmlspecialchars(($instructor['firstName'] ?? '') . ' ' . ($instructor['lastName'] ?? ''));
                            $instructor_links[] = '<a href="#" class="course-meta-link">' . trim($instructor_name) . '</a>';
                        }
                        echo implode(', ', $instructor_links);
                        ?>
                    <?php else: ?>
                        <a href="#" class="course-meta-link">Đang cập nhật</a>
                    <?php endif; ?>
                </div>
                <div class="course-meta-date" aria-label="Thông tin cập nhật và ngôn ngữ khóa học"> <svg width="14" height="14" fill="none" stroke="#999" stroke-width="2" aria-hidden="true">
                        <circle cx="7" cy="7" r="6" />
                        <path d="M7 3v4l3 3" />
                    </svg>
                    <span>Cập nhật lần cuối 5/2020</span>
                </div>

                <div class="course-learn-card" role="region" aria-labelledby="learn-title">
                    <h2 id="learn-title" class="course-learn-title">Bạn sẽ học được gì</h2>
                    <?php if (!empty($course_data['objectives']) && is_array($course_data['objectives'])): ?>
                        <ul class="course-learn-list">
                            <?php foreach ($course_data['objectives'] as $objective): ?>
                                <li>
                                    <svg width="20" height="20" fill="none" stroke="#5624d0" stroke-width="3" aria-hidden="true">
                                        <polyline points="5 11 9 15 16 6" />
                                    </svg>
                                    <?php echo htmlspecialchars($objective['objective'] ?? 'N/A'); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>Thông tin mục tiêu học tập đang được cập nhật.</p>
                    <?php endif; ?>
                </div>

                <div class="course-content-card" role="region" aria-labelledby="content-title">
                    <div class="course-content-header-row">
                        <h2 id="content-title" class="course-content-title">Nội dung khóa học</h2>
                        <a class="course-content-expand" href="#" role="button" aria-expanded="false" aria-controls="course-content-accordion">Mở rộng tất cả</a>
                    </div>
                    <?php if (!empty($course_data['objectives']) && is_array($course_data['objectives'])): ?>
                        <div class="course-content-meta" aria-live="polite" aria-atomic="true">
                            <?php echo count($course_data['objectives']); ?> chủ đề chính </div>
                        <div class="course-content-accordion" id="course-content-accordion">
                            <?php foreach ($course_data['objectives'] as $index => $objective_item): ?>
                                <div class="course-section">
                                    <button class="course-section-toggle" aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>" aria-controls="section-obj-<?php echo $index; ?>-content" id="section-obj-<?php echo $index; ?>-toggle" onclick="toggleSyllabusSection(this)">
                                        <span class="course-section-title"><?php echo htmlspecialchars($objective_item['objective'] ?? 'Chủ đề'); ?></span>
                                        <span class="course-section-info">1 mục</span>
                                    </button>
                                    <div class="course-section-content <?php echo $index === 0 ? 'open' : ''; ?>" id="section-obj-<?php echo $index; ?>-content" role="region" aria-labelledby="section-obj-<?php echo $index; ?>-toggle">
                                        <ul class="course-lecture-list">
                                            <li>
                                                <svg width="18" height="18" fill="none" stroke="#1c1d1f" stroke-width="2" aria-hidden="true"> <rect x="2.5" y="4" width="13" height="10" rx="2" /> </svg>
                                                <?php echo htmlspecialchars($objective_item['objective'] ?? 'Chi tiết đang cập nhật'); ?>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="course-content-meta" aria-live="polite" aria-atomic="true">Thông tin đang cập nhật</div>
                        <p style="padding: 15px 0;">Nội dung chi tiết của khóa học đang được cập nhật.</p>
                    <?php endif; ?>
                </div>
            </div>

            <aside class="course-aside" aria-label="Tùy chọn mua khóa học và thông tin chi tiết">
                <div class="course-aside-imgbox">
                    <?php
                    $course_image_display_url = "https://placehold.co/600x400/EFEFEF/AAAAAA?text=Course+Image"; // Ảnh mặc định
                    if (!empty($course_data['images']) && is_array($course_data['images']) && isset($course_data['images'][0]['imagePath']) && !empty($course_data['courseID'])) {
                        $image_filename = basename($course_data['images'][0]['imagePath']);
                        if (!empty($image_filename)) {
                            $course_image_display_url = htmlspecialchars($file_loader_base_url . "?act=serve_image&course_id=" . urlencode($course_data['courseID']) . "&image=" . urlencode($image_filename));
                        }
                    }
                    ?>
                    <img src="<?php echo $course_image_display_url; ?>" alt="Ảnh xem trước khóa học <?php echo htmlspecialchars($course_data['title'] ?? ''); ?>" class="course-aside-img" onerror="this.onerror=null;this.src='https://placehold.co/600x400/EFEFEF/AAAAAA?text=Image+Not+Found';" />
                    <div class="course-aside-preview" role="button" tabindex="0" aria-label="Xem thử khóa học này">
                        <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <circle cx="24" cy="24" r="24" fill="#fff" fill-opacity="0.88" />
                            <polygon points="20,16 34,24 20,32" fill="#5624d0" />
                        </svg>
                        <span>Xem thử khóa học</span>
                    </div>
                </div>
                <div class="course-price" aria-label="Giá khóa học"><?php echo format_price($course_data['price'] ?? 0); ?></div>
                <button class="course-btn course-btn-cart" type="button">Thêm vào giỏ hàng</button>
                <button class="course-btn course-btn-buy" type="button">Mua ngay</button>
                <div class="course-guarantee">Bảo đảm hoàn tiền trong 30 ngày</div>
                <ul class="course-feature-list"> <li>
                        <svg width="18" height="18" fill="none" stroke="#5624d0" stroke-width="2" aria-hidden="true"><rect x="3" y="4" width="12" height="10" rx="2" /><path d="M3 8h12" /></svg>
                        Video theo yêu cầu
                    </li>
                    <li>
                        <svg width="18" height="18" fill="none" stroke="#5624d0" stroke-width="2" aria-hidden="true"><rect x="3" y="4" width="12" height="10" rx="2" /><path d="M3 8h12" /></svg>
                        Tài nguyên có thể tải xuống
                    </li>
                    <li>
                        <svg width="18" height="18" fill="none" stroke="#5624d0" stroke-width="2" aria-hidden="true"><rect x="2" y="2" width="14" height="14" rx="3" /></svg>
                        Truy cập trên thiết bị di động và TV
                    </li>
                    <li>
                        <svg width="18" height="18" fill="none" stroke="#5624d0" stroke-width="2" aria-hidden="true"><rect x="4" y="2" width="10" height="14" rx="2" /></svg>
                        Truy cập trọn đời
                    </li>
                    <li>
                        <svg width="18" height="18" fill="none" stroke="#5624d0" stroke-width="2" aria-hidden="true"><circle cx="9" cy="9" r="7" /><path d="M9 5v4l3 3" /></svg>
                        Chứng nhận hoàn thành
                    </li>
                </ul>
                <div class="course-aside-actionlinks">
                    <a href="#" tabindex="0">Chia sẻ</a>
                    <a href="#" tabindex="0">Tặng khóa học này</a>
                    <a href="#" tabindex="0">Áp dụng mã giảm giá</a>
                </div>
                <form class="course-aside-coupon" onsubmit="event.preventDefault(); alert('Đã áp dụng mã giảm giá!');" aria-label="Áp dụng mã giảm giá">
                    <input type="text" class="course-aside-input" placeholder="Nhập mã giảm giá" aria-label="Mã giảm giá" />
                    <button type="submit" class="course-btn course-btn-apply">Áp dụng</button>
                </form>
                <div class="course-aside-business" role="region" aria-label="Ưu đãi đào tạo doanh nghiệp">
                    <div class="course-aside-business-title">Đào tạo cho 5 người trở lên?</div>
                    <div class="course-aside-business-desc">Cung cấp cho đội nhóm của bạn quyền truy cập vào hơn 27,000 khóa học hàng đầu mọi lúc, mọi nơi.</div>
                    <button class="course-btn course-btn-business" type="button">Thử gói Doanh nghiệp</button>
                </div>
            </aside>
        </div>
    </div>

    <div class="course-section-main">
        <section class="course-section-block" aria-labelledby="requirements-title">
            <h2 id="requirements-title" class="course-section-title">Yêu cầu</h2>
            <?php if (!empty($course_data['requirements']) && is_array($course_data['requirements'])): ?>
                <ul class="course-req-list">
                    <?php foreach ($course_data['requirements'] as $requirement): ?>
                        <li>
                            <svg width="20" height="20" fill="none" stroke="#5624d0" stroke-width="2" aria-hidden="true">
                                <circle cx="10" cy="10" r="9" />
                                <path d="M7 12l3 3 5-5" />
                            </svg>
                            <?php echo htmlspecialchars($requirement['requirement'] ?? 'N/A'); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Không có yêu cầu cụ thể hoặc thông tin đang được cập nhật.</p>
            <?php endif; ?>
        </section>

        <hr class="course-block-divider" />

        <section class="course-section-block" aria-labelledby="description-title">
            <h2 id="description-title" class="course-section-title">Mô tả</h2>
            <?php if (!empty($course_data['description'])): ?>
                <p><?php echo nl2br(htmlspecialchars($course_data['description'])); ?></p>
            <?php else: ?>
                <p>Thông tin mô tả khóa học đang được cập nhật.</p>
            <?php endif; ?>
        </section>

        <section class="course-section-block" aria-labelledby="instructors-title">
            <h2 id="instructors-title" class="course-section-title">Giảng viên</h2>
            <div class="course-instructors-list">
                <?php if (!empty($course_data['instructors']) && is_array($course_data['instructors'])): ?>
                    <?php foreach ($course_data['instructors'] as $instructor): ?>
                        <?php
                        $instructor_avatar_url = "https://placehold.co/100x100/EFEFEF/AAAAAA?text=Avatar"; // Ảnh mặc định
                        // Giả sử giảng viên có trường 'avatarFilename' hoặc bạn có một tên file mặc định
                        // Nếu API của bạn trả về tên file avatar cho giảng viên, hãy thay thế 'default_avatar.png'
                        $instructor_image_filename = $instructor['avatarFilename'] ?? 'default_avatar.png'; // Thay thế nếu có trường cụ thể
                        if (!empty($instructor['instructorID']) && !empty($instructor_image_filename)) {
                            $instructor_avatar_url = htmlspecialchars($file_loader_base_url . "?act=serve_user_image&user_id=" . urlencode($instructor['instructorID']) . "&image=" . urlencode($instructor_image_filename));
                        }
                        ?>
                        <div class="course-instructor-box">
                            <a href="#" class="course-instructor-name"><?php echo htmlspecialchars(($instructor['firstName'] ?? '') . ' ' . ($instructor['lastName'] ?? 'N/A')); ?></a>
                            <div class="course-instructor-profile">
                                <div class="course-instructor-avt">
                                    <img src="<?php echo $instructor_avatar_url; ?>" alt="Ảnh đại diện <?php echo htmlspecialchars(($instructor['firstName'] ?? '') . ' ' . ($instructor['lastName'] ?? '')); ?>" onerror="this.onerror=null;this.src='https://placehold.co/100x100/EFEFEF/AAAAAA?text=No+Image';S" />
                                </div>
                                <div class="course-instructor-meta"> <div><span class="inst-star" aria-label="Đánh giá giảng viên 4.6 sao">★ 4.6</span> Đánh giá giảng viên</div>
                                    <div><svg width="16" height="16" fill="none" stroke="#6a6f73" stroke-width="1.5" aria-hidden="true"><circle cx="8" cy="8" r="7" /></svg> 1,263,843 nhận xét</div>
                                    <div><svg width="16" height="16" fill="none" stroke="#6a6f73" stroke-width="1.5" aria-hidden="true"><circle cx="8" cy="8" r="7" /><path d="M4 14l8-8" /></svg> 4,237,490 học viên</div>
                                    <div><svg width="16" height="16" fill="none" stroke="#6a6f73" stroke-width="1.5" aria-hidden="true"><circle cx="8" cy="8" r="7" /><path d="M8 4v8" /></svg> 87 khóa học</div>
                                </div>
                            </div>
                            <div class="course-instructor-bio">
                                <?php echo nl2br(htmlspecialchars($instructor['biography'] ?? 'Tiểu sử đang được cập nhật.')); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Thông tin giảng viên đang được cập nhật.</p>
                <?php endif; ?>
            </div>
        </section>
    </div>
<?php else: ?>
    <div class="course-hero-bg">
        <div class="course-hero-container" style="text-align: center; padding: 50px;">
            <h1>Không tìm thấy khóa học</h1>
            <p>Rất tiếc, chúng tôi không thể tìm thấy thông tin cho khóa học bạn yêu cầu.</p>
            <p><a href="<?php echo $app_root_url_for_paths; ?>/">Quay lại trang chủ</a></p>
        </div>
    </div>
<?php endif; ?>

<script>
    function toggleSyllabusSection(button) {
        const contentId = button.getAttribute('aria-controls');
        const content = document.getElementById(contentId);
        const isExpanded = button.getAttribute('aria-expanded') === 'true';

        button.setAttribute('aria-expanded', !isExpanded);
        content.classList.toggle('open');
    }

    const expandAllButton = document.querySelector('.course-content-expand');
    if (expandAllButton) {
        expandAllButton.addEventListener('click', function(event) {
            event.preventDefault();
            const sections = document.querySelectorAll('.course-content-accordion .course-section-toggle');
            const isCurrentlyExpanding = this.textContent.includes('Mở rộng');

            sections.forEach(button => {
                const contentId = button.getAttribute('aria-controls');
                const content = document.getElementById(contentId);

                button.setAttribute('aria-expanded', isCurrentlyExpanding ? 'true' : 'false');
                if (isCurrentlyExpanding) {
                    content.classList.add('open');
                } else {
                    content.classList.remove('open');
                }
            });
            this.textContent = isCurrentlyExpanding ? 'Thu gọn tất cả' : 'Mở rộng tất cả';
            this.setAttribute('aria-expanded', isCurrentlyExpanding ? 'true' : 'false');
        });
    }
</script>
<script src="<?php echo $app_root_url_for_paths; ?>/public/js/course-detail.js"></script>
<?php include('template/footer.php'); ?>
