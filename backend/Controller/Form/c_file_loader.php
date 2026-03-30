<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('PROJECT_ROOT')) {
    define('PROJECT_ROOT', dirname(__DIR__));
}
if (!defined('UPLOADS_DIR')) {
    define('UPLOADS_DIR', PROJECT_ROOT . '/uploads');
}


if (!function_exists('callApi_fileLoader')) {
    function callApi_fileLoader(string $url, string $requestMethod, array $payload = []): array
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
                'timeout' => 15 // Consider if 15s is enough for all API calls from here
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
                'raw_response' => substr($response, 0, 1000), // Limit raw response for brevity
                'http_status_code' => $httpStatusCode
            ];
        }

        if ($response === '' || ($decodedResponse === null && $jsonError === JSON_ERROR_NONE && $response !== '')) {
            if($decodedResponse === null && $jsonError === JSON_ERROR_NONE && $response === 'null'){ // Specifically handle "null" string response
                return [
                    'success' => ($httpStatusCode >= 200 && $httpStatusCode < 300),
                    'message' => $httpStatusCode === 204 ? 'Operation successful with no content.' : 'Operation completed with valid empty JSON response.',
                    'data' => null,
                    'http_status_code' => $httpStatusCode
                ];
            }
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
                // Ensure success reflects HTTP status if not explicitly set
                $decodedResponse['success'] = ($httpStatusCode >= 200 && $httpStatusCode < 300);
            }
            if ($decodedResponse['success'] && !array_key_exists('data', $decodedResponse)) {
                $decodedResponse['data'] = null; // Ensure 'data' key exists on success
            }
            if (!isset($decodedResponse['message']) && !$decodedResponse['success']) {
                $decodedResponse['message'] = 'API request failed with status code ' . $httpStatusCode;
            } elseif (!isset($decodedResponse['message']) && $decodedResponse['success']) {
                $decodedResponse['message'] = 'API request successful.';
            }
        } else { // Handle cases where JSON is valid but not an array (e.g., a single JSON string, number)
            $decodedResponse = [
                'success' => ($httpStatusCode >= 200 && $httpStatusCode < 300),
                'message' => 'API returned non-array JSON, but was decoded.',
                'data' => $decodedResponse, // Store the decoded non-array data
                'http_status_code' => $httpStatusCode
            ];
        }

        return $decodedResponse;
    }
}


