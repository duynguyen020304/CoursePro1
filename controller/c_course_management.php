<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$apiBaseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http")
    . "://" . $_SERVER['HTTP_HOST']
    . dirname(dirname($_SERVER['SCRIPT_NAME']))
    . '/api/course_api.php';

$act = '';
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $act = $_POST['act'] ?? '';
} elseif ($method === 'GET') {
    $act = $_GET['act'] ?? '';
}

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
        return ['success' => false, 'message' => 'API connection failed.', 'http_status_code' => null];
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
            'message' => 'Invalid API response format (not JSON).',
            'raw_response' => $response,
            'http_status_code' => $httpStatusCode
        ];
    }

    if ($response === '' || ($decodedResponse === null && $jsonError === JSON_ERROR_NONE)) {
        return [
            'success' => $httpStatusCode >= 200 && $httpStatusCode < 300,
            'message' => 'Operation completed with empty response.',
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
        $decodedResponse = [
            'success' => $httpStatusCode >= 200 && $httpStatusCode < 300,
            'message' => 'API returned non-array JSON.',
            'data' => $decodedResponse,
            'http_status_code' => $httpStatusCode
        ];
    }

    return $decodedResponse;
}

switch ($act) {
    case 'create':
    case 'update':
        if ($method !== 'POST') {
            header('Location: ../admin/course-management.php?view=list');
            exit;
        }

        $courseID = trim($_POST['CourseID'] ?? '');
        $title = trim($_POST['Title'] ?? '');
        $priceInput = str_replace(',', '', trim($_POST['Price'] ?? '0'));
        $price = is_numeric($priceInput) ? floatval($priceInput) : 0;

        $instructors = isset($_POST['Instructors']) && is_array($_POST['Instructors']) ? $_POST['Instructors'] : [];
        $categories = isset($_POST['Categories']) && is_array($_POST['Categories']) ? $_POST['Categories'] : [];

        $createdBy = trim($_POST['CreatedBy'] ?? ($_SESSION['user']['userID'] ?? ''));
        $description = trim($_POST['Description'] ?? '');

        $isValid = true;
        if (empty($title)) $isValid = false;
        if ($price < 0) $isValid = false;
        if (empty($instructors)) $isValid = false;
        if (empty($categories)) $isValid = false;
        if (empty($createdBy) && $act === 'create') $isValid = false;
        if ($act === 'update' && empty($courseID)) $isValid = false;

        if (!$isValid) {
            header('Location: ../admin/course-management.php?view=' . ($act === 'update' ? 'edit&id=' . urlencode($courseID) : 'add'));
            exit;
        }

        $payload = [
            'title' => $title,
            'description' => $description,
            'price' => $price,
            'instructorsID' => $instructors,
            'categoriesID' => $categories,
            'createdBy' => $createdBy
        ];

        $apiMethodType = 'POST';
        $currentApiUrl = $apiBaseUrl;

        if ($act === 'update') {
            $payload['courseID'] = $courseID;
            $apiMethodType = 'PUT';
        }

        $resp = callApi($currentApiUrl, $apiMethodType, $payload);

        if (!isset($resp['success']) || $resp['success'] !== true) {
            $redirect_param = $act === 'update' ? 'edit&id=' . urlencode($courseID) : 'add';
            header('Location: ../admin/course-management.php?view=' . $redirect_param);
            exit;
        }

        $targetCourseID = null;
        if ($act === 'update') {
            $targetCourseID = $courseID;
        } elseif ($act === 'create') {
            $idKeys = ['CourseID', 'id', 'courseID', 'course_id'];
            if (isset($resp['data']) && is_array($resp['data'])) {
                foreach ($idKeys as $key) {
                    if (isset($resp['data'][$key])) {
                        $targetCourseID = $resp['data'][$key];
                        break;
                    }
                }
                if (!$targetCourseID && count($resp['data']) === 1) {
                    $potentialId = reset($resp['data']);
                    if (is_scalar($potentialId) && !empty($potentialId)) $targetCourseID = $potentialId;
                }
            } else {
                foreach ($idKeys as $key) {
                    if (isset($resp[$key])) {
                        $targetCourseID = $resp[$key];
                        break;
                    }
                }
            }
        }

        $imageUploadSuccess = false;
        $imageFileNameForApi = null;

        if (!function_exists('ensureUploadDirectory')) {
            function ensureUploadDirectory(string $absoluteDirectoryPath): bool
            {
                if (!is_dir($absoluteDirectoryPath)) {
                    if (!mkdir($absoluteDirectoryPath, 0755, true)) {
                        error_log("UPLOAD_ERROR: Không thể tạo thư mục: " . $absoluteDirectoryPath);
                        return false;
                    }
                }
                if (!is_writable($absoluteDirectoryPath)) {
                    error_log("UPLOAD_ERROR: Thư mục không có quyền ghi: " . $absoluteDirectoryPath);
                    return false;
                }
                return true;
            }
        }

        if (
            isset($targetCourseID) && $targetCourseID !== '' &&
            isset($_FILES['CourseImage']) &&
            $_FILES['CourseImage']['error'] === UPLOAD_ERR_OK &&
            !empty($_FILES['CourseImage']['tmp_name'])
        ) {
            $uploadedFile = $_FILES['CourseImage'];

            $originalFileName = $uploadedFile['name'];
            $fileTmpName = $uploadedFile['tmp_name'];
            $fileSize = $uploadedFile['size'];

            $fileExtension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            $maxFileSize = 20 * 1024 * 1024;

            $uploadErrors = [];

            if (!in_array($fileExtension, $allowedExtensions, true)) {
                $uploadErrors[] = "Định dạng file không hợp lệ. Chỉ chấp nhận: " . implode(', ', $allowedExtensions);
            }

            if ($fileSize > $maxFileSize) {
                $uploadErrors[] = "Kích thước file quá lớn. Tối đa: " . ($maxFileSize / 1024 / 1024) . "MB.";
            }

            if (empty($uploadErrors)) {
                $safeCourseID = preg_replace('/[^a-zA-Z0-9_-]/', '_', (string)$targetCourseID);
                $projectRoot = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR;
                $relativeUploadPath = 'uploads' . DIRECTORY_SEPARATOR . $safeCourseID . DIRECTORY_SEPARATOR;
                $absoluteUploadDir = $projectRoot . $relativeUploadPath;

                if (ensureUploadDirectory($absoluteUploadDir)) {
                    $imageID = str_replace('.', '_', uniqid('img_', true));
                    $i = 1;
                    $imageFileName = $imageID . "." . $fileExtension;
                    $destinationPath = $absoluteUploadDir . $imageFileName;

                    while (file_exists($destinationPath)) {
                        $imageFileName = $imageID . "($i)." . $fileExtension;
                        $destination = $absoluteUploadDir . $imageFileName;
                        $i++;
                    }
                    if (move_uploaded_file($fileTmpName, $destinationPath)) {
                        $imageUploadSuccess = true;
                        $imageFileNameForApi = $relativeUploadPath . $imageFileName;
                        $courseImageAPIUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http")
                            . "://" . $_SERVER['HTTP_HOST']
                            . dirname(dirname($_SERVER['SCRIPT_NAME']))
                            . '/api/course_image_api.php';
                        $courseImageResp = callApi($courseImageAPIUrl, "POST", [
                            'courseID' => $targetCourseID,
                            'imageID' => $imageID,
                            'imagePath' => $imageFileName
                        ]);
                    } else {
                        $uploadErrors[] = "Lỗi hệ thống: Không thể lưu file đã tải lên.";
                        error_log("UPLOAD_ERROR: move_uploaded_file thất bại từ {$fileTmpName} tới {$destinationPath}");
                    }
                } else {
                    $uploadErrors[] = "Lỗi hệ thống: Không thể chuẩn bị thư mục lưu trữ.";
                }
            }

            if (!empty($uploadErrors)) {
                foreach ($uploadErrors as $error) {
                    error_log("UPLOAD_VALIDATION_ERROR: " . $error);
                }
            }
        } elseif (isset($_FILES['CourseImage']) && $_FILES['CourseImage']['error'] !== UPLOAD_ERR_OK && $_FILES['CourseImage']['error'] !== UPLOAD_ERR_NO_FILE) {
            error_log("UPLOAD_ERROR: Mã lỗi tải lên CourseImage: " . $_FILES['CourseImage']['error']);
        }

    // 5. Gọi service nếu upload thành công
//        if ($imageFileNameForApi) {
//            $serviceFile = __DIR__ . '/../service/service_course_image.php';
//            if (is_readable($serviceFile)) {
//                require_once $serviceFile;
//                if (class_exists('CourseImageService')) {
//                    $caption = $title ?? 'Course Image';
//                    (new CourseImageService())
//                        ->add_image($targetCourseID, $imageFileNameForApi, $caption, 0);
//                }
//            }
//        }

        $_SESSION['success'] = $resp['message'] ?? ($act === 'create' ? 'Course created successfully.' : 'Course updated successfully.');

        header('Location: ../admin/course-management.php?view=list&operation_status=1');
        exit;

    case 'delete':
        $deleteID = $_POST['courseID'] ?? $_GET['courseID'] ?? null;
        if ($deleteID == null || !is_scalar($deleteID) || trim($deleteID) === '') {
            header('Location: ../admin/course-management.php?view=list');
            exit;
        }
        $payload = [
            'courseID' => $deleteID,
        ];
        $resp = callApi($apiBaseUrl, "DELETE", $payload);

        if ($resp && isset($resp->success) && $resp->success) {
            $_SESSION['success'] = $delRes->message ?? "Course deleted successfully!";
        }
        header('Location: ../admin/course-management.php?view=list&delete_status=1');
        exit;

    default:
        header('Location: ../admin/course-management.php?view=list');
        exit;
}
