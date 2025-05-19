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
    } elseif ($app_root_path_relative === '.' && ltrim($script_path, '/') !== basename($script_path)) {
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
// Sử dụng course_id từ GET parameter, không phải course_id_param nữa để nhất quán
$course_id_get_param = $_GET['courseID'] ?? $_GET['course_id'] ?? null;


// URL cơ sở cho controller tải file
$file_loader_base_url = $app_root_path_relative . '/controller/c_file_loader.php';

function format_price($price)
{
    if (!is_numeric($price)) return 'N/A';
    return '₫' . number_format($price, 0, ',', '.');
}

if ($course_id_get_param) {
    $api_response = callApi('course_api.php', 'GET', ['courseID' => $course_id_get_param]);

    if (isset($api_response['success']) && $api_response['success'] && isset($api_response['data'])) {
        $raw_data = $api_response['data'];

        if (is_array($raw_data) && !empty($raw_data)) {
            if (isset($raw_data['courseID'])) {
                $course_data = $raw_data;
            } elseif (isset($raw_data[0]['courseID'])) {
                $found_course = null;
                foreach ($raw_data as $c) {
                    if (isset($c['courseID']) && $c['courseID'] == $course_id_get_param) {
                        $found_course = $c;
                        break;
                    }
                }
                if ($found_course) {
                    $course_data = $found_course;
                } else {
                    $error_message = 'Không tìm thấy khóa học với ID (' . htmlspecialchars($course_id_get_param) . ') trong dữ liệu trả về.';
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
<style>
    /* --- BEGIN CSS KHẮC PHỤC TRÀN & CẢI TIỆN BỐ CỤC --- */
    .course-hero-container {
        max-width: 960px;
        margin: 0 auto;
        display: flex;
        gap: 20px;
        /* Giảm khoảng cách để tiết kiệm không gian */
        flex-wrap: wrap;
        /* Cho phép wrap trên màn hình nhỏ */
    }

    .course-hero-main {
        flex: 1 1 580px;
        /* Có thể co giãn, cơ sở 580px */
        min-width: 300px;
        /* Không thu nhỏ hơn 300px */
        /* max-width: 100%; Đã được xử lý bởi flex */
    }

    .course-aside {
        flex: 0 0 300px;
        /* Không co giãn, kích thước cố định 300px */
        /* width: 300px; */
    }

    /* Đảm bảo từ dài tự xuống dòng */
    .course-hero-title,
    .course-section-title,
    /* Áp dụng cho cả tiêu đề section bên dưới */
    .lesson-title-area span,
    .course-meta-author,
    /* Cho tên giảng viên dài */
    .course-breadcrumbs a {
        /* Cho breadcrumbs */
        word-break: break-word;
        overflow-wrap: break-word;
        -webkit-hyphens: auto;
        -ms-hyphens: auto;
        hyphens: auto;
    }

    /* --- END CSS KHẮC PHỤC TRÀN --- */


    /* --- BEGIN CSS CHO HIỂN THỊ BÀI HỌC MỚI --- */
    .course-lecture-list {
        /* Reset padding cho ul cha của các lesson-entry */
        padding-left: 0;
        list-style-type: none;
        /* Đảm bảo không có bullet points mặc định */
    }

    .lesson-entry {
        border-bottom: 1px solid #e8e8e8;
        /* Đường kẻ phân cách nhẹ nhàng hơn */
        padding: 12px 0;
    }

    .lesson-entry:last-child {
        border-bottom: none;
    }

    .lesson-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        cursor: default;
        /* Hoặc pointer nếu toàn bộ header có thể click để mở rộng */
    }

    .lesson-title-area {
        display: flex;
        align-items: center;
        gap: 10px;
        /* Khoảng cách giữa icon và tiêu đề */
        flex-grow: 1;
        min-width: 0;
        /* Quan trọng cho text ellipsis trong flex child */
    }

    .lesson-title-area span {
        font-weight: 500;
        color: #2d2f31;
        /* Màu chữ tiêu đề bài học */
        font-size: 15px;
        /* Kích thước chữ tiêu đề */
        line-height: 1.4;
        white-space: normal;
        /* Cho phép tiêu đề dài xuống dòng */
    }

    .lesson-icon-svg {
        width: 20px;
        /* Kích thước icon */
        height: 20px;
        fill: #5624d0;
        flex-shrink: 0;
    }

    .lesson-actions-area {
        display: flex;
        align-items: center;
        gap: 15px;
        /* Khoảng cách giữa các nút/thông tin meta */
        flex-shrink: 0;
        margin-left: 10px;
        /* Khoảng cách với tiêu đề */
    }

    .btn-lesson-resources {
        background-color: transparent;
        border: 1px solid #5624d0;
        color: #5624d0;
        padding: 5px 12px;
        /* Kích thước nút */
        border-radius: 4px;
        font-size: 13px;
        /* Cỡ chữ nút */
        cursor: pointer;
        font-weight: 600;
        transition: background-color 0.2s ease, color 0.2s ease;
    }

    .btn-lesson-resources:hover {
        background-color: #5624d0;
        color: #fff;
    }

    .btn-lesson-resources.active {
        /* Style khi nút active (details đang mở) */
        background-color: #5624d0;
        color: #fff;
    }


    .lesson-preview-link {
        /* Nếu bạn muốn thêm link xem thử */
        font-size: 13px;
        color: #007791;
        /* Màu khác cho xem thử */
        text-decoration: none;
        font-weight: 600;
    }

    .lesson-preview-link:hover {
        text-decoration: underline;
    }

    .lesson-duration-badge {
        font-size: 13px;
        color: #505759;
        /* Màu chữ thời lượng */
        background-color: #f7f7f7;
        /* Nền nhẹ cho thời lượng */
        padding: 4px 8px;
        border-radius: 4px;
        font-weight: 500;
    }

    .lesson-content-details {
        padding-left: 30px;
        /* Thụt lề cho nội dung (icon width + gap) */
        margin-top: 12px;
        background-color: #fbfbfb;
        /* Nền rất nhẹ để phân biệt */
        border-radius: 4px;
        padding: 12px;
        border: 1px solid #efefef;
        /* display: none; Sẽ được JS điều khiển */
    }

    .resource-list-collapsible,
    .video-list-collapsible {
        list-style-type: none;
        padding-left: 0;
        margin-top: 8px;
    }

    .resource-list-collapsible:first-child,
    .video-list-collapsible:first-child {
        margin-top: 0;
    }

    .resource-list-collapsible li,
    .video-list-collapsible li {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 6px 0;
        /* Khoảng cách giữa các item */
        font-size: 14px;
        color: #333;
    }

    .resource-list-collapsible li svg,
    .video-list-collapsible li svg {
        width: 16px;
        height: 16px;
        fill: currentColor;
        /* Icon sẽ theo màu chữ */
        flex-shrink: 0;
    }

    .resource-list-collapsible li a,
    .video-list-collapsible li a {
        color: #0056d2;
        /* Màu link chuẩn */
        text-decoration: none;
        flex-grow: 1;
        /* Cho phép link chiếm không gian */
        word-break: break-all;
        /* Ngắt link dài nếu cần */
    }

    .resource-list-collapsible li a:hover,
    .video-list-collapsible li a:hover {
        text-decoration: underline;
    }

    .video-item-duration {
        /* Nếu có thời lượng cho từng video */
        font-size: 0.85em;
        color: #777;
        margin-left: auto;
        /* Đẩy sang phải */
        padding-left: 10px;
    }


    .sub-list-title {
        display: block;
        font-weight: 600;
        /* In đậm hơn */
        font-size: 14px;
        /* Cỡ chữ cho tiêu đề phụ */
        color: #1c1d1f;
        /* Màu chữ đậm hơn */
        margin-bottom: 8px;
        margin-top: 10px;
        padding-bottom: 4px;
        border-bottom: 1px solid #e0e0e0;
        /* Đường kẻ nhẹ dưới tiêu đề phụ */
    }

    .sub-list-title:first-child {
        margin-top: 0;
    }

    /* --- END CSS CHO HIỂN THỊ BÀI HỌC MỚI --- */

    /* CSS cũ của bạn cho chapter-description và các thành phần khác vẫn giữ nguyên */
    .chapter-description {
        font-size: 0.9em;
        color: #555;
        margin-bottom: 10px;
        padding-left: 0;
        /* Bỏ padding-left cũ nếu không cần thiết với cấu trúc mới */
        line-height: 1.5;
    }
</style>

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
                    <?php
                    $total_chapters = 0;
                    $total_lessons = 0;
                    if (!empty($course_data['chapters']) && is_array($course_data['chapters'])) {
                        $total_chapters = count($course_data['chapters']);
                        foreach ($course_data['chapters'] as $chapter) {
                            if (!empty($chapter['chapterLessons']) && is_array($chapter['chapterLessons'])) {
                                $total_lessons += count($chapter['chapterLessons']);
                            }
                        }
                    }
                    ?>
                    <div class="course-content-meta" aria-live="polite" aria-atomic="true">
                        <?php echo $total_chapters; ?> chương • <?php echo $total_lessons; ?> bài học
                    </div>
                    <div class="course-content-accordion" id="course-content-accordion">
                        <?php if (!empty($course_data['chapters']) && is_array($course_data['chapters'])): ?>
                            <?php foreach ($course_data['chapters'] as $chapter_index => $chapter): ?>
                                <div class="course-section">
                                    <button class="course-section-toggle" aria-expanded="<?php echo $chapter_index === 0 ? 'true' : 'false'; ?>" aria-controls="chapter-<?php echo $chapter_index; ?>-content" id="chapter-<?php echo $chapter_index; ?>-toggle" onclick="toggleSyllabusSection(this)">
                                        <span class="course-section-title"><?php echo htmlspecialchars($chapter['chapterTitle'] ?? 'Chương không có tiêu đề'); ?></span>
                                        <span class="course-section-info">
                                            <?php echo (!empty($chapter['chapterLessons']) && is_array($chapter['chapterLessons'])) ? count($chapter['chapterLessons']) : 0; ?> bài học
                                        </span>
                                    </button>
                                    <div class="course-section-content <?php echo $chapter_index === 0 ? 'open' : ''; ?>" id="chapter-<?php echo $chapter_index; ?>-content" role="region" aria-labelledby="chapter-<?php echo $chapter_index; ?>-toggle">
                                        <?php if (!empty($chapter['chapterDescription'])): ?>
                                            <p class="chapter-description"><?php echo htmlspecialchars($chapter['chapterDescription']); ?></p>
                                        <?php endif; ?>
                                        <ul class="course-lecture-list">
                                            <?php if (!empty($chapter['chapterLessons']) && is_array($chapter['chapterLessons'])): ?>
                                                <?php foreach ($chapter['chapterLessons'] as $lesson_index => $lesson): ?>
                                                    <li class="lesson-entry">
                                                        <div class="lesson-header">
                                                            <div class="lesson-title-area">
                                                                <svg class="lesson-icon-svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-.84-11.312a.75.75 0 011.062.002l3.5 3.25a.75.75 0 010 1.12l-3.5 3.25a.75.75 0 11-1.062-1.122L11.878 10 9.16 7.812a.75.75 0 01-.002-1.122z" clip-rule="evenodd" />
                                                                </svg>
                                                                <span><?php echo htmlspecialchars($lesson['lessonTitle'] ?? 'Bài học không có tiêu đề'); ?></span>
                                                            </div>
                                                            <div class="lesson-actions-area">
                                                                <?php if (!empty($lesson['lessonResources']) || !empty($lesson['lessonVideos'])): // Hiển thị nút nếu có tài liệu HOẶC video 
                                                                ?>
                                                                    <button type="button" class="btn-lesson-resources" onclick="toggleLessonDetails(this, 'lesson-details-<?php echo $chapter_index . '-' . $lesson_index; ?>')">
                                                                        <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor" style="margin-right: 4px; vertical-align: middle;">
                                                                            <path d="M5.5 16.5A1.5 1.5 0 014 15V5a1.5 1.5 0 011.5-1.5h5A1.5 1.5 0 0112 5v2.5a.75.75 0 001.5 0V5a3 3 0 00-3-3h-5A3 3 0 002.5 5v10a3 3 0 003 3h5a3 3 0 003-3V12.5a.75.75 0 00-1.5 0V15a1.5 1.5 0 01-1.5 1.5h-5z"></path>
                                                                            <path d="M18.25 9.043a.75.75 0 00-1.06 0l-4.5 4.5a.75.75 0 001.06 1.06l4.5-4.5a.75.75 0 000-1.06z"></path>
                                                                            <path d="M13.75 13.543a.75.75 0 001.06 0l4.5-4.5a.75.75 0 00-1.06-1.06l-4.5 4.5a.75.75 0 000 1.06z"></path>
                                                                        </svg>
                                                                        Tài liệu
                                                                    </button>
                                                                <?php endif; ?>
                                                                <?php
                                                                // Placeholder cho thời lượng, bạn cần lấy từ API nếu có
                                                                $lesson_duration_placeholder = "00:00";
                                                                if (isset($lesson['lessonVideos'][0]['videoTitle'])) { // Giả sử video đầu tiên có thể cho thời lượng
                                                                    // Đây chỉ là ví dụ, API thực tế cần cung cấp trường duration
                                                                    $title_length = strlen($lesson['lessonVideos'][0]['videoTitle']);
                                                                    $minutes = $title_length % 15 + 1; // Thời lượng giả dựa trên độ dài tiêu đề
                                                                    $seconds = ($title_length * 3) % 60;
                                                                    $lesson_duration_placeholder = sprintf("%02d:%02d", $minutes, $seconds);
                                                                }
                                                                ?>
                                                                <span class="lesson-duration-badge"><?php echo $lesson_duration_placeholder; ?></span>
                                                            </div>
                                                        </div>
                                                        <div class="lesson-content-details" id="lesson-details-<?php echo $chapter_index . '-' . $lesson_index; ?>" style="display: none;">
                                                            <?php if (!empty($lesson['lessonResources']) && is_array($lesson['lessonResources'])): ?>
                                                                <ul class="resource-list-collapsible">
                                                                    <span class="sub-list-title">Tài liệu đính kèm:</span>
                                                                    <?php foreach ($lesson['lessonResources'] as $resource): ?>
                                                                        <li>
                                                                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                                                                                <path d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5L14 4.5zm-3 0A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4.5h-2z" />
                                                                            </svg>
                                                                            <a href="<?php echo htmlspecialchars($file_loader_base_url . "?act=serve_course_resource&resource_id=" . urlencode($resource['resourceID'] ?? '') . "&filename=" . urlencode($resource['resourcePath'] ?? '')); ?>" target="_blank">
                                                                                <?php echo htmlspecialchars($resource['resourceTitle'] ?? 'Tài liệu'); ?>
                                                                            </a>
                                                                        </li>
                                                                    <?php endforeach; ?>
                                                                </ul>
                                                            <?php endif; ?>

                                                            <?php if (!empty($lesson['lessonVideos']) && is_array($lesson['lessonVideos'])): ?>
                                                                <ul class="video-list-collapsible">
                                                                    <span class="sub-list-title">Video bài giảng:</span>
                                                                    <?php foreach ($lesson['lessonVideos'] as $video): ?>
                                                                        <li>
                                                                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                                                                                <path d="M10.804 8 5 4.633v6.734L10.804 8zm.792-.696a.802.802 0 0 1 0 1.392l-6.363 3.692C4.713 12.69 4 12.345 4 11.692V4.308c0-.653.713-.998 1.233-.696l6.363 3.692z" />
                                                                            </svg>
                                                                            <?php
                                                                            $video_url = $video['videoURL'] ?? '#';
                                                                            $video_target = '_blank';
                                                                            if ($video_url !== '#' && !(substr($video_url, 0, 7) === 'http://' || substr($video_url, 0, 8) === 'https://')) {
                                                                                $video_url = htmlspecialchars($file_loader_base_url . "?act=serve_course_video&video_id=" . urlencode($video['videoID'] ?? '') . "&filename=" . urlencode($video['videoURL'] ?? ''));
                                                                            } else {
                                                                                $video_url = htmlspecialchars($video_url);
                                                                            }
                                                                            ?>
                                                                            <a href="<?php echo $video_url; ?>" target="<?php echo $video_target; ?>">
                                                                                <?php echo htmlspecialchars($video['videoTitle'] ?? 'Video'); ?>
                                                                            </a>
                                                                        </li>
                                                                    <?php endforeach; ?>
                                                                </ul>
                                                            <?php endif; ?>
                                                            <?php if (empty($lesson['lessonResources']) && empty($lesson['lessonVideos'])): ?>
                                                                <p style="font-style: italic; color: #777; font-size: 13px;">Không có tài liệu hoặc video cho bài học này.</p>
                                                            <?php endif; ?>
                                                        </div>
                                                    </li>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <li style="font-style: italic; color: #777; padding: 10px 0;">Không có bài học nào trong chương này.</li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="padding: 15px 0;">Nội dung chi tiết của khóa học đang được cập nhật.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <aside class="course-aside" aria-label="Tùy chọn mua khóa học và thông tin chi tiết">
                <div class="course-aside-imgbox">
                    <?php
                    $course_image_display_url = "https://placehold.co/600x400/EFEFEF/AAAAAA?text=Course+Image";
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
                <ul class="course-feature-list">
                    <li>
                        <svg width="18" height="18" fill="none" stroke="#5624d0" stroke-width="2" aria-hidden="true">
                            <rect x="3" y="4" width="12" height="10" rx="2" />
                            <path d="M3 8h12" />
                        </svg>
                        Video theo yêu cầu
                    </li>
                    <li>
                        <svg width="18" height="18" fill="none" stroke="#5624d0" stroke-width="2" aria-hidden="true">
                            <rect x="3" y="4" width="12" height="10" rx="2" />
                            <path d="M3 8h12" />
                        </svg>
                        Tài nguyên có thể tải xuống
                    </li>
                    <li>
                        <svg width="18" height="18" fill="none" stroke="#5624d0" stroke-width="2" aria-hidden="true">
                            <rect x="2" y="2" width="14" height="14" rx="3" />
                        </svg>
                        Truy cập trên thiết bị di động và TV
                    </li>
                    <li>
                        <svg width="18" height="18" fill="none" stroke="#5624d0" stroke-width="2" aria-hidden="true">
                            <rect x="4" y="2" width="10" height="14" rx="2" />
                        </svg>
                        Truy cập trọn đời
                    </li>
                    <li>
                        <svg width="18" height="18" fill="none" stroke="#5624d0" stroke-width="2" aria-hidden="true">
                            <circle cx="9" cy="9" r="7" />
                            <path d="M9 5v4l3 3" />
                        </svg>
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
                        $instructor_avatar_url = "https://placehold.co/100x100/EFEFEF/AAAAAA?text=Avatar";
                        $instructor_image_filename = $instructor['avatarFilename'] ?? 'default_avatar.png';
                        if (!empty($instructor['instructorID']) && !empty($instructor_image_filename)) {
                            $instructor_avatar_url = htmlspecialchars($file_loader_base_url . "?act=serve_user_image&user_id=" . urlencode($instructor['instructorID']) . "&image=" . urlencode($instructor_image_filename));
                        }
                        ?>
                        <div class="course-instructor-box">
                            <a href="#" class="course-instructor-name"><?php echo htmlspecialchars(($instructor['firstName'] ?? '') . ' ' . ($instructor['lastName'] ?? 'N/A')); ?></a>
                            <div class="course-instructor-profile">
                                <div class="course-instructor-avt">
                                    <img src="<?php echo $instructor_avatar_url; ?>" alt="Ảnh đại diện <?php echo htmlspecialchars(($instructor['firstName'] ?? '') . ' ' . ($instructor['lastName'] ?? '')); ?>" onerror="this.onerror=null;this.src='https://placehold.co/100x100/EFEFEF/AAAAAA?text=No+Image';" />
                                </div>
                                <div class="course-instructor-meta">
                                    <div><span class="inst-star" aria-label="Đánh giá giảng viên 4.6 sao">★ 4.6</span> Đánh giá giảng viên</div>
                                    <div><svg width="16" height="16" fill="none" stroke="#6a6f73" stroke-width="1.5" aria-hidden="true">
                                            <circle cx="8" cy="8" r="7" />
                                        </svg> 1,263,843 nhận xét</div>
                                    <div><svg width="16" height="16" fill="none" stroke="#6a6f73" stroke-width="1.5" aria-hidden="true">
                                            <circle cx="8" cy="8" r="7" />
                                            <path d="M4 14l8-8" />
                                        </svg> 4,237,490 học viên</div>
                                    <div><svg width="16" height="16" fill="none" stroke="#6a6f73" stroke-width="1.5" aria-hidden="true">
                                            <circle cx="8" cy="8" r="7" />
                                            <path d="M8 4v8" />
                                        </svg> 87 khóa học</div>
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
    // Hàm JS để mở/đóng section của syllabus (giữ nguyên)
    function toggleSyllabusSection(button) {
        const contentId = button.getAttribute('aria-controls');
        const content = document.getElementById(contentId);
        const isExpanded = button.getAttribute('aria-expanded') === 'true';

        button.setAttribute('aria-expanded', !isExpanded);
        content.classList.toggle('open');
    }

    // Hàm JS mới để mở/đóng chi tiết bài học (tài liệu, video)
    function toggleLessonDetails(button, contentId) {
        const content = document.getElementById(contentId);
        if (content) {
            const isCurrentlyOpen = content.style.display === 'block';
            content.style.display = isCurrentlyOpen ? 'none' : 'block';
            button.classList.toggle('active', !isCurrentlyOpen); // Thêm/xóa class 'active' cho nút
        }
    }


    const expandAllButton = document.querySelector('.course-content-expand');
    if (expandAllButton) {
        expandAllButton.addEventListener('click', function(event) {
            event.preventDefault();
            const syllabusSections = document.querySelectorAll('.course-content-accordion .course-section-toggle');
            const lessonDetailButtons = document.querySelectorAll('.btn-lesson-resources');

            const isCurrentlyExpanding = this.textContent.includes('Mở rộng');
            this.textContent = isCurrentlyExpanding ? 'Thu gọn tất cả' : 'Mở rộng tất cả';
            this.setAttribute('aria-expanded', isCurrentlyExpanding ? 'true' : 'false');

            // Mở/đóng các chương
            syllabusSections.forEach(button => {
                const contentId = button.getAttribute('aria-controls');
                const content = document.getElementById(contentId);
                button.setAttribute('aria-expanded', isCurrentlyExpanding ? 'true' : 'false');
                if (isCurrentlyExpanding) {
                    content.classList.add('open');
                } else {
                    content.classList.remove('open');
                }
            });

            // Mở/đóng chi tiết tất cả các bài học (nếu muốn)
            // Hiện tại, nút "Mở rộng tất cả" chỉ tác động đến chương.
            // Nếu muốn nó cũng mở tất cả chi tiết bài học, thêm logic tương tự cho lessonDetailButtons
            /*
            lessonDetailButtons.forEach(button => {
                const lessonContentId = button.getAttribute('onclick').match(/'([^']+)'/)[1]; // Trích xuất ID từ onclick
                const lessonContent = document.getElementById(lessonContentId);
                if (lessonContent) {
                    lessonContent.style.display = isCurrentlyExpanding ? 'block' : 'none';
                    button.classList.toggle('active', isCurrentlyExpanding);
                }
            });
            */
        });
    }
</script>
<script src="<?php echo $app_root_url_for_paths; ?>/public/js/course-detail.js"></script>
<?php include('template/footer.php'); ?>