function serve_file(string $filePath, string $requestedFilename, bool $forceDownload = false): void
{
    if (file_exists($filePath) && is_readable($filePath)) {
        $fileSize = filesize($filePath);
        $mimeType = mime_content_type($filePath);

        if (!$mimeType) { // Fallback for mime_content_type failure
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            switch ($extension) {
                case 'jpeg': case 'jpg': $mimeType = 'image/jpeg'; break;
                case 'png': $mimeType = 'image/png'; break;
                case 'gif': $mimeType = 'image/gif'; break;
                case 'pdf': $mimeType = 'application/pdf'; break;
                case 'zip': $mimeType = 'application/zip'; break;
                case 'doc': $mimeType = 'application/msword'; break;
                case 'docx': $mimeType = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'; break;
                case 'xls': $mimeType = 'application/vnd.ms-excel'; break;
                case 'xlsx': $mimeType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'; break;
                case 'ppt': $mimeType = 'application/vnd.ms-powerpoint'; break;
                case 'pptx': $mimeType = 'application/vnd.openxmlformats-officedocument.presentationml.presentation'; break;
                case 'txt': $mimeType = 'text/plain'; break;
                case 'mp4': $mimeType = 'video/mp4'; break;
                case 'webm': $mimeType = 'video/webm'; break;
                case 'ogg': $mimeType = 'video/ogg'; break; // Corrected from 'ogv' to 'ogg' for common video
                case 'mp3': $mimeType = 'audio/mpeg'; break;
                default:
                    $mimeType = 'application/octet-stream'; // Generic binary type
            }
        }

        if (ob_get_level()) { // Clear output buffer
            ob_end_clean();
        }

        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . $fileSize);
        header('Cache-Control: private, max-age=0, must-revalidate'); // Cache control for downloads
        header('Pragma: public'); // For compatibility

        $isMedia = strpos($mimeType, 'video/') === 0 || strpos($mimeType, 'audio/') === 0;
        $isImage = strpos($mimeType, 'image/') === 0;
        $isPdf = $mimeType === 'application/pdf';

        // If $forceDownload is true, OR if it's not media, not an image, and not a PDF, then attach.
        // Otherwise (if $forceDownload is false AND it IS media, image, or PDF), serve inline.
        if ($forceDownload || (!$isMedia && !$isImage && !$isPdf) ) {
            header('Content-Disposition: attachment; filename="' . basename($requestedFilename) . '"');
        } else {
            header('Content-Disposition: inline; filename="' . basename($requestedFilename) . '"');
        }

        // Handle byte range requests for media files (streaming)
        if ($isMedia && isset($_SERVER['HTTP_RANGE'])) {
            header('Accept-Ranges: bytes');
            $range = $_SERVER['HTTP_RANGE'];
            list($start, $end) = [0, $fileSize - 1];

            // Parse range header
            if (preg_match('/bytes=\h*(\d+)-(\d*)[\D.*]?/i', $range, $matches)) {
                $start = intval($matches[1]);
                if (!empty($matches[2])) {
                    $end = intval($matches[2]);
                }
            }
            $end = min($end, $fileSize - 1); // Ensure end doesn't exceed file size

            if ($start > $end || $start >= $fileSize) {
                http_response_code(416); // Range Not Satisfiable
                header("Content-Range: bytes */$fileSize");
                exit;
            }

            http_response_code(206); // Partial Content
            header("Content-Range: bytes $start-$end/$fileSize");
            header("Content-Length: " . ($end - $start + 1));

            $fh = @fopen($filePath, 'rb');
            if ($fh) {
                fseek($fh, $start);
                $bytesToSend = $end - $start + 1;
                while ($bytesToSend > 0 && !feof($fh) && !connection_aborted()) {
                    $bufferSize = min(1024 * 8, $bytesToSend); // Read in 8KB chunks
                    $buffer = fread($fh, $bufferSize);
                    echo $buffer;
                    flush(); // Flush output buffer
                    $bytesToSend -= strlen($buffer);
                }
                fclose($fh);
            }
        } else {
            // For non-range requests or non-media files, read the whole file
            readfile($filePath);
        }
        exit;
    } else {
        error_log("FileLoader: File not found or not readable: " . $filePath . " (Requested: " . $requestedFilename . ")");
        if (!headers_sent()) {
            // Ensure JSON output for errors if headers not already sent
            header('Content-Type: application/json');
        }
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'File not found or not accessible. Requested: ' . basename($requestedFilename)]);
        exit;
    }
}


$act = '';
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Determine action based on request method
if ($requestMethod === 'POST') {
    $act = $_POST['act'] ?? '';
} elseif ($requestMethod === 'GET') {
    $act = $_GET['act'] ?? '';
}

// Set default Content-Type to JSON for actions that return JSON
// Specific file serving actions will override this.
if ($act !== 'serve_image' && $act !== 'serve_user_image' && $act !== 'serve_course_video' && $act !== 'serve_course_resource') {
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
}


