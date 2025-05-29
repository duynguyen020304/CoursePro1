<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!defined('PROJECT_ROOT')) {
    define('PROJECT_ROOT', dirname(__DIR__));
}
if (!defined('UPLOADS_DIR')) {
    define('UPLOADS_DIR', PROJECT_ROOT . '/uploads');
}

function callApi(string $url, string $requestMethod, array $payload = [], ?string $bearerToken = null): array
{
    $jsonPayload = null;
    $methodUpper = strtoupper($requestMethod);

    if ($methodUpper === 'GET' && !empty($payload)) {
        $url .= '?' . http_build_query($payload);
    }

    $headers_array = [
        "Content-Type: application/json; charset=utf-8",
        "Accept: application/json"
    ];
    if ($bearerToken === null && isset($_SESSION['user']['token'])) {
        $bearerToken = $_SESSION['user']['token'];
    }
    if ($bearerToken) {
        $headers_array[] = "Authorization: Bearer " . $bearerToken;
    }
    $headers = implode("\r\n", $headers_array) . "\r\n";


    $opts = [
        'http' => [
            'method' => $methodUpper,
            'header' => $headers,
            'ignore_errors' => true,
            'timeout' => 60
        ]
    ];

    if (in_array($methodUpper, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
        if (!empty($payload)) {
            $jsonPayload = json_encode($payload);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'success' => false,
                    'message' => 'Lỗi nội bộ: Không thể mã hóa payload. Lỗi JSON: ' . json_last_error_msg(),
                    'http_status_code' => 500,
                    'data' => null
                ];
            }
            $opts['http']['content'] = $jsonPayload;
        } elseif (in_array($methodUpper, ['POST', 'PUT'])) {
            $opts['http']['content'] = '{}';
        }
    }


    $context = stream_context_create($opts);
    $response = @file_get_contents($url, false, $context);
    $responseHeaders = $http_response_header ?? [];

    if ($response === false) {
        $error = error_get_last();
        $errorMessage = 'Kết nối API thất bại.';
        if ($error !== null) {
            $errorMessage .= ' Lỗi: ' . $error['message'];
        }
        return ['success' => false, 'message' => $errorMessage, 'http_status_code' => null, 'data' => null];
    }

    $decodedResponse = json_decode($response, true);
    $jsonError = json_last_error();
    $httpStatusCode = null;

    if (!empty($responseHeaders)) {
        foreach ($responseHeaders as $header) {
            if (preg_match('{HTTP/\d\.\d\s+(\d+)\s+}', $header, $match)) {
                $httpStatusCode = intval($match[1]);
                break;
            }
        }
    }
    if ($httpStatusCode === null && $response !== '') $httpStatusCode = 200;
    if ($httpStatusCode === null && $response === '') $httpStatusCode = 204;


    if ($response !== '' && $decodedResponse === null && $jsonError !== JSON_ERROR_NONE) {
        return [
            'success' => false,
            'message' => 'Định dạng phản hồi API không hợp lệ (không phải JSON). Lỗi JSON: ' . json_last_error_msg(),
            'raw_response' => substr($response, 0, 500),
            'http_status_code' => $httpStatusCode,
            'data' => null
        ];
    }

    if ($response === '' || ($decodedResponse === null && $jsonError === JSON_ERROR_NONE)) {
        $isSuccess = ($httpStatusCode >= 200 && $httpStatusCode < 300);
        return [
            'success' => $isSuccess,
            'message' => $isSuccess ? 'Thao tác hoàn tất với phản hồi trống.' : 'Phản hồi trống với mã trạng thái không thành công.',
            'data' => null,
            'raw_response' => $response,
            'http_status_code' => $httpStatusCode
        ];
    }

    if (is_array($decodedResponse)) {
        if (!isset($decodedResponse['http_status_code'])) {
            $decodedResponse['http_status_code'] = $httpStatusCode;
        }
        if (!isset($decodedResponse['success'])) {
            $decodedResponse['success'] = ($httpStatusCode >= 200 && $httpStatusCode < 300);
        }
    } else {
        $isSuccess = ($httpStatusCode >= 200 && $httpStatusCode < 300);
        $decodedResponse = [
            'success' => $isSuccess,
            'message' => $isSuccess ? 'Thao tác thành công.' : 'Thao tác thất bại.',
            'data' => $decodedResponse,
            'http_status_code' => $httpStatusCode
        ];
    }
    return $decodedResponse;
}


