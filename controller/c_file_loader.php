<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define project root and uploads directory.
define('PROJECT_ROOT', dirname(__DIR__));
define('UPLOADS_DIR', PROJECT_ROOT . '/uploads');

/**
 * Calls an external API.
 */
function callApi(string $url, string $requestMethod, array $payload = []): array
{
    $jsonPayload = null;
    if (!empty($payload) && in_array(strtoupper($requestMethod), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
        $jsonPayload = json_encode($payload);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['success' => false, 'message' => 'Internal error: Failed to encode payload. JSON Error: ' . json_last_error_msg()];
        }
    } elseif (empty($payload) && in_array(strtoupper($requestMethod), ['POST', 'PUT'])) {
        $jsonPayload = '{}';
    }

    $headers = "Content-Type: application/json; charset=utf-8\r\n" .
        "Accept: application/json\r\n";

    if (isset($_SESSION['user']['token'])) {
        $token = $_SESSION['user']['token'];
        $headers .= "Authorization: Bearer " . $token . "\r\n";
    }

    $opts = [
        'http' => [
            'method' => strtoupper($requestMethod),
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
        return ['success' => false, 'message' => 'API connection failed. Unable to reach the server at ' . $url, 'http_status_code' => null];
    }

    $decodedResponse = json_decode($response, true);
    $jsonError = json_last_error();

    $httpStatusCode = null;
    foreach ($responseHeaders as $header) {
        if (preg_match('{HTTP/\d\.\d\s+(\d+)\s+}', $header, $match)) {
            $httpStatusCode = intval($match[1]);
            break;
        }
    }

    if ($response !== '' && $decodedResponse === null && $jsonError !== JSON_ERROR_NONE) {
        return [
            'success' => false,
            'message' => 'Invalid API response format (not JSON). Error: ' . json_last_error_msg(),
            'raw_response' => substr($response, 0, 1000),
            'http_status_code' => $httpStatusCode
        ];
    }

    if ($response === '' || ($decodedResponse === null && $jsonError === JSON_ERROR_NONE)) {
        return [
            'success' => ($httpStatusCode >= 200 && $httpStatusCode < 300),
            'message' => $httpStatusCode === 204 ? 'Operation successful with no content.' : 'Operation completed with empty response.',
            'data' => null,
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
        if (!isset($decodedResponse['data'])) {
            $decodedResponse['data'] = null;
        }
        if (!isset($decodedResponse['message']) && !$decodedResponse['success']) {
            $decodedResponse['message'] = 'API request failed with status code ' . $httpStatusCode;
        } elseif (!isset($decodedResponse['message']) && $decodedResponse['success']) {
            $decodedResponse['message'] = 'API request successful.';
        }
    } else {
        $decodedResponse = [
            'success' => ($httpStatusCode >= 200 && $httpStatusCode < 300),
            'message' => 'API returned non-array JSON, but was decoded.',
            'data' => $decodedResponse,
            'http_status_code' => $httpStatusCode
        ];
    }

    return $decodedResponse;
}
// Define project root and uploads directory.
if (!defined('PROJECT_ROOT')) {
    define('PROJECT_ROOT', dirname(__DIR__));
}
if (!defined('UPLOADS_DIR')) {
    define('UPLOADS_DIR', PROJECT_ROOT . '/uploads');
}

// Hàm trợ giúp để phục vụ tệp an toàn
function serve_file(string $filePath, string $requestedFilename): void
{
    if (file_exists($filePath) && is_readable($filePath)) {
        $mimeType = mime_content_type($filePath);
        if (!$mimeType) { // Fallback nếu mime_content_type không hoạt động
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            switch ($extension) {
                case 'jpeg':
                case 'jpg':
                    $mimeType = 'image/jpeg';
                    break;
                case 'png':
                    $mimeType = 'image/png';
                    break;
                case 'gif':
                    $mimeType = 'image/gif';
                    break;
                case 'pdf':
                    $mimeType = 'application/pdf';
                    break;
                case 'mp4':
                    $mimeType = 'video/mp4';
                    break;
                case 'webm':
                    $mimeType = 'video/webm';
                    break;
                case 'ogg':
                    $mimeType = 'video/ogg';
                    break;
                // Thêm các loại tệp khác nếu cần
                default:
                    http_response_code(500);
                    error_log("FileLoader: Unsupported file type or cannot determine MIME type for: " . $filePath);
                    exit;
            }
        }
        header_remove('Content-Type'); // Xóa header JSON mặc định (nếu có từ switch trước)
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: public, max-age=86400'); // Cache 1 ngày
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 86400) . ' GMT');
        // Cho phép streaming cho video/audio
        if (strpos($mimeType, 'video/') === 0 || strpos($mimeType, 'audio/') === 0) {
            header('Accept-Ranges: bytes');
            // Xử lý partial content nếu client yêu cầu (cần thiết cho tua video)
            if (isset($_SERVER['HTTP_RANGE'])) {
                $size = filesize($filePath);
                $length = $size;
                $start = 0;
                $end = $size - 1;

                header("HTTP/1.1 206 Partial Content");

                $range = $_SERVER['HTTP_RANGE'];
                if (preg_match('/bytes=\h*(\d+)-(\d*)[\D.*]?/i', $range, $matches)) {
                    $start = intval($matches[1]);
                    if (!empty($matches[2])) {
                        $end = intval($matches[2]);
                    }
                }

                header("Content-Range: bytes $start-$end/$size");
                header("Content-Length: " . ($end - $start + 1));

                $f = fopen($filePath, 'rb');
                fseek($f, $start);
                $buffer = 1024 * 8; // 8KB buffer
                while (!feof($f) && ($p = ftell($f)) <= $end) {
                    if ($p + $buffer > $end) {
                        $buffer = $end - $p + 1;
                    }
                    set_time_limit(0); // Ngăn script timeout khi stream file lớn
                    echo fread($f, $buffer);
                    flush(); // Gửi buffer ra client
                }
                fclose($f);
                exit;
            }
        } else {
            // Cho phép tải xuống với tên gốc cho các loại tệp khác (ví dụ PDF)
            header('Content-Disposition: inline; filename="' . basename($requestedFilename) . '"');
        }

        ob_clean();
        flush();
        readfile($filePath);
        exit;
    } else {
        error_log("FileLoader: File not found or not readable: " . $filePath);
        http_response_code(404); // Not Found
        // Không echo JSON ở đây vì header Content-Type đã bị xóa hoặc thay đổi
        exit;
    }
}


