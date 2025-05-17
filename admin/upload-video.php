<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- Start: Determine API_BASE ---
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http');
$host = $_SERVER['HTTP_HOST'];
$script_path = $_SERVER['SCRIPT_NAME']; // e.g., /CoursePro1/admin/upload-video.php or /admin/upload-video.php

// Try to find the application root more reliably
$app_root_path_relative = '';
$path_segments = explode('/', trim($script_path, '/'));

if (!empty($path_segments)) {
    // Common first directory names for web apps if not in root
    $potential_app_dirs = ['coursepro1', 'app', 'webapp', 'src']; // Add your project's root folder name if it's in a subfolder of htdocs/www

    // Check if the first segment is a known app directory or if it's directly in admin/api etc.
    if (in_array(strtolower($path_segments[0]), $potential_app_dirs, true) && count($path_segments) > 1) {
        $app_root_path_relative = '/' . $path_segments[0];
    } elseif (count($path_segments) > 0 && !in_array(strtolower($path_segments[0]), ['admin', 'api', 'controller', 'view', 'includes'])) {
        // If the first segment is not a typical functional subdir, it might be the app root itself
        $app_root_path_relative = '/' . $path_segments[0];
    } else {
        // If script is like /admin/file.php, app root is likely empty (meaning domain root)
        // Or if it's /CoursePro1/admin/file.php, $app_root_path_relative should be /CoursePro1
        $path_before_admin_api = $script_path;
        $markers = ['/admin/', '/api/', '/controller/', '/views/', '/pages/'];
        foreach($markers as $marker){
            $pos = strripos($script_path, $marker);
            if($pos !== false){
                $path_before_admin_api = substr($script_path, 0, $pos);
                break;
            }
        }
        if ($path_before_admin_api === $script_path && strpos($script_path, '/') === 0 && count($path_segments) > 1) {
            $app_root_path_relative = '/' . $path_segments[0];
        } else if ($path_before_admin_api !== $script_path) {
            $app_root_path_relative = rtrim($path_before_admin_api, '/');
        } else if (strpos($script_path, '/') !== 0 && count($path_segments) > 1 && !in_array(strtolower($path_segments[0]), ['admin', 'api', 'controller', 'view'])) {
            $app_root_path_relative = $path_segments[0];
        }
    }
}
// Ensure it starts with a slash if not empty, and remove trailing slash
if (!empty($app_root_path_relative) && $app_root_path_relative[0] !== '/') {
    $app_root_path_relative = '/' . $app_root_path_relative;
}
$app_root_path_relative = rtrim($app_root_path_relative, '/');