function ensureUploadDirectory(string $directoryPath): bool {
    if (!is_dir($directoryPath)) {
        if (!mkdir($directoryPath, 0775, true)) {
            error_log("Failed to create directory: " . $directoryPath);
            return false;
        }
    }
    return true;
}

function deleteFileFromServer(string $filePath): bool {
    if (file_exists($filePath) && is_file($filePath)) {
        if (@unlink($filePath)) {
            error_log("Successfully deleted file: " . $filePath);
            return true;
        } else {
            error_log("Failed to delete file: " . $filePath . ". Error: " . (error_get_last()['message'] ?? 'Unknown error'));
            return false;
        }
    }
    error_log("File not found or not a file, cannot delete: " . $filePath);
    return false;
}


$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
$host = $_SERVER['HTTP_HOST'];

$appRootPath = '';
$path_segments_for_api = explode('/', trim(dirname($_SERVER['SCRIPT_NAME']), '/'));
if (!empty($path_segments_for_api)) {
    if (count($path_segments_for_api) > 1 && $path_segments_for_api[count($path_segments_for_api)-1] === 'controller') {
        array_pop($path_segments_for_api);
        $appRootPath = '/' . implode('/', $path_segments_for_api);
    } elseif (!empty($path_segments_for_api[0])) {
        $appRootPath = '/' . $path_segments_for_api[0];
    }
}
$appRootPath = rtrim($appRootPath, '/');


$apiChapterUrl = $protocol . "://" . $host . $appRootPath . '/api/chapter_api.php';
$apiLessonUrl= $protocol . "://" . $host . $appRootPath . '/api/lesson_api.php';
$apiVideoUrl = $protocol . "://" . $host . $appRootPath . '/api/video_api.php';
$apiResourceUrl = $protocol . "://" . $host . $appRootPath . '/api/resource_api.php';

$requestMethod = $_SERVER['REQUEST_METHOD'];
$contentType = trim(explode(';', strtolower($_SERVER['CONTENT_TYPE'] ?? ''))[0]);

$bearerToken = null;
if (isset($_SESSION['user']['token'])) {
    $bearerToken = $_SESSION['user']['token'];
}


