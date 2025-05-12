<?php
// File: c_course.php (Controller using REST API with Debugging)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize or clear debug messages for this request
$_SESSION['debug_messages'] = [];
$_SESSION['debug_messages'][] = "c_course.php: Script execution started at " . date('Y-m-d H:i:s');

// Base URL for the API (adjust if your API is in a different path)
$apiBase = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http")
    . "://" . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['SCRIPT_NAME'])) . '/api/course_api.php'; // Adjusted dirname for typical structure
$_SESSION['debug_messages'][] = "c_course.php: API Base URL set to: " . $apiBase;

$act = '';
$method = $_SERVER['REQUEST_METHOD'];
$_SESSION['debug_messages'][] = "c_course.php: Request Method: " . $method;

if ($method === 'POST') {
    $act = $_POST['act'] ?? '';
    $_SESSION['debug_messages'][] = "c_course.php: Action determined from POST: '" . $act . "'";
} elseif ($method === 'GET') {
    $act = $_GET['act'] ?? '';
    $_SESSION['debug_messages'][] = "c_course.php: Action determined from GET: '" . $act . "'";
} else {
    $_SESSION['debug_messages'][] = "c_course.php: Unsupported request method: " . $method;
    // Handle unsupported methods if necessary
}

// Helper: call API via HTTP with debugging
function callApi(string $url, string $method, array $payload = [], array &$debugMessages): array
{
    $debugMessages[] = "callApi: Attempting to call API.";
    $debugMessages[] = "callApi: URL: " . $url;
    $debugMessages[] = "callApi: Method: " . $method;
    $debugMessages[] = "callApi: Payload (before json_encode): " . var_export($payload, true);

    $jsonPayload = $payload ? json_encode($payload) : null;
    if (json_last_error() !== JSON_ERROR_NONE) {
        $debugMessages[] = "callApi: ERROR - Failed to encode payload to JSON: " . json_last_error_msg();
        return ['success' => false, 'message' => 'Internal error: Failed to encode payload.'];
    }
    $debugMessages[] = "callApi: Payload (JSON encoded): " . ($jsonPayload ?? 'None');

    $opts = [
        'http' => [
            'method'  => $method,
            'header'  => "Content-Type: application/json; charset=utf-8\r\n" . // Ensure proper line ending for headers
                "Accept: application/json\r\n",
            'content' => $jsonPayload,
            'ignore_errors' => true, // Get response body even on HTTP error codes (4xx, 5xx)
            'timeout' => 15 // Added a timeout
        ]
    ];
    $context  = stream_context_create($opts);

    $debugMessages[] = "callApi: Sending request...";
    $response = file_get_contents($url, false, $context);
    $http_response_header = $http_response_header ?? []; // Initialize if not set
    $debugMessages[] = "callApi: Raw Response Headers: " . implode("\n", $http_response_header);
    $debugMessages[] = "callApi: Raw Response Body: " . $response;

    if ($response === false) {
        $error = error_get_last();
        $debugMessages[] = "callApi: ERROR - file_get_contents failed. Error: " . ($error['message'] ?? 'Unknown error');
        return ['success' => false, 'message' => 'API connection failed.'];
    }

    $decodedResponse = json_decode($response, true);

    if ($decodedResponse === null && json_last_error() !== JSON_ERROR_NONE) {
        $debugMessages[] = "callApi: ERROR - Failed to decode JSON response: " . json_last_error_msg();
        // Attempt to find HTTP status code from headers
        $httpStatusCode = 'Unknown';
        foreach ($http_response_header as $header) {
            if (preg_match('{HTTP/\d\.\d\s+(\d+)\s+}', $header, $match)) {
                $httpStatusCode = $match[1];
                break;
            }
        }
        $debugMessages[] = "callApi: HTTP Status Code from headers (approx): " . $httpStatusCode;
        return ['success' => false, 'message' => 'Invalid API response format. Status: ' . $httpStatusCode];
    }

    $debugMessages[] = "callApi: Decoded Response: " . var_export($decodedResponse, true);
    return $decodedResponse;
}

