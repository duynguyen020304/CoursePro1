<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('PROJECT_ROOT', dirname(__DIR__));
define('UPLOADS_DIR', PROJECT_ROOT . '/uploads');
define('API_BASE_URL', getenv('API_BASE_URL') ?: 'http://your-api-domain.com/api');

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

$redirectUrl = '../edit_profile.php';

switch ($action) {
    case 'update_user_profile':
        if ($requestMethod !== 'POST') {
            $_SESSION['update_message'] = 'Invalid request method.';
            $_SESSION['message_type'] = 'danger';
            header("Location: " . $redirectUrl);
            exit;
        }

        $userID = $_POST['userID'] ?? null;
        $firstName = isset($_POST['firstName']) ? trim($_POST['firstName']) : null;
        $lastName = isset($_POST['lastName']) ? trim($_POST['lastName']) : null;

        $formErrors = [];
        $apiPayload = [];

        if (empty($userID)) {
            $formErrors[] = "User ID is missing. Cannot update profile.";
        } else {
            $apiPayload['userID'] = $userID;
        }

        if (isset($_POST['firstName'])) {
            $apiPayload['firstName'] = $firstName;
        }
        if (isset($_POST['lastName'])) {
            $apiPayload['lastName'] = $lastName;
        }

        $newProfileImageFileName = null;

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
                        }
                    } else {
                        $formErrors[] = "System error: Could not prepare storage for profile image.";
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
            if (count($apiPayload) > 1) {
                $updateApiUrl = API_BASE_URL . '/users/update_profile';
                $response = callApi($updateApiUrl, 'POST', $apiPayload);

                if ($response['success']) {
                    $_SESSION['update_message'] = $response['message'] ?? 'Profile updated successfully.';
                    $_SESSION['message_type'] = 'success';
                    if (isset($response['data']['user'])) {
                        if (isset($apiPayload['firstName'])) $_SESSION['user']['firstName'] = $apiPayload['firstName'];
                        if (isset($apiPayload['lastName'])) $_SESSION['user']['lastName'] = $apiPayload['lastName'];
                        if (isset($apiPayload['profileImage'])) $_SESSION['user']['profileImage'] = $apiPayload['profileImage'];
                    }
                } else {
                    $_SESSION['update_message'] = 'Failed to update profile: ' . ($response['message'] ?? 'Unknown API error.');
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
            'newPassword' => $newPassword
        ];

        $passwordApiUrl = API_BASE_URL . '/users/change_password';
        $response = callApi($passwordApiUrl, 'POST', $apiPayload);

        if ($response['success']) {
            $_SESSION['update_message'] = $response['message'] ?? 'Password changed successfully.';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['update_message'] = 'Failed to change password: ' . ($response['message'] ?? 'Unknown API error.');
        }
        header("Location: " . $redirectUrl . '#profileContent');
        exit;

    default:
        if (!empty($action)) {
            $_SESSION['update_message'] = 'Unknown action requested.';
            $_SESSION['message_type'] = 'warning';
        }
        header("Location: " . $redirectUrl);
        exit;
}
