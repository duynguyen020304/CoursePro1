<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http');
$host = $_SERVER['HTTP_HOST'];
$script_path = $_SERVER['SCRIPT_NAME'];

$app_root_path_relative = '';
$path_segments = explode('/', trim($script_path, '/'));

if (!empty($path_segments)) {
    $potential_app_dirs = ['coursepro1', 'app', 'webapp', 'src'];
    $stop_segments = ['admin', 'api', 'controller', 'view', 'includes', 'pages'];

    $current_path_index = 0;
    if (in_array(strtolower($path_segments[0]), $potential_app_dirs, true)) {
        $app_root_path_relative = '/' . $path_segments[0];
    } else {
        $base_segments = [];
        foreach ($path_segments as $index => $segment) {
            if (in_array(strtolower($segment), $stop_segments, true)) {
                if ($index === 0 && count($path_segments) > 1) {
                    $base_segments = [];
                }
                break;
            }
            $base_segments[] = $segment;
            if (isset($path_segments[$index+1]) && strpos($path_segments[$index+1], '.php') !== false && count($base_segments) > 0) {
                if (strpos($segment, '.php') !== false && $index === 0) {
                    $base_segments = [];
                }
                break;
            }
        }
        if (count($base_segments) === 1 && strpos($base_segments[0], '.php') !== false) {
            $app_root_path_relative = '';
        } elseif (!empty($base_segments)) {
            if (end($base_segments) === basename($_SERVER['SCRIPT_FILENAME'])) {
                array_pop($base_segments);
            }
            if (!empty($base_segments)) {
                $app_root_path_relative = '/' . implode('/', $base_segments);
            } else {
                $app_root_path_relative = '';
            }
        } else {
            $app_root_path_relative = '';
        }
    }
}
$app_root_path_relative = rtrim($app_root_path_relative, '/');


define('API_BASE', $protocol . '://' . $host . $app_root_path_relative . '/api');

function callApiForView(string $endpoint, string $method = 'GET', array $payload = []): array
{
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
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
            'timeout'       => 5000
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
    $result   = json_decode($response, true);

    $status_code = 0;
    if (isset($http_response_header) && is_array($http_response_header) && isset($http_response_header[0])) {
        if (preg_match('{HTTP/\S*\s(\d{3})}', $http_response_header[0], $match)) {
            if (isset($match[1])) {
                $status_code = intval($match[1]);
            }
        }
    }
    if ($status_code === 0 && $response !== false && $response !== '') {
        $status_code = 200;
    }
    if ($response === false) $status_code = 0;


    if ($response === false) {
        return [
            'success' => false,
            'message' => 'API request failed. Could not connect or other stream error. URL: ' . $url,
            'data' => null,
            'raw_response' => null,
            'http_status_code' => $status_code
        ];
    }

    if ($result === null && $response !== '' && json_last_error() !== JSON_ERROR_NONE) {
        return [
            'success' => false,
            'message' => 'Invalid API response or failed to decode JSON. JSON Error: ' . json_last_error_msg() . '. Raw response snippet: ' . substr($response, 0, 250),
            'data' => null,
            'raw_response' => $response,
            'http_status_code' => $status_code
        ];
    }

    if ($result === null && ($response === '' || $response === 'null') && json_last_error() === JSON_ERROR_NONE) {
        $isSuccess = ($status_code >= 200 && $status_code < 300);
        return [
            'success' => $isSuccess,
            'message' => $isSuccess ? 'Operation successful with empty or null response.' : 'Empty or null response with non-success status code.',
            'data' => null,
            'raw_response' => $response,
            'http_status_code' => $status_code
        ];
    }


    if (is_array($result)) {
        if (!isset($result['success'])) {
            $result['success'] = ($status_code >= 200 && $status_code < 300);
        }
        if (!isset($result['http_status_code'])) {
            $result['http_status_code'] = $status_code;
        }
    } else {
        $isSuccess = ($status_code >= 200 && $status_code < 300);
        $result = [
            'success' => $isSuccess,
            'message' => $isSuccess ? 'Operation successful.' : 'Operation failed.',
            'data' => $result,
            'http_status_code' => $status_code,
            'raw_response' => $response
        ];
    }
    return $result;
}

$courseResp = callApiForView('course_api.php?isGetAllCourse=true&option=3', 'GET');
$courses    = ($courseResp['success'] && isset($courseResp['data']) && is_array($courseResp['data'])) ? $courseResp['data'] : [];

$controller_path_relative = $app_root_path_relative . '/controller/c_video.php';
$c_video_controller_url = $protocol . '://' . $host . $controller_path_relative;

$c_file_loader_controller_path_relative = $app_root_path_relative . '/controller/c_file_loader.php';
$c_file_loader_controller_url = $protocol . '://' . $host . $c_file_loader_controller_path_relative;


