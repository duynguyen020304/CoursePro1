<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION['debug_messages'] = [];

$apiBase = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http")
    . "://" . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['SCRIPT_NAME'])) . '/api/course_api.php';

$act = '';
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $act = $_POST['act'] ?? '';
} elseif ($method === 'GET') {
    $act = $_GET['act'] ?? '';
}

function callApi(string $url, string $method, array $payload = [], array &$debugMessages): array
{
    $jsonPayload = $payload ? json_encode($payload) : null;
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['success' => false, 'message' => 'Internal error: Failed to encode payload.'];
    }

    $opts = [
        'http' => [
            'method' => $method,
            'header' => "Content-Type: application/json; charset=utf-8\r\n" .
                "Accept: application/json\r\n",
            'content' => $jsonPayload,
            'ignore_errors' => true,
            'timeout' => 15
        ]
    ];
    $context = stream_context_create($opts);
    $response = @file_get_contents($url, false, $context);
    $http_response_header = $http_response_header ?? [];
    if ($response === false) {
        $error = error_get_last();
        if (strpos(($error['message'] ?? ''), 'timed out') !== false) {
            return ['success' => false, 'message' => 'API connection timed out.'];
        }
        return ['success' => false, 'message' => 'API connection failed.'];
    }

    $decodedResponse = json_decode($response, true);
    if ($response !== '' && $decodedResponse === null && json_last_error() !== JSON_ERROR_NONE) {
        $httpStatusCode = 'Unknown';
        foreach ($http_response_header as $header) {
            if (preg_match('{HTTP/\d\.\d\s+(\d+)\s+}', $header, $match)) {
                $httpStatusCode = $match[1];
                break;
            }
        }
        return ['success' => false, 'message' => 'Invalid API response format. Status: ' . $httpStatusCode];
    }

    if ($response !== '' && $decodedResponse === null && json_last_error() === JSON_ERROR_NONE) {
        $httpStatusCode = 'Unknown';
        foreach ($http_response_header as $header) {
            if (preg_match('{HTTP/\d\.\d\s+(\d+)\s+}', $header, $match)) {
                $httpStatusCode = $match[1];
                break;
            }
        }
        if ($httpStatusCode >= 200 && $httpStatusCode < 300) {
            return ['success' => true, 'message' => 'Operation successful (non-JSON response)', 'raw_response' => $response];
        } else {
            return ['success' => false, 'message' => 'API request failed (non-JSON response). Status: ' . $httpStatusCode, 'raw_response' => $response];
        }
    }

    if (!isset($decodedResponse['success']) && isset($decodedResponse['message']) && stripos($decodedResponse['message'], 'error') !== false) {
        $decodedResponse['success'] = false;
    } elseif (!isset($decodedResponse['success'])) {
        $isLikelySuccess = !isset($decodedResponse['message']) || stripos($decodedResponse['message'], 'error') === false;
        $decodedResponse['success'] = $isLikelySuccess;
    }

    return $decodedResponse;
}