switch ($act) {
    case 'home_page':
        $baseAppPath = dirname(dirname($_SERVER['SCRIPT_NAME']));
        if ($baseAppPath === '/' || $baseAppPath === '\\') { // Normalize base path
            $baseAppPath = '';
        }
        $allCourseURL = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http")
            . "://" . $_SERVER['HTTP_HOST']
            . $baseAppPath // Use normalized base path
            . '/api/course_api.php?isGetAllCourse=true&option=1';
        $allCourseResp = callApi_fileLoader($allCourseURL, "GET"); // Use renamed function
        http_response_code($allCourseResp['http_status_code'] ?? ($allCourseResp['success'] ? 200 : 500));
        echo json_encode($allCourseResp);
        break;

    case 'get_instructors_home_page':
        // Consider making the base path construction more robust or configurable
        $baseAppPathForInstructors = dirname(dirname($_SERVER['SCRIPT_NAME']));
        if ($baseAppPathForInstructors === '/' || $baseAppPathForInstructors === '\\') {
            $baseAppPathForInstructors = '';
        }
        // The '/CoursePro1' part seems specific to localhost, might need a better way to handle this
        $apiSubPath = (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false && $baseAppPathForInstructors === '' ? '/CoursePro1' : $baseAppPathForInstructors);

        $allInstructorsURL = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http")
            . "://" . $_SERVER['HTTP_HOST']
            . $apiSubPath // Use determined API subpath
            . '/api/instructor_api.php?isGetInstructorHomePage=true';
        $allInstructorsResp = callApi_fileLoader($allInstructorsURL, "GET"); // Use renamed function
        http_response_code($allInstructorsResp['http_status_code'] ?? ($allInstructorsResp['success'] ? 200 : 500));
        echo json_encode($allInstructorsResp);
        break;

    case 'serve_image':
        $courseId = $_GET['course_id'] ?? null;
        $imageName = $_GET['image'] ?? null;

        if (!$courseId || !$imageName) {
            if (!headers_sent()) { header('Content-Type: application/json');}
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing course_id or image name.']);
            exit;
        }
        $imageName = basename($imageName); // Sanitize filename
        $imagePath = UPLOADS_DIR . DIRECTORY_SEPARATOR . preg_replace('/[^a-zA-Z0-9_-]/', '_', (string)$courseId) . DIRECTORY_SEPARATOR . $imageName;
        serve_file($imagePath, $imageName, false); // Images are typically served inline
        break;

    case 'serve_user_image':
        $userId = $_GET['user_id'] ?? null;
        $imageName = $_GET['image'] ?? null;

        if (!$userId || !$imageName) {
            if (!headers_sent()) { header('Content-Type: application/json');}
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing user_id or image name.']);
            exit;
        }
        $imageName = basename($imageName); // Sanitize filename
        $imagePath = UPLOADS_DIR . DIRECTORY_SEPARATOR . preg_replace('/[^a-zA-Z0-9_-]/', '_', (string)$userId) . DIRECTORY_SEPARATOR . $imageName;
        serve_file($imagePath, $imageName, false); // User avatars are typically served inline
        break;

    case 'serve_course_video':
        $courseId = $_GET['course_id'] ?? null;
        $chapterId = $_GET['chapter_id'] ?? null;
        $videoFilename = $_GET['filename'] ?? null;

        if (!$courseId || !$chapterId || !$videoFilename) {
            if (!headers_sent()) { header('Content-Type: application/json');}
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing course_id, chapter_id, or video filename.']);
            exit;
        }
        $videoFilename = basename($videoFilename); // Sanitize filename
        $safeCourseId = preg_replace('/[^a-zA-Z0-9_-]/', '_', (string)$courseId);
        $safeChapterId = preg_replace('/[^a-zA-Z0-9_-]/', '_', (string)$chapterId);
        $videoPath = UPLOADS_DIR . DIRECTORY_SEPARATOR . $safeCourseId . DIRECTORY_SEPARATOR . $safeChapterId . DIRECTORY_SEPARATOR . 'videos' . DIRECTORY_SEPARATOR . $videoFilename;
        serve_file($videoPath, $videoFilename, false); // Videos are served inline (allowing streaming)
        break;

    case 'serve_course_resource':
        $courseId = $_GET['course_id'] ?? null;
        $chapterId = $_GET['chapter_id'] ?? null;
        $resourceFilename = $_GET['filename'] ?? null;

        // MODIFIED: Always force download for resources
        $forceDownload = true;

        if (!$courseId || !$chapterId || !$resourceFilename) {
            if (!headers_sent()) { header('Content-Type: application/json');}
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing course_id, chapter_id, or resource filename.']);
            exit;
        }
        $resourceFilename = basename($resourceFilename); // Sanitize filename
        $safeCourseId = preg_replace('/[^a-zA-Z0-9_-]/', '_', (string)$courseId);
        $safeChapterId = preg_replace('/[^a-zA-Z0-9_-]/', '_', (string)$chapterId);
        $resourcePath = UPLOADS_DIR . DIRECTORY_SEPARATOR . $safeCourseId . DIRECTORY_SEPARATOR . $safeChapterId . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . $resourceFilename;

        serve_file($resourcePath, $resourceFilename, $forceDownload); // $forceDownload is now always true
        break;

    default:
        if (!headers_sent()) { header('Content-Type: application/json');}
        http_response_code(404); // Not Found for invalid actions
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action specified. Requested action: \'' . htmlspecialchars($act) . '\''
        ]);
        break;
}
?>