?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1.0" />
    <title>Thêm Nội dung Khóa học - Trang Quản Trị</title>
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="stylesheet" href="css/font_awesome_all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
    <link rel="stylesheet" href="css/admin_style.css" />
    <link rel="stylesheet" href="css/base_dashboard.css" />
    <style>
        .container-fluid {
            padding-top: 20px;
        }

        .form-container {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .section-title {
            margin: 20px 0 15px;
            font-size: 1.25rem;
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .topic-item .card-header {
            cursor: pointer;
        }
        .topic-item .card-header .bi-arrows-move {
            cursor: move;
        }

        #global-message {
            margin-top: 15px;
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            min-width: 300px;
        }
        #global-message .alert {
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
        }

        .spinner-container, .lessons-spinner-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 70px;
        }
        .lesson-item.existing-lesson .badge.bg-success {
            font-size: 0.75em;
        }
        .lesson-attachments { padding-left: 1.5rem; margin-top: 0.5rem;}
        .lesson-attachments li { margin-bottom: 0.25rem; font-size: 0.85em; }
        .lesson-video-info { font-size: 0.85em; color: #555; margin-top: 0.3rem;}
        .resource-item { display: flex; justify-content: space-between; align-items: center; font-size: 0.9em; padding: 0.25rem 0;}
        .lesson-video-info a, .lesson-attachments a {
            text-decoration: none;
            color: #0d6efd;
        }
        .lesson-video-info a:hover, .lesson-attachments a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
<div class="dashboard-container">
    <?php if (file_exists('template/dashboard.php')) include 'template/dashboard.php'; ?>
    <div class="main-content">
        <div class="container-fluid">
            <div class="form-container">
                <h2>Thêm Nội dung Khóa học (Chương & Bài học)</h2>
                <hr>
                <div id="global-message"></div>

                <form id="addContentForm" action="#" method="POST" onsubmit="return false;"> <div class="mb-3">
                        <label for="course_id" class="form-label"><strong>1. Chọn Khóa học:</strong> <span class="text-danger">*</span></label>
                        <select id="course_id" name="course_id" class="form-select" required>
                            <option value="">-- Chọn Khóa học --</option>
                            <?php if (!empty($courses)): ?>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?= htmlspecialchars($course['courseID'] ?? '') ?>">
                                        <?= htmlspecialchars($course['title'] ?? 'N/A') ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>Không có khóa học nào</option>
                                <?php if (!$courseResp['success']): ?>
                                    <option value="" disabled>Lỗi: <?= htmlspecialchars($courseResp['message'] ?? 'Không thể tải khóa học') ?></option>
                                <?php endif; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <h4 class="section-title"><i class="bi bi-folder-plus"></i> 2. Xây dựng nội dung khóa học (Chương)</h4>
                    <div id="chapters-section" style="display: none;"> <button id="btn-add-topic" type="button" class="btn btn-primary mb-3">
                            <i class="bi bi-plus-lg"></i> Thêm Chương Mới
                        </button>
                        <div id="add-topic-form" class="card p-3 mb-4 collapse">
                            <div class="mb-3">
                                <label for="topic-name" class="form-label">Tên Chương</label>
                                <input type="text" id="topic-name" class="form-control" placeholder="Ví dụ: Chương 1 - Giới thiệu">
                            </div>
                            <div class="mb-3">
                                <label for="topic-summary" class="form-label">Mô tả Chương (tùy chọn)</label>
                                <textarea id="topic-summary" class="form-control" rows="2" placeholder="Mô tả ngắn..."></textarea>
                            </div>
                            <button id="save-topic-locally" type="button" class="btn btn-success">Thêm Chương (Vào danh sách)</button>
                        </div>

                        <div id="topics-list-container">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5>Danh sách các Chương:</h5>
                                <span id="chapter-count" class="badge bg-secondary"></span>
                            </div>
                            <div id="topics-list">
                                <p class="text-muted">Vui lòng chọn một khóa học để xem hoặc thêm chương.</p>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="button" id="saveAllContentButton" class="btn btn-primary">
                                <i class="bi bi-save-fill"></i> Lưu Chương Mới Lên Server
                            </button>
                            <a href="course-management.php" class="btn btn-secondary"> <i class="bi bi-arrow-left"></i> Quay lại Quản lý khóa học
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="lessonModal" tabindex="-1" aria-labelledby="lessonModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="lessonModalLabel">Thêm Bài học</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="current-chapter-id-for-lesson">
                <input type="hidden" id="edit-lesson-id">
                <input type="hidden" id="edit-video-id"> <input type="hidden" id="current-course-id-for-lesson">


                <div class="mb-3">
                    <label for="lesson-title" class="form-label">Tên Bài học <span class="text-danger">*</span></label>
                    <input type="text" id="lesson-title" class="form-control" placeholder="Ví dụ: Bài 1 - Lời chào">
                </div>
                <div class="mb-3">
                    <label for="lesson-content" class="form-label">Nội dung/Tóm tắt (tùy chọn)</label>
                    <textarea id="lesson-content" class="form-control" rows="3"></textarea>
                </div>

                <hr>
                <h5>Video cho Bài học</h5>
                <div id="existing-video-info" class="mb-2" style="display:none;">
                    <p class="mb-1"><strong>Video hiện tại:</strong> <span id="current-video-filename"></span></p>
                    <button type="button" class="btn btn-sm btn-outline-danger" id="btn-remove-current-video"><i class="bi bi-trash"></i> Xóa Video hiện tại</button>
                </div>

                <div class="mb-3">
                    <label for="video-source" class="form-label">Nguồn Video <span id="video-required-star" class="text-danger">*</span></label>
                    <select id="video-source" class="form-select">
                        <option value="">-- Chọn nguồn video --</option>
                        <option value="mp4">Tải lên file MP4/Video</option>
                        <option value="youtube">YouTube/Vimeo URL</option>
                    </select>
                </div>
                <div id="video-url-group" class="mb-3" style="display:none;">
                    <label for="video-url" class="form-label">Video URL (VD: YouTube, Vimeo) <span class="text-danger">*</span></label>
                    <input type="text" id="video-url" class="form-control" placeholder="https://www.youtube.com/watch?v=...">
                </div>
                <div id="video-file-group" class="mb-3" style="display:none;">
                    <label for="video-file" class="form-label">Tải lên file Video (mp4, mov, avi, webm) <span class="text-danger">*</span></label>
                    <input type="file" id="video-file" class="form-control" accept=".mp4,.mov,.avi,.webm,.mkv">
                </div>

                <hr>
                <h5>Tài liệu đính kèm</h5>
                <div id="existing-resources-list" class="mb-2">
                </div>
                <div class="mb-3">
                    <label for="lesson-attachments" class="form-label">Thêm tài liệu mới (pdf, zip, doc, ...)</label>
                    <input type="file" id="lesson-attachments" class="form-control" multiple accept=".pdf,.zip,.doc,.docx,.ppt,.pptx,.txt,.xls,.xlsx,.jpg,.jpeg,.png,.gif" />
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button id="save-lesson-server" type="button" class="btn btn-primary" data-action="add">
                    <i class="bi bi-cloud-upload"></i> Thêm Bài học (Lưu vào Server)
                </button>
            </div>
        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
<script src="js/bootstrap.bundle.min.js"></script>
<script>
    const API_BASE_URL = '<?= API_BASE ?>';
    const C_VIDEO_CONTROLLER_URL = '<?= htmlspecialchars_decode($c_video_controller_url) ?>';
    const C_FILE_LOADER_URL = '<?= htmlspecialchars_decode($c_file_loader_controller_url) ?>';
    const LESSON_API_URL = `${API_BASE_URL}/lesson_api.php`;
    const VIDEO_API_URL = `${API_BASE_URL}/video_api.php`;
    const RESOURCE_API_URL = `${API_BASE_URL}/resource_api.php`;
    const CHAPTER_API_URL = `${API_BASE_URL}/chapter_api.php`;


    $(function() {
        const bearerToken = '<?php echo $_SESSION['user']['token'] ?? null; ?>';
        const authHeaders = { 'Accept': 'application/json' };
        if (bearerToken) {
            authHeaders['Authorization'] = `Bearer ${bearerToken}`;
        }

        function showGlobalMessage(message, type = 'info', autoDismissDelay = 5000) {
            const messageId = 'msg-' + Date.now();
            const alertHtml = `<div id="${messageId}" class="alert alert-${type} alert-dismissible fade show" role="alert">
                                        ${message}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>`;
            $('#global-message').html(alertHtml);
            if (autoDismissDelay > 0) {
                setTimeout(() => {
                    $(`#${messageId}`).alert('close');
                }, autoDismissDelay);
            }
        }

        function createTopicCardHtml(topic, isExisting = false) {
            const topicId = topic.chapterID || 'new-topic-' + Date.now();
            const title = $('<div>').text(topic.title || 'Chưa có tiêu đề').html();
            const description = topic.description ? $('<div>').text(topic.description).html() : '';

            const existingClass = isExisting ? 'existing-topic' : '';
            const chapterIdAttr = topic.chapterID ? `data-chapter-id="${topic.chapterID}"` : '';
            const isExistingAttr = isExisting ? 'data-is-existing="true"' : '';

            const displayDescription = description || 'Không có mô tả.';

            return `
                <div class="card mb-2 topic-item ${existingClass}" id="${topicId}"
                     data-topic-name="${encodeURIComponent(topic.title || 'Chưa có tiêu đề')}"
                     data-topic-summary="${encodeURIComponent(topic.description || '')}"
                     ${chapterIdAttr}
                     ${isExistingAttr}>
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-arrows-move me-2" title="Sắp xếp (chưa hoạt động)"></i>
                            <span>${title}</span>
                            ${isExisting ? '<span class="badge bg-success ms-2">Đã lưu</span>' : '<span class="badge bg-warning ms-2">Chưa lưu</span>'}
                        </div>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-add-lesson" title="Thêm Bài học cho chương này"><i class="bi bi-plus-circle"></i> Bài học</button>
                            <button type="button" class="btn btn-sm btn-outline-info btn-edit-topic" title="Sửa Chương" style="display: ${isExisting ? 'inline-block' : 'none'};"><i class="bi bi-pencil-square"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete-topic" title="Xóa Chương này"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                    <div class="card-body p-2" style="display: none;">
                        <p class="card-text small text-muted mb-2 topic-description-display">${displayDescription}</p>
                        <h6>Bài học trong chương:</h6>
                        <ul class="list-group list-group-flush lessons">
                            <li class="list-group-item no-lessons-yet text-muted small" style="display: none;">Chưa có bài học nào cho chương này hoặc đang tải...</li>
                        </ul>
                    </div>
                </div>`;
        }

        function createLessonItemHtml(lesson, videoDataArray, resourcesDataArray, chapterId, courseId) {
            const lessonTitle = $('<div>').text(lesson.title || 'Bài học không có tiêu đề').html();
            const lessonId = lesson.lessonID;
            const lessonContent = lesson.content || '';

            let videoHtml = '<p class="lesson-video-info text-muted small">Không có video.</p>';
            let videoIdAttr = '';
            let videoUrlAttr = '';
            let videoFilenameAttr = '';

            if (videoDataArray && videoDataArray.length > 0) {
                const firstVideo = videoDataArray[0];
                videoIdAttr = firstVideo.videoID ? `data-video-id="${firstVideo.videoID}"` : '';
                videoUrlAttr = firstVideo.url ? `data-video-url="${encodeURIComponent(firstVideo.url)}"` : '';
                videoFilenameAttr = firstVideo.title ? `data-video-title="${encodeURIComponent(firstVideo.title)}"` : '';

                let videoDisplayHtml;
                const videoTitleText = $('<div>').text(firstVideo.title || firstVideo.url).html();

                if (firstVideo.url && (firstVideo.url.startsWith('http://') || firstVideo.url.startsWith('https://'))) {
                    videoDisplayHtml = `<a href="${firstVideo.url}" target="_blank" title="Xem video trên ${firstVideo.url.includes('youtube') ? 'YouTube' : 'Vimeo'}">${videoTitleText}</a>`;
                } else if (firstVideo.url && courseId && chapterId) {
                    const fileUrl = `${C_FILE_LOADER_URL}?act=serve_course_video&course_id=${encodeURIComponent(courseId)}&chapter_id=${encodeURIComponent(chapterId)}&filename=${encodeURIComponent(firstVideo.url)}`;
                    videoDisplayHtml = `<a href="${fileUrl}" target="_blank" title="Xem video đã tải lên">${videoTitleText}</a>`;
                } else {
                    videoDisplayHtml = videoTitleText;
                }

                videoHtml = `<p class="lesson-video-info" ${videoIdAttr} ${videoUrlAttr} ${videoFilenameAttr}>
                                     <i class="bi bi-play-circle-fill me-1"></i>
                                     <strong>Video:</strong> ${videoDisplayHtml}
                                     ${firstVideo.duration ? ` (${formatDuration(firstVideo.duration)})` : ''}
                                   </p>`;
            }

            let resourcesHtml = '';
            if (resourcesDataArray && resourcesDataArray.length > 0) {
                resourcesHtml = '<ul class="lesson-attachments list-unstyled">';
                resourcesDataArray.forEach(resource => {
                    const resourceIdAttr = resource.resourceID ? `data-resource-id="${resource.resourceID}"` : '';
                    const resourcePathAttr = resource.resourcePath ? `data-resource-path="${encodeURIComponent(resource.resourcePath)}"` : '';
                    const resourceTitleAttr = resource.title ? `data-resource-title="${encodeURIComponent(resource.title)}"` : '';
                    const resourceDisplayText = $('<div>').text(resource.title || resource.resourcePath).html();
                    let resourceLinkHtml;

                    if (resource.resourcePath && courseId && chapterId) {
                        const fileUrl = `${C_FILE_LOADER_URL}?act=serve_course_resource&course_id=${encodeURIComponent(courseId)}&chapter_id=${encodeURIComponent(chapterId)}&filename=${encodeURIComponent(resource.resourcePath)}`;
                        resourceLinkHtml = `<a href="${fileUrl}" target="_blank" title="Tải tài liệu">${resourceDisplayText}</a>`;
                    } else {
                        resourceLinkHtml = resourceDisplayText;
                    }
                    resourcesHtml += `<li ${resourceIdAttr} ${resourcePathAttr} ${resourceTitleAttr}><i class="bi bi-paperclip me-1"></i>${resourceLinkHtml}</li>`;
                });
                resourcesHtml += '</ul>';
            } else {
                resourcesHtml = '<p class="small text-muted">Không có tài liệu đính kèm.</p>';
            }

            const statusBadge = lesson.isNew ? '<span class="badge bg-success ms-2">Đã lưu</span>' : '<span class="badge bg-info ms-2">Đã tải</span>';

            return `
                <li class="list-group-item lesson-item existing-lesson d-flex justify-content-between align-items-center" id="lesson-${lessonId}" data-lesson-id="${lessonId}" data-lesson-title="${encodeURIComponent(lesson.title || '')}" data-lesson-content="${encodeURIComponent(lessonContent)}" data-chapter-id="${encodeURIComponent(chapterId || '')}">
                    <div>
                        <i class="bi bi-book-half me-2"></i><strong>${lessonTitle}</strong> ${statusBadge}
                        ${videoHtml}
                        ${resourcesHtml}
                    </div>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-secondary btn-edit-lesson" title="Sửa Bài học"><i class="bi bi-pencil-square"></i></button>
                        <button class="btn btn-sm btn-outline-danger btn-delete-lesson-server" title="Xóa Bài học"><i class="bi bi-trash"></i></button>
                    </div>
                </li>`;
        }


        function formatDuration(totalSeconds) {
            if (isNaN(totalSeconds) || totalSeconds === null || totalSeconds === 0) return "00:00";
            const hours = Math.floor(totalSeconds / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = Math.floor(totalSeconds % 60);
            let durationString = "";
            if (hours > 0) {
                durationString += `${String(hours).padStart(2, '0')}:`;
            }
            durationString += `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            return durationString;
        }

        async function fetchLessonDetailsForEdit(lessonId) {
            try {
                const lessonResp = await fetch(`${LESSON_API_URL}?lessonID=${lessonId}`, { headers: authHeaders });
                if (!lessonResp.ok) throw new Error(`Failed to fetch lesson details: ${lessonResp.status} ${await lessonResp.text()}`);
                const lessonResult = await lessonResp.json();
                if (!lessonResult.success || !lessonResult.data) throw new Error(lessonResult.message || "Lesson data not found.");

                const lessonData = Array.isArray(lessonResult.data) ? lessonResult.data[0] : lessonResult.data;


                const videoResp = await fetch(`${VIDEO_API_URL}?lessonID=${lessonId}`, { headers: authHeaders });
                const videoResult = videoResp.ok ? await videoResp.json() : { success: false, data: [] };

                const resourceResp = await fetch(`${RESOURCE_API_URL}?lessonID=${lessonId}`, { headers: authHeaders });
                const resourceResult = resourceResp.ok ? await resourceResp.json() : { success: false, data: [] };

                return {
                    lesson: lessonData,
                    videos: videoResult.data || [],
                    resources: resourceResult.data || []
                };
            } catch (error) {
                console.error("Error fetching lesson details for edit:", error);
                showGlobalMessage(`Lỗi tải chi tiết bài học để sửa: ${error.message}`, 'danger');
                return null;
            }
        }


        async function fetchLessonDetailsAndRender(lesson, $lessonsListUl, chapterId, courseId) {
            if (!lesson || !lesson.lessonID) return;

            const lessonId = lesson.lessonID;
            const tempLessonItemId = `loading-lesson-${lessonId}`;

            $lessonsListUl.find(`#lesson-${lessonId}`).remove();
            if ($lessonsListUl.find(`#${tempLessonItemId}`).length === 0) {
                $lessonsListUl.append(`<li id="${tempLessonItemId}" class="list-group-item text-muted small"><div class="spinner-border spinner-border-sm me-2" role="status"></div>Đang tải chi tiết bài học: ${$('<div>').text(lesson.title).html()}...</li>`);
            }

            try {
                const [videoResponse, resourceResponse] = await Promise.all([
                    fetch(`${VIDEO_API_URL}?lessonID=${lessonId}`, { headers: authHeaders }),
                    fetch(`${RESOURCE_API_URL}?lessonID=${lessonId}`, { headers: authHeaders })
                ]);

                const videoResult = videoResponse.ok ? await videoResponse.json() : { success: false, data: [], message: `Video API Error ${videoResponse.status}` };
                const resourceResult = resourceResponse.ok ? await resourceResponse.json() : { success: false, data: [], message: `Resource API Error ${resourceResponse.status}`};

                $(`#${tempLessonItemId}`).remove();
                const lessonHtml = createLessonItemHtml(lesson, videoResult.data || [], resourceResult.data || [], chapterId, courseId);
                $lessonsListUl.append(lessonHtml);

            } catch (error) {
                console.error(`Lỗi tải chi tiết cho bài học ${lessonId}:`, error);
                $(`#${tempLessonItemId}`).html(`<i class="bi bi-exclamation-triangle-fill text-danger me-1"></i> Lỗi tải chi tiết cho bài học: ${$('<div>').text(lesson.title).html()}`);
            }
        }

        async function fetchAndDisplayLessonsForChapter(chapterId, $chapterItem) {
            const $lessonsListUl = $chapterItem.find('.lessons');
            const $noLessonsYetMsg = $lessonsListUl.find('.no-lessons-yet');
            const courseId = $('#course_id').val();

            $chapterItem.data('lessons-loading', true);
            $noLessonsYetMsg.hide();
            $lessonsListUl.html('<div class="lessons-spinner-container"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Đang tải bài học...</span></div> <span class="ms-2 text-muted small">Đang tải bài học...</span></div>');

            try {
                const response = await fetch(`${LESSON_API_URL}?chapterID=${chapterId}`, { headers: authHeaders });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({ message: 'Lỗi không xác định khi tải bài học.' }));
                    throw new Error(`Lỗi ${response.status}: ${errorData.message || response.statusText}`);
                }
                const result = await response.json();
                $lessonsListUl.empty();

                if (result.success && result.data && result.data.length > 0) {
                    for (const lesson of result.data) {
                        const lessonChapterId = lesson.chapterID || chapterId;
                        await fetchLessonDetailsAndRender(lesson, $lessonsListUl, lessonChapterId, courseId);
                    }
                    $chapterItem.data('lessons-loaded', true);
                } else if (result.success && (!result.data || result.data.length === 0)) {
                } else {
                    $lessonsListUl.html(`<li class="list-group-item text-danger small">Không thể tải bài học: ${result.message || 'Lỗi không rõ'}</li>`);
                }
            } catch (error) {
                console.error(`Lỗi fetch bài học cho chương ${chapterId}:`, error);
                $lessonsListUl.html(`<li class="list-group-item text-danger small">Lỗi kết nối hoặc xử lý khi tải bài học: ${error.message}</li>`);
            } finally {
                $chapterItem.removeData('lessons-loading');
                updateNoLessonsMessage(chapterId);
            }
        }

        function updateNoLessonsMessage(chapterId) {
            const safeChapterId = String(chapterId).replace(/[!"#$%&'()*+,.\/:;<=>?@[\\\]^`{|}~]/g, "\\$&");
            const $lessonsList = $(`#${safeChapterId} .lessons`);
            if (!$lessonsList.length) return;

            const $noLessonsMsg = $lessonsList.find('.no-lessons-yet');
            if ($lessonsList.find('.lesson-item').length === 0 && $lessonsList.find('.lessons-spinner-container').length === 0) {
                if($noLessonsMsg.length === 0){
                    $lessonsList.append('<li class="list-group-item no-lessons-yet text-muted small">Chưa có bài học nào cho chương này.</li>');
                } else {
                    $noLessonsMsg.text('Chưa có bài học nào cho chương này.').show();
                }
            } else {
                if ($noLessonsMsg.length > 0) $noLessonsMsg.hide();
            }
        }


        function updateChapterCount() {
            const count = $('#topics-list .topic-item').length;
            $('#chapter-count').text(`${count} chương`);
        }

        $('#course_id').on('change', async function() {
            const courseId = $(this).val();
            const $topicsList = $('#topics-list');
            const $chaptersSection = $('#chapters-section');

            $topicsList.html('');
            $('#add-topic-form').collapse('hide');
            $('#topic-name, #topic-summary').val('');

            if (courseId) {
                $chaptersSection.show();
                $topicsList.html('<div class="spinner-container"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Đang tải...</span></div> <span class="ms-2">Đang tải chương...</span></div>');
                showGlobalMessage('Đang tải danh sách chương...', 'info', 2000);

                try {
                    const response = await fetch(`${CHAPTER_API_URL}?courseID=${courseId}`, {
                        method: 'GET',
                        headers: authHeaders
                    });

                    if (!response.ok) {
                        const errorData = await response.json().catch(() => ({ message: 'Lỗi không xác định.' }));
                        throw new Error(`Lỗi ${response.status}: ${errorData.message || response.statusText}`);
                    }

                    const result = await response.json();
                    $topicsList.empty();

                    if (result.success && result.data && result.data.length > 0) {
                        result.data.forEach(chapter => {
                            const chapterHtml = createTopicCardHtml(chapter, true);
                            $topicsList.append(chapterHtml);
                            if(chapter.chapterID) {
                                if ($(`#${chapter.chapterID}`).find('.lessons').length > 0) {
                                    updateNoLessonsMessage(chapter.chapterID);
                                }
                            }
                        });
                        showGlobalMessage(`Đã tải ${result.data.length} chương.`, 'success', 3000);
                    } else if (result.success && (!result.data || result.data.length === 0)) {
                        $topicsList.html('<p class="text-muted">Khóa học này chưa có chương nào. Hãy thêm chương mới.</p>');
                        showGlobalMessage('Khóa học này chưa có chương.', 'info');
                    } else {
                        $topicsList.html('<p class="text-danger">Không thể tải chương: ' + (result.message || 'Lỗi không rõ') + '</p>');
                        showGlobalMessage('Lỗi khi tải chương: ' + (result.message || 'Lỗi không rõ'), 'danger');
                    }
                } catch (error) {
                    console.error('Lỗi fetch chương:', error);
                    $topicsList.html(`<p class="text-danger">Lỗi kết nối hoặc xử lý: ${error.message}</p>`);
                    showGlobalMessage(`Lỗi kết nối: ${error.message}`, 'danger');
                }
            } else {
                $chaptersSection.hide();
                $topicsList.html('<p class="text-muted">Vui lòng chọn một khóa học để xem hoặc thêm chương.</p>');
            }
            updateChapterCount();
        });

        $('#btn-add-topic').on('click', () => {
            $('#add-topic-form').collapse('toggle');
            $('#topic-name').focus();
        });

        $('#save-topic-locally').on('click', () => {
            const name = $('#topic-name').val().trim();
            const summary = $('#topic-summary').val().trim();
            if (!name) {
                showGlobalMessage('Vui lòng nhập tên chương.', 'warning', 3000);
                $('#topic-name').focus();
                return;
            }
            if ($('#topics-list').find('p.text-muted, p.text-danger').length > 0 && $('#topics-list .topic-item').length === 0) {
                $('#topics-list').empty();
            }

            const newTopic = { title: name, description: summary };
            const topicHtml = createTopicCardHtml(newTopic, false);
            $('#topics-list').append(topicHtml);
            const newTopicId = $(topicHtml).attr('id');
            if(newTopicId){
                const $newTopicElement = $('#' + newTopicId.replace(/[!"#$%&'()*+,.\/:;<=>?@[\\\]^`{|}~]/g, "\\$&"));
                if ($newTopicElement.find('.lessons .no-lessons-yet').length === 0) {
                    $newTopicElement.find('.lessons').html('<li class="list-group-item no-lessons-yet text-muted small">Lưu chương để thêm bài học.</li>');
                } else {
                    $newTopicElement.find('.lessons .no-lessons-yet').text('Lưu chương để thêm bài học.').show();
                }
            }


            $('#topic-name, #topic-summary').val('');
            $('#add-topic-form').collapse('hide');
            showGlobalMessage(`Chương "${name}" đã được thêm vào danh sách (chưa lưu lên server).`, 'info', 4000);
            updateChapterCount();
        });

        $('#topics-list').on('click', '.btn-delete-topic', async function() {
            const $topicItem = $(this).closest('.topic-item');
            const topicName = decodeURIComponent($topicItem.data('topic-name'));
            const chapterId = $topicItem.data('chapter-id');

            if (confirm(`Bạn có chắc chắn muốn xóa chương "${topicName}"?` + (chapterId ? "\nHành động này sẽ xóa chương và TẤT CẢ bài học, video, tài liệu bên trong nó khỏi server." : "\nChương này chưa được lưu, sẽ chỉ xóa cục bộ."))) {
                if (chapterId) {
                    showGlobalMessage(`Đang xóa chương "${topicName}" từ server...`, 'info');
                    try {
                        const response = await fetch(`${CHAPTER_API_URL}?id=${chapterId}`, {
                            method: 'DELETE',
                            headers: authHeaders
                        });
                        const result = await response.json();
                        if (response.ok && result.success) {
                            $topicItem.remove();
                            showGlobalMessage(`Chương "${topicName}" và nội dung của nó đã được xóa.`, 'success');
                        } else {
                            showGlobalMessage(`Lỗi xóa chương "${topicName}": ${result.message || 'Lỗi không xác định từ server.'}`, 'danger');
                        }
                    } catch (error) {
                        showGlobalMessage(`Lỗi kết nối khi xóa chương "${topicName}": ${error.message}`, 'danger');
                        console.error("Error deleting chapter:", error);
                    }
                } else {
                    $topicItem.remove();
                    showGlobalMessage(`Chương "${topicName}" đã được xóa cục bộ.`, 'info', 3000);
                }
                if ($('#topics-list .topic-item').length === 0 && $('#topics-list').find('p.text-muted').length === 0) {
                    $('#topics-list').html('<p class="text-muted">Không còn chương nào. Hãy thêm chương mới.</p>');
                }
                updateChapterCount();
            }
        });


        $('#topics-list').on('click', '.btn-add-lesson', function() {
            const $topicItem = $(this).closest('.topic-item');
            const chapterIdForLesson = $topicItem.data('chapter-id');
            const courseId = $('#course_id').val();
            const chapterTitle = decodeURIComponent($topicItem.data('topic-name'));


            if (!chapterIdForLesson) {
                showGlobalMessage('Chương này cần được lưu lên server trước khi thêm bài học.', 'warning', 4000);
                return;
            }
            if (!courseId) {
                showGlobalMessage('Vui lòng chọn khóa học.', 'warning');
                return;
            }


            $('#current-chapter-id-for-lesson').val(chapterIdForLesson);
            $('#current-course-id-for-lesson').val(courseId);
            $('#lessonModalLabel').text('Thêm Bài học cho chương: ' + chapterTitle);
            $('#save-lesson-server').text('Thêm Bài học (Lưu vào Server)').data('action', 'add');
            $('#lessonModal').find('input[type="text"], input[type="file"], textarea, select').val('');
            $('#lesson-content').val('');
            $('#video-source').val('');
            $('#video-file-group, #video-url-group, #existing-video-info').hide();
            $('#existing-resources-list').empty().html('<p class="text-muted small">Không có tài liệu đính kèm.</p>');
            $('#edit-lesson-id, #edit-video-id').val('');
            $('#video-required-star').show();

            $('#lessonModal').modal('show');
        });


        $('#topics-list').on('click', '.card-header', function(e) {
            if ($(e.target).is('button, i, input, .badge') || $(e.target).closest('button, input, .badge').length) {
                return;
            }
            const $chapterItem = $(this).closest('.topic-item');
            const chapterId = $chapterItem.data('chapter-id');
            const $cardBody = $(this).siblings('.card-body');

            $cardBody.slideToggle('fast', function() {
                if ($(this).is(':visible') && chapterId &&
                    $chapterItem.data('lessons-loaded') !== true &&
                    $chapterItem.data('lessons-loading') !== true) {
                    fetchAndDisplayLessonsForChapter(chapterId, $chapterItem);
                } else if ($(this).is(':visible') && chapterId && $chapterItem.data('lessons-loaded') === true) {
                    updateNoLessonsMessage(chapterId);
                }
            });
        });

        $('#video-source').on('change', function() {
            const selectedSource = this.value;
            $('#video-file-group').toggle(selectedSource === 'mp4');
            $('#video-url-group').toggle(selectedSource === 'youtube');
        });

        $('#lessonModal').on('hidden.bs.modal', function() {
            $('#lesson-title, #lesson-content, #video-url, #current-chapter-id-for-lesson, #edit-lesson-id, #edit-video-id, #current-course-id-for-lesson').val('');
            $('#video-file, #lesson-attachments').val(null);
            $('#video-source').val('');
            $('#video-file-group, #video-url-group, #existing-video-info').hide();
            $('#existing-resources-list').empty().html('<p class="text-muted small">Không có tài liệu đính kèm.</p>');
            $('#save-lesson-server').data('action', 'add').html('<i class="bi bi-cloud-upload"></i> Thêm Bài học (Lưu vào Server)');
            $('#lessonModalLabel').text('Thêm Bài học');
            $('#video-required-star').show();
        });

        $('#save-lesson-server').on('click', async function() {
            const $saveButton = $(this);
            const originalButtonText = $saveButton.html();
            const action = $saveButton.data('action');

            const courseId = $('#current-course-id-for-lesson').val() || $('#course_id').val();
            const chapterId = $('#current-chapter-id-for-lesson').val();
            const lessonId = $('#edit-lesson-id').val();
            const lessonTitle = $('#lesson-title').val().trim();
            const lessonContent = $('#lesson-content').val().trim();

            const videoSource = $('#video-source').val();
            const videoUrlInput = $('#video-url').val().trim();
            const videoFile = $('#video-file')[0].files.length > 0 ? $('#video-file')[0].files[0] : null;
            const existingVideoId = $('#edit-video-id').val();

            const newResourceFiles = Array.from($('#lesson-attachments')[0].files);

            let validationError = false;
            if (!lessonTitle) { showGlobalMessage('Vui lòng nhập tiêu đề bài học.', 'warning'); validationError = true; }
            if (!chapterId) { showGlobalMessage('Lỗi: Không tìm thấy ID Chương.', 'danger'); validationError = true; }
            if (!courseId) { showGlobalMessage('Lỗi: Không tìm thấy ID Khóa học.', 'danger'); validationError = true; }

            if (action === 'add') {
                if (!videoSource) { showGlobalMessage('Vui lòng chọn nguồn video.', 'warning'); validationError = true; }
                if (videoSource === 'mp4' && !videoFile) { showGlobalMessage('Vui lòng chọn một file video.', 'warning'); validationError = true; }
                if (videoSource === 'youtube' && !videoUrlInput) { showGlobalMessage('Vui lòng nhập URL video.', 'warning'); validationError = true; }
            } else if (action === 'edit') {
                if (videoSource === 'mp4' && !videoFile && !existingVideoId) {
                    showGlobalMessage('Vui lòng chọn một file video để thay thế hoặc xóa lựa chọn nguồn.', 'warning'); validationError = true;
                }
                if (videoSource === 'youtube' && !videoUrlInput && !existingVideoId) {
                    showGlobalMessage('Vui lòng nhập URL video để thay thế hoặc xóa lựa chọn nguồn.', 'warning'); validationError = true;
                }
            }

            if (validationError) {
                $saveButton.prop('disabled', false).html(originalButtonText);
                return;
            }

            $saveButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Đang lưu...');

            const formData = new FormData();

            if (action === 'add') {
                formData.append('action', 'save_lesson_content');
                formData.append('courseID', courseId);
                formData.append('chapterID', chapterId);
                formData.append('lessonTitle', lessonTitle);
                formData.append('lessonContent', lessonContent);
                formData.append('videoTitle', lessonTitle);

                if (videoSource === 'mp4' && videoFile) {
                    formData.append('video_file', videoFile, videoFile.name);
                } else if (videoSource === 'youtube' && videoUrlInput) {
                    formData.append('video_url', videoUrlInput);
                }
                newResourceFiles.forEach((file) => {
                    formData.append('resource_files[]', file, file.name);
                });
            } else if (action === 'edit') {
                try {
                    const lessonPayload = {
                        lessonID: lessonId,
                        courseID: courseId,
                        chapterID: chapterId,
                        title: lessonTitle,
                        content: lessonContent
                    };
                    const lessonUpdateResponse = await fetch(LESSON_API_URL, {
                        method: 'PUT',
                        headers: { ...authHeaders, 'Content-Type': 'application/json' },
                        body: JSON.stringify(lessonPayload)
                    });
                    const lessonUpdateResult = await lessonUpdateResponse.json();
                    if (!lessonUpdateResponse.ok || !lessonUpdateResult.success) {
                        throw new Error(`Cập nhật thông tin bài học thất bại: ${lessonUpdateResult.message || 'Lỗi không rõ'}`);
                    }
                    showGlobalMessage('Thông tin bài học đã được cập nhật.', 'success', 2500);
                } catch (error) {
                    showGlobalMessage(error.message, 'danger', 8000);
                    $saveButton.prop('disabled', false).html(originalButtonText);
                    return;
                }

                formData.append('lessonID', lessonId);
                formData.append('lessonTitle', lessonTitle);
                formData.append('courseID', courseId);
                formData.append('chapterID', chapterId);


                if ((videoSource === 'mp4' && videoFile) || (videoSource === 'youtube' && videoUrlInput)) {
                    formData.append('action', 'update_lesson_video');
                    if (existingVideoId) {
                        formData.append('existingVideoID', existingVideoId);
                    }
                    if (videoSource === 'mp4' && videoFile) {
                        formData.append('video_file', videoFile, videoFile.name);
                    } else if (videoSource === 'youtube' && videoUrlInput) {
                        formData.append('video_url', videoUrlInput);
                    }
                }

                if (newResourceFiles.length > 0) {
                    if (!formData.has('action')) {
                        formData.append('action', 'add_resources_to_lesson_edit');
                    }
                    newResourceFiles.forEach(file => formData.append('resource_files[]', file, file.name));
                }
            }

            if (formData.has('action')) {
                try {
                    const response = await fetch(C_VIDEO_CONTROLLER_URL, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', ...(bearerToken && {'Authorization': `Bearer ${bearerToken}`}) },
                        body: formData
                    });
                    const result = await response.json();

                    if (response.ok && result.success) {
                        showGlobalMessage(result.message || `Bài học đã được ${action === 'add' ? 'lưu' : 'cập nhật'} thành công.`, 'success');
                        const $chapterElement = $('#' + String(chapterId).replace(/[!"#$%&'()*+,.\/:;<=>?@[\\\]^`{|}~]/g, "\\$&"));
                        if ($chapterElement.length) {
                            await fetchAndDisplayLessonsForChapter(chapterId, $chapterElement);
                            if (action === 'add' || !$chapterElement.find('.card-body').is(':visible')) {
                                $chapterElement.find('.card-body').slideDown('fast');
                            }
                        }
                        $('#lessonModal').modal('hide');
                    } else {
                        let errorMessages = result.message || 'Lỗi không xác định từ server.';
                        if (result.errors && Array.isArray(result.errors)) { errorMessages += "<br>Chi tiết: <ul><li>" + result.errors.join("</li><li>") + "</li></ul>"; }
                        showGlobalMessage(`${action === 'add' ? 'Lưu' : 'Cập nhật'} bài học thất bại: ${errorMessages}`, 'danger', 8000);
                    }
                } catch (error) {
                    console.error(`Lỗi network/JS khi ${action === 'add' ? 'gửi' : 'cập nhật'} dữ liệu bài học:`, error);
                    showGlobalMessage(`Lỗi kết nối hoặc client-side: ${error.message}`, 'danger', 8000);
                } finally {
                    $saveButton.prop('disabled', false).html(originalButtonText);
                }
            } else if (action === 'edit' && !formData.has('action')) {
                const $chapterElement = $('#' + String(chapterId).replace(/[!"#$%&'()*+,.\/:;<=>?@[\\\]^`{|}~]/g, "\\$&"));
                if ($chapterElement.length) {
                    await fetchAndDisplayLessonsForChapter(chapterId, $chapterElement);
                }
                $('#lessonModal').modal('hide');
                $saveButton.prop('disabled', false).html(originalButtonText);
            } else {
                $saveButton.prop('disabled', false).html(originalButtonText);
            }
        });

        $('#topics-list').on('click', '.btn-edit-lesson', async function() {
            const $lessonItem = $(this).closest('.lesson-item');
            const lessonId = $lessonItem.data('lesson-id');
            const chapterId = $lessonItem.data('chapter-id') || $lessonItem.closest('.topic-item').data('chapter-id');
            const courseId = $('#course_id').val();


            if (!lessonId || !chapterId || !courseId) {
                showGlobalMessage('Không thể lấy đủ thông tin để sửa bài học. Thiếu lessonId, chapterId, hoặc courseId.', 'danger');
                console.error("Missing IDs for edit:", {lessonId, chapterId, courseId});
                return;
            }

            showGlobalMessage('Đang tải dữ liệu bài học để chỉnh sửa...', 'info', 0);
            const details = await fetchLessonDetailsForEdit(lessonId);
            $('#global-message .alert').alert('close');

            if (!details || !details.lesson) {
                showGlobalMessage('Không thể tải dữ liệu bài học.', 'danger');
                return;
            }

            const lessonModalLabelText = details.lesson.title ? details.lesson.title : (decodeURIComponent($lessonItem.data('lesson-title')) || 'Không có tiêu đề');


            $('#lessonModalLabel').text('Chỉnh sửa Bài học: ' + lessonModalLabelText);
            $('#save-lesson-server').text('Cập nhật Bài học').data('action', 'edit');

            $('#edit-lesson-id').val(lessonId);
            $('#current-chapter-id-for-lesson').val(chapterId);
            $('#current-course-id-for-lesson').val(courseId);

            $('#lesson-title').val(details.lesson.title);
            $('#lesson-content').val(details.lesson.content || '');

            $('#video-source').val('');
            $('#video-file').val(null);
            $('#video-url').val('');
            $('#video-file-group, #video-url-group').hide();
            $('#existing-video-info').hide();
            $('#edit-video-id').val('');
            $('#video-required-star').hide();

            if (details.videos && details.videos.length > 0) {
                const video = details.videos[0];
                $('#existing-video-info').show();
                $('#current-video-filename').text(`${video.title || 'Video không tên'} (${video.url && video.url.startsWith('http') ? 'URL' : 'File'})`);
                $('#edit-video-id').val(video.videoID);

                if (video.url) {
                    if (video.url.toLowerCase().includes("youtube.com/") || video.url.toLowerCase().includes("youtu.be/") || video.url.toLowerCase().includes("vimeo.com/")) {
                        $('#video-source').val('youtube');
                        $('#video-url').val(video.url);
                        $('#video-url-group').show();
                    } else if (!video.url.startsWith('http')) {
                        $('#video-source').val('mp4');
                        $('#video-file-group').show();
                    }
                }
            } else {
                $('#video-required-star').show();
            }


            const $resourcesList = $('#existing-resources-list').empty();
            if (details.resources && details.resources.length > 0) {
                details.resources.forEach(res => {
                    $resourcesList.append(`
                        <div class="resource-item" id="resource-item-${res.resourceID}" data-resource-id="${res.resourceID}" data-resource-path="${encodeURIComponent(res.resourcePath || '')}">
                            <span><i class="bi bi-paperclip"></i> ${$('<div>').text(res.title || res.resourcePath).html()}</span>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-modal-delete-resource" title="Xóa tài liệu này"><i class="bi bi-trash"></i></button>
                        </div>
                    `);
                });
            } else {
                $resourcesList.html('<p class="text-muted small">Không có tài liệu đính kèm.</p>');
            }

            $('#lessonModal').modal('show');
        });

        $('#topics-list').on('click', '.btn-delete-lesson-server', async function() {
            const $lessonItem = $(this).closest('.lesson-item');
            const lessonId = $lessonItem.data('lesson-id');
            const lessonTitle = decodeURIComponent($lessonItem.data('lesson-title') || 'Bài học này');
            const chapterId = $lessonItem.data('chapter-id') || $lessonItem.closest('.topic-item').data('chapter-id');


            if (confirm(`Bạn có chắc chắn muốn xóa bài học "${lessonTitle}" vĩnh viễn khỏi server? Hành động này không thể hoàn tác và sẽ xóa cả video, tài liệu liên quan.`)) {
                showGlobalMessage(`Đang xóa bài học "${lessonTitle}"...`, 'info');
                try {
                    const response = await fetch(LESSON_API_URL, {
                        method: 'DELETE',
                        headers: { ...authHeaders, 'Content-Type': 'application/json' },
                        body: JSON.stringify({ lessonID: lessonId })
                    });

                    const result = await response.json();

                    if (response.ok && result.success) {
                        $lessonItem.remove();
                        showGlobalMessage(`Bài học "${lessonTitle}" đã được xóa.`, 'success');
                        if (chapterId) updateNoLessonsMessage(chapterId);
                    } else {
                        showGlobalMessage(`Lỗi xóa bài học: ${result.message || 'Lỗi không xác định từ server.'}`, 'danger');
                    }
                } catch (error) {
                    showGlobalMessage(`Lỗi kết nối khi xóa bài học: ${error.message}`, 'danger');
                    console.error("Error deleting lesson:", error);
                }
            }
        });

        $('#lessonModal').on('click', '.btn-modal-delete-resource', async function() {
            const $resourceItemDiv = $(this).closest('.resource-item');
            const resourceId = $resourceItemDiv.data('resource-id');
            const resourceName = $resourceItemDiv.find('span').text().trim();
            const resourcePath = decodeURIComponent($resourceItemDiv.data('resource-path') || '');


            if (confirm(`Bạn có chắc chắn muốn xóa tài liệu "${resourceName}"?`)) {
                if (!resourceId) {
                    showGlobalMessage('Không thể xác định ID tài liệu.', 'warning');
                    return;
                }
                showGlobalMessage(`Đang xóa tài liệu "${resourceName}"...`, 'info');
                try {
                    const response = await fetch(`${RESOURCE_API_URL}?id=${resourceId}&filePath=${encodeURIComponent(resourcePath)}`, {
                        method: 'DELETE',
                        headers: authHeaders
                    });
                    const result = await response.json();
                    if (response.ok && result.success) {
                        $resourceItemDiv.remove();
                        showGlobalMessage(`Tài liệu "${resourceName}" đã được xóa.`, 'success');
                        if ($('#existing-resources-list .resource-item').length === 0) {
                            $('#existing-resources-list').html('<p class="text-muted small">Không có tài liệu đính kèm.</p>');
                        }
                    } else {
                        showGlobalMessage(`Lỗi xóa tài liệu: ${result.message || 'Lỗi server.'}`, 'danger');
                    }
                } catch (error) {
                    showGlobalMessage(`Lỗi kết nối khi xóa tài liệu: ${error.message}`, 'danger');
                }
            }
        });

        $('#btn-remove-current-video').on('click', async function() {
            const videoId = $('#edit-video-id').val();
            const videoFileName = $('#current-video-filename').text();
            const lessonId = $('#edit-lesson-id').val();

            if (confirm(`Bạn có chắc chắn muốn xóa video "${videoFileName}"? Hành động này sẽ xóa video khỏi bài học.`)) {
                if (!videoId) {
                    showGlobalMessage('Không tìm thấy ID video hiện tại.', 'warning');
                    return;
                }
                showGlobalMessage(`Đang xóa video "${videoFileName}"...`, 'info');
                try {
                    const response = await fetch(VIDEO_API_URL, {
                        method: 'DELETE',
                        headers: { ...authHeaders, 'Content-Type': 'application/json' },
                        body: JSON.stringify({ videoID: videoId })
                    });
                    const result = await response.json();

                    if (response.ok && result.success) {
                        $('#existing-video-info').hide();
                        $('#edit-video-id').val('');
                        $('#current-video-filename').text('');
                        $('#video-source').val('');
                        $('#video-file').val(null);
                        $('#video-url').val('');
                        $('#video-file-group, #video-url-group').hide();
                        showGlobalMessage(`Video "${videoFileName}" đã được xóa. Bạn có thể tải lên video mới.`, 'success');
                        $('#video-required-star').show();

                        const chapterId = $('#current-chapter-id-for-lesson').val();
                        const courseId = $('#current-course-id-for-lesson').val();
                        const $chapterElement = $('#' + String(chapterId).replace(/[!"#$%&'()*+,.\/:;<=>?@[\\\]^`{|}~]/g, "\\$&"));
                        if (lessonId && $chapterElement.length) {
                            const lessonToRefresh = await fetchLessonDetailsForEdit(lessonId);
                            if(lessonToRefresh && lessonToRefresh.lesson){
                                await fetchLessonDetailsAndRender(lessonToRefresh.lesson, $chapterElement.find('.lessons'), chapterId, courseId);
                            }
                        }

                    } else {
                        showGlobalMessage(`Lỗi xóa video: ${result.message || 'Lỗi server.'}`, 'danger');
                    }
                } catch (error) {
                    showGlobalMessage(`Lỗi kết nối khi xóa video: ${error.message}`, 'danger');
                }
            }
        });


        $('#saveAllContentButton').on('click', async function() {
            const courseId = $('#course_id').val();
            const $saveButton = $(this);
            const originalButtonText = $saveButton.html();

            if (!courseId) {
                showGlobalMessage('Vui lòng chọn một khóa học trước khi lưu chương.', 'warning', 3000);
                return;
            }

            const $newTopics = $('#topics-list .topic-item:not([data-is-existing="true"]):not([data-chapter-id])');

            if ($newTopics.length === 0) {
                showGlobalMessage('Không có chương mới nào (chưa được lưu lên server) để thực hiện.', 'info', 4000);
                return;
            }

            const topicsToSaveData = [];
            $newTopics.each(function(topicIndex) {
                const $topicEl = $(this);
                topicsToSaveData.push({
                    localId: $topicEl.attr('id'),
                    data: {
                        title: decodeURIComponent($topicEl.data('topic-name')),
                        description: decodeURIComponent($topicEl.data('topic-summary'))
                    }
                });
            });

            $saveButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Đang lưu chương...');
            showGlobalMessage(`Bắt đầu lưu ${topicsToSaveData.length} chương mới...`, 'info');

            let successfulSaves = 0, failedSaves = 0;
            let resultsSummary = `<strong class="mt-2 d-block">Kết quả lưu các chương mới (CourseID: ${courseId}):</strong><ul>`;

            for (const item of topicsToSaveData) {
                const payload = { courseID: courseId, title: item.data.title, description: item.data.description };
                try {
                    const response = await fetch(CHAPTER_API_URL, {
                        method: 'POST',
                        headers: { ...authHeaders, 'Content-Type': 'application/json'},
                        body: JSON.stringify(payload)
                    });
                    const result = await response.json();

                    if (response.ok && result.success) {
                        resultsSummary += `<li class="text-success">Chương "${payload.title}": Lưu thành công. ${result.message || ''} (ID: ${result.data?.chapterID})</li>`;
                        successfulSaves++;
                        const safeLocalId = String(item.localId).replace(/[!"#$%&'()*+,.\/:;<=>?@[\\\]^`{|}~]/g, "\\$&");
                        const $savedTopicEl = $('#' + safeLocalId);

                        if (result.data && result.data.chapterID) {
                            const newChapterId = result.data.chapterID;
                            $savedTopicEl.attr('id', newChapterId);
                            $savedTopicEl.attr('data-chapter-id', newChapterId);
                            $savedTopicEl.data('chapter-id', newChapterId);

                            if ($('#current-chapter-id-for-lesson').val() === item.localId) {
                                $('#current-chapter-id-for-lesson').val(newChapterId);
                            }
                            $savedTopicEl.find('.btn-add-lesson').prop('disabled', false);
                            $savedTopicEl.find('.btn-edit-topic').show();
                            updateNoLessonsMessage(newChapterId);
                        }
                        $savedTopicEl.attr('data-is-existing', 'true').addClass('existing-topic');
                        $savedTopicEl.find('.badge.bg-warning').removeClass('bg-warning').addClass('bg-success').text('Đã lưu');
                    } else {
                        failedSaves++;
                        resultsSummary += `<li class="text-danger">Chương "${payload.title}": Lưu thất bại. ${result.message || 'Lỗi không xác định.'} (Code: ${response.status})</li>`;
                    }
                } catch (error) {
                    failedSaves++;
                    resultsSummary += `<li class="text-danger">Chương "${payload.title}": Lỗi kết nối/JS. ${error.message}</li>`;
                }
            }
            resultsSummary += '</ul>';

            if (successfulSaves === 0 && failedSaves === 0) {
                showGlobalMessage('Không có chương mới nào được xử lý.', 'info');
            } else if (failedSaves === 0 && successfulSaves > 0) {
                showGlobalMessage(`Tất cả ${successfulSaves} chương mới đã lưu thành công! <br>` + resultsSummary, 'success', 7000);
            } else {
                showGlobalMessage(`Hoàn tất. Thành công: ${successfulSaves}. Thất bại: ${failedSaves}.<br>` + resultsSummary, failedSaves > 0 ? 'warning' : 'success', 10000);
            }
            $saveButton.prop('disabled', false).html(originalButtonText);
            updateChapterCount();
        });

        if (!$('#course_id').val()) {
            $('#chapters-section').hide();
            $('#topics-list').html('<p class="text-muted">Vui lòng chọn một khóa học để xem hoặc thêm chương.</p>');
        }
        updateChapterCount();
    });
</script>
</body>
</html>
