<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http');
$host = $_SERVER['HTTP_HOST'];
$script_path = $_SERVER['SCRIPT_NAME'];
$path_parts = explode('/', ltrim($script_path, '/'));

$app_root_path_relative = '';
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
    if (($app_root_path_relative === '/' || $app_root_path_relative === '\\') && $script_path !== '/') {
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

if (!defined('APP_ROOT_PATH_RELATIVE_HEADER')) {
    define('APP_ROOT_PATH_RELATIVE_DETAIL', $app_root_path_relative);
} else {
    define('APP_ROOT_PATH_RELATIVE_DETAIL', APP_ROOT_PATH_RELATIVE_HEADER);
}
if (!defined('API_BASE_HEADER')) {
    define('API_BASE_DETAIL', $protocol . '://' . $host . APP_ROOT_PATH_RELATIVE_DETAIL . '/api');
} else {
    define('API_BASE_DETAIL', API_BASE_HEADER);
}

$file_loader_base_url = APP_ROOT_PATH_RELATIVE_DETAIL . '/controller/c_file_loader.php';

if (!function_exists('callApi')) {
    function callApi(string $endpoint, string $method = 'GET', array $payload = []): array
    {
        $url = API_BASE_DETAIL . '/' . ltrim($endpoint, '/');
        $methodUpper = strtoupper($method);

        if ($methodUpper === 'GET' && !empty($payload)) {
            $url .= '?' . http_build_query($payload);
        }

        $headers_arr = ["Content-Type: application/json; charset=utf-8", "Accept: application/json"];
        $token = $_SESSION['user']['token'] ?? null;
        if ($token) {
            $headers_arr[] = "Authorization: Bearer " . $token;
        }
        $headers_str = implode("\r\n", $headers_arr);

        $options = [
            'http' => [
                'method'        => $methodUpper,
                'header'        => $headers_str,
                'ignore_errors' => true,
                'timeout'       => 15
            ]
        ];

        if ($methodUpper !== 'GET' && !empty($payload)) {
            $options['http']['content'] = json_encode($payload);
        } elseif (in_array($methodUpper, ['POST', 'PUT']) && empty($payload)) {
            $options['http']['content'] = '{}';
        }

        $context  = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);

        $status_code = 0;
        if (isset($http_response_header[0]) && preg_match('{HTTP\/\S*\s(\d{3})}', $http_response_header[0], $match)) {
            $status_code = intval($match[1]);
        }

        if ($response === false) {
            return ['success' => false, 'message' => 'API connection failed. URL: ' . htmlspecialchars($url), 'http_status_code' => $status_code, 'data' => null];
        }

        $result = json_decode($response, true);

        if ($result === null && json_last_error() !== JSON_ERROR_NONE && !empty($response)) {
            return ['success' => false, 'message' => 'Invalid API response (not JSON). Error: ' . json_last_error_msg(), 'http_status_code' => $status_code, 'data' => null, 'raw_response' => $response];
        }

        if (!is_array($result)) $result = [];
        $result['http_status_code'] = $status_code;
        if (!isset($result['success'])) {
            $result['success'] = ($status_code >= 200 && $status_code < 300);
        }
        return $result;
    }
}


$course_data = null;
$error_message = null;
$course_id_get_param = $_GET['courseID'] ?? $_GET['course_id'] ?? null;


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
                if ($found_course) $course_data = $found_course;
                else $error_message = 'Không tìm thấy khóa học với ID (' . htmlspecialchars($course_id_get_param) . ') trong dữ liệu trả về.';
            } else {
                $error_message = 'Dữ liệu khóa học nhận được không hợp lệ.';
            }
        } else {
            $error_message = 'Dữ liệu khóa học trống hoặc không hợp lệ từ API.';
        }
        if (!$course_data && !$error_message) $error_message = 'Không thể xử lý dữ liệu khóa học từ API.';
    } else {
        $error_message = $api_response['message'] ?? 'Không thể tải dữ liệu khóa học.';
        if (isset($api_response['http_status_code']) && $api_response['http_status_code'] !== 200) {
            $error_message .= " (Mã lỗi HTTP: " . $api_response['http_status_code'] . ")";
        }
    }
} else {
    $error_message = 'Không có ID khóa học nào được cung cấp.';
}

$app_root_url_for_paths = htmlspecialchars(APP_ROOT_PATH_RELATIVE_DETAIL);