$_SESSION['debug_messages'][] = "c_course.php: Entering switch statement for action: '" . $act . "'";
switch ($act) {
    case 'create':
    case 'update':
        $_SESSION['debug_messages'][] = "c_course.php: Action is '" . $act . "'";
        if ($method !== 'POST') {
            $_SESSION['error'] = 'Invalid request method for ' . $act . '.';
            $_SESSION['debug_messages'][] = "c_course.php: ERROR - Invalid method for {$act}. Expected POST, got {$method}. Redirecting.";
            header('Location: ../admin/course-management.php?error=invalid_request');
            exit;
        }
        $_SESSION['debug_messages'][] = "c_course.php: Method is POST, proceeding with {$act}.";

        // Collect form data
        $courseID    = trim($_POST['CourseID'] ?? ''); // Only relevant for update
        $title       = trim($_POST['Title'] ?? '');
        $price       = floatval($_POST['Price'] ?? 0);
        $instructor  = $_POST['Instructors']; // Assuming this is InstructorID based on payload key
        $categories  = $_POST['Categories'] ?? [];
        $description = trim($_POST['Description'] ?? ''); // Changed default to empty string

        $_SESSION['debug_messages'][] = "c_course.php: Form data collected - CourseID: '{$courseID}', Title: '{$title}', Price: {$price}, InstructorID: '{$instructor}', Categories: " . implode(',', $categories) . ", Description: '" . substr($description, 0, 50) . "...'";

        // Basic Validation
        if (!$title || $price < 0 || !$instructor || empty($categories)) {
            $_SESSION['error'] = 'Please fill in all required fields (Title, Price >= 0, Instructor, Categories).';
            $_SESSION['debug_messages'][] = "c_course.php: ERROR - Validation failed. Missing required fields. Redirecting.";
            header('Location: ../admin/course-management.php?error=validation');
            exit;
        }
        $_SESSION['debug_messages'][] = "c_course.php: Basic validation passed.";

        // Prepare payload
        $payload = [
            'title'        => $title,
            'description'  => $description,
            'price'        => $price,
            'instructorID' => $instructor, // Changed key to match collected variable assumption
            'categoriesID'   => $categories // Ensure categories are strings
        ];
        if ($act === 'update') {
            if (empty($courseID)) {
                $_SESSION['error'] = 'Course ID is missing for update.';
                $_SESSION['debug_messages'][] = "c_course.php: ERROR - Validation failed. Missing CourseID for update. Redirecting.";
                header('Location: ../admin/course-management.php?error=validation');
                exit;
            }
            $payload['courseID']  = $courseID;
            // 'createdBy' might be set automatically by the API based on logged-in user,
            // or you might need to fetch it from the session if required by the API.
            // $payload['createdBy'] = $_SESSION['user']['userID'] ?? null; // Example if needed
            $_SESSION['debug_messages'][] = "c_course.php: Added CourseID '{$courseID}' to payload for update.";
        }
        $_SESSION['debug_messages'][] = "c_course.php: Payload prepared for API call: " . json_encode($payload);

        // Call API
        $apiMethod = $act === 'create' ? 'POST' : 'PUT';
        $_SESSION['debug_messages'][] = "c_course.php: Calling API for {$act} using method {$apiMethod}.";
        $resp = callApi($apiBase, $apiMethod, $payload, $_SESSION['debug_messages']);
        $_SESSION['debug_messages'][] = "c_course.php: API call completed for {$act}. API Response: " . var_export($resp, true);

        if (!isset($resp['success']) || $resp['success'] !== true) {
            $_SESSION['error'] = $resp['message'] ?? 'API operation failed.';
            $_SESSION['debug_messages'][] = "c_course.php: ERROR - API call for {$act} failed. Message: '{$_SESSION['error']}'. Redirecting.";
            header('Location: ../admin/course-management.php?error=api');
            exit;
        }
        $_SESSION['debug_messages'][] = "c_course.php: API call for {$act} successful.";

        // Get created/updated ID
        $targetCourseID = $courseID; // For update
        if ($act === 'create' && isset($resp['data']['courseID'])) { // Assuming API returns new ID in data->courseID
            $targetCourseID = $resp['data']['courseID'];
            $_SESSION['debug_messages'][] = "c_course.php: New Course ID from API response: '{$targetCourseID}'";
        } elseif ($act === 'create') {
            $_SESSION['debug_messages'][] = "c_course.php: WARNING - API did not return a new course ID in 'data.courseID' field for create operation.";
            // Maybe try to get it from location header if API sets it?
            $targetCourseID = null; // Set to null if ID is unknown after creation
        }

        // Handle image upload via save image API (Only if we have a target Course ID)
        if ($targetCourseID && !empty($_FILES['CourseImage']['tmp_name']) && $_FILES['CourseImage']['error'] === UPLOAD_ERR_OK) {
            $_SESSION['debug_messages'][] = "c_course.php: Image file uploaded: Name: '{$_FILES['CourseImage']['name']}', Size: {$_FILES['CourseImage']['size']}, Type: '{$_FILES['CourseImage']['type']}'.";
            $tmpFile  = $_FILES['CourseImage']['tmp_name'];
            $origName = basename($_FILES['CourseImage']['name']); // Sanitize original name
            $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
            $allowed  = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($ext, $allowed)) {
                $_SESSION['debug_messages'][] = "c_course.php: Image extension '{$ext}' is allowed.";
                $destDir = __DIR__ . '/../uploads/courses/'; // Relative to this controller file
                $_SESSION['debug_messages'][] = "c_course.php: Destination directory: '{$destDir}'.";

                if (!is_dir($destDir)) {
                    $_SESSION['debug_messages'][] = "c_course.php: Destination directory does not exist, attempting to create.";
                    if (!mkdir($destDir, 0755, true)) {
                        $_SESSION['debug_messages'][] = "c_course.php: ERROR - Failed to create destination directory '{$destDir}'.";
                        // Decide if this is a critical error or just skip image upload
                    } else {
                        $_SESSION['debug_messages'][] = "c_course.php: Destination directory created successfully.";
                    }
                }

                if (is_dir($destDir) && is_writable($destDir)) {
                    $filename = "course_{$targetCourseID}_" . uniqid() . ".{$ext}";
                    $destinationPath = $destDir . $filename;
                    $_SESSION['debug_messages'][] = "c_course.php: Attempting to move uploaded file to '{$destinationPath}'.";

                    if (move_uploaded_file($tmpFile, $destinationPath)) {
                        $_SESSION['debug_messages'][] = "c_course.php: File moved successfully.";
                        $imageApiPath = dirname($apiBase) . '/course_image_api.php'; // Assuming API is in the same parent dir
                        $imagePayload = [
                            'courseID' => $targetCourseID,
                            'imagePath' => 'uploads/courses/' . $filename, // Path relative to web root usually
                            'caption'  => $title, // Use course title as caption, or leave null
                            'sortOrder' => 0
                        ];
                        $_SESSION['debug_messages'][] = "c_course.php: Calling image save API at '{$imageApiPath}' with payload: " . json_encode($imagePayload);
                        $imgResp = callApi($imageApiPath, 'POST', $imagePayload, $_SESSION['debug_messages']);
                        $_SESSION['debug_messages'][] = "c_course.php: Image save API Response: " . var_export($imgResp, true);
                        if (!isset($imgResp['success']) || $imgResp['success'] !== true) {
                            $_SESSION['warning_message'] = "Course saved/updated, but failed to save image via API: " . ($imgResp['message'] ?? 'Unknown error');
                            $_SESSION['debug_messages'][] = "c_course.php: WARNING - Failed to save image via API. Message: '{$_SESSION['warning_message']}'.";
                        } else {
                            $_SESSION['debug_messages'][] = "c_course.php: Image saved successfully via API.";
                        }
                    } else {
                        $_SESSION['warning_message'] = "Course saved/updated, but failed to move uploaded image file.";
                        $_SESSION['debug_messages'][] = "c_course.php: ERROR - move_uploaded_file failed.";
                    }
                } else {
                    $_SESSION['warning_message'] = "Course saved/updated, but image destination directory is not writable or does not exist.";
                    $_SESSION['debug_messages'][] = "c_course.php: ERROR - Destination directory '{$destDir}' not writable or doesn't exist.";
                }
            } else {
                $_SESSION['warning_message'] = "Course saved/updated, but uploaded image type ('{$ext}') is not allowed.";
                $_SESSION['debug_messages'][] = "c_course.php: WARNING - Invalid image extension '{$ext}'.";
            }
        } elseif (!empty($_FILES['CourseImage']['name'])) {
            $_SESSION['debug_messages'][] = "c_course.php: Image upload failed. Error code: " . ($_FILES['CourseImage']['error'] ?? 'Unknown');
            $_SESSION['warning_message'] = "Course saved/updated, but there was an error uploading the image (Code: " . ($_FILES['CourseImage']['error'] ?? 'N/A') . ").";
        } else {
            $_SESSION['debug_messages'][] = "c_course.php: No image file uploaded.";
        }

        $_SESSION['success'] = $resp['message'] ?? ($act === 'create' ? 'Course created successfully.' : 'Course updated successfully.');
        $_SESSION['debug_messages'][] = "c_course.php: Operation '{$act}' completed. Success message: '{$_SESSION['success']}'. Redirecting.";
        header('Location: ../admin/course-management.php?success=1');
        exit;

    case 'delete':
        $_SESSION['debug_messages'][] = "c_course.php: Action is 'delete'";
        if ($method !== 'GET' && $method !== 'POST') { // Allow POST for delete if preferred for forms
            $_SESSION['error'] = 'Invalid request method for delete.';
            $_SESSION['debug_messages'][] = "c_course.php: ERROR - Invalid method for delete. Expected GET or POST, got {$method}. Redirecting.";
            header('Location: ../admin/course-management.php?error=invalid_request');
            exit;
        }
        $_SESSION['debug_messages'][] = "c_course.php: Method is {$method}, proceeding with delete.";

        $courseID = trim(($method === 'GET' ? $_GET['CourseID'] : $_POST['CourseID']) ?? '');
        $_SESSION['debug_messages'][] = "c_course.php: CourseID for deletion: '{$courseID}'";

        if (!$courseID) {
            $_SESSION['error'] = 'Invalid course ID for deletion.';
            $_SESSION['debug_messages'][] = "c_course.php: ERROR - Invalid or missing CourseID for deletion. Redirecting.";
            header('Location: ../admin/course-management.php?error=invalid_id');
            exit;
        }
        $_SESSION['debug_messages'][] = "c_course.php: CourseID validation passed.";

        // Prepare payload for DELETE (API might expect ID in URL or body)
        // This example assumes body payload based on callApi structure
        $payload = ['courseID' => $courseID];
        $_SESSION['debug_messages'][] = "c_course.php: Payload prepared for DELETE API call: " . json_encode($payload);

        $_SESSION['debug_messages'][] = "c_course.php: Calling API for delete.";
        // Note: file_get_contents with stream_context might not correctly send a body with DELETE method depending on PHP version/config.
        // cURL is generally more reliable for methods other than GET/POST.
        $resp = callApi($apiBase, 'DELETE', $payload, $_SESSION['debug_messages']);
        $_SESSION['debug_messages'][] = "c_course.php: API call completed for delete. API Response: " . var_export($resp, true);

        $isSuccess = isset($resp['success']) && $resp['success'] === true;
        $_SESSION[$isSuccess ? 'success' : 'error'] = $resp['message'] ?? ($isSuccess ? 'Course deleted successfully.' : 'Failed to delete course.');
        $_SESSION['debug_messages'][] = "c_course.php: Delete operation " . ($isSuccess ? 'successful' : 'failed') . ". Message: '{$_SESSION[$isSuccess ? 'success' : 'error']}'. Redirecting.";
        header('Location: ../admin/course-management.php?' . ($isSuccess ? 'success=1' : 'error=api'));
        exit;

    default:
        $_SESSION['error'] = "Unsupported action: '" . htmlspecialchars($act) . "'.";
        $_SESSION['debug_messages'][] = "c_course.php: ERROR - Unsupported action requested: '" . $act . "'. Redirecting.";
        header('Location: ../admin/course-management.php?error=unknown_action');
        exit;
}
