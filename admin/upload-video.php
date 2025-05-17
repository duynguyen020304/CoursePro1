<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http');
$host = $_SERVER['HTTP_HOST'];
$script_path = $_SERVER['SCRIPT_NAME'];

$path_parts = explode('/', ltrim($script_path, '/'));
$app_root_path_relative = '';

if (!empty($path_parts) && $path_parts[0] !== '') {
    $known_app_subdir_markers = ['/admin/', '/api/', '/includes/', '/controller/'];
    $found_marker = false;
    foreach ($known_app_subdir_markers as $marker) {
        $pos = strripos($script_path, $marker);
        if ($pos !== false) {
            $path_before_marker = substr($script_path, 0, $pos);
            $segments_before_marker = explode('/', ltrim($path_before_marker, '/'));
            if (!empty($segments_before_marker) && $segments_before_marker[0] !== '') {
                $app_root_path_relative = '/' . $segments_before_marker[0];
            } else {
                $app_root_path_relative = '';
            }
            $found_marker = true;
            break;
        }
    }
    if (!$found_marker) {
        if (count($path_parts) > 0 && $path_parts[0] !== '') {
            $app_root_path_relative = '/' . $path_parts[0];
        }
    }
}

if ($app_root_path_relative !== '/' && $app_root_path_relative !== '' && substr($app_root_path_relative, -1) === '/') {
    $app_root_path_relative = rtrim($app_root_path_relative, '/');
}

define('API_BASE', $protocol . '://' . $host . $app_root_path_relative . '/api');

function callApi(string $endpoint, string $method = 'GET', array $payload = []): array
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

    $status_code = 500;
    if (isset($http_response_header) && is_array($http_response_header) && isset($http_response_header[0])) {
        preg_match('{HTTP/\S*\s(\d{3})}', $http_response_header[0], $match);
        if (isset($match[1])) {
            $status_code = intval($match[1]);
        }
    }

    if ($response === false) {
        return [
            'success' => false,
            'message' => 'API request failed. Could not connect or other stream error.',
            'data' => null,
            'raw_response' => null,
            'http_status_code' => 0
        ];
    }

    if (!is_array($result)) {
        return [
            'success' => false,
            'message' => 'Invalid API response or failed to decode JSON. Raw response snippet: ' . substr($response, 0, 250),
            'data' => null,
            'raw_response' => $response,
            'http_status_code' => $status_code
        ];
    }

    if (!isset($result['success'])) {
        $result['success'] = ($status_code >= 200 && $status_code < 300);
    }
    $result['http_status_code'] = $status_code;
    return $result;
}

