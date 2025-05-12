<?php
// File: c_course.php (Controller using REST API with Enhanced File Upload & Debugging)
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
    $response = @file_get_contents($url, false, $context); // Suppress warnings, check response === false
    $http_response_header = $http_response_header ?? []; // Initialize if not set
    $debugMessages[] = "callApi: Raw Response Headers: " . implode("\n", $http_response_header);
    $debugMessages[] = "callApi: Raw Response Body: " . $response;

    if ($response === false) {
        $error = error_get_last();
        $debugMessages[] = "callApi: ERROR - file_get_contents failed. Error: " . ($error['message'] ?? 'Unknown error');
        // Check for timeout specifically
        if (strpos(($error['message'] ?? ''), 'timed out') !== false) {
             return ['success' => false, 'message' => 'API connection timed out.'];
        }
        return ['success' => false, 'message' => 'API connection failed.'];
    }

    $decodedResponse = json_decode($response, true);

    // Check for JSON decoding errors ONLY if the response body is not empty
    if ($response !== '' && $decodedResponse === null && json_last_error() !== JSON_ERROR_NONE) {
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
        // Include raw response snippet in error if decoding failed
        $rawSnippet = substr(trim($response), 0, 100); // Get first 100 chars
        return ['success' => false, 'message' => 'Invalid API response format. Status: ' . $httpStatusCode . '. Response starts with: ' . htmlspecialchars($rawSnippet)];
    }

    // Handle cases where API returns non-JSON success/error messages (e.g., plain text "OK")
     if ($response !== '' && $decodedResponse === null && json_last_error() === JSON_ERROR_NONE) {
         $debugMessages[] = "callApi: WARNING - Response was not JSON, but valid (e.g., empty or plain text). Treating as potential success/failure based on HTTP status.";
          // Try to determine success based on HTTP status code
         $httpStatusCode = 'Unknown';
         foreach ($http_response_header as $header) {
            if (preg_match('{HTTP/\d\.\d\s+(\d+)\s+}', $header, $match)) {
                $httpStatusCode = $match[1];
                break;
            }
         }
          if ($httpStatusCode >= 200 && $httpStatusCode < 300) {
               $debugMessages[] = "callApi: HTTP status ($httpStatusCode) indicates success despite non-JSON body.";
               // Return a generic success structure, maybe include the raw response
              return ['success' => true, 'message' => 'Operation successful (non-JSON response)', 'raw_response' => $response];
          } else {
               $debugMessages[] = "callApi: HTTP status ($httpStatusCode) indicates failure with non-JSON body.";
              return ['success' => false, 'message' => 'API request failed (non-JSON response). Status: ' . $httpStatusCode, 'raw_response' => $response];
          }
     }


    $debugMessages[] = "callApi: Decoded Response: " . var_export($decodedResponse, true);
    // Add success = false if not present and message suggests error based on common API patterns
    if (!isset($decodedResponse['success']) && isset($decodedResponse['message']) && stripos($decodedResponse['message'], 'error') !== false) {
         $debugMessages[] = "callApi: WARNING - 'success' key missing, but message implies error. Setting success=false.";
         $decodedResponse['success'] = false;
    } elseif (!isset($decodedResponse['success'])) {
         $debugMessages[] = "callApi: WARNING - 'success' key missing. Assuming success based on decodable JSON.";
        // Potentially assume success if JSON decoded and no obvious error message? Risky.
        // It's better if the API *always* returns a 'success' key.
        // For now, let's assume success if it decodes and has some data or a non-error message
         $isLikelySuccess = !isset($decodedResponse['message']) || stripos($decodedResponse['message'], 'error') === false;
         $decodedResponse['success'] = $isLikelySuccess;
     }


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
            header('Location: ../admin/course-management.php?view=list&error=invalid_request'); // Redirect back to list view
            exit;
        }
        $_SESSION['debug_messages'][] = "c_course.php: Method is POST, proceeding with {$act}.";

        // Collect form data
        $courseID   = trim($_POST['CourseID'] ?? ''); // Only relevant for update
        $title      = trim($_POST['Title'] ?? '');
        $price      = isset($_POST['Price']) ? str_replace(',', '', trim($_POST['Price'])) : 0; // Remove commas if any
        $price      = floatval($price); // Convert to float
        $instructor = trim($_POST['Instructors'] ?? ''); // Assuming single InstructorID
        $categories = $_POST['Categories'] ?? [];
        $description = trim($_POST['Description'] ?? '');

        $_SESSION['debug_messages'][] = "c_course.php: Form data collected - CourseID: '{$courseID}', Title: '{$title}', Price: {$price}, InstructorID: '{$instructor}', Categories: " . implode(',', $categories) . ", Description: '" . substr($description, 0, 50) . "...'";

        // Basic Validation
        $errors = [];
        if (!$title) $errors[] = "Title is required.";
        if (!is_numeric($price) || $price < 0) $errors[] = "Price must be a non-negative number.";
        if (!$instructor) $errors[] = "Instructor is required.";
        if (empty($categories)) $errors[] = "At least one Category is required.";
        if ($act === 'update' && empty($courseID)) $errors[] = 'Course ID is missing for update.';

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['form_data'] = $_POST; // Preserve form data for repopulation
            $_SESSION['debug_messages'][] = "c_course.php: ERROR - Validation failed: " . implode('; ', $errors) . ". Redirecting back.";
            $redirect_url = '../admin/course-management.php?view=' . ($act === 'update' ? 'edit&id=' . $courseID : 'add');
            header('Location: ' . $redirect_url . '&error=validation');
            exit;
        }
        $_SESSION['debug_messages'][] = "c_course.php: Basic validation passed.";

        // Prepare payload for course API
        $payload = [
            'title'        => $title,
            'description'  => $description,
            'price'        => $price,
            'instructorID' => $instructor,
            'categoriesID' => $categories // API expects categoriesID based on original code
        ];
        if ($act === 'update') {
            $payload['courseID']  = $courseID;
            $_SESSION['debug_messages'][] = "c_course.php: Added CourseID '{$courseID}' to payload for update.";
        }
         // Assuming createdBy might be handled by the API based on session/token, if needed.
         // $payload['createdBy'] = $_SESSION['user_id'] ?? null; // Example if you need to send it

        $_SESSION['debug_messages'][] = "c_course.php: Payload prepared for Course API call: " . json_encode($payload);

        // Call course API (Create or Update)
        $apiMethod = $act === 'create' ? 'POST' : 'PUT';
        $_SESSION['debug_messages'][] = "c_course.php: Calling Course API for {$act} using method {$apiMethod}.";
        $resp = callApi($apiBase, $apiMethod, $payload, $_SESSION['debug_messages']);
        $_SESSION['debug_messages'][] = "c_course.php: Course API call completed for {$act}. API Response: " . var_export($resp, true);

        if (!isset($resp['success']) || $resp['success'] !== true) {
            $_SESSION['error'] = $resp['message'] ?? 'API operation failed.';
            $_SESSION['form_data'] = $_POST; // Preserve form data
            $_SESSION['debug_messages'][] = "c_course.php: ERROR - Course API call for {$act} failed. Message: '{$_SESSION['error']}'. Redirecting back.";
             $redirect_url = '../admin/course-management.php?view=' . ($act === 'update' ? 'edit&id=' . $courseID : 'add');
            header('Location: ' . $redirect_url . '&error=api');
            exit;
        }
        $_SESSION['debug_messages'][] = "c_course.php: Course API call for {$act} successful.";
        unset($_SESSION['form_data']); // Clear preserved form data on success

        // --- Determine the Course ID after successful operation ---
        $targetCourseID = null; // Initialize targetCourseID

        if ($act === 'update') {
             $targetCourseID = $courseID; // For update, use the ID from the form
             $_SESSION['debug_messages'][] = "c_course.php: Using CourseID '{$targetCourseID}' from form for update action.";
        } elseif ($act === 'create') {
             $_SESSION['debug_messages'][] = "c_course.php: Attempting to determine new Course ID for 'create' action.";
             // Try common keys for the new ID returned by the API
             if (isset($resp['data']['CourseID'])) { // Check specific keys first
                 $targetCourseID = $resp['data']['CourseID'];
             } elseif (isset($resp['data']['id'])) {
                  $targetCourseID = $resp['data']['id'];
             } elseif (isset($resp['courseID'])) { // Check top-level key
                  $targetCourseID = $resp['courseID'];
             } elseif (isset($resp['id'])) {
                  $targetCourseID = $resp['id'];
             } elseif (isset($resp['course_id'])) {
                  $targetCourseID = $resp['course_id'];
             // --- ADD MORE CHECKS HERE if you discover the actual key ---
             // elseif (isset($resp['your_actual_id_key'])) {
             //     $targetCourseID = $resp['your_actual_id_key'];
             // }
             // --- End additional checks ---
             } else {
                 // Attempt to guess if only one numeric/string value is in 'data'
                  if (isset($resp['data']) && is_array($resp['data']) && count($resp['data']) === 1) {
                      $potentialId = reset($resp['data']);
                      // Allow string IDs as well (e.g., GUIDs or prefixed IDs)
                      if (is_numeric($potentialId) || (is_string($potentialId) && !empty($potentialId)) ) {
                          $targetCourseID = $potentialId;
                          $_SESSION['debug_messages'][] = "c_course.php: Guessed new Course ID from single 'data' field: '{$targetCourseID}'";
                      }
                  }
                  // If still not found, $targetCourseID remains null
             }

             // --- Handle the outcome of ID determination ---
             if ($targetCourseID) {
                  $_SESSION['debug_messages'][] = "c_course.php: Determined new Course ID from API response: '{$targetCourseID}'";
             } else {
                  // ID could not be determined - CRITICAL IF IMAGE UPLOADED
                  $_SESSION['debug_messages'][] = "c_course.php: CRITICAL/WARNING - Could not determine new Course ID from API response for 'create' action. API Response was: " . var_export($resp, true);

                  // Check if an image was actually part of this request and successfully uploaded to temp dir
                  $imageWasUploadedAndOk = isset($_FILES['CourseImage']) && $_FILES['CourseImage']['error'] === UPLOAD_ERR_OK;
                  $_SESSION['debug_messages'][] = "c_course.php: Checking if image was uploaded for this request: " . ($imageWasUploadedAndOk ? 'Yes' : 'No');


                  if ($imageWasUploadedAndOk) {
                       // If an image was uploaded, this is now a critical error because we can't associate it.
                       $_SESSION['error'] = "Course created via API, but its ID could not be retrieved from the API response. Failed to process uploaded image. Please check the course manually and upload the image again.";
                       $_SESSION['debug_messages'][] = "c_course.php: ERROR - Treating missing ID as critical because an image was uploaded. Redirecting back to add form.";
                       $_SESSION['form_data'] = $_POST; // Preserve form data
                       // Do NOT unset $_FILES, let the browser potentially resubmit if user refreshes? Or clear it? Let's clear maybe.
                       // unset($_FILES['CourseImage']); // Optional: clear file state
                       header('Location: ../admin/course-management.php?view=add&error=api_id_missing');
                       exit;
                  } else {
                       // No image was uploaded (or upload failed before this point), so just a warning is okay.
                       $_SESSION['warning_message'] = "Course created, but could not determine its ID from the API response. Some operations (like image linking) might require manual intervention.";
                       $_SESSION['debug_messages'][] = "c_course.php: WARNING - Missing ID, but no image uploaded (or upload failed earlier). Proceeding with warning.";
                       // Let the script continue to the image handling section (which will be skipped) and then redirect.
                       // $targetCourseID remains null.
                  }
             }
        }
        // --- End Determine the Course ID ---


        // --- Enhanced Image Upload Handling ---
        $imageUploadSuccess = false; // Flag to track image upload status
        $imageFileNameForApi = null; // Store the final relative path for the API

        // Check if a file was uploaded AND there is a target Course ID
        // $targetCourseID check is now crucial here
        if ($targetCourseID && isset($_FILES['CourseImage']) && $_FILES['CourseImage']['error'] !== UPLOAD_ERR_NO_FILE) {
            $_SESSION['debug_messages'][] = "c_course.php: Processing uploaded file 'CourseImage' for Course ID '{$targetCourseID}'.";
            $file = $_FILES['CourseImage'];

            // 1. Check for Upload Errors (again, in case code reaches here unexpectedly)
            if ($file['error'] !== UPLOAD_ERR_OK) {
                // This block might be redundant if the check above handles UPLOAD_ERR_OK correctly, but safe to keep.
                $uploadError = 'Unknown upload error.';
                // ... (switch statement for errors as before) ...
                 switch ($file['error']) {
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        $uploadError = "File size exceeds the limit.";
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $uploadError = "File was only partially uploaded.";
                        break;
                    case UPLOAD_ERR_NO_TMP_DIR:
                        $uploadError = "Server configuration error: Missing temporary folder.";
                        break;
                    case UPLOAD_ERR_CANT_WRITE:
                        $uploadError = "Server configuration error: Failed to write file to disk.";
                        break;
                    case UPLOAD_ERR_EXTENSION:
                        $uploadError = "A PHP extension stopped the file upload.";
                        break;
                 }
                $_SESSION['warning_message'] = "Course saved/updated, but image upload failed: " . $uploadError . " (Code: {$file['error']})";
                $_SESSION['debug_messages'][] = "c_course.php: ERROR - Image Upload Error: " . $uploadError . " (Code: {$file['error']}).";

            } else {
                 $_SESSION['debug_messages'][] = "c_course.php: File uploaded with no initial errors. Name: '{$file['name']}', Size: {$file['size']}, Type: '{$file['type']}'.";

                // 2. Define Constraints & Destination
                $destDirRelative = 'uploads/courses/'; // Path relative to web root / where API expects it
                $destDirAbsolute = __DIR__ . '/../' . $destDirRelative; // Absolute path from this script's location
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp']; // Allowed image extensions
                $max_size = 5 * 1024 * 1024; // 5 MB limit

                $_SESSION['debug_messages'][] = "c_course.php: Absolute destination directory: '{$destDirAbsolute}'.";
                $_SESSION['debug_messages'][] = "c_course.php: Relative destination directory (for API): '{$destDirRelative}'.";

                // 3. Validate File Size
                if ($file['size'] > $max_size) {
                    $_SESSION['warning_message'] = "Course saved/updated, but image '{$file['name']}' was too large (Max: " . ($max_size / 1024 / 1024) . "MB).";
                    $_SESSION['debug_messages'][] = "c_course.php: ERROR - File size validation failed. Size: {$file['size']}, Max: {$max_size}.";
                } else {
                    $_SESSION['debug_messages'][] = "c_course.php: File size validation passed.";

                    // 4. Validate File Type (Extension)
                    $origName = basename($file["name"]); // Sanitize original name
                    $fileType = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                    if (!in_array($fileType, $allowed_types)) {
                        $_SESSION['warning_message'] = "Course saved/updated, but image type '{$fileType}' is not allowed. Allowed types: " . implode(', ', $allowed_types) . ".";
                        $_SESSION['debug_messages'][] = "c_course.php: ERROR - File type validation failed. Type: '{$fileType}'.";
                    } else {
                         $_SESSION['debug_messages'][] = "c_course.php: File type validation passed ('{$fileType}').";

                        // 5. Ensure Destination Directory Exists and is Writable
                        if (!is_dir($destDirAbsolute)) {
                            $_SESSION['debug_messages'][] = "c_course.php: Destination directory does not exist, attempting to create.";
                            if (!@mkdir($destDirAbsolute, 0755, true)) { // Use @ to suppress default warning, handle error below
                                $error = error_get_last();
                                $_SESSION['warning_message'] = "Course saved/updated, but failed to create image directory. Check server permissions.";
                                $_SESSION['debug_messages'][] = "c_course.php: ERROR - Failed to create destination directory '{$destDirAbsolute}'. Error: " . ($error['message'] ?? 'Unknown error');
                                $destDirAbsolute = null; // Mark directory as unusable
                            } else {
                                $_SESSION['debug_messages'][] = "c_course.php: Destination directory created successfully.";
                                // Optional: Add .htaccess to deny direct access
                                 @file_put_contents($destDirAbsolute . '.htaccess', 'Deny from all');
                            }
                        }

                        if ($destDirAbsolute && !is_writable($destDirAbsolute)) {
                             $_SESSION['warning_message'] = "Course saved/updated, but the image directory is not writable. Check server permissions.";
                             $_SESSION['debug_messages'][] = "c_course.php: ERROR - Destination directory '{$destDirAbsolute}' is not writable.";
                             $destDirAbsolute = null; // Mark directory as unusable
                        }

                        // 6. Generate Unique Filename and Move File
                        if ($destDirAbsolute) { // Proceed only if directory is ready
                            // Generate a unique name to avoid conflicts and potentially hide original name
                            $filename = "course_{$targetCourseID}_" . uniqid() . "." . $fileType;
                            $destinationPath = $destDirAbsolute . $filename;
                            $imageFileNameForApi = $destDirRelative . $filename; // Relative path for the API call

                            $_SESSION['debug_messages'][] = "c_course.php: Generated unique filename: '{$filename}'.";
                            $_SESSION['debug_messages'][] = "c_course.php: Attempting to move uploaded file from '{$file['tmp_name']}' to '{$destinationPath}'.";

                            if (move_uploaded_file($file['tmp_name'], $destinationPath)) {
                                $_SESSION['debug_messages'][] = "c_course.php: File moved successfully to '{$destinationPath}'.";
                                $imageUploadSuccess = true;
                            } else {
                                $error = error_get_last();
                                $_SESSION['warning_message'] = "Course saved/updated, but failed to save the uploaded image file.";
                                $_SESSION['debug_messages'][] = "c_course.php: ERROR - move_uploaded_file failed. Error: " . ($error['message'] ?? 'Unknown error') . ". Check permissions and configuration.";
                                $imageFileNameForApi = null; // Clear filename if move failed
                            }
                        }
                    } // End type check
                } // End size check
            } // End initial upload error check
        } elseif (isset($_FILES['CourseImage']) && $_FILES['CourseImage']['error'] === UPLOAD_ERR_NO_FILE) {
            // No file submitted - this is normal, not an error. Log if needed.
             $_SESSION['debug_messages'][] = "c_course.php: No image file was submitted in the form.";
        } elseif (!$targetCourseID && isset($_FILES['CourseImage']) && $_FILES['CourseImage']['error'] === UPLOAD_ERR_OK) {
             // This case should now be caught by the critical error handling after ID determination failure
             $_SESSION['debug_messages'][] = "c_course.php: Image upload skipped because targetCourseID was not determined.";
        } else {
             // Other potential $_FILES issues or $targetCourseID is null without an image upload attempt
              $_SESSION['debug_messages'][] = "c_course.php: Image upload section skipped. Reason: No valid Course ID or no file uploaded/processed.";
        }
        // --- End Enhanced Image Upload Handling ---

        // --- Lưu thông tin ảnh bằng Service (KHÔNG gọi API lưu ảnh nữa) ---
        if ($imageUploadSuccess && $imageFileNameForApi && $targetCourseID) {
            require_once __DIR__ . '/../service/service_course_image.php';
            $caption = $title ?? '';
            $sortOrder = 0;
            $courseImageService = new CourseImageService();
            $saveResp = $courseImageService->add_image(
                $targetCourseID,
                $imageFileNameForApi, // đường dẫn tương đối
                $caption,
                $sortOrder
            );
            if ($saveResp->success) {
                $_SESSION['success'] = 'Thêm khóa học và ảnh thành công!';
            } else {
                $_SESSION['warning_message'] = 'Thêm khóa học thành công, nhưng lưu thông tin ảnh thất bại: ' . $saveResp->message;
                $_SESSION['debug_messages'][] = 'Lưu thông tin ảnh bằng Service thất bại: ' . $saveResp->message;
            }
        } else if ($imageUploadSuccess && (!$imageFileNameForApi || !$targetCourseID)) {
            $_SESSION['warning_message'] = 'Ảnh đã được upload nhưng không lưu được vào hệ thống do thiếu thông tin.';
        }
        // --- XÓA hoặc BỎ QUA tất cả những đoạn gọi API lưu ảnh ---

        // Final Redirect
        // Set success message from the main course API response
        if (!isset($_SESSION['success'])) {
            $_SESSION['success'] = $resp['message'] ?? ($act === 'create' ? 'Course created successfully.' : 'Course updated successfully.');
        }

        $_SESSION['debug_messages'][] = "c_course.php: Operation '{$act}' completed. Redirecting to list view.";
        header('Location: ../admin/course-management.php?view=list&success=1'); // Redirect to list view on success
        exit;

    case 'delete':
        // Bổ sung case 'delete' xử lý xoá khoá học bằng courseID cho phép thao tác xoá từ giao diện.
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
        $_SESSION['debug_messages'][] = "c_course.php: ERROR - Unsupported action requested: '" . $act . "'. Redirecting.";
        header('Location: ../admin/course-management.php?view=list&error=unknown_action');
        exit;
}
