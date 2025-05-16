<?php
session_start();

if (session_status() == PHP_SESSION_NONE) {
}

$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http');
$host = $_SERVER['HTTP_HOST'];
$script_path = $_SERVER['SCRIPT_NAME'];
$path_parts = explode('/', ltrim($script_path, '/'));
$app_root_directory_name = $path_parts[0];

$script_dir = dirname($script_path);
if ($script_dir === '/' || $script_dir === '\\') {
    $app_root_path_relative = '';
} else {
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
        $path_segments = explode('/', trim($script_dir, '/'));
        if (count($path_segments) > 0 && $path_segments[0] != '') {
            $app_root_path_relative = '/' . $path_segments[0];
        } else {
            $app_root_path_relative = '';
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
    if (isset($http_response_header[0])) {
        preg_match('{HTTP\/\S*\s(\d{3})}', $http_response_header[0], $match);
        if (isset($match[1])) {
            $status_code = intval($match[1]);
        }
    }

    if (!is_array($result)) {
        return [
            'success' => false,
            'message' => 'Invalid API response or failed to decode JSON. Raw response: ' . substr($response, 0, 200),
            'data' => null,
            'raw_response' => $response,
            'http_status_code' => $status_code
        ];
    }

    $result['http_status_code'] = $status_code;
    if (!isset($result['success'])) {
        $result['success'] = ($status_code >= 200 && $status_code < 300);
    }
    return $result;
}

$courseResp = callApi('course_api.php', 'GET');
$courses    = $courseResp['success'] && isset($courseResp['data']) && is_array($courseResp['data']) ? $courseResp['data'] : [];

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

        #global-message {
            margin-top: 15px;
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

                    <form id="addContentForm" action="../controller/c_video_api.php" method="POST">
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
                        <h4 class="section-title"><i class="bi bi-folder-plus"></i> 2. Xây dựng nội dung khóa học</h4>
                        <button id="btn-add-topic" type="button" class="btn btn-primary mb-3">
                            <i class="bi bi-plus-lg"></i> Add New Topic
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
                            <button id="save-topic" type="button" class="btn btn-success">Add Topic Locally</button>
                        </div>
                        <div id="topics-list"></div>
                        <div class="mt-4">
                            <button type="button" id="saveAllContentButton" class="btn btn-primary">
                                <i class="bi bi-save-fill"></i> Lưu Toàn Bộ Nội Dung
                            </button>
                            <a href="course-management.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Quay lại Quản lý khóa học
                            </a>
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
                    <h5 class="modal-title" id="lessonModalLabel">Add Lesson</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="lesson-title" class="form-label">Tên Bài học</label>
                        <input type="text" id="lesson-title" class="form-control" placeholder="Ví dụ: Bài 1 - Lời chào">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nội dung/Tóm tắt</label>
                        <textarea id="lesson-content" class="form-control" rows="4"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Feature Image (tùy chọn)</label>
                        <input type="file" id="lesson-image" class="form-control" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label for="video-source" class="form-label">Video Source</label>
                        <select id="video-source" class="form-select">
                            <option value="">Chọn nguồn video</option>
                            <option value="mp4">HTML5 (mp4)</option>
                            <option value="external">External URL</option>
                            <option value="youtube">YouTube</option>
                            <option value="vimeo">Vimeo</option>
                            <option value="embedded">Embedded</option>
                        </select>
                    </div>
                    <div id="video-url-group" class="mb-3" style="display:none;">
                        <label for="video-url" class="form-label">Video URL / Embed</label>
                        <input type="text" id="video-url" class="form-control" placeholder="https://... hoặc embed code">
                    </div>
                    <div id="video-file-group" class="mb-3" style="display:none;">
                        <label for="video-file" class="form-label">Upload MP4 File</label>
                        <input type="file" id="video-file" class="form-control" accept="video/mp4">
                    </div>
                    <label class="form-label">Video playback time</label>
                    <div class="d-flex gap-2 mb-3">
                        <input type="number" id="video-hh" class="form-control" placeholder="HH" min="0">
                        <input type="number" id="video-mm" class="form-control" placeholder="MM" min="0" max="59">
                        <input type="number" id="video-ss" class="form-control" placeholder="SS" min="0" max="59">
                    </div>
                    <div class="mb-3">
                        <label for="lesson-attachments" class="form-label">Attachments (pdf, zip, ...)</label>
                        <input type="file" id="lesson-attachments" class="form-control" multiple accept=".pdf,.zip,.doc,.ppt,.pptx">
                    </div>
                </div>
                <div class="modal-footer">
                    <button id="update-lesson" type="button" class="btn btn-primary">Update Lesson</button>
                </div>
            </div>
        </div>
    </div>
    <script src="js/jquery-3.7.1.slim.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script>
        $(function() {
            function showGlobalMessage(message, type = 'info') {
                $('#global-message').html(`<div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>`);
            }

            $('#btn-add-topic').on('click', () => $('#add-topic-form').collapse('toggle'));

            $('#save-topic').on('click', () => {
                const name = $('#topic-name').val().trim();
                const sum = $('#topic-summary').val().trim();
                if (!name) {
                    showGlobalMessage('Vui lòng nhập tên chương.', 'warning');
                    return;
                }
                const id = 'topic-' + Date.now();
                const tpl = `
        <div class="card mb-2 topic-item" id="${id}" data-topic-name="${name}" data-topic-summary="${sum}">
          <div class="card-header d-flex justify-content-between align-items-center">
            <div><i class="bi bi-arrows-move me-2"></i><span>${name}</span></div>
            <div>
              <button class="btn btn-sm btn-outline-primary btn-add-lesson" title="Add Lesson"><i class="bi bi-plus-circle"></i> Lesson</button>
              <button class="btn btn-sm btn-outline-info btn-add-quiz" title="Add Quiz"><i class="bi bi-patch-question"></i> Quiz</button>
              <button class="btn btn-sm btn-outline-danger btn-delete-topic" title="Delete Topic"><i class="bi bi-trash"></i></button>
            </div>
          </div>
          <ul class="list-group list-group-flush lessons"></ul>
        </div>`;
                $('#topics-list').append(tpl);
                $('#topic-name, #topic-summary').val('');
                $('#add-topic-form').collapse('hide');
                showGlobalMessage(`Chương "${name}" đã được thêm cục bộ. Nhấn "Lưu Toàn Bộ Nội Dung" để lưu lên server.`, 'info');
            });

            $('#topics-list').on('click', '.btn-add-lesson', function() {
                const topicId = $(this).closest('.topic-item').attr('id');
                $('#lessonModal').data('topicId', topicId).modal('show');
                $('#lessonModalLabel').text('Add Lesson');
                $('#lesson-title, #lesson-content, #lesson-image, #video-source, #video-url, #video-file, #video-hh, #video-mm, #video-ss, #lesson-attachments').val('');
                $('#video-file-group, #video-url-group').hide();
            });

            $('#topics-list').on('click', '.btn-delete-topic', function() {
                if (confirm('Bạn có chắc chắn muốn xóa chương này và tất cả bài học bên trong (thao tác cục bộ)?')) {
                    $(this).closest('.topic-item').remove();
                    showGlobalMessage('Chương đã được xóa cục bộ.', 'info');
                }
            });

            $('#video-source').on('change', function() {
                const selectedSource = this.value;
                $('#video-file-group').toggle(selectedSource === 'mp4');
                $('#video-url-group').toggle(['external', 'youtube', 'vimeo', 'embedded'].includes(selectedSource));
            });

            $('#lessonModal').on('hidden.bs.modal', function() {
                $(this).find('input[type="text"], input[type="file"], input[type="number"], textarea, select').val('');
                $('#video-file-group, #video-url-group').hide();
                $(this).removeData('topicId').removeData('lessonId');
            });

            $('#update-lesson').on('click', () => {
                const modal = $('#lessonModal');
                const topicId = modal.data('topicId');
                const lessonId = modal.data('lessonId');
                const title = $('#lesson-title').val().trim();
                if (!title) {
                    alert('Chưa nhập tiêu đề bài học');
                    return;
                }
                if (!topicId) {
                    alert('Lỗi: Không tìm thấy Topic ID để thêm bài học.');
                    return;
                }
                const files = $('#lesson-attachments')[0].files;
                let attachHtml = '';
                if (files && files.length > 0) {
                    attachHtml = '<ul class="lesson-attachments mt-2 mb-0 small">';
                    for (let i = 0; i < files.length; i++) {
                        attachHtml += `<li><i class="bi bi-paperclip me-1"></i>${files[i].name}</li>`;
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
            data-title="${title}"
            data-content="${lessonData.content}"
            data-image="${lessonData.image}"
            data-video-source="${lessonData.videoSource}"
            data-video-url="${lessonData.videoUrl}"
            data-video-file="${lessonData.videoFile}"
            data-duration="${lessonData.duration}">
          <div>
            <i class="bi bi-file-earmark-play me-2"></i>${title}
            <span class="text-muted small ms-2">(${lessonData.duration})</span>
            ${attachHtml}
          </div>
          <div>
            <button class="btn btn-sm btn-outline-success btn-edit-lesson" title="Edit Lesson"><i class="bi bi-pencil-square"></i></button>
            <button class="btn btn-sm btn-outline-danger btn-delete-lesson" title="Delete Lesson"><i class="bi bi-trash"></i></button>
          </div>
        </li>`;
                const topicElement = $('#' + topicId);
                if (topicElement.length) {
                    topicElement.find('.lessons').append(lessonItemHtml);
                    showGlobalMessage(`Bài học "${title}" đã được thêm vào chương cục bộ.`, 'info');
                } else {
                    showGlobalMessage('Lỗi: Không tìm thấy chương để thêm bài học.', 'danger');
                    return;
                }
                modal.modal('hide');
            });

            $('#topics-list').on('click', '.btn-delete-lesson', function() {
                if (confirm('Bạn có chắc chắn muốn xóa bài học này (thao tác cục bộ)?')) {
                    $(this).closest('.lesson-item').remove();
                    showGlobalMessage('Bài học đã được xóa cục bộ.', 'info');
                }
            });

            $('#saveAllContentButton').on('click', async function() {
                const courseId = $('#course_id').val();
                const $saveButton = $(this);
                const originalButtonText = $saveButton.html();

                if (!courseId) {
                    showGlobalMessage('Vui lòng chọn một khóa học.', 'warning');
                    return;
                }

                const topicsData = [];
                $('#topics-list .topic-item').each(function(topicIndex) {
                    const topicEl = $(this);
                    const topic = {
                        title: topicEl.data('topic-name') || topicEl.find('.card-header > div:first-child > span').text().trim(),
                        description: topicEl.data('topic-summary') || ''
                    };
                    topicsData.push(topic);
                });

                if (topicsData.length === 0) {
                    showGlobalMessage('Vui lòng thêm ít nhất một chương để lưu.', 'warning');
                    return;
                }

                $saveButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang lưu...');
                showGlobalMessage('Bắt đầu quá trình lưu toàn bộ nội dung...', 'info');

                const controllerUrl = $('#addContentForm').attr('action');
                let allSuccess = true;
                let successfulSaves = 0;
                let failedSaves = 0;
                let resultsSummary = '<strong>Kết quả lưu các chương:</strong><ul>';

                for (const topic of topicsData) {
                    const payload = {
                        courseID: courseId,
                        title: topic.title,
                        description: topic.description
                    };

                    try {
                        const response = await fetch(controllerUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        });

                        const result = await response.json();

                        if (response.ok && result.success) {
                            console.log('Lưu chương thành công:', result);
                            resultsSummary += `<li class="text-success">Chương "${payload.title}": Lưu thành công. ${result.message || ''}</li>`;
                            successfulSaves++;
                        } else {
                            allSuccess = false;
                            failedSaves++;
                            console.error('Lỗi khi lưu chương:', result);
                            resultsSummary += `<li class="text-danger">Chương "${payload.title}": Lưu thất bại. ${result.message || 'Lỗi không xác định từ server.'} (Code: ${response.status})</li>`;
                        }
                    } catch (error) {
                        allSuccess = false;
                        failedSaves++;
                        console.error('Lỗi network hoặc JavaScript khi gửi dữ liệu chương:', error);
                        resultsSummary += `<li class="text-danger">Chương "${payload.title}": Lỗi kết nối hoặc xử lý. ${error.message}</li>`;
                    }
                }
                resultsSummary += '</ul>';

                if (allSuccess) {
                    showGlobalMessage(`Tất cả ${successfulSaves} chương đã được lưu thành công! <br>` + resultsSummary, 'success');
                    $('#topics-list').empty();
                } else {
                    let finalMessage = `Hoàn tất quá trình lưu. <br>Thành công: ${successfulSaves}. Thất bại: ${failedSaves}.<br>`;
                    finalMessage += resultsSummary;
                    showGlobalMessage(finalMessage, 'warning');
                }
                $saveButton.prop('disabled', false).html(originalButtonText);
            });
        });
    </script>
</body>

</html>