if ($requestMethod === 'POST') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? null;
    $response = ['success' => false, 'message' => 'Hành động không xác định.', 'data' => null, 'errors' => []];

    $courseID = $_POST['courseID'] ?? null;
    $chapterID = $_POST['chapterID'] ?? null;
    $lessonID = $_POST['lessonID'] ?? null;
    $lessonTitle = $_POST['lessonTitle'] ?? null;
    $lessonContent = $_POST['lessonContent'] ?? null;

    if ($action === 'save_lesson_content') {
        $lessonID = str_replace('.', '_', uniqid('lesson_', true));
        $videoTitleForNewLesson = $_POST['videoTitle'] ?? $lessonTitle ?? 'Video không có tiêu đề';
        $videoUrlFromPost = $_POST['video_url'] ?? null;

        if (empty($courseID)) $response['errors'][] = "Course ID không được để trống.";
        if (empty($chapterID)) $response['errors'][] = "Chapter ID không được để trống.";
        if (empty($lessonTitle)) $response['errors'][] = "Tiêu đề bài học không được để trống.";


        if (empty($response['errors'])) {
            $lessonApiPayload = [
                "lessonID" => $lessonID,
                "courseID" => $courseID,
                "chapterID" => $chapterID,
                "title"    => $lessonTitle,
                "content"  => $lessonContent,
            ];
            $lessonApiResponse = callApi($apiLessonUrl, 'POST', $lessonApiPayload, $bearerToken);

            if (!$lessonApiResponse['success']) {
                $response['errors'][] = "Không tạo được bài học: " . ($lessonApiResponse['message'] ?? 'Lỗi API không xác định');
                echo json_encode($response);
                exit;
            }
            $response['data']['lesson'] = $lessonApiResponse['data'] ?? ['lessonID' => $lessonID];
            $response['message'] = $lessonApiResponse['message'] ?? 'Bài học đã được tạo.';


            $videoApiCalled = false;
            if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
                $videoFile = $_FILES['video_file'];
                $allowedVideoExtensions = ['mp4', 'mov', 'avi', 'webm', 'mkv'];
                $videoFileExtension = strtolower(pathinfo($videoFile['name'], PATHINFO_EXTENSION));
                if (!in_array($videoFileExtension, $allowedVideoExtensions)) {
                    $response['errors'][] = "Định dạng file video không hợp lệ. Cho phép: " . implode(', ', $allowedVideoExtensions);
                }

                if(empty($response['errors'])) {
                    $safeCourseID = preg_replace('/[^a-zA-Z0-9_-]/', '_', (string)$courseID);
                    $safeChapterID = preg_replace('/[^a-zA-Z0-9_-]/', '_', (string)$chapterID);
                    $videoUploadDir = UPLOADS_DIR . DIRECTORY_SEPARATOR . $safeCourseID . DIRECTORY_SEPARATOR . $safeChapterID . DIRECTORY_SEPARATOR . 'videos';
                    if (ensureUploadDirectory($videoUploadDir)) {
                        $uniqueFileID = str_replace('.', '_', uniqid('vid_', true));
                        $newVideoFileName = $uniqueFileID . "." . $videoFileExtension;
                        $videoDestinationPath = $videoUploadDir . DIRECTORY_SEPARATOR . $newVideoFileName;

                        if (move_uploaded_file($videoFile['tmp_name'], $videoDestinationPath)) {
                            $videoApiPayload = ['lessonID' => $lessonID, 'url' => $newVideoFileName, 'title' => $videoFile['name']];
                            $apiVideoResponse = callApi($apiVideoUrl, 'POST', $videoApiPayload, $bearerToken);
                            if ($apiVideoResponse['success']) {
                                $response['data']['video'] = $apiVideoResponse['data'];
                            } else {
                                $response['errors'][] = "Lưu file video thất bại (API): " . ($apiVideoResponse['message'] ?? '');
                                deleteFileFromServer($videoDestinationPath);
                            }
                        } else { $response['errors'][] = "Không thể di chuyển file video đã tải lên."; }
                    } else { $response['errors'][] = "Không thể tạo thư mục video."; }
                }
                $videoApiCalled = true;
            } elseif (!empty($videoUrlFromPost)) {
                $finalVideoTitleForApi = $videoTitleForNewLesson;
                if (preg_match('/(youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $videoUrlFromPost, $matches)) {
                    $youtubeVideoId = $matches[2];
                    $oembed_url = "https://www.youtube.com/oembed?url=" . urlencode("https://www.youtube.com/watch?v=" . $youtubeVideoId) . "&format=json";

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $oembed_url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                    curl_setopt($ch, CURLOPT_USERAGENT, 'CourseProApp/1.0 (PHP cURL)');
                    $oembed_response_json = curl_exec($ch);
                    $curl_error = curl_error($ch);
                    curl_close($ch);

                    if (!$curl_error && $oembed_response_json) {
                        $oEmbedData = json_decode($oembed_response_json, true);
                        if (json_last_error() === JSON_ERROR_NONE && isset($oEmbedData['title'])) {
                            $finalVideoTitleForApi = $oEmbedData['title'];
                        } else {
                            error_log("YouTube oEmbed JSON Decode Error or missing title for URL ($videoUrlFromPost): " . json_last_error_msg());
                        }
                    } else {
                        error_log("YouTube oEmbed cURL Error for URL ($videoUrlFromPost): " . $curl_error);
                    }
                }

                $videoApiPayload = ['lessonID' => $lessonID, 'url' => $videoUrlFromPost, 'title' => $finalVideoTitleForApi];
                $apiVideoResponse = callApi($apiVideoUrl, 'POST', $videoApiPayload, $bearerToken);
                if ($apiVideoResponse['success']) {
                    $response['data']['video'] = $apiVideoResponse['data'];
                } else {
                    $response['errors'][] = "Lưu URL video thất bại (API): " . ($apiVideoResponse['message'] ?? '');
                }
                $videoApiCalled = true;
            }

            if (isset($_FILES['resource_files']) && is_array($_FILES['resource_files']['name'])) {
                $safeCourseID = preg_replace('/[^a-zA-Z0-9_-]/', '_', (string)$courseID);
                $safeChapterID = preg_replace('/[^a-zA-Z0-9_-]/', '_', (string)$chapterID);
                $resourceUploadDir = UPLOADS_DIR . DIRECTORY_SEPARATOR . $safeCourseID . DIRECTORY_SEPARATOR . $safeChapterID . DIRECTORY_SEPARATOR . 'resources';
                ensureUploadDirectory($resourceUploadDir);

                $response['data']['resources'] = [];
                for ($i = 0; $i < count($_FILES['resource_files']['name']); $i++) {
                    if ($_FILES['resource_files']['error'][$i] === UPLOAD_ERR_OK) {
                        $originalResourceFileName = $_FILES['resource_files']['name'][$i];

                        $uniqueFileID = str_replace('.', '_', uniqid('res_', true));
                        $resourceFileExtension = strtolower(pathinfo($originalResourceFileName, PATHINFO_EXTENSION));
                        $newResourceFileName = $uniqueFileID . "." . $resourceFileExtension;
                        $resourceDestinationPath = $resourceUploadDir . DIRECTORY_SEPARATOR . $newResourceFileName;

                        if (move_uploaded_file($_FILES['resource_files']['tmp_name'][$i], $resourceDestinationPath)) {
                            $resourceApiPayload = ['lessonID' => $lessonID, 'resourcePath' => $newResourceFileName, 'title' => $originalResourceFileName];
                            $apiResourceResponse = callApi($apiResourceUrl, 'POST', $resourceApiPayload, $bearerToken);
                            if ($apiResourceResponse['success']) {
                                $response['data']['resources'][] = $apiResourceResponse['data'];
                            } else {
                                $response['errors'][] = "Lưu tài liệu '{$originalResourceFileName}' thất bại (API): " . ($apiResourceResponse['message'] ?? '');
                                deleteFileFromServer($resourceDestinationPath);
                            }
                        } else { $response['errors'][] = "Không thể di chuyển file tài liệu '{$originalResourceFileName}'."; }
                    }
                }
            }
            $response['success'] = empty($response['errors']);
            if (!$response['success']) {
                $response['message'] = "Đã xảy ra lỗi khi lưu nội dung bài học.";
            } else if (!$videoApiCalled && (!isset($response['data']['resources']) || empty($response['data']['resources']))) {
                $response['message'] = 'Bài học đã được tạo (không có video/tài liệu mới).';
            } else {
                $response['message'] = 'Nội dung bài học đã được xử lý và lưu.';
            }

        } else {
            $response['message'] = "Dữ liệu đầu vào không hợp lệ.";
        }
        http_response_code($response['success'] ? 200 : 400);
        echo json_encode($response);
        exit;

    } elseif ($action === 'update_lesson_video') {
        $lessonID = $_POST['lessonID'] ?? null;
        $existingVideoID = $_POST['existingVideoID'] ?? null;
        $videoUrlFromPost = $_POST['video_url'] ?? null;
        $videoTitleForUpdate = $_POST['lessonTitle'] ?? 'Video cập nhật';

        if (empty($lessonID)) $response['errors'][] = "Lesson ID không được để trống để cập nhật video.";
        if (empty($courseID)) $response['errors'][] = "Course ID không được để trống (cho đường dẫn file).";
        if (empty($chapterID)) $response['errors'][] = "Chapter ID không được để trống (cho đường dẫn file).";


        if (empty($response['errors'])) {
            if ($existingVideoID && ( (isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) || !empty($videoUrlFromPost) ) ) {
                $oldVideoDetailsResp = callApi($apiVideoUrl . '?videoID=' . $existingVideoID, 'GET', [], $bearerToken);
                if ($oldVideoDetailsResp['success'] && !empty($oldVideoDetailsResp['data'])) {
                    $oldVideoData = is_array($oldVideoDetailsResp['data']) ? $oldVideoDetailsResp['data'][0] : $oldVideoDetailsResp['data'];
                    $oldVideoUrl = $oldVideoData['url'] ?? null;

                    $deleteApiResp = callApi($apiVideoUrl, 'DELETE', ['videoID' => $existingVideoID], $bearerToken);
                    if ($deleteApiResp['success']) {
                        $response['message'] = 'Video cũ đã được xóa khỏi DB. ';
                        if ($oldVideoUrl && !filter_var($oldVideoUrl, FILTER_VALIDATE_URL)) {
                            $safeCourseID = preg_replace('/[^a-zA-Z0-9_-]/', '_', (string)$courseID);
                            $safeChapterID = preg_replace('/[^a-zA-Z0-9_-]/', '_', (string)$chapterID);
                            $oldVideoPath = UPLOADS_DIR . DIRECTORY_SEPARATOR . $safeCourseID . DIRECTORY_SEPARATOR . $safeChapterID . DIRECTORY_SEPARATOR . 'videos' . DIRECTORY_SEPARATOR . $oldVideoUrl;
                            if (deleteFileFromServer($oldVideoPath)) {
                                $response['message'] .= 'File video cũ đã được xóa khỏi server. ';
                            } else {
                                $response['errors'][] = 'Không thể xóa file video cũ từ server: ' . $oldVideoPath;
                            }
                        }
                    } else {
                        $response['errors'][] = 'Không thể xóa video cũ khỏi DB (API): ' . ($deleteApiResp['message'] ?? 'Lỗi không rõ');
                    }
                } else {
                    $response['errors'][] = 'Không tìm thấy chi tiết video cũ để xóa file.';
                }
            }

            $newVideoData = null;
            if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
                $videoFile = $_FILES['video_file'];
                $allowedVideoExtensions = ['mp4', 'mov', 'avi', 'webm', 'mkv'];
                $videoFileExtension = strtolower(pathinfo($videoFile['name'], PATHINFO_EXTENSION));
                if (!in_array($videoFileExtension, $allowedVideoExtensions)) {
                    $response['errors'][] = "Định dạng file video không hợp lệ. Cho phép: " . implode(', ', $allowedVideoExtensions);
                }

                if(empty($response['errors'])) {
                    $safeCourseID = preg_replace('/[^a-zA-Z0-9_-]/', '_', (string)$courseID);
                    $safeChapterID = preg_replace('/[^a-zA-Z0-9_-]/', '_', (string)$chapterID);
                    $videoUploadDir = UPLOADS_DIR . DIRECTORY_SEPARATOR . $safeCourseID . DIRECTORY_SEPARATOR . $safeChapterID . DIRECTORY_SEPARATOR . 'videos';

                    if (ensureUploadDirectory($videoUploadDir)) {
                        $uniqueFileID = str_replace('.', '_', uniqid('vid_', true));
                        $newVideoFileName = $uniqueFileID . "." . $videoFileExtension;
                        $videoDestinationPath = $videoUploadDir . DIRECTORY_SEPARATOR . $newVideoFileName;

                        if (move_uploaded_file($videoFile['tmp_name'], $videoDestinationPath)) {
                            $videoApiPayload = ['lessonID' => $lessonID, 'url' => $newVideoFileName, 'title' => $videoFile['name']];
                            $apiVideoResponse = callApi($apiVideoUrl, 'POST', $videoApiPayload, $bearerToken);
                            if ($apiVideoResponse['success']) {
                                $newVideoData = $apiVideoResponse['data'];
                                $response['message'] .= 'Video mới đã được tải lên và lưu. ';
                            } else {
                                $response['errors'][] = "Lưu file video mới thất bại (API): " . ($apiVideoResponse['message'] ?? '');
                                deleteFileFromServer($videoDestinationPath);
                            }
                        } else { $response['errors'][] = "Không thể di chuyển file video mới."; }
                    } else { $response['errors'][] = "Không thể tạo thư mục video mới."; }
                }
            } elseif (!empty($videoUrlFromPost)) {
                $finalVideoTitleForApi = $videoTitleForUpdate;
                if (preg_match('/(youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $videoUrlFromPost, $matches)) {
                    $youtubeVideoId = $matches[2];
                    $oembed_url = "https://www.youtube.com/oembed?url=" . urlencode("https://www.youtube.com/watch?v=" . $youtubeVideoId) . "&format=json";

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $oembed_url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                    curl_setopt($ch, CURLOPT_USERAGENT, 'CourseProApp/1.0 (PHP cURL)');
                    $oembed_response_json = curl_exec($ch);
                    $curl_error = curl_error($ch);
                    curl_close($ch);

                    if (!$curl_error && $oembed_response_json) {
                        $oEmbedData = json_decode($oembed_response_json, true);
                        if (json_last_error() === JSON_ERROR_NONE && isset($oEmbedData['title'])) {
                            $finalVideoTitleForApi = $oEmbedData['title'];
                        } else {
                            error_log("YouTube oEmbed JSON Decode Error or missing title for URL ($videoUrlFromPost) during update: " . json_last_error_msg());
                        }
                    } else {
                        error_log("YouTube oEmbed cURL Error for URL ($videoUrlFromPost) during update: " . $curl_error);
                    }
                }

                $videoApiPayload = ['lessonID' => $lessonID, 'url' => $videoUrlFromPost, 'title' => $finalVideoTitleForApi];
                $apiVideoResponse = callApi($apiVideoUrl, 'POST', $videoApiPayload, $bearerToken);
                if ($apiVideoResponse['success']) {
                    $newVideoData = $apiVideoResponse['data'];
                    $response['message'] .= 'URL video mới đã được lưu. ';
                } else {
                    $response['errors'][] = "Lưu URL video mới thất bại (API): " . ($apiVideoResponse['message'] ?? '');
                }
            }
            $response['data']['video'] = $newVideoData;

            if (isset($_FILES['resource_files']) && is_array($_FILES['resource_files']['name'])) {
                $safeCourseID_res = preg_replace('/[^a-zA-Z0-9_-]/', '_', (string)$courseID);
                $safeChapterID_res = preg_replace('/[^a-zA-Z0-9_-]/', '_', (string)$chapterID);
                $resourceUploadDir = UPLOADS_DIR . DIRECTORY_SEPARATOR . $safeCourseID_res . DIRECTORY_SEPARATOR . $safeChapterID_res . DIRECTORY_SEPARATOR . 'resources';
                ensureUploadDirectory($resourceUploadDir);
                $savedResourcesData = [];

                for ($i = 0; $i < count($_FILES['resource_files']['name']); $i++) {
                    if ($_FILES['resource_files']['error'][$i] === UPLOAD_ERR_OK) {
                        $originalResourceFileName = $_FILES['resource_files']['name'][$i];
                        $uniqueFileID_res = str_replace('.', '_', uniqid('res_', true));
                        $resourceFileExtension = strtolower(pathinfo($originalResourceFileName, PATHINFO_EXTENSION));
                        $newResourceFileName = $uniqueFileID_res . "." . $resourceFileExtension;
                        $resourceDestinationPath = $resourceUploadDir . DIRECTORY_SEPARATOR . $newResourceFileName;

                        if (move_uploaded_file($_FILES['resource_files']['tmp_name'][$i], $resourceDestinationPath)) {
                            $resourceApiPayload = ['lessonID' => $lessonID, 'resourcePath' => $newResourceFileName, 'title' => $originalResourceFileName];
                            $apiResourceResponse = callApi($apiResourceUrl, 'POST', $resourceApiPayload, $bearerToken);
                            if ($apiResourceResponse['success']) {
                                $savedResourcesData[] = $apiResourceResponse['data'];
                            } else {
                                $response['errors'][] = "Lưu tài liệu '{$originalResourceFileName}' thất bại (API): " . ($apiResourceResponse['message'] ?? '');
                                deleteFileFromServer($resourceDestinationPath);
                            }
                        } else { $response['errors'][] = "Không thể di chuyển file tài liệu '{$originalResourceFileName}'."; }
                    }
                }
                if (!empty($savedResourcesData)) {
                    $response['data']['resources'] = $savedResourcesData;
                    $response['message'] .= count($savedResourcesData) . ' tài liệu mới đã được thêm. ';
                }
            }


            if (empty($response['errors'])) {
                $response['success'] = true;
                if (trim($response['message']) === '' || trim($response['message']) === 'Video cũ đã được xóa khỏi DB.'){
                    $response['message'] = 'Không có thay đổi video hoặc tài liệu nào được thực hiện, hoặc chỉ video cũ được xóa.';
                }
            } else {
                $response['message'] = implode(" ", $response['errors']);
            }

        } else {
            $response['message'] = "Dữ liệu đầu vào không hợp lệ cho việc cập nhật video.";
        }
        http_response_code($response['success'] ? 200 : 400);
        echo json_encode($response);
        exit;

    } elseif ($action === 'add_resources_to_lesson_edit') {
        $lessonID = $_POST['lessonID'] ?? null;
        if (empty($lessonID)) $response['errors'][] = "Lesson ID không được để trống để thêm tài liệu.";
        if (empty($courseID)) $response['errors'][] = "Course ID không được để trống (cho đường dẫn file).";
        if (empty($chapterID)) $response['errors'][] = "Chapter ID không được để trống (cho đường dẫn file).";


        if (empty($response['errors']) && isset($_FILES['resource_files']) && is_array($_FILES['resource_files']['name']) && count(array_filter($_FILES['resource_files']['name'])) > 0 ) {
            $safeCourseID = preg_replace('/[^a-zA-Z0-9_-]/', '_', (string)$courseID);
            $safeChapterID = preg_replace('/[^a-zA-Z0-9_-]/', '_', (string)$chapterID);
            $resourceUploadDir = UPLOADS_DIR . DIRECTORY_SEPARATOR . $safeCourseID . DIRECTORY_SEPARATOR . $safeChapterID . DIRECTORY_SEPARATOR . 'resources';
            ensureUploadDirectory($resourceUploadDir);

            $savedResourcesData = [];
            $filesProcessed = false;
            for ($i = 0; $i < count($_FILES['resource_files']['name']); $i++) {
                if ($_FILES['resource_files']['error'][$i] === UPLOAD_ERR_OK) {
                    $filesProcessed = true;
                    $originalResourceFileName = $_FILES['resource_files']['name'][$i];

                    $uniqueFileID = str_replace('.', '_', uniqid('res_', true));
                    $resourceFileExtension = strtolower(pathinfo($originalResourceFileName, PATHINFO_EXTENSION));
                    $newResourceFileName = $uniqueFileID . "." . $resourceFileExtension;
                    $resourceDestinationPath = $resourceUploadDir . DIRECTORY_SEPARATOR . $newResourceFileName;

                    if (move_uploaded_file($_FILES['resource_files']['tmp_name'][$i], $resourceDestinationPath)) {
                        $resourceApiPayload = ['lessonID' => $lessonID, 'resourcePath' => $newResourceFileName, 'title' => $originalResourceFileName];
                        $apiResourceResponse = callApi($apiResourceUrl, 'POST', $resourceApiPayload, $bearerToken);
                        if ($apiResourceResponse['success']) {
                            $savedResourcesData[] = $apiResourceResponse['data'];
                        } else {
                            $response['errors'][] = "Lưu tài liệu '{$originalResourceFileName}' thất bại (API): " . ($apiResourceResponse['message'] ?? '');
                            deleteFileFromServer($resourceDestinationPath);
                        }
                    } else { $response['errors'][] = "Không thể di chuyển file tài liệu '{$originalResourceFileName}'."; }
                }
            }
            if (empty($response['errors']) && $filesProcessed) {
                $response['success'] = true;
                $response['message'] = 'Tài liệu mới đã được thêm vào bài học.';
                $response['data']['resources'] = $savedResourcesData;
            } elseif (!$filesProcessed && empty($response['errors'])) {
                $response['success'] = true;
                $response['message'] = "Không có file tài liệu hợp lệ nào được cung cấp để thêm.";
            }
            else {
                $response['message'] = "Đã xảy ra lỗi khi thêm tài liệu.";
            }
        } elseif (empty($response['errors'])) {
            $response['message'] = "Không có file tài liệu nào được cung cấp để thêm.";
            $response['success'] = true;
        } else {
            $response['message'] = "Dữ liệu đầu vào không hợp lệ để thêm tài liệu.";
        }
        http_response_code($response['success'] ? 200 : 400);
        echo json_encode($response);
        exit;

    } elseif ($contentType === 'application/json') {
        $inputJSON = file_get_contents('php://input');
        $inputData = json_decode($inputJSON, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Dữ liệu JSON không hợp lệ: ' . json_last_error_msg()]);
            exit;
        }

        $requiredFields = ['courseID', 'title'];
        $missingFields = [];
        foreach ($requiredFields as $field) {
            if (!isset($inputData[$field]) || (is_string($inputData[$field]) && trim($inputData[$field]) === '')) {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu các trường bắt buộc cho chương: ' . implode(', ', $missingFields)]);
            exit;
        }


        $chapterPayload = [
            'courseID'    => $inputData['courseID'],
            'title'       => $inputData['title'],
            'description' => $inputData['description'] ?? ''
        ];
        $apiResponse = callApi($apiChapterUrl, 'POST', $chapterPayload, $bearerToken);
        http_response_code($apiResponse['http_status_code'] ?? ($apiResponse['success'] ? 201 : 500));
        echo json_encode($apiResponse);
        exit;
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Yêu cầu POST không hợp lệ hoặc action không được hỗ trợ.']);
        exit;
    }

} elseif ($requestMethod === 'GET') {
    header('Content-Type: application/json');
    $queryParams = [];
    if (isset($_GET['courseID'])) {
        $queryParams['courseID'] = $_GET['courseID'];
    }
    $apiResponse = callApi($apiChapterUrl, 'GET', $queryParams, $bearerToken);
    http_response_code($apiResponse['http_status_code'] ?? ($apiResponse['success'] ? 200 : 500));
    echo json_encode($apiResponse);
    exit;

} else {
    header('Content-Type: application/json');
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Phương thức ' . $requestMethod . ' không được phép cho tài nguyên này.']);
    exit;
}
?>