$js_api_base = API_BASE_DETAIL;
$js_user_token = $_SESSION['user']['token'] ?? null;
$js_current_course_id = $course_data['courseID'] ?? null;
$js_is_user_logged_in = isset($_SESSION['user']['token']);
$js_signin_url = APP_ROOT_PATH_RELATIVE_DETAIL . '/signin.php';
$js_cart_page_url = APP_ROOT_PATH_RELATIVE_DETAIL . '/cart.php';
?>
<?php include('template/head.php'); ?>
    <script type="text/javascript">
        const API_BASE_JS = <?php echo json_encode($js_api_base); ?>;
        const USER_TOKEN_JS = <?php echo json_encode($js_user_token); ?>;
        const CURRENT_COURSE_ID_JS = <?php echo json_encode($js_current_course_id); ?>;
        const IS_USER_LOGGED_IN_JS = <?php echo json_encode($js_is_user_logged_in); ?>;
        const SIGNIN_URL_JS = <?php echo json_encode($js_signin_url); ?>;
        const CART_PAGE_URL_JS = <?php echo json_encode($js_cart_page_url); ?>;
    </script>
<?php include('template/header.php'); ?>
    <link href="<?php echo $app_root_url_for_paths; ?>/public/css/course-detail.css" rel="stylesheet">
    <style>
        .course-hero-container {
            max-width: 960px;
            margin: 0 auto;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .course-hero-main {
            flex: 1 1 580px;
            min-width: 300px;
        }

        .course-aside {
            flex: 0 0 300px;
        }

        .course-hero-title,
        .course-section-title,
        .lesson-title-area span,
        .course-meta-author,
        .course-breadcrumbs a {
            word-break: break-word;
            overflow-wrap: break-word;
            -webkit-hyphens: auto;
            -ms-hyphens: auto;
            hyphens: auto;
        }

        .course-lecture-list {
            padding-left: 0;
            list-style-type: none;
        }

        .lesson-entry {
            border-bottom: 1px solid #e8e8e8;
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
        }

        .lesson-title-area {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-grow: 1;
            min-width: 0;
        }

        .lesson-title-area span {
            font-weight: 500;
            color: #2d2f31;
            font-size: 15px;
            line-height: 1.4;
            white-space: normal;
        }

        .lesson-icon-svg {
            width: 20px;
            height: 20px;
            fill: #5624d0;
            flex-shrink: 0;
        }

        .lesson-actions-area {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-shrink: 0;
            margin-left: 10px;
        }

        .btn-lesson-resources {
            background-color: transparent;
            border: 1px solid #5624d0;
            color: #5624d0;
            padding: 5px 12px;
            border-radius: 4px;
            font-size: 13px;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .btn-lesson-resources:hover,
        .btn-lesson-resources.active {
            background-color: #5624d0;
            color: #fff;
        }

        .lesson-preview-link {
            font-size: 13px;
            color: #007791;
            text-decoration: none;
            font-weight: 600;
        }

        .lesson-preview-link:hover {
            text-decoration: underline;
        }

        .lesson-duration-badge {
            font-size: 13px;
            color: #505759;
            background-color: #f7f7f7;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 500;
        }

        .lesson-content-details {
            padding-left: 30px;
            margin-top: 12px;
            background-color: #fbfbfb;
            border-radius: 4px;
            padding: 12px;
            border: 1px solid #efefef;
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
            font-size: 14px;
            color: #333;
        }

        .resource-list-collapsible li svg,
        .video-list-collapsible li svg {
            width: 16px;
            height: 16px;
            fill: currentColor;
            flex-shrink: 0;
        }

        .resource-list-collapsible li a,
        .video-list-collapsible li a {
            color: #0056d2;
            text-decoration: none;
            flex-grow: 1;
            word-break: break-all;
        }

        .resource-list-collapsible li a:hover,
        .video-list-collapsible li a:hover {
            text-decoration: underline;
        }

        .video-item-duration {
            font-size: 0.85em;
            color: #777;
            margin-left: auto;
            padding-left: 10px;
        }

        .sub-list-title {
            display: block;
            font-weight: 600;
            font-size: 14px;
            color: #1c1d1f;
            margin-bottom: 8px;
            margin-top: 10px;
            padding-bottom: 4px;
            border-bottom: 1px solid #e0e0e0;
        }

        .sub-list-title:first-child {
            margin-top: 0;
        }

        .chapter-description {
            font-size: 0.9em;
            color: #555;
            margin-bottom: 10px;
            padding-left: 0;
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
                            <?php if ($index < count($course_data['categories']) - 1): ?> <span aria-hidden="true">›</span> <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?> <a href="#" tabindex="0">Chưa phân loại</a> <?php endif; ?>
                </nav>
                <h1 class="course-hero-title"><?php echo htmlspecialchars($course_data['title'] ?? 'N/A'); ?></h1>
                <div class="course-hero-meta" role="list">
                    <span class="course-badge" role="listitem">Bán chạy nhất</span> <span class="course-rating" role="listitem"> <span class="course-rating-num">4.6</span>
                        <span class="course-stars" aria-hidden="true">
                            <?php for ($i = 0; $i < 5; $i++): ?> <svg viewBox="0 0 20 20" width="14" height="14" fill="#f7b500">
                                    <path d="M10 15.27L16.18 19l-1.64-7.03L20 7.24l-7.19-.61L10 0 7.19 6.63 0 7.24l5.46 4.73L3.82 19z"></path>
                                </svg> <?php endfor; ?>
                        </span>
                    </span>
                    <a href="#" class="course-link-reviews" role="listitem">(150,860 đánh giá)</a> <span class="course-students" role="listitem"> <svg width="16" height="16" fill="none" stroke="#f7b500" stroke-width="2">
                            <circle cx="8" cy="8" r="7" />
                        </svg>764,815 học viên</span>
                </div>
                <div class="course-meta-author">Được dạy bởi
                    <?php if (!empty($course_data['instructors']) && is_array($course_data['instructors'])):
                        $instructor_links = [];
                        foreach ($course_data['instructors'] as $instructor) {
                            $instructor_name = htmlspecialchars(($instructor['firstName'] ?? '') . ' ' . ($instructor['lastName'] ?? ''));
                            $instructor_links[] = '<a href="#" class="course-meta-link">' . trim($instructor_name) . '</a>';
                        }
                        echo implode(', ', $instructor_links);
                    else: echo '<a href="#" class="course-meta-link">Đang cập nhật</a>';
                    endif; ?>
                </div>
                <div class="course-meta-date"> <svg width="14" height="14" fill="none" stroke="#999" stroke-width="2">
                        <circle cx="7" cy="7" r="6" />
                        <path d="M7 3v4l3 3" />
                    </svg> <span>Cập nhật lần cuối 5/2020</span></div>
                <div class="course-learn-card" role="region" aria-labelledby="learn-title">
                    <h2 id="learn-title" class="course-learn-title">Bạn sẽ học được gì</h2>
                    <?php if (!empty($course_data['objectives']) && is_array($course_data['objectives'])): ?>
                        <ul class="course-learn-list">
                            <?php foreach ($course_data['objectives'] as $objective): ?> <li> <svg width="20" height="20" fill="none" stroke="#5624d0" stroke-width="3">
                                    <polyline points="5 11 9 15 16 6" />
                                </svg> <?php echo htmlspecialchars($objective['objective'] ?? 'N/A'); ?> </li> <?php endforeach; ?>
                        </ul>
                    <?php else: ?> <p>Thông tin mục tiêu học tập đang được cập nhật.</p> <?php endif; ?>
                </div>
                <div class="course-content-card" role="region" aria-labelledby="content-title">
                    <div class="course-content-header-row">
                        <h2 id="content-title" class="course-content-title">Nội dung khóa học</h2>
                        <a class="course-content-expand" href="#" role="button" aria-expanded="false">Mở rộng tất cả</a>
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
                    <div class="course-content-meta"><?php echo $total_chapters; ?> chương • <?php echo $total_lessons; ?> bài học</div>
                    <div class="course-content-accordion" id="course-content-accordion">
                        <?php if (!empty($course_data['chapters']) && is_array($course_data['chapters'])): ?>
                            <?php foreach ($course_data['chapters'] as $chapter_index => $chapter): ?>
                                <div class="course-section">
                                    <button class="course-section-toggle" aria-expanded="<?php echo $chapter_index === 0 ? 'true' : 'false'; ?>" aria-controls="chapter-<?php echo $chapter_index; ?>-content" onclick="toggleSyllabusSection(this)">
                                        <span class="course-section-title"><?php echo htmlspecialchars($chapter['chapterTitle'] ?? 'Chương'); ?></span>
                                        <span class="course-section-info"><?php echo (!empty($chapter['chapterLessons']) && is_array($chapter['chapterLessons'])) ? count($chapter['chapterLessons']) : 0; ?> bài học</span>
                                    </button>
                                    <div class="course-section-content <?php echo $chapter_index === 0 ? 'open' : ''; ?>" id="chapter-<?php echo $chapter_index; ?>-content">
                                        <?php if (!empty($chapter['chapterDescription'])): ?> <p class="chapter-description"><?php echo htmlspecialchars($chapter['chapterDescription']); ?></p> <?php endif; ?>
                                        <ul class="course-lecture-list">
                                            <?php if (!empty($chapter['chapterLessons']) && is_array($chapter['chapterLessons'])): ?>
                                                <?php foreach ($chapter['chapterLessons'] as $lesson_index => $lesson): ?>
                                                    <li class="lesson-entry">
                                                        <div class="lesson-header">
                                                            <div class="lesson-title-area">
                                                                <svg class="lesson-icon-svg" viewBox="0 0 20 20" fill="currentColor">
                                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-.84-11.312a.75.75 0 011.062.002l3.5 3.25a.75.75 0 010 1.12l-3.5 3.25a.75.75 0 11-1.062-1.122L11.878 10 9.16 7.812a.75.75 0 01-.002-1.122z" clip-rule="evenodd" />
                                                                </svg>
                                                                <span><?php echo htmlspecialchars($lesson['lessonTitle'] ?? 'Bài học'); ?></span>
                                                            </div>
                                                            <div class="lesson-actions-area">
                                                                <?php if (!empty($lesson['lessonResources']) || !empty($lesson['lessonVideos'])): ?>
                                                                    <button type="button" class="btn-lesson-resources" onclick="toggleLessonDetails(this, 'lesson-details-<?php echo $chapter_index . '-' . $lesson_index; ?>')">
                                                                        <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor" style="margin-right: 4px; vertical-align: middle;">
                                                                            <path d="M5.5 16.5A1.5 1.5 0 014 15V5a1.5 1.5 0 011.5-1.5h5A1.5 1.5 0 0112 5v2.5a.75.75 0 001.5 0V5a3 3 0 00-3-3h-5A3 3 0 002.5 5v10a3 3 0 003 3h5a3 3 0 003-3V12.5a.75.75 0 00-1.5 0V15a1.5 1.5 0 01-1.5 1.5h-5z"></path>
                                                                            <path d="M18.25 9.043a.75.75 0 00-1.06 0l-4.5 4.5a.75.75 0 001.06 1.06l4.5-4.5a.75.75 0 000-1.06z"></path>
                                                                            <path d="M13.75 13.543a.75.75 0 001.06 0l4.5-4.5a.75.75 0 00-1.06-1.06l-4.5 4.5a.75.75 0 000 1.06z"></path>
                                                                        </svg>Tài liệu
                                                                    </button>
                                                                <?php endif; ?>

                                                            </div>
                                                        </div>
                                                        <div class="lesson-content-details" id="lesson-details-<?php echo $chapter_index . '-' . $lesson_index; ?>" style="display: none;">
                                                            <?php if (!empty($lesson['lessonResources']) && is_array($lesson['lessonResources'])): ?>
                                                                <ul class="resource-list-collapsible"><span class="sub-list-title">Tài liệu đính kèm:</span>
                                                                    <?php foreach ($lesson['lessonResources'] as $resource): ?> <li><svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                                            <path d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5L14 4.5zm-3 0A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4.5h-2z" />
                                                                        </svg><a href="<?php echo htmlspecialchars($file_loader_base_url . "?act=serve_course_resource&resource_id=" . urlencode($resource['resourceID'] ?? '') . "&filename=" . urlencode($resource['resourcePath'] ?? '')); ?>" target="_blank"><?php echo htmlspecialchars($resource['resourceTitle'] ?? 'Tài liệu'); ?></a></li> <?php endforeach; ?>
                                                                </ul>
                                                            <?php endif; ?>
                                                            <?php if (!empty($lesson['lessonVideos']) && is_array($lesson['lessonVideos'])): ?>
                                                                <ul class="video-list-collapsible"><span class="sub-list-title">Video bài giảng:</span>
                                                                    <?php foreach ($lesson['lessonVideos'] as $video): ?> <li><svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                                            <path d="M10.804 8 5 4.633v6.734L10.804 8zm.792-.696a.802.802 0 0 1 0 1.392l-6.363 3.692C4.713 12.69 4 12.345 4 11.692V4.308c0-.653.713-.998 1.233-.696l6.363 3.692z" />
                                                                        </svg> <?php $video_url = $video['videoURL'] ?? '#';
                                                                        if ($video_url !== '#' && !(substr($video_url, 0, 7) === 'http://' || substr($video_url, 0, 8) === 'https://')) {
                                                                            $video_url = htmlspecialchars($file_loader_base_url . "?act=serve_course_video&video_id=" . urlencode($video['videoID'] ?? '') . "&filename=" . urlencode($video['videoURL'] ?? ''));
                                                                        } else {
                                                                            $video_url = htmlspecialchars($video_url);
                                                                        } ?><a href="<?php echo $video_url; ?>" target="_blank"><?php echo htmlspecialchars($video['videoTitle'] ?? 'Video'); ?></a></li> <?php endforeach; ?>
                                                                </ul>
                                                            <?php endif; ?>
                                                            <?php if (empty($lesson['lessonResources']) && empty($lesson['lessonVideos'])): ?> <p style="font-style: italic; color: #777; font-size: 13px;">Không có tài liệu hoặc video.</p> <?php endif; ?>
                                                        </div>
                                                    </li>
                                                <?php endforeach; ?>
                                            <?php else: ?> <li style="font-style: italic; color: #777; padding: 10px 0;">Không có bài học.</li> <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?> <p style="padding: 15px 0;">Nội dung khóa học đang cập nhật.</p> <?php endif; ?>
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
                    <div class="course-aside-preview" role="button" tabindex="0"><svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="24" cy="24" r="24" fill="#fff" fill-opacity="0.88" />
                            <polygon points="20,16 34,24 20,32" fill="#5624d0" />
                        </svg><span>Xem thử</span></div>
                </div>
                <div class="course-price"><?php echo format_price($course_data['price'] ?? 0); ?></div>
                <button id="addToCartBtn" class="course-btn course-btn-cart" type="button">Thêm vào giỏ hàng</button>
                <button id="buyNowBtn" class="course-btn course-btn-buy" type="button">Mua ngay</button>
                <div class="course-guarantee">Bảo đảm hoàn tiền trong 30 ngày</div>
                <ul class="course-feature-list">
                    <li><svg width="18" height="18" fill="none" stroke="#5624d0" stroke-width="2">
                            <rect x="3" y="4" width="12" height="10" rx="2" />
                            <path d="M3 8h12" />
                        </svg>Video theo yêu cầu</li>
                    <li><svg width="18" height="18" fill="none" stroke="#5624d0" stroke-width="2">
                            <rect x="3" y="4" width="12" height="10" rx="2" />
                            <path d="M3 8h12" />
                        </svg>Tài nguyên tải xuống</li>
                    <li><svg width="18" height="18" fill="none" stroke="#5624d0" stroke-width="2">
                            <rect x="2" y="2" width="14" height="14" rx="3" />
                        </svg>Truy cập trên di động & TV</li>
                    <li><svg width="18" height="18" fill="none" stroke="#5624d0" stroke-width="2">
                            <rect x="4" y="2" width="10" height="14" rx="2" />
                        </svg>Truy cập trọn đời</li>
                    <li><svg width="18" height="18" fill="none" stroke="#5624d0" stroke-width="2">
                            <circle cx="9" cy="9" r="7" />
                            <path d="M9 5v4l3 3" />
                        </svg>Chứng nhận hoàn thành</li>
                </ul>
                <a href="#" tabindex="0">Chia sẻ</a> <a href="#" tabindex="0">Tặng khóa học</a> <a href="#" tabindex="0">Áp dụng mã giảm giá</a>
                <form class="course-aside-coupon" onsubmit="event.preventDefault(); alert('Đã áp dụng mã giảm giá!');">
                    <input type="text" class="course-aside-input" placeholder="Nhập mã giảm giá" />
                    <button type="submit" class="course-btn course-btn-apply">Áp dụng</button>
                </form>
                <div class="course-aside-business">
                    <div class="course-aside-business-title">Đào tạo cho 5+ người?</div>
                    <div class="course-aside-business-desc">Cung cấp cho đội nhóm quyền truy cập vào 27,000+ khóa học hàng đầu.</div><button class="course-btn course-btn-business" type="button">Thử gói Doanh nghiệp</button>
                </div>
            </aside>
        </div>
    </div>
    <div class="course-section-main">
        <section class="course-section-block" aria-labelledby="requirements-title">
            <h2 id="requirements-title" class="course-section-title">Yêu cầu</h2>
            <?php if (!empty($course_data['requirements']) && is_array($course_data['requirements'])): ?>
                <ul class="course-req-list">
                    <?php foreach ($course_data['requirements'] as $requirement): ?> <li><svg width="20" height="20" fill="none" stroke="#5624d0" stroke-width="2">
                            <circle cx="10" cy="10" r="9" />
                            <path d="M7 12l3 3 5-5" />
                        </svg><?php echo htmlspecialchars($requirement['requirement'] ?? 'N/A'); ?></li> <?php endforeach; ?>
                </ul>
            <?php else: ?> <p>Không có yêu cầu cụ thể.</p> <?php endif; ?>
        </section>
        <hr class="course-block-divider" />
        <section class="course-section-block" aria-labelledby="description-title">
            <h2 id="description-title" class="course-section-title">Mô tả</h2>
            <?php if (!empty($course_data['description'])): ?> <p><?php echo nl2br(htmlspecialchars($course_data['description'])); ?></p> <?php else: ?> <p>Mô tả khóa học đang cập nhật.</p> <?php endif; ?>
        </section>
        <section class="course-section-block" aria-labelledby="instructors-title">
            <h2 id="instructors-title" class="course-section-title">Giảng viên</h2>
            <div class="course-instructors-list">
                <?php if (!empty($course_data['instructors']) && is_array($course_data['instructors'])): ?>
                    <?php foreach ($course_data['instructors'] as $instructor): ?>
                        <?php
                        $instructor_avatar_url = "https://placehold.co/100x100/EFEFEF/AAAAAA?text=Avatar";
                        $instructor_image_filename = $instructor['profileImage'] ?? 'default_avatar.png';
                        if (!empty($instructor['userID']) && !empty($instructor_image_filename)) {
                            $instructor_avatar_url = htmlspecialchars($file_loader_base_url . "?act=serve_user_image&user_id=" . urlencode($instructor['userID']) . "&image=" . urlencode($instructor_image_filename));
                        }
                        ?>
                        <div class="course-instructor-box">
                            <a href="#" class="course-instructor-name"><?php echo htmlspecialchars(($instructor['firstName'] ?? '') . ' ' . ($instructor['lastName'] ?? 'N/A')); ?></a>
                            <div class="course-instructor-profile">
                                <div class="course-instructor-avt"><img src="<?php echo $instructor_avatar_url; ?>" alt="Ảnh đại diện <?php echo htmlspecialchars(($instructor['firstName'] ?? '') . ' ' . ($instructor['lastName'] ?? '')); ?>" onerror="this.onerror=null;this.src='https://placehold.co/100x100/EFEFEF/AAAAAA?text=No+Image';" /></div>
                                <div class="course-instructor-meta">
                                    <div><span class="inst-star">★ 4.6</span> Đánh giá</div>
                                    <div><svg width="16" height="16" fill="none" stroke="#6a6f73" stroke-width="1.5">
                                            <circle cx="8" cy="8" r="7" />
                                        </svg> 1,2M nhận xét</div>
                                    <div><svg width="16" height="16" fill="none" stroke="#6a6f73" stroke-width="1.5">
                                            <circle cx="8" cy="8" r="7" />
                                            <path d="M4 14l8-8" />
                                        </svg> 4,2M học viên</div>
                                    <div><svg width="16" height="16" fill="none" stroke="#6a6f73" stroke-width="1.5">
                                            <circle cx="8" cy="8" r="7" />
                                            <path d="M8 4v8" />
                                        </svg> 87 khóa học</div>
                                </div>
                            </div>
                            <div class="course-instructor-bio"><?php echo nl2br(htmlspecialchars($instructor['biography'] ?? 'Tiểu sử đang cập nhật.')); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?> <p>Thông tin giảng viên đang cập nhật.</p> <?php endif; ?>
            </div>
        </section>
    </div>
<?php else: ?>
    <div class="course-hero-bg">
        <div class="course-hero-container" style="text-align: center; padding: 50px;">
            <h1>Không tìm thấy khóa học</h1>
            <p>Rất tiếc, không thể tìm thấy thông tin khóa học.</p>
            <p><a href="<?php echo $app_root_url_for_paths; ?>/">Quay lại trang chủ</a></p>
        </div>
    </div>
<?php endif; ?>

    <script>
        function toggleSyllabusSection(button) {
            const content = button.nextElementSibling;
            const isExpanded = button.getAttribute('aria-expanded') === 'true';

            button.setAttribute('aria-expanded', !isExpanded);
            if (!isExpanded) {
                content.classList.add('open');
            } else {
                content.classList.remove('open');
            }
        }

        function toggleLessonDetails(button, contentId) {
            const content = document.getElementById(contentId);
            if (content) {
                const isVisible = content.style.display === 'block';
                content.style.display = isVisible ? 'none' : 'block';
                button.classList.toggle('active', !isVisible);
            }
        }
        const expandAllButton = document.querySelector('.course-content-expand');
        if (expandAllButton) {
            expandAllButton.addEventListener('click', function(e) {
                e.preventDefault();
                const sections = document.querySelectorAll('.course-section-content');
                const toggles = document.querySelectorAll('.course-section-toggle');
                const isAnyOpen = Array.from(sections).some(section => section.classList.contains('open'));

                sections.forEach(section => {
                    if (isAnyOpen) {
                        section.classList.remove('open');
                    } else {
                        section.classList.add('open');
                    }
                });

                toggles.forEach(toggle => {
                    if (isAnyOpen) {
                        toggle.setAttribute('aria-expanded', 'false');
                    } else {
                        toggle.setAttribute('aria-expanded', 'true');
                    }
                });

                this.textContent = isAnyOpen ? 'Mở rộng tất cả' : 'Thu gọn tất cả';
                this.setAttribute('aria-expanded', !isAnyOpen);
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const addToCartButton = document.getElementById('addToCartBtn');

            if (addToCartButton) {
                addToCartButton.addEventListener('click', async function() {
                    if (!IS_USER_LOGGED_IN_JS) {
                        alert('Bạn cần đăng nhập để thêm khóa học vào giỏ hàng.');
                        window.location.href = SIGNIN_URL_JS;
                        return;
                    }

                    if (!CURRENT_COURSE_ID_JS) {
                        alert('Lỗi: Không xác định được ID khóa học.');
                        return;
                    }

                    if (!USER_TOKEN_JS) {
                        alert('Lỗi: Không tìm thấy token xác thực. Vui lòng đăng nhập lại.');
                        window.location.href = SIGNIN_URL_JS;
                        return;
                    }

                    this.disabled = true;
                    this.textContent = 'Đang xử lý...';

                    try {
                        console.log(`${API_BASE_JS}/cart_api.php`)
                        const cartApiResponse = await fetch(`${API_BASE_JS}/cart_api.php`, {
                            method: 'GET',
                            headers: {
                                'Authorization': 'Bearer ' + USER_TOKEN_JS,
                                'Accept': 'application/json'
                            }
                        });


                        if (!cartApiResponse.ok) {
                            const errorText = await cartApiResponse.text();
                            throw new Error(`Lỗi khi lấy giỏ hàng: ${cartApiResponse.status} - ${errorText.substring(0,100)}`);
                        }

                        const cartData = await cartApiResponse.json();
                        let cartId;

                        if (cartData.success && cartData.cartID) {
                            cartId = cartData.cartID;
                        } else if (cartData.success && cartData.data && cartData.cartID) {
                            cartId = cartData.cartID;
                        } else if (cartData.sucesss && cartData.cartID) {
                            cartId = cartData.cartID;
                            console.warn("API 'cart_api.php' responded with typo 'sucesss'.");
                        } else {
                            throw new Error('Không thể lấy hoặc tạo cartID: ' + (cartData.message || 'Phản hồi không hợp lệ từ cart_api.php'));
                        }

                        const addItemPayload = {
                            cartID: cartId,
                            courseID: CURRENT_COURSE_ID_JS,
                            quantity: 1
                        };

                        const addItemResponse = await fetch(`${API_BASE_JS}/cart_item_api.php`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': 'Bearer ' + USER_TOKEN_JS,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(addItemPayload)
                        });

                        if (!addItemResponse.ok) {
                            const errorText = await addItemResponse.text();
                            throw new Error(`Lỗi khi thêm vào giỏ hàng: ${addItemResponse.status} - ${errorText.substring(0,100)}`);
                        }

                        const addItemData = await addItemResponse.json();

                        if (addItemData.status === 'success') {
                            alert('Đã thêm khóa học vào giỏ hàng!');
                            location.reload();
                        } else {
                            throw new Error('Không thể thêm vào giỏ hàng: ' + (addItemData.message || 'Lỗi không xác định từ cart_item_api.php'));
                        }

                    } catch (error) {
                        console.error('Lỗi khi thêm vào giỏ hàng:', error);
                        alert('Đã xảy ra lỗi: ' + error.message);
                    } finally {
                        this.disabled = false;
                        this.textContent = 'Thêm vào giỏ hàng';
                    }
                });
            }
            const buyNowButton = document.getElementById('buyNowBtn');

            if (buyNowButton) {
                buyNowButton.addEventListener('click', async function() {
                    if (!IS_USER_LOGGED_IN_JS) {
                        alert('Bạn cần đăng nhập để mua khóa học.');
                        window.location.href = SIGNIN_URL_JS;
                        return;
                    }

                    if (!CURRENT_COURSE_ID_JS) {
                        alert('Lỗi: Không xác định được ID khóa học.');
                        return;
                    }

                    if (!USER_TOKEN_JS) {
                        alert('Lỗi: Không tìm thấy token xác thực. Vui lòng đăng nhập lại.');
                        window.location.href = SIGNIN_URL_JS;
                        return;
                    }

                    this.disabled = true;
                    this.textContent = 'Đang xử lý...';

                    try {
                        // Lấy thông tin giỏ hàng hoặc tạo mới (tương tự addToCart)
                        const cartApiResponse = await fetch(`${API_BASE_JS}/cart_api.php`, {
                            method: 'GET',
                            headers: {
                                'Authorization': 'Bearer ' + USER_TOKEN_JS,
                                'Accept': 'application/json'
                            }
                        });

                        if (!cartApiResponse.ok) {
                            const errorText = await cartApiResponse.text();
                            throw new Error(`Lỗi khi lấy giỏ hàng: ${cartApiResponse.status} - ${errorText.substring(0, 100)}`);
                        }

                        const cartData = await cartApiResponse.json();
                        let cartId;

                        // Xử lý các trường hợp trả về cartID (bao gồm cả trường hợp có thể có lỗiพิมพ์ 'sucesss')
                        if (cartData.success && cartData.cartID) {
                            cartId = cartData.cartID;
                        } else if (cartData.success && cartData.data && cartData.cartID) { // Thêm kiểm tra cartData.data tồn tại
                            cartId = cartData.data.cartID; // Giả sử cartID nằm trong data nếu cấu trúc là vậy
                        } else if (cartData.cartID) { // Kiểm tra trực tiếp cartID nếu có
                            cartId = cartData.cartID;
                            if (cartData.sucesss) { // Kiểm tra lỗi typo 'sucesss' như trong code gốc
                                console.warn("API 'cart_api.php' responded with typo 'sucesss'.");
                            }
                        }
                        else {
                            // Nếu API tạo cart mới và trả về cartID trong một cấu trúc khác, cần điều chỉnh ở đây
                            // Ví dụ: nếu API trả về { success: true, data: { cartID: "xyz" } }
                            // if (cartData.success && cartData.data && cartData.data.cartID) {
                            //    cartId = cartData.data.cartID;
                            // } else {
                            throw new Error('Không thể lấy hoặc tạo cartID: ' + (cartData.message || 'Phản hồi không hợp lệ từ cart_api.php'));
                            // }
                        }


                        const addItemPayload = {
                            cartID: cartId,
                            courseID: CURRENT_COURSE_ID_JS,
                            quantity: 1
                        };

                        // Thêm sản phẩm vào giỏ hàng (tương tự addToCart)
                        const addItemResponse = await fetch(`${API_BASE_JS}/cart_item_api.php`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': 'Bearer ' + USER_TOKEN_JS,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(addItemPayload)
                        });

                        if (!addItemResponse.ok) {
                            const errorText = await addItemResponse.text();
                            throw new Error(`Lỗi khi thêm vào giỏ hàng: ${addItemResponse.status} - ${errorText.substring(0, 100)}`);
                        }

                        const addItemData = await addItemResponse.json();

                        if (addItemData.status === 'success' || addItemData.success === true) { // Kiểm tra cả 'status' và 'success'
                            alert('Đã thêm khóa học vào giỏ hàng! Đang chuyển hướng đến giỏ hàng...');
                            // Chuyển hướng đến trang giỏ hàng
                            window.location.href = CART_PAGE_URL_JS;
                            // Không cần re-enable nút vì đã chuyển trang
                        } else {
                            throw new Error('Không thể thêm vào giỏ hàng: ' + (addItemData.message || 'Lỗi không xác định từ cart_item_api.php'));
                        }

                    } catch (error) {
                        console.error('Lỗi khi thực hiện mua ngay:', error);
                        alert('Đã xảy ra lỗi: ' + error.message);
                        this.disabled = false; // Kích hoạt lại nút nếu có lỗi
                        this.textContent = 'Mua ngay'; // Khôi phục văn bản nút
                    }
                });
            }
        });
    </script>
    <script src="<?php echo $app_root_url_for_paths; ?>/public/js/course-detail.js"></script>
<?php include('template/footer.php'); ?>