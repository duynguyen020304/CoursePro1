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
    define('APP_ROOT_PATH_RELATIVE_WATCH', $app_root_path_relative);
} else {
    define('APP_ROOT_PATH_RELATIVE_WATCH', APP_ROOT_PATH_RELATIVE_HEADER);
}
if (!defined('API_BASE_HEADER')) {
    define('API_BASE_WATCH', $protocol . '://' . $host . APP_ROOT_PATH_RELATIVE_WATCH . '/api');
} else {
    define('API_BASE_WATCH', API_BASE_HEADER);
}
$file_loader_url = APP_ROOT_PATH_RELATIVE_WATCH . '/controller/c_file_loader.php';

if (!function_exists('callApi')) {
    function callApi(string $endpoint, string $method = 'GET', array $payload = []): array
    {
        $url = API_BASE_WATCH . '/' . ltrim($endpoint, '/');
        $methodUpper = strtoupper($method);
        if ($methodUpper === 'GET' && !empty($payload)) $url .= '?' . http_build_query($payload);
        $headers_arr = ["Content-Type: application/json; charset=utf-8", "Accept: application/json"];
        $token = $_SESSION['user']['token'] ?? null;
        if ($token) $headers_arr[] = "Authorization: Bearer " . $token;
        $options = ['http' => ['method' => $methodUpper, 'header' => implode("\r\n", $headers_arr), 'ignore_errors' => true, 'timeout' => 20]];
        if ($methodUpper !== 'GET' && !empty($payload)) $options['http']['content'] = json_encode($payload);
        elseif (in_array($methodUpper, ['POST', 'PUT']) && empty($payload)) $options['http']['content'] = '{}';
        $context  = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);
        $status_code = 0;
        if (isset($http_response_header[0]) && preg_match('{HTTP\/\S*\s(\d{3})}', $http_response_header[0], $match)) $status_code = intval($match[1]);
        if ($response === false) return ['success' => false, 'message' => 'API call failed: ' . $url, 'http_status_code' => $status_code, 'data' => null];
        $result = json_decode($response, true);
        if ($result === null && json_last_error() !== JSON_ERROR_NONE && !empty($response)) return ['success' => false, 'message' => 'Invalid API JSON response. Error: ' . json_last_error_msg(), 'http_status_code' => $status_code, 'data' => null, 'raw_response' => $response];
        if (!is_array($result)) $result = [];
        $result['http_status_code'] = $status_code;
        if (!isset($result['success'])) $result['success'] = ($status_code >= 200 && $status_code < 300);
        return $result;
    }
}

function getYouTubeOEmbed(string $youtubeUrl): ?array
{
    $oembedUrl = "https://www.youtube.com/oembed?url=" . urlencode($youtubeUrl) . "&format=json";
    $context_options = [
        "ssl" => [
            "verify_peer" => false,
            "verify_peer_name" => false,
        ],
        'http' => [
            'timeout' => 10
        ]
    ];
    $context = stream_context_create($context_options);
    $response = @file_get_contents($oembedUrl, false, $context);
    if ($response === false) {
        error_log("WatchVideo: Failed to fetch YouTube oEmbed for URL: " . $youtubeUrl);
        return null;
    }
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE || !isset($data['html'])) {
        error_log("WatchVideo: Invalid JSON or missing HTML in YouTube oEmbed response for URL: " . $youtubeUrl . " Response: " . $response);
        return null;
    }
    return $data;
}


$course_id_from_get = $_GET['courseID'] ?? null;
$lesson_id_from_get = $_GET['lessonID'] ?? null;
$course_data = null;
$page_error = null;
$course_content_for_js = [];

