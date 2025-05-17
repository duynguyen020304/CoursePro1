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

function callApi(string $url, string $requestMethod, array $payload = []): array
{
    $jsonPayload = null;
    $methodUpper = strtoupper($requestMethod);
    if (in_array($methodUpper, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
        if (!empty($payload)) {
            $jsonPayload = json_encode($payload);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'success' => false,
                    'message' => 'Lỗi nội bộ: Không thể mã hóa payload. Lỗi JSON: ' . json_last_error_msg(),
                    'http_status_code' => 500
                ];
            }
        } elseif (in_array($methodUpper, ['POST', 'PUT'])) {
            $jsonPayload = '{}';
        }
    } elseif ($methodUpper === 'GET' && !empty($payload)) {
        $url .= '?' . http_build_query($payload);
    }

    $headers = "Content-Type: application/json; charset=utf-8\r\n" .
        "Accept: application/json\r\n";
    if (isset($_SESSION['user']['token'])) {
        $token = $_SESSION['user']['token'];
        $headers .= "Authorization: Bearer " . $token . "\r\n";
    }

    $opts = [
        'http' => [
            'method' => $methodUpper,
            'header' => $headers,
            'ignore_errors' => true,
            'timeout' => 15
        ]
    ];

    if ($jsonPayload !== null) {
        $opts['http']['content'] = $jsonPayload;
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
        return ['success' => false, 'message' => $errorMessage, 'http_status_code' => null];
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

    if ($response !== '' && $decodedResponse === null && $jsonError !== JSON_ERROR_NONE) {
        return [
            'success' => false,
            'message' => 'Định dạng phản hồi API không hợp lệ (không phải JSON). Lỗi JSON: ' . json_last_error_msg(),
            'raw_response' => $response,
            'http_status_code' => $httpStatusCode
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

$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
$host = $_SERVER['HTTP_HOST'];
$scriptContainingDir = dirname($_SERVER['SCRIPT_NAME']);
$appRootPath = dirname($scriptContainingDir);
if ($appRootPath === '/' || $appRootPath === '\\') {
    $appRootPath = '';
}

$apiChapterUrl = $protocol . "://" . $host . $appRootPath . '/api/chapter_api.php';
$apiLessonUrl= $protocol . "://" . $host . $appRootPath . '/api/lesson_api.php';
$apiVideoUrl = $protocol . "://" . $host . $appRootPath . '/api/video_api.php';
$apiResourceUrl = $protocol . "://" . $host . $appRootPath . '/api/resource_api.php';

$requestMethod = $_SERVER['REQUEST_METHOD'];
$contentType = trim(explode(';', strtolower($_SERVER['CONTENT_TYPE'] ?? ''))[0]);

if ($requestMethod === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'save_lesson_content') {
        header('Content-Type: application/json');
        $response = ['success' => false, 'message' => 'Bắt đầu xử lý nội dung bài học.', 'data' => null, 'errors' => []];
        $formErrors = [];

        $courseID = $_POST['courseID'] ?? null;
        $chapterID = $_POST['chapterID'] ?? null;
        $lessonID = str_replace('.', '_', uniqid('lesson_', true));
        $lessonTitle = $_POST['lessonTitle'] ?? 'Bài học không có tiêu đề';
        $videoTitle = $_POST['videoTitle'] ?? $lessonTitle;
        $videoUrlFromPost = $_POST['video_url'] ?? null;

        if (empty($courseID)) $formErrors[] = "Course ID không được để trống.";
        if (empty($chapterID)) $formErrors[] = "Lesson ID (Chapter ID) không được để trống.";

        $savedData = ['video' => null, 'resources' => []];

        if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
            $videoFile = $_FILES['video_file'];
            $originalVideoFileName = $videoFile['name'];
            $videoFileTmpName = $videoFile['tmp_name'];
            $videoFileSize = $videoFile['size'];
            $videoFileExtension = strtolower(pathinfo($originalVideoFileName, PATHINFO_EXTENSION));

            $allowedVideoExtensions = ['mp4', 'mov', 'avi', 'webm', 'mkv'];
            $maxVideoFileSize = 200 * 1024 * 1024;

            if (!in_array($videoFileExtension, $allowedVideoExtensions)) {
                $formErrors[] = "Định dạng file video không hợp lệ. Cho phép: " . implode(', ', $allowedVideoExtensions);
            }
            if ($videoFileSize > $maxVideoFileSize) {
                $formErrors[] = "Kích thước file video quá lớn. Tối đa: " . ($maxVideoFileSize / 1024 / 1024) . "MB.";
            }

            if (empty($formErrors)) {
                $lessonApiPayload = [
                    "lessonID" => $lessonID,
                    "courseID" => $courseID,
                    "chapterID" => $chapterID,
                    "title"    => $lessonTitle,
                ];
                $lessonApiResponse = callApi($apiLessonUrl, 'POST', $lessonApiPayload);
                if ($lessonApiResponse['success']) {
                    $savedData['lesson']['message'] = $lessonApiResponse['message'] ?? 'Bài học đã được thêm qua API.';
                }
                else {
                    $formErrors[] = "Không tạo được bài học: " . ($lessonApiResponse['message'] ?? 'Lỗi API không xác định');
                    exit;
                }
                $safeCourseID = preg_replace('/[^a-zA-Z0-9_-]/', '_', (string)$courseID);
                $safeChapterID = preg_replace('/[^a-zA-Z0-9_-]/', '_', (string)$chapterID);
                $videoUploadDir = UPLOADS_DIR . DIRECTORY_SEPARATOR . $safeCourseID . DIRECTORY_SEPARATOR . $safeChapterID . DIRECTORY_SEPARATOR . 'videos';

                if (ensureUploadDirectory($videoUploadDir)) {
                    $uniqueFileID = str_replace('.', '_', uniqid('vid_', true));
                    $newVideoFileName = $uniqueFileID . "." . $videoFileExtension;
                    $videoDestinationPath = $videoUploadDir . DIRECTORY_SEPARATOR . $newVideoFileName;

                    if (move_uploaded_file($videoFileTmpName, $videoDestinationPath)) {
                        $videoApiPayload = [
                            'lessonID' => $lessonID,
                            'url'      => $newVideoFileName,
                            'title'    => $originalVideoFileName
                        ];
                        $apiVideoResponse = callApi($apiVideoUrl, 'POST', $videoApiPayload);
                        $videoApiCalled = true;
                        if ($apiVideoResponse['success']) {
                            $savedData['video'] = $apiVideoResponse['data'] ?? ['path' => $newVideoFileName, 'title' => $videoTitle];
                            $savedData['video']['message'] = $apiVideoResponse['message'] ?? 'Video đã được thêm qua API.';
                        } else {
                            $formErrors[] = "File video đã tải lên nhưng gọi API thất bại: " . ($apiVideoResponse['message'] ?? 'Lỗi API không xác định');
                        }
                    } else {
                        $formErrors[] = "Lỗi hệ thống: Không thể lưu file video đã tải lên.";
                    }
                } else {
                    $formErrors[] = "Lỗi hệ thống: Không thể chuẩn bị thư mục lưu trữ cho file video.";
                }
            }
        } elseif (isset($_FILES['video_file']) && $_FILES['video_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            $formErrors[] = "Lỗi tải lên file video. Mã lỗi: " . $_FILES['video_file']['error'];
        } elseif (!empty($videoUrlFromPost)) {
            $lessonApiPayload = [
                "lessonID" => $lessonID,
                "courseID" => $courseID,
                "chapterID" => $chapterID,
                "title"    => $lessonTitle,
            ];
            $lessonApiResponse = callApi($apiLessonUrl, 'POST', $lessonApiPayload);
            if ($lessonApiResponse['success']) {
                $savedData['lesson']['message'] = $lessonApiResponse['message'] ?? 'Bài học đã được thêm qua API.';
            }
            else {
                $formErrors[] = "Không tạo được bài học: " . ($lessonApiResponse['message'] ?? 'Lỗi API không xác định');
                exit;
            }
            $ch = curl_init();
            $youtube_oembed_url = "https://www.youtube.com/oembed?url=". $videoUrlFromPost ."&format=json";
            curl_setopt($ch, CURLOPT_URL, $youtube_oembed_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response_curl = curl_exec($ch);
            curl_close($ch);
            $data = json_decode($response_curl, true);
            $videoApiPayload = [
                'lessonID' => $lessonID,
                'url'      => $videoUrlFromPost,
                'title'    => $data['title'] ?? $videoTitle
            ];
            $apiVideoResponse = callApi($apiVideoUrl, 'POST', $videoApiPayload);
            $videoApiCalled = true;
            if ($apiVideoResponse['success']) {
                $savedData['video'] = $apiVideoResponse['data'] ?? ['url' => $videoUrlFromPost, 'title' => $videoTitle];
                $savedData['video']['message'] = $apiVideoResponse['message'] ?? 'URL video đã được thêm qua API.';
            } else {
                $formErrors[] = "URL video được cung cấp nhưng gọi API thất bại: " . ($apiVideoResponse['message'] ?? 'Lỗi API không xác định');
            }
        }

        if (isset($_FILES['resource_files']) && is_array($_FILES['resource_files']['name'])) {
            $safeCourseID = preg_replace('/[^a-zA-Z0-9_-]/', '_', (string)$courseID);
            $safeChapterID = preg_replace('/[^a-zA-Z0-9_-]/', '_', (string)$chapterID);
            $resourceUploadDir = UPLOADS_DIR . DIRECTORY_SEPARATOR . $safeCourseID . DIRECTORY_SEPARATOR . $safeChapterID . DIRECTORY_SEPARATOR . 'resources';

            for ($i = 0; $i < count($_FILES['resource_files']['name']); $i++) {
                if ($_FILES['resource_files']['error'][$i] === UPLOAD_ERR_OK) {
                    $originalResourceFileName = $_FILES['resource_files']['name'][$i];
                    $resourceFileTmpName = $_FILES['resource_files']['tmp_name'][$i];
                    $resourceFileSize = $_FILES['resource_files']['size'][$i];
                    $resourceFileExtension = strtolower(pathinfo($originalResourceFileName, PATHINFO_EXTENSION));
                    $resourceTitle = $_POST['resource_titles'][$i] ?? pathinfo($originalResourceFileName, PATHINFO_FILENAME);
                    $allowedResourceExtensions = ['pdf', 'zip', 'doc', 'docx', 'ppt', 'pptx', 'txt', 'xls', 'xlsx', 'jpg',
                        'jpeg', 'png', 'gif', 'rar', 'mp3', 'mp4', 'avi', 'webm', 'mkv'];
                    $maxResourceFileSize = 500 * 1024 * 1024;

                    $currentFileErrors = [];
                    if (!in_array($resourceFileExtension, $allowedResourceExtensions)) {
                        $currentFileErrors[] = "Định dạng file không hợp lệ cho tài liệu '{$originalResourceFileName}'. Cho phép: " . implode(', ', $allowedResourceExtensions);
                    }
                    if ($resourceFileSize > $maxResourceFileSize) {
                        $currentFileErrors[] = "Kích thước file tài liệu '{$originalResourceFileName}' quá lớn. Tối đa: " . ($maxResourceFileSize / 1024 / 1024) . "MB.";
                    }

                    if (!empty($currentFileErrors)) {
                        $formErrors = array_merge($formErrors, $currentFileErrors);
                        continue;
                    }

                    if (ensureUploadDirectory($resourceUploadDir)) {
                        $uniqueFileID = str_replace('.', '_', uniqid('res_', true));
                        $newResourceFileName = $uniqueFileID . "." . $resourceFileExtension;
                        $resourceDestinationPath = $resourceUploadDir . DIRECTORY_SEPARATOR . $newResourceFileName;

                        if (move_uploaded_file($resourceFileTmpName, $resourceDestinationPath)) {
                            $apiResourcePayload = [
                                'lessonID'     => $lessonID,
                                'resourcePath' => $newResourceFileName,
                                'title'        => $resourceTitle
                            ];
                            $apiResourceResponse = callApi($apiResourceUrl, 'POST', $apiResourcePayload);
                            if ($apiResourceResponse['success']) {
                                $savedData['resources'][] = $apiResourceResponse['data'] ?? ['path' => $newResourceFileName, 'title' => $resourceTitle, 'message' => $apiResourceResponse['message'] ?? 'Tài liệu đã được thêm qua API.'];
                            } else {
                                $formErrors[] = "Tài liệu '{$originalResourceFileName}' đã tải lên nhưng gọi API thất bại: " . ($apiResourceResponse['message'] ?? 'Lỗi API không xác định');
                            }
                        } else {
                            $formErrors[] = "Lỗi hệ thống: Không thể lưu file tài liệu '{$originalResourceFileName}'.";
                        }
                    } else {
                        $formErrors[] = "Lỗi hệ thống: Không thể chuẩn bị thư mục lưu trữ cho tài liệu '{$originalResourceFileName}'.";
                    }
                } elseif ($_FILES['resource_files']['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                    $formErrors[] = "Lỗi tải lên file tài liệu '{$_FILES['resource_files']['name'][$i]}'. Mã lỗi: " . $_FILES['resource_files']['error'][$i];
                }
            }
        }

        if (empty($formErrors)) {
            if ($savedData['video'] || !empty($savedData['resources']) || $videoApiCalled) {
                $response['success'] = true;
                $response['message'] = 'Nội dung bài học đã được xử lý.';
                $response['data'] = $savedData;
                http_response_code(200);
            } else {
                $response['success'] = true;
                $response['message'] = 'Không có file video hoặc tài liệu nào được cung cấp hoặc xử lý.';
                http_response_code(200);
            }
        } else {
            $response['success'] = false;
            $response['message'] = 'Đã xảy ra lỗi trong quá trình xử lý nội dung bài học.';
            $response['errors'] = $formErrors;
            http_response_code(400);
        }
        echo json_encode($response);
        exit;

    } elseif ($contentType === 'application/json') {
        $inputJSON = file_get_contents('php://input');
        $inputData = json_decode($inputJSON, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            header('Content-Type: application/json');
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
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu các trường bắt buộc cho chương: ' . implode(', ', $missingFields)]);
            exit;
        }

        $chapterPayload = [
            'courseID'    => $inputData['courseID'],
            'title'       => $inputData['title'],
            'description' => $inputData['description'] ?? ''
        ];
        $apiResponse = callApi($apiChapterUrl, 'POST', $chapterPayload);
        header('Content-Type: application/json');
        $statusCode = $apiResponse['http_status_code'] ?? ($apiResponse['success'] ? 201 : 500);
        http_response_code($statusCode);
        echo json_encode($apiResponse);
        exit;
    } else {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Yêu cầu POST không hợp lệ. Content-Type không được hỗ trợ hoặc thiếu thông tin action.']);
        exit;
    }

} elseif ($requestMethod === 'GET') {
    $queryParams = [];
    if (isset($_GET['courseID'])) {
        $queryParams['courseID'] = $_GET['courseID'];
    }
    $apiResponse = callApi($apiChapterUrl, 'GET', $queryParams);
    header('Content-Type: application/json');
    $statusCode = $apiResponse['http_status_code'] ?? ($apiResponse['success'] ? 200 : 500);
    http_response_code($statusCode);
    echo json_encode($apiResponse);
    exit;

} else {
    header('Content-Type: application/json');
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Phương thức ' . $requestMethod . ' không được phép cho tài nguyên này.']);
    exit;
}
?>