switch ($act) {
    case 'create':
    case 'update':
        if ($method !== 'POST') {
            $_SESSION['error'] = 'Invalid request method for ' . $act . '.';
            header('Location: ../admin/course-management.php?view=list&error=invalid_request');
            exit;
        }

        $courseID = trim($_POST['CourseID'] ?? '');
        $title = trim($_POST['Title'] ?? '');
        $price = isset($_POST['Price']) ? str_replace(',', '', trim($_POST['Price'])) : 0;
        $price = floatval($price);
        $instructor = $_POST['Instructors'] ?? [];
        $categories = $_POST['Categories'] ?? [];
        $createdBy = $_POST['CreatedBy'] ?? '';
        $description = trim($_POST['Description'] ?? '');

        $errors = [];
        if (!$title) $errors[] = "Title is required.";
        if (!is_numeric($price) || $price < 0) $errors[] = "Price must be a non-negative number.";
        if (empty($instructor)) $errors[] = "Instructor is required.";
        if (empty($categories)) $errors[] = "At least one Category is required.";
        if ($act === 'update' && empty($courseID)) $errors[] = 'Course ID is missing for update.';

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['form_data'] = $_POST;
            $redirect_url = '../admin/course-management.php?view=' . ($act === 'update' ? 'edit&id=' . $courseID : 'add');
            header('Location: ' . $redirect_url . '&error=validation');
            exit;
        }

        $payload = [
            'title' => $title,
            'description' => $description,
            'price' => $price,
            'instructorsID' => $instructor,
            'categoriesID' => $categories,
            'createdBy' => $createdBy
        ];
        if ($act === 'update') {
            $payload['courseID'] = $courseID;
        }

        $apiMethod = $act === 'create' ? 'POST' : 'PUT';
        $resp = callApi($apiBase, $apiMethod, $payload, $_SESSION['debug_messages']);

        if (!isset($resp['success']) || $resp['success'] !== true) {
            $_SESSION['error'] = $resp['message'] ?? 'API operation failed.';
            $_SESSION['form_data'] = $_POST;
            $redirect_url = '../admin/course-management.php?view=' . ($act === 'update' ? 'edit&id=' . $courseID : 'add');
            header('Location: ' . $redirect_url . '&error=api');
            exit;
        }

        unset($_SESSION['form_data']);

        $targetCourseID = null;
        if ($act === 'update') {
            $targetCourseID = $courseID;
        } elseif ($act === 'create') {
            if (isset($resp['data']['CourseID'])) {
                $targetCourseID = $resp['data']['CourseID'];
            } elseif (isset($resp['data']['id'])) {
                $targetCourseID = $resp['data']['id'];
            } elseif (isset($resp['courseID'])) {
                $targetCourseID = $resp['courseID'];
            } elseif (isset($resp['id'])) {
                $targetCourseID = $resp['id'];
            } elseif (isset($resp['course_id'])) {
                $targetCourseID = $resp['course_id'];
            } else {
                if (isset($resp['data']) && is_array($resp['data']) && count($resp['data']) === 1) {
                    $potentialId = reset($resp['data']);
                    if (is_numeric($potentialId) || (is_string($potentialId) && !empty($potentialId))) {
                        $targetCourseID = $potentialId;
                    }
                }
            }
        }

        if (!$targetCourseID) {
            if (isset($_FILES['CourseImage']) && $_FILES['CourseImage']['error'] === UPLOAD_ERR_OK) {
                header('Location: ../admin/course-management.php?view=add&error=api_id_missing');
                exit;
            } else {
                // No image uploaded, proceed
            }
        }

        // Image upload handling
        $imageUploadSuccess = false;
        $imageFileNameForApi = null;
        if ($targetCourseID && isset($_FILES['CourseImage']) && $_FILES['CourseImage']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['CourseImage'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                // handle error, but just skip
            } else {
                $destDirRelative = 'uploads/courses/';
                $destDirAbsolute = __DIR__ . '/../' . $destDirRelative;
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $max_size = 5 * 1024 * 1024;

                if ($file['size'] <= $max_size) {
                    $origName = basename($file["name"]);
                    $fileType = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                    if (in_array($fileType, $allowed_types)) {
                        if (!is_dir($destDirAbsolute)) {
                            @mkdir($destDirAbsolute, 0755, true);
                            @file_put_contents($destDirAbsolute . '.htaccess', 'Deny from all');
                        }
                        if (is_dir($destDirAbsolute) && is_writable($destDirAbsolute)) {
                            $filename = "course_{$targetCourseID}_" . uniqid() . "." . $fileType;
                            $destinationPath = $destDirAbsolute . $filename;
                            $imageFileNameForApi = $destDirRelative . $filename;
                            if (move_uploaded_file($file['tmp_name'], $destinationPath)) {
                                $imageUploadSuccess = true;
                            } else {
                                $imageFileNameForApi = null;
                            }
                        }
                    }
                }
            }
        }

        if ($imageUploadSuccess && $imageFileNameForApi && $targetCourseID) {
            require_once __DIR__ . '/../service/service_course_image.php';
            $caption = $title ?? '';
            $sortOrder = 0;
            $courseImageService = new CourseImageService();
            $saveResp = $courseImageService->add_image($targetCourseID, $imageFileNameForApi, $caption, $sortOrder);
            if ($saveResp->success) {
                $_SESSION['success'] = 'Thêm khóa học và ảnh thành công!';
            } else {
                $_SESSION['warning_message'] = 'Thêm khóa học thành công, nhưng lưu thông tin ảnh thất bại: ' . $saveResp->message;
            }
        } else if ($imageUploadSuccess && (!$imageFileNameForApi || !$targetCourseID)) {
            $_SESSION['warning_message'] = 'Ảnh đã được upload nhưng không lưu được vào hệ thống do thiếu thông tin.';
        }

        if (!isset($_SESSION['success'])) {
            $_SESSION['success'] = $resp['message'] ?? ($act === 'create' ? 'Course created successfully.' : 'Course updated successfully.');
        }

        header('Location: ../admin/course-management.php?view=list&success=1');
        exit;

    case 'delete':
        $deleteID = $_POST['courseID'] ?? $_GET['courseID'] ?? null;
        if (!$deleteID) {
            $_SESSION['error'] = "Invalid or missing course ID for deletion.";
            header('Location: ../admin/course-management.php');
            exit;
        }
        require_once __DIR__ . '/../service/service_course.php';
        $svc = new CourseService();
        $delRes = $svc->delete_course($deleteID);
        if ($delRes->success) {
            $_SESSION['success'] = "Xoá khoá học thành công!";
        } else {
            $_SESSION['error'] = "Xoá thất bại: " . $delRes->message;
        }
        header('Location: ../admin/course-management.php');
        exit;

    default:
        $_SESSION['error'] = "Unsupported action: '" . htmlspecialchars($act) . "'.";
        header('Location: ../admin/course-management.php?view=list&error=unknown_action');
        exit;
}