if (!$course_id_from_get) {
    $page_error = "Không tìm thấy ID khóa học.";
} else {
    $api_course_response = callApi('course_api.php', 'GET', ['courseID' => $course_id_from_get]);
    if (isset($api_course_response['success']) && $api_course_response['success'] && !empty($api_course_response['data'])) {
        $course_data = $api_course_response['data'];
        if (isset($course_data['chapters']) && is_array($course_data['chapters'])) {
            foreach ($course_data['chapters'] as $chapter) {
                $js_chapter = [
                    'id' => $chapter['chapterID'],
                    'title' => $chapter['chapterTitle'],
                    'lessons' => []
                ];
                if (isset($chapter['chapterLessons']) && is_array($chapter['chapterLessons'])) {
                    foreach ($chapter['chapterLessons'] as $lesson) {
                        $js_lesson = [
                            'id' => $lesson['lessonID'],
                            'title' => $lesson['lessonTitle'],
                            'videoUrl' => null,
                            'videoType' => null,
                            'resources' => []
                        ];
                        if (!empty($lesson['lessonVideos']) && isset($lesson['lessonVideos'][0]['videoURL'])) {
                            $videoUrl = $lesson['lessonVideos'][0]['videoURL'];
                            if (preg_match('/(youtube\.com\/watch\?v=|youtu\.be\/)/', $videoUrl)) {
                                $js_lesson['videoUrl'] = $videoUrl;
                                $js_lesson['videoType'] = 'youtube';
                            } else {
                                $js_lesson['videoUrl'] = $videoUrl;
                                $js_lesson['videoType'] = 'local';
                            }
                        }
                        if (!empty($lesson['lessonResources']) && is_array($lesson['lessonResources'])) {
                            foreach ($lesson['lessonResources'] as $resource) {
                                $js_lesson['resources'][] = [
                                    'id' => $resource['resourceID'],
                                    'title' => $resource['resourceTitle'],
                                    'path' => $resource['resourcePath']
                                ];
                            }
                        }
                        $js_chapter['lessons'][] = $js_lesson;
                    }
                }
                $course_content_for_js[] = $js_chapter;
            }
        }
    } else {
        $page_error = "Không thể tải dữ liệu khóa học: " . ($api_course_response['message'] ?? "Lỗi không xác định.");
    }
}

$initial_video_html = '<p class="text-center p-5">Chọn một bài học để bắt đầu.</p>';
$initial_lesson_id = null;
$initial_chapter_id_for_video = null;

if (!$page_error && !empty($course_content_for_js)) {
    if ($lesson_id_from_get) {
        foreach ($course_content_for_js as $chapter) {
            foreach ($chapter['lessons'] as $lesson) {
                if ($lesson['id'] === $lesson_id_from_get) {
                    $initial_lesson_id = $lesson['id'];
                    $initial_chapter_id_for_video = $chapter['id'];
                    if ($lesson['videoType'] === 'youtube' && $lesson['videoUrl']) {
                        $oembed_data = getYouTubeOEmbed($lesson['videoUrl']);
                        if ($oembed_data && isset($oembed_data['html'])) {
                            $initial_video_html = $oembed_data['html'];
                        } else {
                            $initial_video_html = '<p class="text-danger text-center p-3">Lỗi khi tải video YouTube.</p>';
                        }
                    } elseif ($lesson['videoType'] === 'local' && $lesson['videoUrl']) {
                        $local_video_url = htmlspecialchars($file_loader_url . "?act=serve_course_video&course_id=" . urlencode($course_id_from_get) . "&chapter_id=" . urlencode($initial_chapter_id_for_video) . "&filename=" . urlencode($lesson['videoUrl']));
                        $initial_video_html = '<video controls width="100%" height="auto" id="localVideoPlayer"><source src="' . $local_video_url . '" type="video/mp4">Trình duyệt không hỗ trợ video.</video>';
                    }
                    break 2;
                }
            }
        }
    }
    if (!$initial_lesson_id && isset($course_content_for_js[0]['lessons'][0])) {
        $first_chapter = $course_content_for_js[0];
        $first_lesson = $first_chapter['lessons'][0];
        $initial_lesson_id = $first_lesson['id'];
        $initial_chapter_id_for_video = $first_chapter['id'];

        if ($first_lesson['videoType'] === 'youtube' && $first_lesson['videoUrl']) {
            $oembed_data = getYouTubeOEmbed($first_lesson['videoUrl']);
            if ($oembed_data && isset($oembed_data['html'])) {
                $initial_video_html = $oembed_data['html'];
            } else {
                $initial_video_html = '<p class="text-danger text-center p-3">Lỗi khi tải video YouTube đầu tiên.</p>';
            }
        } elseif ($first_lesson['videoType'] === 'local' && $first_lesson['videoUrl']) {
            $local_video_url = htmlspecialchars($file_loader_url . "?act=serve_course_video&course_id=" . urlencode($course_id_from_get) . "&chapter_id=" . urlencode($initial_chapter_id_for_video) . "&filename=" . urlencode($first_lesson['videoUrl']));
            $initial_video_html = '<video controls width="100%" height="auto" id="localVideoPlayer"><source src="' . $local_video_url . '" type="video/mp4">Trình duyệt không hỗ trợ video.</video>';
        }
    }
}