define('API_BASE', $protocol . '://' . $host . $app_root_path_relative . '/api');
// --- End: Determine API_BASE ---


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
            'timeout'       => 30
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

    if ($response === false) {
        return [
            'success' => false,
            'message' => 'API request failed. Could not connect or other stream error. URL: ' . $url,
            'data' => null,
            'raw_response' => null,
            'http_status_code' => 0
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

    if ($result === null && $response === '') {
        $isSuccess = ($status_code >= 200 && $status_code < 300);
        return [
            'success' => $isSuccess,
            'message' => $isSuccess ? 'Operation successful with empty response.' : 'Empty response with non-success status code.',
            'data' => null,
            'raw_response' => '',
            'http_status_code' => $status_code
        ];
    }

    if (is_array($result)) {
        if (!isset($result['success'])) {
            $result['success'] = ($status_code >= 200 && $status_code < 300);
        }
        $result['http_status_code'] = $status_code;
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

$courseResp = callApiForView('course_api.php', 'GET');
$courses    = ($courseResp['success'] && isset($courseResp['data']) && is_array($courseResp['data'])) ? $courseResp['data'] : [];

$controller_path_relative = $app_root_path_relative . '/controller/c_video.php';
$c_video_controller_url = $protocol . '://' . $host . $controller_path_relative;

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
            min-height: 70px; /* Adjusted height */
        }
        .lesson-item.existing-lesson .badge.bg-success {
            font-size: 0.75em;
        }
        .lesson-attachments { padding-left: 1.5rem; margin-top: 0.5rem;}
        .lesson-attachments li { margin-bottom: 0.25rem; font-size: 0.85em; }
        .lesson-video-info { font-size: 0.85em; color: #555; margin-top: 0.3rem;}
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
                <div class="mb-3">
                    <label for="lesson-title" class="form-label">Tên Bài học <span class="text-danger">*</span></label>
                    <input type="text" id="lesson-title" class="form-control" placeholder="Ví dụ: Bài 1 - Lời chào">
                </div>
                <div class="mb-3">
                    <label class="form-label">Nội dung/Tóm tắt (tùy chọn)</label>
                    <textarea id="lesson-content" class="form-control" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label for="video-source" class="form-label">Nguồn Video <span class="text-danger">*</span></label>
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
                <label class="form-label">Thời lượng Video (HH:MM:SS - tùy chọn)</label>
                <div class="d-flex gap-2 mb-3">
                    <input type="number" id="video-hh" class="form-control" placeholder="HH" min="0" max="99" value="00" />
                    <input type="number" id="video-mm" class="form-control" placeholder="MM" min="0" max="59" value="00" />
                    <input type="number" id="video-ss" class="form-control" placeholder="SS" min="0" max="59" value="00" />
                </div>
                <div class="mb-3">
                    <label for="lesson-attachments" class="form-label">Tài liệu đính kèm (pdf, zip, doc, ...)</label>
                    <input type="file" id="lesson-attachments" class="form-control" multiple accept=".pdf,.zip,.doc,.docx,.ppt,.pptx,.txt,.xls,.xlsx,.jpg,.jpeg,.png,.gif" />
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button id="save-lesson-server" type="button" class="btn btn-primary">
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
    const LESSON_API_URL = `${API_BASE_URL}/lesson_api.php`;
    const VIDEO_API_URL = `${API_BASE_URL}/video_api.php`;
    const RESOURCE_API_URL = `${API_BASE_URL}/resource_api.php`;

    $(function() {
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

            return `
                <div class="card mb-2 topic-item ${existingClass}" id="${topicId}"
                     data-topic-name="${title}"
                     data-topic-summary="${description}"
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
                      <button type="button" class="btn btn-sm btn-outline-danger btn-delete-topic" title="Xóa Chương này (cục bộ)"><i class="bi bi-trash"></i></button>
                    </div>
                  </div>
                  <div class="card-body p-2" style="display: none;">
                    <p class="card-text small text-muted mb-2">${description || 'Không có mô tả.'}</p>
                    <h6>Bài học trong chương:</h6>
                    <ul class="list-group list-group-flush lessons">
                        <li class="list-group-item no-lessons-yet text-muted small" style="display: none;">Chưa có bài học nào cho chương này hoặc đang tải...</li>
                    </ul>
                  </div>
                </div>`;
        }

        function createLessonItemHtml(lesson, videoDataArray, resourcesDataArray) {
            const lessonTitle = $('<div>').text(lesson.title || 'Bài học không có tiêu đề').html();
            const lessonId = lesson.lessonID;
            let videoHtml = '<p class="lesson-video-info text-muted small">Không có video.</p>';
            if (videoDataArray && videoDataArray.length > 0) {
                const firstVideo = videoDataArray[0]; // Assuming one video per lesson for now, or display first
                videoHtml = `<p class="lesson-video-info">
                                    <i class="bi bi-play-circle-fill me-1"></i>
                                    <strong>Video:</strong> ${$('<div>').text(firstVideo.title || firstVideo.url).html()}
                                    ${firstVideo.duration ? ` (${formatDuration(firstVideo.duration)})` : ''}
                                 </p>`;
            }

            let resourcesHtml = '';
            if (resourcesDataArray && resourcesDataArray.length > 0) {
                resourcesHtml = '<ul class="lesson-attachments list-unstyled">';
                resourcesDataArray.forEach(resource => {
                    resourcesHtml += `<li><i class="bi bi-paperclip me-1"></i>${$('<div>').text(resource.title || resource.resourcePath).html()}</li>`;
                });
                resourcesHtml += '</ul>';
            } else {
                resourcesHtml = '<p class="small text-muted">Không có tài liệu đính kèm.</p>';
            }

            // Determine if lesson is newly saved or fetched (for badge, not fully implemented here yet)
            const statusBadge = lesson.isNew ? '<span class="badge bg-success ms-2">Đã lưu</span>' : '<span class="badge bg-info ms-2">Đã tải</span>';


            return `
                <li class="list-group-item lesson-item existing-lesson" id="${lessonId}" data-lesson-id="${lessonId}">
                  <div>
                    <i class="bi bi-book-half me-2"></i><strong>${lessonTitle}</strong> ${statusBadge}
                    ${videoHtml}
                    ${resourcesHtml}
                  </div>
                  <div>
                    <button class="btn btn-sm btn-outline-secondary btn-edit-lesson" title="Sửa Bài học (chưa hoạt động)"><i class="bi bi-pencil-square"></i></button>
                    <button class="btn btn-sm btn-outline-danger btn-delete-lesson-server" title="Xóa Bài học (từ server - chưa hoạt động)"><i class="bi bi-trash"></i></button>
                  </div>
                </li>`;
        }

        function formatDuration(totalSeconds) {
            if (isNaN(totalSeconds) || totalSeconds === null || totalSeconds === 0) return "00:00";
            const hours = Math.floor(totalSeconds / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = totalSeconds % 60;
            let durationString = "";
            if (hours > 0) {
                durationString += `${String(hours).padStart(2, '0')}:`;
            }
            durationString += `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            return durationString;
        }


        async function fetchLessonDetailsAndRender(lesson, $lessonsListUl) {
            if (!lesson || !lesson.lessonID) return;

            const lessonId = lesson.lessonID;
            const tempLessonItemId = `loading-lesson-${lessonId}`;
            $lessonsListUl.append(`<li id="${tempLessonItemId}" class="list-group-item text-muted small"><div class="spinner-border spinner-border-sm me-2" role="status"></div>Đang tải chi tiết bài học: ${$('<div>').text(lesson.title).html()}...</li>`);

            try {
                const headers = { 'Accept': 'application/json' };
                <?php if (isset($_SESSION['user']['token'])): ?>
                headers['Authorization'] = 'Bearer <?= $_SESSION['user']['token'] ?>';
                <?php endif; ?>

                const [videoResponse, resourceResponse] = await Promise.all([
                    fetch(`${VIDEO_API_URL}?lessonID=${lessonId}`, { headers }),
                    fetch(`${RESOURCE_API_URL}?lessonID=${lessonId}`, { headers })
                ]);

                const videoResult = videoResponse.ok ? await videoResponse.json() : { success: false, data: [], message: `Video API Error ${videoResponse.status}` };
                const resourceResult = resourceResponse.ok ? await resourceResponse.json() : { success: false, data: [], message: `Resource API Error ${resourceResponse.status}`};

                $(`#${tempLessonItemId}`).remove(); // Remove temporary loading item

                const lessonHtml = createLessonItemHtml(lesson, videoResult.data || [], resourceResult.data || []);
                $lessonsListUl.append(lessonHtml);

            } catch (error) {
                console.error(`Lỗi tải chi tiết cho bài học ${lessonId}:`, error);
                $(`#${tempLessonItemId}`).html(`<i class="bi bi-exclamation-triangle-fill text-danger me-1"></i> Lỗi tải chi tiết cho bài học: ${$('<div>').text(lesson.title).html()}`);
            }
        }

        async function fetchAndDisplayLessonsForChapter(chapterId, $chapterItem) {
            const $lessonsListUl = $chapterItem.find('.lessons');
            const $noLessonsYetMsg = $lessonsListUl.find('.no-lessons-yet');

            $chapterItem.data('lessons-loading', true);
            $noLessonsYetMsg.hide(); // Hide initial message
            $lessonsListUl.html('<div class="lessons-spinner-container"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Đang tải bài học...</span></div> <span class="ms-2 text-muted small">Đang tải bài học...</span></div>');

            try {
                const headers = { 'Accept': 'application/json' };
                <?php if (isset($_SESSION['user']['token'])): ?>
                headers['Authorization'] = 'Bearer <?= $_SESSION['user']['token'] ?>';
                <?php endif; ?>

                const response = await fetch(`${LESSON_API_URL}?chapterID=${chapterId}`, { headers });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({ message: 'Lỗi không xác định khi tải bài học.' }));
                    throw new Error(`Lỗi ${response.status}: ${errorData.message || response.statusText}`);
                }
                const result = await response.json();
                $lessonsListUl.empty(); // Clear spinner

                if (result.success && result.data && result.data.length > 0) {
                    for (const lesson of result.data) {
                        await fetchLessonDetailsAndRender(lesson, $lessonsListUl); // Wait for each lesson's details
                    }
                    $chapterItem.data('lessons-loaded', true);
                } else if (result.success && (!result.data || result.data.length === 0)) {
                    $noLessonsYetMsg.text('Chương này chưa có bài học nào.').show();
                } else {
                    $lessonsListUl.html(`<li class="list-group-item text-danger small">Không thể tải bài học: ${result.message || 'Lỗi không rõ'}</li>`);
                }
            } catch (error) {
                console.error(`Lỗi fetch bài học cho chương ${chapterId}:`, error);
                $lessonsListUl.html(`<li class="list-group-item text-danger small">Lỗi kết nối hoặc xử lý khi tải bài học: ${error.message}</li>`);
            } finally {
                $chapterItem.removeData('lessons-loading');
                updateNoLessonsMessage(chapterId); // Final check for the message
            }
        }

        function updateNoLessonsMessage(chapterId) {
            const safeChapterId = String(chapterId).replace(/[!"#$%&'()*+,.\/:;<=>?@[\\\]^`{|}~]/g, "\\$&");
            const $lessonsList = $(`#${safeChapterId} .lessons`);
            const $noLessonsMsg = $lessonsList.find('.no-lessons-yet');
            if ($lessonsList.find('.lesson-item').length === 0 && $lessonsList.find('.lessons-spinner-container').length === 0) {
                if($noLessonsMsg.length === 0){ // if message element doesn't exist, create it
                    $lessonsList.append('<li class="list-group-item no-lessons-yet text-muted small">Chưa có bài học nào cho chương này.</li>');
                } else {
                    $noLessonsMsg.text('Chưa có bài học nào cho chương này.').show();
                }
            } else {
                $noLessonsMsg.hide();
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
                showGlobalMessage('Đang tải danh sách chương...', 'info');

                try {
                    const headers = { 'Accept': 'application/json' };
                    <?php if (isset($_SESSION['user']['token'])): ?>
                    headers['Authorization'] = 'Bearer <?= $_SESSION['user']['token'] ?>';
                    <?php endif; ?>

                    const response = await fetch(`${API_BASE_URL}/chapter_api.php?courseID=${courseId}`, {
                        method: 'GET',
                        headers: headers
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
                                updateNoLessonsMessage(chapter.chapterID);
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
                showGlobalMessage('Vui lòng chọn một khóa học.', 'info');
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
                updateNoLessonsMessage(newTopicId);
            }

            $('#topic-name, #topic-summary').val('');
            $('#add-topic-form').collapse('hide');
            showGlobalMessage(`Chương "${name}" đã được thêm vào danh sách (chưa lưu lên server).`, 'info', 4000);
            updateChapterCount();
        });

        $('#topics-list').on('click', '.btn-add-lesson', function() {
            const $topicItem = $(this).closest('.topic-item');
            const chapterIdForLesson = $topicItem.data('chapter-id') || $topicItem.attr('id');

            if (!chapterIdForLesson) {
                showGlobalMessage('Không thể xác định ID của chương. Chương có thể cần được lưu trước.', 'warning');
                return;
            }
            if (!$topicItem.data('is-existing') && !$topicItem.data('chapter-id')) {
                showGlobalMessage('Vui lòng lưu chương này lên server trước khi thêm bài học.', 'warning', 4000);
                return;
            }

            $('#current-chapter-id-for-lesson').val(chapterIdForLesson); // Changed from current-topic-id...
            $('#lessonModalLabel').text('Thêm Bài học cho chương: ' + $topicItem.data('topic-name'));

            $('#lessonModal').find('input[type="text"], input[type="file"], input[type="number"], textarea, select').val('');
            $('#video-hh, #video-mm, #video-ss').val('00');
            $('#video-source').val('');
            $('#video-file-group, #video-url-group').hide();
            $('#lessonModal').removeData('lessonId');

            $('#lessonModal').modal('show');
        });

        $('#topics-list').on('click', '.btn-delete-topic', function() {
            const $topicItem = $(this).closest('.topic-item');
            const topicName = $topicItem.data('topic-name');
            if (confirm(`Bạn có chắc chắn muốn xóa chương "${topicName}" khỏi danh sách này (chỉ xóa cục bộ)?`)) {
                $topicItem.remove();
                showGlobalMessage(`Chương "${topicName}" đã được xóa cục bộ.`, 'info', 3000);
                if ($('#topics-list .topic-item').length === 0) {
                    $('#topics-list').html('<p class="text-muted">Không còn chương nào. Hãy thêm chương mới.</p>');
                }
                updateChapterCount();
            }
        });

        $('#topics-list').on('click', '.card-header', function(e) {
            if ($(e.target).is('button, i, input') || $(e.target).closest('button, input').length) {
                return;
            }
            const $chapterItem = $(this).closest('.topic-item');
            const chapterId = $chapterItem.data('chapter-id'); // Only for existing, server-saved chapters

            $(this).siblings('.card-body').slideToggle('fast', function() {
                // After slide toggle completes, check if lessons need to be loaded
                if ($(this).is(':visible') && chapterId &&
                    $chapterItem.data('lessons-loaded') !== true &&
                    $chapterItem.data('lessons-loading') !== true) {
                    fetchAndDisplayLessonsForChapter(chapterId, $chapterItem);
                }
            });
        });

        $('#video-source').on('change', function() {
            const selectedSource = this.value;
            $('#video-file-group').toggle(selectedSource === 'mp4');
            $('#video-url-group').toggle(selectedSource === 'youtube');
        });

        $('#lessonModal').on('hidden.bs.modal', function() {
            $(this).find('input[type="text"], input[type="file"], input[type="number"], textarea, select').val('');
            $('#video-source').val('');
            $('#video-hh, #video-mm, #video-ss').val('00');
            $('#video-file-group, #video-url-group').hide();
            $('#current-chapter-id-for-lesson').val('');
        });

        $('#save-lesson-server').on('click', async function() {
            const $saveButton = $(this);
            const originalButtonText = $saveButton.html();

            const chapterId = $('#current-chapter-id-for-lesson').val(); // This is the ID of the chapter the lesson belongs to
            const courseId = $('#course_id').val();
            const lessonTitle = $('#lesson-title').val().trim();
            // const lessonContent = $('#lesson-content').val().trim(); // If you send content

            const videoSource = $('#video-source').val();
            const videoUrlInput = $('#video-url').val().trim();
            const videoFile = $('#video-file')[0].files.length > 0 ? $('#video-file')[0].files[0] : null;

            const resourceFiles = Array.from($('#lesson-attachments')[0].files);
            const durationHH = $('#video-hh').val() || '00';
            const durationMM = $('#video-mm').val() || '00';
            const durationSS = $('#video-ss').val() || '00';
            const durationForDisplay = `${durationHH}:${durationMM}:${durationSS}`;


            let validationError = false;
            if (!lessonTitle) { showGlobalMessage('Vui lòng nhập tiêu đề bài học.', 'warning'); validationError = true; }
            if (!videoSource) { showGlobalMessage('Vui lòng chọn nguồn video.', 'warning'); validationError = true; }
            if (videoSource === 'mp4' && !videoFile) { showGlobalMessage('Vui lòng chọn một file video.', 'warning'); validationError = true; }
            if (videoSource === 'youtube' && !videoUrlInput) { showGlobalMessage('Vui lòng nhập URL video.', 'warning'); validationError = true; }
            if (!chapterId) { showGlobalMessage('Lỗi: Không tìm thấy ID Chương.', 'danger'); validationError = true; }
            if (!courseId) { showGlobalMessage('Lỗi: Không tìm thấy ID Khóa học.', 'danger'); validationError = true; }
            if (validationError) return;

            $saveButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Đang lưu...');

            const formData = new FormData();
            formData.append('action', 'save_lesson_content');
            formData.append('courseID', courseId);
            formData.append('chapterID', chapterId); // Send chapterID to backend
            formData.append('lessonTitle', lessonTitle);
            formData.append('videoTitle', lessonTitle); // Assuming video title is same as lesson title for now
            // formData.append('lessonContent', lessonContent); // if API supports lesson content

            if (videoSource === 'mp4' && videoFile) {
                formData.append('video_file', videoFile, videoFile.name);
            } else if (videoSource === 'youtube' && videoUrlInput) {
                formData.append('video_url', videoUrlInput);
            }
            // formData.append('duration', totalSeconds); // If API expects total seconds

            resourceFiles.forEach((file) => {
                formData.append('resource_files[]', file, file.name);
            });

            try {
                const response = await fetch(C_VIDEO_CONTROLLER_URL, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', <?php if (isset($_SESSION['user']['token'])): ?> 'Authorization': 'Bearer <?= $_SESSION['user']['token'] ?>' <?php endif; ?> },
                    body: formData
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    showGlobalMessage(`Bài học "${lessonTitle}" đã lưu thành công. ${result.message || ''}`, 'success');

                    // Assuming server returns the newly created lessonID, video details, and resource details
                    // For example: result.data.newLesson.lessonID, result.data.video, result.data.resources
                    const newLessonData = {
                        lessonID: result.data.newLessonID || 'lesson-' + Date.now(), // IMPORTANT: Expecting newLessonID from server
                        title: lessonTitle,
                        isNew: true // Flag to differentiate from fetched lessons if needed for styling
                    };
                    const videoDetailsArray = result.data.video ? [result.data.video] : []; // video is an object
                    const resourcesDetailsArray = result.data.resources || []; // resources is an array

                    const lessonItemHtml = createLessonItemHtml(newLessonData, videoDetailsArray, resourcesDetailsArray);

                    const safeChapterId = String(chapterId).replace(/[!"#$%&'()*+,.\/:;<=>?@[\\\]^`{|}~]/g, "\\$&");
                    const $chapterElement = $('#' + safeChapterId);

                    if ($chapterElement.length) {
                        const $lessonsUl = $chapterElement.find('.lessons');
                        $lessonsUl.append(lessonItemHtml);
                        updateNoLessonsMessage(chapterId);
                        $chapterElement.find('.card-body').slideDown('fast');
                    }
                    $('#lessonModal').modal('hide');

                } else {
                    let errorMessages = result.message || 'Lỗi không xác định từ server.';
                    if (result.errors && Array.isArray(result.errors)) { errorMessages += "<br>Chi tiết: <ul><li>" + result.errors.join("</li><li>") + "</li></ul>"; }
                    showGlobalMessage(`Lưu bài học thất bại: ${errorMessages}`, 'danger', 8000);
                }

            } catch (error) {
                console.error('Lỗi network/JS khi gửi dữ liệu bài học:', error);
                showGlobalMessage(`Lỗi kết nối hoặc client-side: ${error.message}`, 'danger', 8000);
            } finally {
                $saveButton.prop('disabled', false).html('<i class="bi bi-cloud-upload"></i> Thêm Bài học (Lưu vào Server)');
            }
        });

        $('#topics-list').on('click', '.btn-delete-lesson', function() {
            if (confirm('Bạn có chắc chắn muốn xóa bài học này (chức năng xóa server chưa hoàn thiện, chỉ xóa cục bộ)?')) {
                const $lessonItem = $(this).closest('.lesson-item');
                const chapterId = $lessonItem.closest('.topic-item').attr('id');
                $lessonItem.remove();
                showGlobalMessage('Bài học đã được xóa cục bộ.', 'info', 3000);
                if (chapterId) updateNoLessonsMessage(chapterId);
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
                        title: $topicEl.data('topic-name') || $topicEl.find('.card-header > div:first-child > span').text().trim(),
                        description: $topicEl.data('topic-summary') || ''
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
                    const response = await fetch(C_VIDEO_CONTROLLER_URL, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', <?php if (isset($_SESSION['user']['token'])): ?> 'Authorization': 'Bearer <?= $_SESSION['user']['token'] ?>' <?php endif; ?> },
                        body: JSON.stringify(payload)
                    });
                    const result = await response.json();

                    if (response.ok && result.success) {
                        resultsSummary += `<li class="text-success">Chương "${payload.title}": Lưu thành công. ${result.message || ''}</li>`;
                        successfulSaves++;
                        const safeLocalId = String(item.localId).replace(/[!"#$%&'()*+,.\/:;<=>?@[\\\]^`{|}~]/g, "\\$&");
                        const $savedTopicEl = $('#' + safeLocalId);

                        if (result.data && result.data.chapterID) {
                            $savedTopicEl.attr('id', result.data.chapterID);
                            $savedTopicEl.attr('data-chapter-id', result.data.chapterID);
                            if ($('#current-chapter-id-for-lesson').val() === item.localId) { // Update if it was selected for lesson
                                $('#current-chapter-id-for-lesson').val(result.data.chapterID);
                            }
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
