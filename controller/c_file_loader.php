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
            // Log error: error_log("Image not found: " . $imagePath);
            http_response_code(404); // Not Found
            exit;
        }
        break;

    default:
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action specified. Requested action: \'' . htmlspecialchars($act) . '\''
        ]);
        break;
}