$courseTitle = $course_data['title'] ?? 'Đang tải...';
$instructorName = $course_data['instructors'][0]['firstName'] ?? 'N/A';
$instructorName .= ' ' . ($course_data['instructors'][0]['lastName'] ?? '');
$instructorTitle = $course_data['instructors'][0]['biography'] ?? 'Giảng viên';
$courseDescription = $course_data['description'] ?? 'Không có mô tả.';

?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?php echo htmlspecialchars($courseTitle); ?> | Course Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="<?php echo APP_ROOT_PATH_RELATIVE_WATCH; ?>/public/css/watch_video.css" />
</head>

<body>
<header class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="<?php echo APP_ROOT_PATH_RELATIVE_WATCH; ?>/home.php">COURSE ONLINE</a>
        <div class="course-title-header text-light d-none d-lg-block text-truncate px-3">
            <?php echo htmlspecialchars($courseTitle); ?>
        </div>
        <button class="btn btn-outline-light d-lg-none ms-auto me-2" type="button" id="openCourseContentSidebarMobile">
            <i class="bi bi-list-nested"></i> <span class="d-none d-sm-inline">Nội dung</span>
        </button>
        <div class="ms-auto d-flex align-items-center d-none d-lg-flex">
            <div class="dropdown me-2">
                <button class="btn btn-outline-light btn-sm dropdown-toggle" type="button" id="progressDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-check-circle"></i> Tiến độ của bạn
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="progressDropdown">
                    <li><a class="dropdown-item" href="#">Đã hoàn thành: <span id="completedLessonsCount">0</span>/<span id="totalLessonsCount">0</span></a></li>
                    <li>
                        <hr class="dropdown-divider" />
                    </li>
                    <li><a class="dropdown-item" href="#" id="markLessonCompleteBtn">Đánh dấu hoàn thành</a></li>
                </ul>
            </div>
            <button class="btn btn-outline-light btn-sm" type="button" id="shareCourseBtn">
                <i class="bi bi-share"></i> Chia sẻ
            </button>
        </div>
    </div>
</header>

