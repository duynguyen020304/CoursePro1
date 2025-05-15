<?php
session_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
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
        $decodedResponse['http_status_code'] = $httpStatusCode;
        $decodedResponse['success'] = ($httpStatusCode >= 200 && $httpStatusCode < 300);
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

$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
$host = $_SERVER['HTTP_HOST'];
$scriptContainingDir = dirname($_SERVER['SCRIPT_NAME']);
$appRootPath = dirname($scriptContainingDir);
if ($appRootPath === '/' || $appRootPath === '\\') {
    $appRootPath = '';
}
$apiChapterUrl = $protocol . "://" . $host . $appRootPath . '/api/chapter_api.php';

$requestMethod = $_SERVER['REQUEST_METHOD'];
if ($requestMethod === 'POST') {
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
        echo json_encode(['success' => false, 'message' => 'Thiếu các trường bắt buộc: ' . implode(', ', $missingFields)]);
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