<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('PROJECT_ROOT', dirname(__DIR__));
define('UPLOADS_DIR', PROJECT_ROOT . '/uploads');

$baseAppPath = dirname(dirname($_SERVER['SCRIPT_NAME']));
if ($baseAppPath === '/' || $baseAppPath === '\\') {
    $baseAppPath = '';
}


if (!function_exists('ensureUploadDirectory')) {
    function ensureUploadDirectory(string $absoluteDirectoryPath): bool
    {
        if (!is_dir($absoluteDirectoryPath)) {
            if (!mkdir($absoluteDirectoryPath, 0755, true)) {
                return false;
            }
        }
        if (!is_writable($absoluteDirectoryPath)) {
            return false;
        }
        return true;
    }
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
            $decodedResponse['data'] = $decodedResponse['data'] ?? null;
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

$action = '';
$requestMethod = $_SERVER['REQUEST_METHOD'];

if ($requestMethod === 'POST') {
    $action = $_POST['action'] ?? '';
} elseif ($requestMethod === 'GET') {
    $action = $_GET['action'] ?? '';
}

$redirectUrl = '../edit-profile.php';

switch ($action) {
    case 'update_user_profile':
        if ($requestMethod !== 'POST') {
            $_SESSION['update_message'] = 'Invalid request method.';
            $_SESSION['message_type'] = 'danger';
            header("Location: " . $redirectUrl);
            exit;
        }

        $userID = $_POST['userID'] ?? null;
        $oldProfileImageFileNameFromSession = $_SESSION['user']['profileImage'] ?? null;

        $firstName = isset($_POST['firstName']) ? trim($_POST['firstName']) : null;
        $lastName = isset($_POST['lastName']) ? trim($_POST['lastName']) : null;

        $formErrors = [];
        $apiPayload = [];

        if (empty($userID)) {
            $formErrors[] = "User ID is missing. Cannot update profile.";
        } else {
            $apiPayload['userID'] = $userID;
        }

        if (isset($_POST['firstName']) && $firstName !== ($_SESSION['user']['firstName'] ?? null)) {
            $apiPayload['firstName'] = $firstName;
        }
        if (isset($_POST['lastName']) && $lastName !== ($_SESSION['user']['lastName'] ?? null)) {
            $apiPayload['lastName'] = $lastName;
        }

        $newProfileImageFileName = null;
        $userUploadDir = null;

        if (isset($_FILES['profileImageFile']) && $_FILES['profileImageFile']['error'] === UPLOAD_ERR_OK && !empty($_FILES['profileImageFile']['tmp_name'])) {
            if (empty($userID)) {
                $formErrors[] = "Cannot upload image without User ID.";
            } else {
                $uploadedFile = $_FILES['profileImageFile'];
                $originalFileName = $uploadedFile['name'];
                $fileTmpName = $uploadedFile['tmp_name'];
                $fileSize = $uploadedFile['size'];
                $fileExtension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));

                $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                $maxFileSize = 5 * 1024 * 1024;

                if (!in_array($fileExtension, $allowedExtensions, true)) {
                    $formErrors[] = "Invalid file format for profile image. Allowed: " . implode(', ', $allowedExtensions);
                }
                if ($fileSize > $maxFileSize) {
                    $formErrors[] = "Profile image file size is too large. Max: " . ($maxFileSize / 1024 / 1024) . "MB.";
                }

                if (empty($formErrors)) {
                    $safeUserID = preg_replace('/[^a-zA-Z0-9_-]/', '_', (string)$userID);
                    $userUploadDir = UPLOADS_DIR . DIRECTORY_SEPARATOR . $safeUserID;

                    if (ensureUploadDirectory($userUploadDir)) {
                        $imageID = str_replace('.', '_', uniqid('avatar_', true));
                        $newProfileImageFileName = $imageID . "." . $fileExtension;
                        $destinationPath = $userUploadDir . DIRECTORY_SEPARATOR . $newProfileImageFileName;

                        $i = 1;
                        while (file_exists($destinationPath)) {
                            $newProfileImageFileName = $imageID . "_($i)." . $fileExtension;
                            $destinationPath = $userUploadDir . DIRECTORY_SEPARATOR . $newProfileImageFileName;
                            $i++;
                        }

                        if (move_uploaded_file($fileTmpName, $destinationPath)) {
                            $apiPayload['profileImage'] = $newProfileImageFileName;
                        } else {
                            $formErrors[] = "System error: Could not save uploaded profile image.";
                            error_log("Error moving uploaded file for user {$userID}: move_uploaded_file failed from {$fileTmpName} to {$destinationPath}");
                        }
                    } else {
                        $formErrors[] = "System error: Could not prepare storage for profile image.";
                        error_log("Error ensuring upload directory for user {$userID}: {$userUploadDir}");
                    }
                }
            }
        } elseif (isset($_FILES['profileImageFile']) && $_FILES['profileImageFile']['error'] !== UPLOAD_ERR_OK && $_FILES['profileImageFile']['error'] !== UPLOAD_ERR_NO_FILE) {
            $formErrors[] = "Error uploading profile image. Code: " . $_FILES['profileImageFile']['error'];
        }

        if (!empty($formErrors)) {
            $_SESSION['update_message'] = implode("<br>", $formErrors);
            $_SESSION['message_type'] = 'danger';
        } else {
            $numberOfFieldsToUpdate = 0;
            if (isset($apiPayload['firstName'])) $numberOfFieldsToUpdate++;
            if (isset($apiPayload['lastName'])) $numberOfFieldsToUpdate++;
            if (isset($apiPayload['profileImage'])) $numberOfFieldsToUpdate++;

            if ($numberOfFieldsToUpdate > 0) {
                $updateUserURL = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http")
                    . "://" . $_SERVER['HTTP_HOST']
                    . $baseAppPath
                    . '/api/user_api.php';

                $response = callApi($updateUserURL, 'PUT', $apiPayload);

                if ($response && isset($response['success']) && $response['success']) {
                    $_SESSION['update_message'] = $response['message'] ?? 'Profile updated successfully.';
                    $_SESSION['message_type'] = 'success';

                    if (isset($apiPayload['firstName'])) {
                        $_SESSION['user']['firstName'] = $apiPayload['firstName'];
                    }
                    if (isset($apiPayload['lastName'])) {
                        $_SESSION['user']['lastName'] = $apiPayload['lastName'];
                    }

                    if (isset($apiPayload['profileImage'])) {
                        $newlyUploadedImage = $apiPayload['profileImage'];
                        $_SESSION['user']['profileImage'] = $newlyUploadedImage;

                        if ($oldProfileImageFileNameFromSession &&
                            $oldProfileImageFileNameFromSession !== $newlyUploadedImage &&
                            $userUploadDir
                        ) {
                            $oldImageFilePath = $userUploadDir . DIRECTORY_SEPARATOR . $oldProfileImageFileNameFromSession;
                            if (file_exists($oldImageFilePath)) {
                                if (@unlink($oldImageFilePath)) {
                                    error_log("Successfully deleted old profile image: {$oldImageFilePath}");
                                } else {
                                    error_log("Failed to delete old profile image: {$oldImageFilePath}. Check permissions.");
                                }
                            } else {
                                error_log("Old profile image not found for deletion (this might be OK): {$oldImageFilePath}");
                            }
                        }
                    }
                } else {
                    $_SESSION['update_message'] = 'Failed to update profile: ' . ($response['message'] ?? 'Unknown API error.');
                    $_SESSION['message_type'] = 'danger';

                    if ($newProfileImageFileName && $userUploadDir) {
                        error_log("API update failed for user {$userID} but new image {$newProfileImageFileName} was uploaded to {$userUploadDir}. Consider manual cleanup or retry logic.");
                    }
                }
            } else {
                $_SESSION['update_message'] = 'No changes were submitted.';
                $_SESSION['message_type'] = 'info';
            }
        }
        header("Location: " . $redirectUrl);
        exit;

    case 'save_password':
        if ($requestMethod !== 'POST') {
            $_SESSION['update_message'] = 'Invalid request method for password change.';
            $_SESSION['message_type'] = 'danger';
            header("Location: " . $redirectUrl);
            exit;
        }

        $userID = $_POST['userID'] ?? null;
        $currentPassword = $_POST['currentPassword'] ?? '';
        $newPassword = $_POST['newPassword'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';

        if (empty($userID) || empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $_SESSION['update_message'] = 'All password fields are required.';
            $_SESSION['message_type'] = 'danger';
            header("Location: " . $redirectUrl);
            exit;
        }

        if ($newPassword !== $confirmPassword) {
            $_SESSION['update_message'] = 'New password and confirmation password do not match.';
            $_SESSION['message_type'] = 'danger';
            header("Location: " . $redirectUrl);
            exit;
        }

        if (strlen($newPassword) < 8) {
            $_SESSION['update_message'] = 'New password must be at least 8 characters long.';
            $_SESSION['message_type'] = 'danger';
            header("Location: " . $redirectUrl);
            exit;
        }

        $apiPayload = [
            'userID' => $userID,
            'currentPassword' => $currentPassword,
            'newPassword' => $newPassword,
            'isChangePassword' => true,
        ];

        $passwordApiUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http")
            . "://" . $_SERVER['HTTP_HOST']
            . $baseAppPath
            . '/api/user_api.php';
        $response = callApi($passwordApiUrl, 'PUT', $apiPayload);

        if ($response && isset($response['success']) && $response['success']) {
            $_SESSION['update_message'] = $response['message'] ?? 'Password changed successfully.';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['update_message'] = 'Failed to change password: ' . ($response['message'] ?? 'Unknown API error.');
            $_SESSION['message_type'] = 'danger';
        }
        header("Location: " . $redirectUrl . '#profileContent');
        exit;
        break;


    case 'save_password':
        if ($requestMethod !== 'POST') {
            $_SESSION['update_message'] = 'Invalid request method for password change.';
            $_SESSION['message_type'] = 'danger';
            header("Location: " . $redirectUrl);
            exit;
        }

        $userID = $_POST['userID'] ?? null;
        $currentPassword = $_POST['currentPassword'] ?? '';
        $newPassword = $_POST['newPassword'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';

        if (empty($userID) || empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $_SESSION['update_message'] = 'All password fields are required.';
            $_SESSION['message_type'] = 'danger';
            header("Location: " . $redirectUrl);
            exit;
        }

        if ($newPassword !== $confirmPassword) {
            $_SESSION['update_message'] = 'New password and confirmation password do not match.';
            $_SESSION['message_type'] = 'danger';
            header("Location: " . $redirectUrl);
            exit;
        }

        if (strlen($newPassword) < 8) {
            $_SESSION['update_message'] = 'New password must be at least 8 characters long.';
            $_SESSION['message_type'] = 'danger';
            header("Location: " . $redirectUrl);
            exit;
        }

        $apiPayload = [
            'userID' => $userID,
            'currentPassword' => $currentPassword,
            'newPassword' => $newPassword,
            'isChangePassword' => true,
        ];

        $passwordApiUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http")
            . "://" . $_SERVER['HTTP_HOST']
            . $baseAppPath
            . '/api/user_api.php';
        $response = callApi($passwordApiUrl, 'PUT', $apiPayload);

        if ($response['success']) {
            $_SESSION['update_message'] = $response['message'] ?? 'Password changed successfully.';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['update_message'] = 'Failed to change password: ' . ($response['message'] ?? 'Unknown API error.');
        }
        header("Location: " . $redirectUrl . '#profileContent');
        exit;
        break;
    case 'load_img_profile':
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
        if (!empty($action)) {
            $_SESSION['update_message'] = 'Unknown action requested.';
            $_SESSION['message_type'] = 'warning';
        }
        header("Location: " . $redirectUrl);
        exit;
}
