<?php

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize debug messages array in session if not already present
// (This is no longer necessary since we're removing debug logs)
if (!isset($_SESSION['debug_messages'])) {
    $_SESSION['debug_messages'] = [];
}

// Construct the base URL for the course API
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

/**
 * Calls an API endpoint.
 *
 * @param string $url The full URL of the API endpoint.
 * @param string $requestMethod The HTTP method (GET, POST, PUT, DELETE).
 * @param array $payload The data to send with the request.
 * @return array The decoded API response.
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
        return ['success' => false, 'message' => 'API connection failed.', 'http_status_code' => null];
    }

    $decodedResponse = json_decode($response, true);
    $jsonError = json_last_error();

    // Extract HTTP status code
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
            'message' => 'Invalid API response format (not JSON). Status: ' . ($httpStatusCode ?? 'Unknown') . '. Error: ' . json_last_error_msg(),
            'raw_response' => $response,
            'http_status_code' => $httpStatusCode
        ];
    }

    if ($response === '' || ($decodedResponse === null && $jsonError === JSON_ERROR_NONE)) {
        return [
            'success' => $httpStatusCode >= 200 && $httpStatusCode < 300,
            'message' => 'Operation successful (empty or non-JSON response).',
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
    }

    return $decodedResponse;
}

// Main action switch
switch ($act) {
    case 'create':
    case 'update':
        if ($method !== 'POST') {
            $_SESSION['error'] = 'Invalid request method for ' . htmlspecialchars($act) . '.';
            header('Location: ../admin/course-management.php?view=list&error=invalid_request_method');
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

        $errors = [];
        if (empty($title)) $errors[] = "Title is required.";
        if ($price < 0) $errors[] = "Price must be a non-negative number.";
        if (empty($instructors)) $errors[] = "At least one Instructor is required.";
        if (empty($categories)) $errors[] = "At least one Category is required.";
        if (empty($createdBy) && $act === 'create') $errors[] = "CreatedBy information is missing.";
        if ($act === 'update' && empty($courseID)) $errors[] = 'Course ID is missing for update operation.';

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['form_data'] = $_POST;
            $redirect_param = $act === 'update' ? 'edit&id=' . urlencode($courseID) : 'add';
            header('Location: ../admin/course-management.php?view=' . $redirect_param . '&error=validation');
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
            $_SESSION['error'] = $resp['message'] ?? 'API operation failed for ' . htmlspecialchars($act) . '.';
            if (isset($resp['errors']) && is_array($resp['errors'])) {
                $_SESSION['error'] .= '<br>' . implode('<br>', $resp['errors']);
            }
            $_SESSION['form_data'] = $_POST;
            $redirect_param = $act === 'update' ? 'edit&id=' . urlencode($courseID) : 'add';
            header('Location: ../admin/course-management.php?view=' . $redirect_param . '&error=api_operation_failed');
            exit;
        }

        unset($_SESSION['form_data']);

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

        if ($targetCourseID && isset($_FILES['CourseImage']) && $_FILES['CourseImage']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['CourseImage'];

            if ($file['error'] === UPLOAD_ERR_OK) {
                $destDirRelative = 'uploads/courses/';
                $destDirAbsolute = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/' . ltrim($destDirRelative, '/');

                $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $max_size = 5 * 1024 * 1024;

                if ($file['size'] > $max_size) {
                    $_SESSION['warning_message'] = ($act === 'create' ? 'Course created' : 'Course updated') . ' successfully, but image upload failed: File too large (max 5MB).';
                } else {
                    $origName = basename($file["name"]);
                    $fileType = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

                    if (!in_array($fileType, $allowed_types)) {
                        $_SESSION['warning_message'] = ($act === 'create' ? 'Course created' : 'Course updated') . ' successfully, but image upload failed: Invalid file type.';
                    } else {
                        if (!is_dir($destDirAbsolute)) {
                            if (!@mkdir($destDirAbsolute, 0755, true)) {
                                $_SESSION['warning_message'] = ($act === 'create' ? 'Course created' : 'Course updated') . ' successfully, but image upload failed: Could not create upload directory.';
                            } else {
                                @file_put_contents($destDirAbsolute . '.htaccess', "Options -Indexes\nDeny from all");
                                @file_put_contents($destDirAbsolute . 'index.html', '');
                            }
                        }

                        if (is_dir($destDirAbsolute) && is_writable($destDirAbsolute)) {
                            $safeTargetCourseID = preg_replace('/[^a-zA-Z0-9_-]/', '_', $targetCourseID);
                            $filename = "course_{$safeTargetCourseID}_" . uniqid() . "." . $fileType;
                            $destinationPath = $destDirAbsolute . $filename;

                            $imageFileNameForApi = $destDirRelative . $filename;

                            if (move_uploaded_file($file['tmp_name'], $destinationPath)) {
                                $imageUploadSuccess = true;
                            } else {
                                $imageFileNameForApi = null;
                                $_SESSION['warning_message'] = ($act === 'create' ? 'Course created' : 'Course updated') . ' successfully, but image upload failed: Could not move uploaded file.';
                            }
                        } else {
                            $_SESSION['warning_message'] = ($act === 'create' ? 'Course created' : 'Course updated') . ' successfully, but image upload failed: Upload directory not writable or does not exist.';
                        }
                    }
                }
            } elseif ($file['error'] !== UPLOAD_ERR_NO_FILE) {
                $_SESSION['warning_message'] = ($act === 'create' ? 'Course created' : 'Course updated') . ' successfully, but image upload encountered an error: Code ' . $file['error'];
            }
        } elseif ($targetCourseID && isset($_FILES['CourseImage']) && $_FILES['CourseImage']['error'] === UPLOAD_ERR_NO_FILE) {
            // No uploaded file, do nothing
        } elseif (!$targetCourseID && isset($_FILES['CourseImage']) && $_FILES['CourseImage']['error'] === UPLOAD_ERR_OK) {
            $_SESSION['warning_message'] = 'Course information processed, but image could not be associated because Course ID was not determined from API response.';
        }

        if ($imageUploadSuccess && $imageFileNameForApi && $targetCourseID) {
            $servicePath = __DIR__ . '/../service/service_course_image.php';
            if (file_exists($servicePath)) {
                require_once $servicePath;
                if (class_exists('CourseImageService')) {
                    $caption = $title ?? 'Course Image';
                    $sortOrder = 0;
                    $courseImageService = new CourseImageService();
                    $saveResp = $courseImageService->add_image($targetCourseID, $imageFileNameForApi, $caption, $sortOrder);
                    if ($saveResp && isset($saveResp->success) && $saveResp->success) {
                        $_SESSION['success'] = ($act === 'create' ? 'Course created' : 'Course updated') . ' and image saved successfully!';
                    } else {
                        $_SESSION['warning_message'] = ($act === 'create' ? 'Course created' : 'Course updated') . ' successfully, but saving image information failed: ' . ($saveResp->message ?? 'Unknown error from Image Service.');
                    }
                } else {
                    $_SESSION['warning_message'] = ($act === 'create' ? 'Course created' : 'Course updated') . ' successfully, but CourseImageService class not found.';
                }
            } else {
                $_SESSION['warning_message'] = ($act === 'create' ? 'Course created' : 'Course updated') . ' successfully, but image service file not found.';
            }
        }

        if (!isset($_SESSION['success']) && !isset($_SESSION['warning_message'])) {
            $_SESSION['success'] = $resp['message'] ?? ($act === 'create' ? 'Course created successfully.' : 'Course updated successfully.');
        } elseif (isset($_SESSION['success']) && isset($_SESSION['warning_message'])) {
            $_SESSION['success'] = null;
        }

        header('Location: ../admin/course-management.php?view=list&operation_status=1');
        exit;

    case 'delete':
        if (!in_array($method, ['POST', 'GET'])) {
            $_SESSION['error'] = 'Invalid request method for delete.';
            header('Location: ../admin/course-management.php?error=invalid_method_for_delete');
            exit;
        }

        $deleteID = $_POST['courseID'] ?? $_GET['courseID'] ?? null;

        if (empty($deleteID)) {
            $_SESSION['error'] = "Invalid or missing course ID for deletion.";
            header('Location: ../admin/course-management.php?error=missing_id_for_delete');
            exit;
        }

        $servicePath = __DIR__ . '/../service/service_course.php';
        if (!file_exists($servicePath)) {
            $_SESSION['error'] = "Course service file not found. Deletion failed.";
            header('Location: ../admin/course-management.php?error=service_unavailable');
            exit;
        }
        require_once $servicePath;

        if (!class_exists('CourseService')) {
            $_SESSION['error'] = "CourseService class not found. Deletion failed.";
            header('Location: ../admin/course-management.php?error=service_class_missing');
            exit;
        }

        $svc = new CourseService();
        $delRes = $svc->delete_course($deleteID);

        if ($delRes && isset($delRes->success) && $delRes->success) {
            $_SESSION['success'] = $delRes->message ?? "Course deleted successfully!";
        } else {
            $_SESSION['error'] = "Deletion failed: " . ($delRes->message ?? "Unknown error from service.");
        }
        header('Location: ../admin/course-management.php?view=list&delete_status=1');
        exit;

    default:
        $_SESSION['error'] = "Unsupported action: '" . htmlspecialchars($act) . "'.";
        header('Location: ../admin/course-management.php?view=list&error=unknown_action');
        exit;
}
?>