$courseResp = callApi('course_api.php', 'GET');
$courses    = ($courseResp['success'] && isset($courseResp['data']) && is_array($courseResp['data'])) ? $courseResp['data'] : [];

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
            cursor: move;
        }

        .topic-item.existing-topic .card-header {
            background-color: #e9ecef;
        }

        #global-message {
            margin-top: 15px;
        }

        .spinner-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100px;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <?php if (file_exists('template/dashboard.php')) include 'template/dashboard.php'; ?>
        <div class="main-content">
            <div class="container-fluid">
                <div class="form-container">
                    <h2>Thêm Nội dung Khóa học (Chương & Bài học Video)</h2>
                    <hr>
                    <div id="global-message"></div>

                    <form id="addContentForm" action="../controller/c_video.php" method="POST">
                        <div class="mb-3">
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
                                <button id="save-topic-locally" type="button" class="btn btn-success">Thêm Chương (Cục bộ)</button>
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
                                    <i class="bi bi-save-fill"></i> Lưu Thay Đổi
                                </button>
                                <a href="course-management.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Quay lại Quản lý khóa học
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
                    <input type="hidden" id="current-topic-id-for-lesson">
                    <div class="mb-3">
                        <label for="lesson-title" class="form-label">Tên Bài học <span class="text-danger">*</span></label>
                        <input type="text" id="lesson-title" class="form-control" placeholder="Ví dụ: Bài 1 - Lời chào">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nội dung/Tóm tắt</label>
                        <textarea id="lesson-content" class="form-control" rows="4"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Thumbnail (tùy chọn)</label>
                        <input type="file" id="lesson-image" class="form-control" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label for="video-source" class="form-label">Nguồn Video</label>
                        <select id="video-source" class="form-select">
                            <option value="">Chọn nguồn video</option>
                            <option value="mp4">HTML5 (mp4)</option>
                            <option value="youtube">YouTube</option>
                        </select>
                    </div>
                    <div id="video-url-group" class="mb-3" style="display:none;">
                        <label for="video-url" class="form-label">Video URL / Embed Code</label>
                        <input type="text" id="video-url" class="form-control" placeholder="https://... hoặc mã nhúng">
                    </div>
                    <div id="video-file-group" class="mb-3" style="display:none;">
                        <label for="video-file" class="form-label">Tải lên file MP4</label>
                        <input type="file" id="video-file" class="form-control" accept="video/mp4">
                    </div>
                    <label class="form-label">Thời lượng Video</label>
                    <div class="d-flex gap-2 mb-3">
                        <input type="number" id="video-hh" class="form-control" placeholder="HH" min="0" value="00">
                        <input type="number" id="video-mm" class="form-control" placeholder="MM" min="0" max="59" value="00">
                        <input type="number" id="video-ss" class="form-control" placeholder="SS" min="0" max="59" value="00">
                    </div>
                    <div class="mb-3">
                        <label for="lesson-attachments" class="form-label">Tài liệu đính kèm (pdf, zip, ...)</label>
                        <input type="file" id="lesson-attachments" class="form-control" multiple accept=".pdf,.zip,.doc,.docx,.ppt,.pptx">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button id="save-lesson-locally" type="button" class="btn btn-primary">Thêm Bài học (Cục bộ)</button>
                </div>
            </div>
        </div>
    </div>

    <script src="js/jquery-3.7.1.slim.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script>
        const API_BASE_URL = '<?= API_BASE ?>';
        const CHAPTER_SAVE_URL = '<?= htmlspecialchars((string)($_SERVER['REQUEST_SCHEME'] ?? 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? '') . dirname($_SERVER['SCRIPT_NAME']) . '/../controller/c_video.php') ?>';

        $(function() {
            function showGlobalMessage(message, type = 'info', autoDismissDelay = 0) {
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
                const sortOrder = topic.sortOrder !== undefined ? topic.sortOrder : '';

                const existingClass = isExisting ? 'existing-topic' : '';
                const chapterIdAttr = isExisting && topic.chapterID ? `data-chapter-id="${topic.chapterID}"` : '';
                const isExistingAttr = isExisting ? 'data-is-existing="true"' : '';

                return `
                <div class="card mb-2 topic-item ${existingClass}" id="${topicId}"
                     data-topic-name="${title}"
                     data-topic-summary="${description}"
                     data-sort-order="${sortOrder}"
                     ${chapterIdAttr}
                     ${isExistingAttr}>
                  <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-arrows-move me-2" title="Sắp xếp (chức năng chưa hoàn thiện)"></i>
                        <span>${title}</span>
                        ${isExisting ? '<span class="badge bg-info ms-2">Đã lưu</span>' : '<span class="badge bg-warning ms-2">Chưa lưu</span>'}
                    </div>
                    <div>
                      <button type="button" class="btn btn-sm btn-outline-primary btn-add-lesson" title="Thêm Bài học"><i class="bi bi-plus-circle"></i> Bài học</button>
                      <button type="button" class="btn btn-sm btn-outline-info btn-add-quiz" title="Thêm Câu hỏi (chưa hoạt động)"><i class="bi bi-patch-question"></i> Câu hỏi</button>
                      <button type="button" class="btn btn-sm btn-outline-danger btn-delete-topic" title="Xóa Chương này (cục bộ)"><i class="bi bi-trash"></i></button>
                    </div>
                  </div>
                  <div class="card-body p-2" style="display: none;"> <p class="card-text small text-muted">${description || 'Không có mô tả.'}</p>
                  </div>
                  <ul class="list-group list-group-flush lessons"></ul>
                </div>`;
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
                    showGlobalMessage('Đang tải danh sách chương cho khóa học đã chọn...', 'info');

                    try {
                        const headers = {
                            'Accept': 'application/json'
                        };
                        <?php if (isset($_SESSION['user']['token'])): ?>
                        headers['Authorization'] = 'Bearer <?= $_SESSION['user']['token'] ?>';
                        <?php endif; ?>
                        const response = await fetch(`${API_BASE_URL}/chapter_api.php?courseID=${courseId}`, {
                            method: 'GET',
                            headers: headers
                        });

                        if (!response.ok) {
                            const errorData = await response.json().catch(() => ({
                                message: 'Lỗi không xác định khi tải chương.'
                            }));
                            throw new Error(`Lỗi ${response.status}: ${errorData.message || response.statusText}`);
                        }

                        const result = await response.json();

                        $topicsList.empty();
                        if (result.success && result.data && result.data.length > 0) {
                            result.data.forEach(chapter => {
                                const chapterHtml = createTopicCardHtml(chapter, true);
                                $topicsList.append(chapterHtml);
                            });
                            showGlobalMessage(`Đã tải ${result.data.length} chương thành công.`, 'success', 3000);
                        } else if (result.success && (!result.data || result.data.length === 0)) {
                            $topicsList.html('<p class="text-muted">Khóa học này chưa có chương nào. Hãy thêm chương mới.</p>');
                            showGlobalMessage('Khóa học này chưa có chương nào.', 'info');
                        } else {
                            $topicsList.html('<p class="text-danger">Không thể tải chương: ' + (result.message || 'Lỗi không rõ') + '</p>');
                            showGlobalMessage('Lỗi khi tải chương: ' + (result.message || 'Lỗi không rõ'), 'danger');
                        }
                    } catch (error) {
                        console.error('Lỗi fetch chương:', error);
                        $topicsList.html(`<p class="text-danger">Lỗi kết nối hoặc xử lý khi tải chương: ${error.message}</p>`);
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

                const newTopic = {
                    title: name,
                    description: summary
                };
                const topicHtml = createTopicCardHtml(newTopic, false);
                $('#topics-list').append(topicHtml);

                $('#topic-name, #topic-summary').val('');
                $('#add-topic-form').collapse('hide');
                showGlobalMessage(`Chương "${name}" đã được thêm vào danh sách (chưa lưu lên server).`, 'info', 4000);
                updateChapterCount();
            });

            $('#topics-list').on('click', '.btn-add-lesson', function() {
                const $topicItem = $(this).closest('.topic-item');
                const topicId = $topicItem.attr('id');
                const chapterIdForLesson = $topicItem.data('chapter-id');

                $('#current-topic-id-for-lesson').val(chapterIdForLesson || topicId);

                $('#lessonModalLabel').text('Thêm Bài học cho chương: ' + $topicItem.data('topic-name'));
                $('#lessonModal').find('input[type="text"], input[type="file"], input[type="number"], textarea, select').val('');
                $('#video-hh, #video-mm, #video-ss').val('00');
                $('#video-file-group, #video-url-group').hide();
                $('#lessonModal').removeData('lessonId');
                $('#lessonModal').modal('show');
            });

            $('#topics-list').on('click', '.btn-delete-topic', function() {
                const $topicItem = $(this).closest('.topic-item');
                const topicName = $topicItem.data('topic-name');
                const isExisting = $topicItem.data('is-existing') === true;

                let confirmMessage = `Bạn có chắc chắn muốn xóa chương "${topicName}"?`;
                if (isExisting) {
                    confirmMessage += "\nLƯU Ý: Chương này đã được lưu trên server. Việc xóa ở đây chỉ là xóa cục bộ khỏi danh sách hiện tại. Để xóa vĩnh viễn, cần chức năng xóa trên server.";
                } else {
                    confirmMessage += "\nThao tác này chỉ xóa cục bộ, chương chưa được lưu lên server.";
                }

                if (confirm(confirmMessage)) {
                    $topicItem.remove();
                    showGlobalMessage(`Chương "${topicName}" đã được xóa khỏi danh sách cục bộ.`, 'info', 3000);
                    if ($('#topics-list .topic-item').length === 0 && !$('#course_id').val()) {
                        $('#topics-list').html('<p class="text-muted">Vui lòng chọn một khóa học để xem hoặc thêm chương.</p>');
                    } else if ($('#topics-list .topic-item').length === 0 && $('#course_id').val()) {
                        $('#topics-list').html('<p class="text-muted">Khóa học này chưa có chương nào hoặc tất cả đã bị xóa cục bộ. Hãy thêm chương mới.</p>');
                    }
                    updateChapterCount();
                }
            });

            $('#topics-list').on('click', '.card-header', function(e) {
                if ($(e.target).is('button, i, input') || $(e.target).closest('button, input').length) {
                    return;
                }
                $(this).siblings('.card-body, .lessons').slideToggle('fast');
            });

            $('#video-source').on('change', function() {
                const selectedSource = this.value;
                $('#video-file-group').toggle(selectedSource === 'mp4');
                $('#video-url-group').toggle(['youtube'].includes(selectedSource));
            });

            $('#lessonModal').on('hidden.bs.modal', function() {
                $(this).find('input[type="text"], input[type="file"], input[type="number"], textarea, select').val('');
                $('#video-hh, #video-mm, #video-ss').val('00');
                $('#video-file-group, #video-url-group').hide();
                $(this).removeData('topicId').removeData('lessonId');
                $('#current-topic-id-for-lesson').val('');
            });

            $('#save-lesson-locally').on('click', () => {
                const modal = $('#lessonModal');
                const topicIdForLesson = $('#current-topic-id-for-lesson').val();
                const title = $('#lesson-title').val().trim();

                if (!title) {
                    alert('Vui lòng nhập tiêu đề bài học.');
                    $('#lesson-title').focus();
                    return;
                }
                if (!topicIdForLesson) {
                    alert('Lỗi: Không tìm thấy ID Chương (Topic ID) để thêm bài học. Hãy chắc chắn bạn đã chọn một chương.');
                    return;
                }

                const files = $('#lesson-attachments')[0].files;
                let attachHtml = '';
                if (files && files.length > 0) {
                    attachHtml = '<ul class="lesson-attachments mt-2 mb-0 small list-unstyled">';
                    for (let i = 0; i < files.length; i++) {
                        attachHtml += `<li><i class="bi bi-paperclip me-1"></i>${$('<div>').text(files[i].name).html()}</li>`;
                    }
                    attachHtml += '</ul>';
                }

                const lessonData = {
                    title: title,
                    content: $('#lesson-content').val(),
                    image: $('#lesson-image').val() ? $('#lesson-image')[0].files[0].name : '',
                    videoSource: $('#video-source').val(),
                    videoUrl: $('#video-url').val(),
                    videoFile: $('#video-file').val() ? $('#video-file')[0].files[0].name : '',
                    duration: `${$('#video-hh').val() || '00'}:${$('#video-mm').val() || '00'}:${$('#video-ss').val() || '00'}`
                };

                const uniqueLessonId = 'lesson-' + Date.now();
                const lessonItemHtml = `
                <li class="list-group-item d-flex justify-content-between align-items-center lesson-item" id="${uniqueLessonId}"
                    data-title="${$('<div>').text(title).html()}"
                    data-content="${$('<div>').text(lessonData.content).html()}"
                    data-image="${$('<div>').text(lessonData.image).html()}"
                    data-video-source="${lessonData.videoSource}"
                    data-video-url="${$('<div>').text(lessonData.videoUrl).html()}"
                    data-video-file="${$('<div>').text(lessonData.videoFile).html()}"
                    data-duration="${lessonData.duration}">
                  <div>
                    <i class="bi bi-file-earmark-play me-2"></i>${$('<div>').text(title).html()}
                    <span class="text-muted small ms-2">(${lessonData.duration})</span>
                    ${attachHtml}
                  </div>
                  <div>
                    <button class="btn btn-sm btn-outline-success btn-edit-lesson" title="Sửa Bài học (chưa hoạt động)"><i class="bi bi-pencil-square"></i></button>
                    <button class="btn btn-sm btn-outline-danger btn-delete-lesson" title="Xóa Bài học (cục bộ)"><i class="bi bi-trash"></i></button>
                  </div>
                </li>`;

                const $topicElement = $('#' + topicIdForLesson.replace(/[!"#$%&'()*+,.\/:;<=>?@[\\\]^`{|}~]/g, "\\$&"));
                if ($topicElement.length) {
                    $topicElement.find('.lessons').append(lessonItemHtml).closest('.card-body, .lessons').slideDown();
                    showGlobalMessage(`Bài học "${title}" đã được thêm vào chương (cục bộ).`, 'success', 3000);
                } else {
                    showGlobalMessage(`Không tìm thấy chương (ID: ${topicIdForLesson}) để thêm bài học.`);
                    console.error("Could not find topic element with ID:", topicIdForLesson);
                    return;
                }
                modal.modal('hide');
            });

            $('#topics-list').on('click', '.btn-delete-lesson', function() {
                if (confirm('Bạn có chắc chắn muốn xóa bài học này (thao tác cục bộ)?')) {
                    $(this).closest('.lesson-item').remove();
                    showGlobalMessage('Bài học đã được xóa cục bộ.', 'info', 3000);
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

                const $newTopics = $('#topics-list .topic-item:not([data-is-existing="true"])');

                if ($newTopics.length === 0) {
                    showGlobalMessage('Không có chương mới nào để lưu. Các chương hiện tại đã được lưu hoặc chưa có chương mới được thêm.', 'info', 4000);
                    return;
                }

                const topicsToSaveData = [];
                $newTopics.each(function(topicIndex) {
                    const $topicEl = $(this);
                    const topic = {
                        title: $topicEl.data('topic-name') || $topicEl.find('.card-header > div:first-child > span').text().trim(),
                        description: $topicEl.data('topic-summary') || '',
                        sortOrder: $topicEl.data('sort-order') || topicIndex
                    };
                    topicsToSaveData.push({
                        localId: $topicEl.attr('id'),
                        data: topic
                    });
                });

                $saveButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang lưu...');
                showGlobalMessage(`Bắt đầu lưu ${topicsToSaveData.length} chương mới...`, 'info');

                let allSuccess = true;
                let successfulSaves = 0;
                let failedSaves = 0;
                let resultsSummary = `<strong>Kết quả lưu các chương mới (CourseID: ${courseId}):</strong><ul>`;

                for (const item of topicsToSaveData) {
                    const payload = {
                        courseID: courseId,
                        title: item.data.title,
                        description: item.data.description,
                        sortOrder: item.data.sortOrder
                    };

                    try {
                        const response = await fetch(CHAPTER_SAVE_URL, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                <?php if (isset($_SESSION['user']['token'])): ?> 'Authorization': 'Bearer <?= $_SESSION['user']['token'] ?>'<?php endif; ?>
                            },
                            body: JSON.stringify(payload)
                        });

                        const result = await response.json();

                        if (response.ok && result.success) {
                            console.log('Lưu chương thành công:', result);
                            resultsSummary += `<li class="text-success">Chương "${payload.title}": Lưu thành công. ${result.message || ''}</li>`;
                            successfulSaves++;
                            const $savedTopicEl = $('#' + item.localId.replace(/[!"#$%&'()*+,.\/:;<=>?@[\\\]^`{|}~]/g, "\\$&"));
                            if (result.data && result.data.chapterID) {
                                $savedTopicEl.attr('id', result.data.chapterID);
                                $savedTopicEl.attr('data-chapter-id', result.data.chapterID);
                            }
                            $savedTopicEl.attr('data-is-existing', 'true');
                            $savedTopicEl.addClass('existing-topic');
                            $savedTopicEl.find('.badge.bg-warning').removeClass('bg-warning').addClass('bg-info').text('Đã lưu');

                        } else {
                            allSuccess = false;
                            failedSaves++;
                            console.error('Lỗi khi lưu chương:', result);
                            resultsSummary += `<li class="text-danger">Chương "${payload.title}": Lưu thất bại. ${result.message || 'Lỗi không xác định từ server.'} (HTTP Code: ${response.status})</li>`;
                        }
                    } catch (error) {
                        allSuccess = false;
                        failedSaves++;
                        console.error('Lỗi network/JS khi gửi dữ liệu chương:', error);
                        resultsSummary += `<li class="text-danger">Chương "${payload.title}": Lỗi kết nối hoặc xử lý client-side. ${error.message}</li>`;
                    }
                }
                resultsSummary += '</ul>';

                if (allSuccess && successfulSaves > 0) {
                    showGlobalMessage(`Tất cả ${successfulSaves} chương mới đã được lưu thành công! <br>` + resultsSummary, 'success');
                } else if (successfulSaves === 0 && failedSaves === 0) {
                    showGlobalMessage('Không có chương mới nào được xử lý.', 'info');
                } else {
                    let finalMessage = `Hoàn tất quá trình lưu. <br>Thành công: ${successfulSaves}. Thất bại: ${failedSaves}.<br>`;
                    finalMessage += resultsSummary;
                    showGlobalMessage(finalMessage, failedSaves > 0 ? 'warning' : 'success');
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