<div class="course-main-layout">
    <div class="video-player-area">
        <div class="video-container ratio ratio-16x9 bg-black" id="videoPlayerWrapper">
            <?php echo $initial_video_html;
            ?>
        </div>

        <div class="video-bottom-tabs-container container-fluid px-lg-0">
            <ul class="nav nav-tabs mt-3" id="videoTab" role="tablist">
                <li class="nav-item" role="presentation"><button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button">Tổng quan</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link" id="resources-tab" data-bs-toggle="tab" data-bs-target="#resources" type="button">Tài liệu</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link" id="notes-tab" data-bs-toggle="tab" data-bs-target="#notes" type="button">Ghi chú</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link" id="announcements-tab" data-bs-toggle="tab" data-bs-target="#announcements" type="button">Thông báo</button></li>
            </ul>
            <div class="tab-content p-3 p-lg-4" id="videoTabContent">
                <div class="tab-pane fade show active" id="overview" role="tabpanel">
                    <h2 class="mb-3 course-title-in-overview"><?php echo htmlspecialchars($courseTitle); ?></h2>
                    <p><?php echo nl2br(htmlspecialchars($courseDescription)); ?></p>
                    <h5 class="mt-4">Giảng viên</h5>
                    <div class="d-flex align-items-start bg-light p-3 rounded shadow-sm">
                        <img src="<?php echo APP_ROOT_PATH_RELATIVE_WATCH; ?>/public/images/default_avatar.png" alt="Instructor" class="rounded-circle me-3" width="64" height="64" />
                        <div>
                            <h6 class="mb-0"><?php echo htmlspecialchars($instructorName); ?></h6>
                            <small class="text-muted"><?php echo htmlspecialchars($instructorTitle); ?></small>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="resources" role="tabpanel">
                    <h4>Tài liệu bài học hiện tại</h4>
                    <ul class="list-group" id="currentLessonResourcesList">
                        <li class="list-group-item text-muted">Chọn một bài học để xem tài liệu.</li>
                    </ul>
                </div>
                <div class="tab-pane fade" id="notes" role="tabpanel">
                    <p>Tính năng ghi chú sắp ra mắt.</p>
                </div>
                <div class="tab-pane fade" id="announcements" role="tabpanel">
                    <p>Chưa có thông báo nào.</p>
                </div>
            </div>
        </div>
    </div>

    <aside class="course-content-sidebar">
        <div class="sidebar-header d-flex justify-content-between align-items-center p-3">
            <h5 class="mb-0">Nội dung khóa học</h5>
            <button class="btn-close d-lg-none" type="button" id="closeCourseContentSidebarMobile" aria-label="Close"></button>
        </div>
        <div class="accordion accordion-flush" id="courseContentAccordion">
            <?php if ($page_error): ?>
                <div class="p-3 text-danger"><?php echo htmlspecialchars($page_error); ?></div>
            <?php elseif (empty($course_content_for_js)): ?>
                <div class="p-3 text-muted">Không có nội dung cho khóa học này.</div>
            <?php else: ?>
                <?php foreach ($course_content_for_js as $chapter_index => $chapter): ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading-chapter-<?php echo $chapter['id']; ?>">
                            <button class="accordion-button <?php echo $chapter_index !== 0 ? 'collapsed' : ''; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-chapter-<?php echo $chapter['id']; ?>" aria-expanded="<?php echo $chapter_index === 0 ? 'true' : 'false'; ?>">
                                <?php echo htmlspecialchars($chapter['title']); ?>
                                <small class="text-muted ms-auto lectures-count"><?php echo count($chapter['lessons']); ?> bài</small>
                            </button>
                        </h2>
                        <div id="collapse-chapter-<?php echo $chapter['id']; ?>" class="accordion-collapse collapse <?php echo $chapter_index === 0 ? 'show' : ''; ?>" data-bs-parent="#courseContentAccordion">
                            <div class="accordion-body">
                                <ul class="list-unstyled lesson-list">
                                    <?php if (empty($chapter['lessons'])): ?>
                                        <li class="text-muted p-2">Chương này chưa có bài học.</li>
                                    <?php else: ?>
                                        <?php foreach ($chapter['lessons'] as $lesson): ?>
                                            <li>
                                                <a href="#" class="lesson-link <?php echo ($lesson['id'] === $initial_lesson_id) ? 'active-lesson' : ''; ?>"
                                                   data-lesson-id="<?php echo htmlspecialchars($lesson['id']); ?>"
                                                   data-chapter-id="<?php echo htmlspecialchars($chapter['id']); ?>"
                                                   data-video-type="<?php echo htmlspecialchars($lesson['videoType'] ?? ''); ?>"
                                                   data-video-url="<?php echo htmlspecialchars($lesson['videoUrl'] ?? ''); ?>">
                                                    <i class="bi <?php echo ($lesson['id'] === $initial_lesson_id) ? 'bi-pause-circle-fill' : 'bi-play-circle'; ?> me-2 lesson-icon"></i>
                                                    <?php echo htmlspecialchars($lesson['title']); ?>
                                                    <?php if (!empty($lesson['videoUrl'])):
                                                        ?>
                                                    <?php endif; ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </aside>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const courseContentData = <?php echo json_encode($course_content_for_js); ?>;
    const courseIdForFiles = <?php echo json_encode($course_id_from_get); ?>;
    const fileLoaderUrl = <?php echo json_encode($file_loader_url); ?>;
    const youtubeOembedBaseUrl = "https://www.youtube.com/oembed?url=";

    const videoPlayerWrapper = document.getElementById('videoPlayerWrapper');
    const lessonLinks = document.querySelectorAll('.lesson-link');
    const currentLessonResourcesList = document.getElementById('currentLessonResourcesList');

    let currentActiveLessonElement = document.querySelector('.lesson-link.active-lesson');

    function updateVideoPlayer(videoType, videoUrl, chapterId) {
        videoPlayerWrapper.innerHTML = '';
        if (videoType === 'youtube' && videoUrl) {
            fetch(`${youtubeOembedBaseUrl}?url=${encodeURIComponent(videoUrl)}&format=json`)
                .then(response => {
                    if (!response.ok) throw new Error(`YouTube oEmbed API error: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    if (data.html) {
                        let iframeHtml = data.html;
                        iframeHtml = iframeHtml.replace(/width="\d+"/, 'width="100%"');
                        iframeHtml = iframeHtml.replace(/height="\d+"/, 'height="100%"');
                        videoPlayerWrapper.innerHTML = iframeHtml;
                    } else {
                        videoPlayerWrapper.innerHTML = '<p class="text-danger text-center p-3">Lỗi: Không thể nhúng video YouTube.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching YouTube oEmbed:', error);
                    videoPlayerWrapper.innerHTML = `<p class="text-danger text-center p-3">Lỗi tải video YouTube: ${error.message}</p>`;
                });
        } else if (videoType === 'local' && videoUrl && chapterId && courseIdForFiles) {
            const localVideoSrc = `${fileLoaderUrl}?act=serve_course_video&course_id=${encodeURIComponent(courseIdForFiles)}&chapter_id=${encodeURIComponent(chapterId)}&filename=${encodeURIComponent(videoUrl)}`;
            videoPlayerWrapper.innerHTML = `<video controls width="100%" height="auto" id="localVideoPlayer" autoplay><source src="${localVideoSrc}" type="video/mp4">Trình duyệt không hỗ trợ video.</video>`;
        } else if (!videoUrl) {
            videoPlayerWrapper.innerHTML = '<p class="text-center p-5">Bài học này không có video.</p>';
        } else {
            videoPlayerWrapper.innerHTML = '<p class="text-center p-5">Định dạng video không được hỗ trợ hoặc thiếu thông tin.</p>';
        }
    }

    function updateLessonResources(lessonId, chapterId) {
        currentLessonResourcesList.innerHTML = '<li class="list-group-item text-muted">Đang tải tài liệu...</li>';
        let foundLesson = null;
        const chapter = courseContentData.find(ch => ch.id === chapterId);
        if (chapter) {
            foundLesson = chapter.lessons.find(ls => ls.id === lessonId);
        }

        if (foundLesson && foundLesson.resources && foundLesson.resources.length > 0) {
            let resourcesHtml = '';
            foundLesson.resources.forEach(resource => {
                const resourceUrl = `${fileLoaderUrl}?act=serve_course_resource&course_id=${encodeURIComponent(courseIdForFiles)}&chapter_id=${encodeURIComponent(chapterId)}&filename=${encodeURIComponent(resource.path)}`;
                resourcesHtml += `<li class="list-group-item">
                                    <a href="${resourceUrl}" target="_blank" download="${resource.path}">
                                        <i class="bi bi-file-earmark-arrow-down-fill me-2"></i>
                                        ${resource.title} (${resource.path})
                                    </a>
                                  </li>`;
            });
            currentLessonResourcesList.innerHTML = resourcesHtml;
        } else {
            currentLessonResourcesList.innerHTML = '<li class="list-group-item text-muted">Bài học này không có tài liệu nào.</li>';
        }
    }

    lessonLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();

            if (currentActiveLessonElement) {
                currentActiveLessonElement.classList.remove('active-lesson');
                currentActiveLessonElement.querySelector('.lesson-icon').classList.replace('bi-pause-circle-fill', 'bi-play-circle');
            }
            this.classList.add('active-lesson');
            this.querySelector('.lesson-icon').classList.replace('bi-play-circle', 'bi-pause-circle-fill');
            currentActiveLessonElement = this;

            const lessonId = this.dataset.lessonId;
            const chapterId = this.dataset.chapterId;
            const videoType = this.dataset.videoType;
            const videoUrl = this.dataset.videoUrl;

            updateVideoPlayer(videoType, videoUrl, chapterId);
            updateLessonResources(lessonId, chapterId);

            const newUrl = new URL(window.location);
            newUrl.searchParams.set('lessonID', lessonId);
            window.history.pushState({
                path: newUrl.href
            }, '', newUrl.href);

        });
    });

    if (currentActiveLessonElement) {
        const initialLessonId = currentActiveLessonElement.dataset.lessonId;
        const initialChapterId = currentActiveLessonElement.dataset.chapterId;
        if (initialLessonId && initialChapterId) {
            updateLessonResources(initialLessonId, initialChapterId);
        }
    }


    const openSidebarBtn = document.getElementById('openCourseContentSidebarMobile');
    const closeSidebarBtn = document.getElementById('closeCourseContentSidebarMobile');
    const sidebar = document.querySelector('.course-content-sidebar');
    const mainLayout = document.querySelector('.course-main-layout');

    if (openSidebarBtn && sidebar && mainLayout) {
        openSidebarBtn.addEventListener('click', () => {
            sidebar.classList.add('active');
            mainLayout.classList.add('sidebar-active');
        });
    }
    if (closeSidebarBtn && sidebar && mainLayout) {
        closeSidebarBtn.addEventListener('click', () => {
            sidebar.classList.remove('active');
            mainLayout.classList.remove('sidebar-active');
        });
    }

    const totalLessonsCountEl = document.getElementById('totalLessonsCount');
    if (totalLessonsCountEl && courseContentData) {
        let totalLessons = 0;
        courseContentData.forEach(chap => totalLessons += chap.lessons.length);
        totalLessonsCountEl.textContent = totalLessons;
    }
</script>
</body>

</html>