$act = '';
$requestMethod = $_SERVER['REQUEST_METHOD'];

if ($requestMethod === 'POST') {
    $act = $_POST['act'] ?? '';
} elseif ($requestMethod === 'GET') {
    $act = $_GET['act'] ?? '';
}

header('Content-Type: application/json');

switch ($act) {
    case 'home_page':
        $baseAppPath = dirname(dirname($_SERVER['SCRIPT_NAME']));
        if ($baseAppPath === '/' || $baseAppPath === '\\') {
            $baseAppPath = '';
        }
        $allCourseURL = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http")
            . "://" . $_SERVER['HTTP_HOST']
            . $baseAppPath
            . '/api/course_api.php?isGetAllCourse=true';
        $allCourseResp = callApi($allCourseURL, "GET");
        http_response_code($allCourseResp['http_status_code'] ?? ($allCourseResp['success'] ? 200 : 500));
        echo json_encode($allCourseResp);
        break;

    case 'get_instructors_home_page':
        $allInstructorsURL = "http://localhost/CoursePro1/api/instructor_api.php?isGetInstructorHomePage=true";
        $allInstructorsResp = callApi($allInstructorsURL, "GET");
        http_response_code($allInstructorsResp['http_status_code'] ?? ($allInstructorsResp['success'] ? 200 : 500));
        echo json_encode($allInstructorsResp);
        break;

    case 'serve_image': // For course images
        $courseId = $_GET['course_id'] ?? null;
        $imageName = $_GET['image'] ?? null;

        if (!$courseId || !$imageName) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing course_id or image name.']);
            exit;
        }
        $imageName = basename($imageName);
        $imagePath = UPLOADS_DIR . '/' . $courseId . '/' . $imageName; // Path: uploads/{course_id}/{imageName}

        if (file_exists($imagePath) && is_readable($imagePath)) {
            $mimeType = mime_content_type($imagePath);
            if (!$mimeType) {
                $extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
                switch ($extension) {
                    case 'jpeg':
                    case 'jpg':
                        $mimeType = 'image/jpeg';
                        break;
                    case 'png':
                        $mimeType = 'image/png';
                        break;
                    case 'gif':
                        $mimeType = 'image/gif';
                        break;
                    default:
                        http_response_code(500);
                        exit;
                }
            }
            header_remove('Content-Type');
            header('Content-Type: ' . $mimeType);
            header('Content-Length: ' . filesize($imagePath));
            header('Cache-Control: public, max-age=3600');
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');
            ob_clean();
            flush();
            readfile($imagePath);
            exit;
        } else {
            http_response_code(404);
            exit;
        }
        break;

    case 'serve_user_image': // For user (instructor) profile images
        $userId = $_GET['user_id'] ?? null;
        $imageName = $_GET['image'] ?? null;

        if (!$userId || !$imageName) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing user_id or image name.']);
            exit;
        }
        $imageName = basename($imageName); // Security: prevent directory traversal
        // Path structure: uploads/{userID}/{imageName}
        $imagePath = UPLOADS_DIR . '/' . $userId . '/' . $imageName;

        if (file_exists($imagePath) && is_readable($imagePath)) {
            $mimeType = mime_content_type($imagePath);
            if (!$mimeType) {
                $extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
                switch ($extension) {
                    case 'jpeg':
                    case 'jpg':
                        $mimeType = 'image/jpeg';
                        break;
                    case 'png':
                        $mimeType = 'image/png';
                        break;
                    case 'gif':
                        $mimeType = 'image/gif';
                        break;
                    default:
                        http_response_code(500);
                        exit;
                }
            }
            header_remove('Content-Type'); // Remove default JSON header
            header('Content-Type: ' . $mimeType);
            header('Content-Length: ' . filesize($imagePath));
            header('Cache-Control: public, max-age=3600'); // Cache for 1 hour
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');
            ob_clean();
            flush();
            readfile($imagePath);
            exit;
        } else {
            http_response_code(404); // Not Found
            exit;
        }
        break;
    case 'serve_course_video':
        header('Content-Type: application/json'); // Cho các lỗi JSON tiềm ẩn
        $courseId = $_GET['course_id'] ?? null;
        $chapterId = $_GET['chapter_id'] ?? null; // Cần chapterID để xây dựng đường dẫn
        $videoFilename = $_GET['filename'] ?? null; // Tên file video

        if (!$courseId || !$chapterId || !$videoFilename) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing course_id, chapter_id, or video filename.']);
            exit;
        }
        $videoFilename = basename($videoFilename);
        // Đường dẫn theo cấu trúc: uploads/{courseID}/{chapterID}/videos/{videoFilename}
        $videoPath = UPLOADS_DIR . '/' . $courseId . '/' . $chapterId . '/videos/' . $videoFilename;
        serve_file($videoPath, $videoFilename);
        break;

    case 'serve_course_resource':
        header('Content-Type: application/json');
        $courseId = $_GET['course_id'] ?? null;
        $chapterId = $_GET['chapter_id'] ?? null; // Cần chapterID
        $resourceFilename = $_GET['filename'] ?? null; // Tên file tài liệu

        if (!$courseId || !$chapterId || !$resourceFilename) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing course_id, chapter_id, or resource filename.']);
            exit;
        }
        $resourceFilename = basename($resourceFilename);
        // Đường dẫn theo cấu trúc: uploads/{courseID}/{chapterID}/resources/{resourceFilename}
        $resourcePath = UPLOADS_DIR . '/' . $courseId . '/' . $chapterId . '/resources/' . $resourceFilename;
        serve_file($resourcePath, $resourceFilename);
        break;

    default:
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action specified. Requested action: \'' . htmlspecialchars($act) . '\''
        ]);
        break;
}
