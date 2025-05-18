<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

define('ROOT_APP_PATH', dirname(__DIR__));
define('UPLOADS_BASE_DIR_ABSOLUTE', ROOT_APP_PATH . '/uploads');
define('USER_UPLOADS_SUBDIR_RELATIVE', 'uploads/');

function ensureUploadDirectory(string $absoluteDirectoryPath): bool
{
    if (!is_dir($absoluteDirectoryPath)) {
        if (!mkdir($absoluteDirectoryPath, 0755, true)) {
            error_log("UPLOAD_ERROR_CONTROLLER: Cannot create directory: " . $absoluteDirectoryPath);
            return false;
        }
    }
    if (!is_writable($absoluteDirectoryPath)) {
        error_log("UPLOAD_ERROR_CONTROLLER: Directory not writable: " . $absoluteDirectoryPath);
        return false;
    }
    return true;
}

function callUserApi(string $url, string $requestMethod, array $payload = []): array
{
    $jsonPayload = null;
    if (!empty($payload) && in_array(strtoupper($requestMethod), ['POST', 'PUT', 'DELETE'])) {
        $jsonPayload = json_encode($payload);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['success' => false, 'message' => 'Lỗi mã hóa payload JSON: ' . json_last_error_msg()];
        }
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
        return ['success' => false, 'message' => 'Lỗi kết nối API hoặc API không phản hồi.'];
    }

    $decodedResponse = json_decode($response, true);
    $jsonError = json_last_error();

    $httpStatusCode = 0;
    if (!empty($responseHeaders)) {
        preg_match('{HTTP\/\d\.\d\s+(\d+)\s+}', $responseHeaders[0], $match);
        if (isset($match[1])) {
            $httpStatusCode = intval($match[1]);
        }
    }

    if ($decodedResponse === null && $jsonError !== JSON_ERROR_NONE) {
        return [
            'success' => false,
            'message' => 'Phản hồi API không hợp lệ (không phải JSON). Raw: ' . $response,
            'http_status_code' => $httpStatusCode
        ];
    }

    if (is_array($decodedResponse) && !isset($decodedResponse['success'])) {
        $decodedResponse['success'] = ($httpStatusCode >= 200 && $httpStatusCode < 300);
    }
    if (is_array($decodedResponse) && !isset($decodedResponse['http_status_code'])) {
        $decodedResponse['http_status_code'] = $httpStatusCode;
    }

    return $decodedResponse ?? ['success' => false, 'message' => 'Phản hồi API không thể giải mã.', 'http_status_code' => $httpStatusCode];
}

$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http');
$host = $_SERVER['HTTP_HOST'];
$script_path_for_base = dirname($_SERVER['SCRIPT_NAME']);
$app_root_path_relative_for_base = dirname($script_path_for_base);
if ($app_root_path_relative_for_base === '/' || $app_root_path_relative_for_base === '\\') {
    $app_root_path_relative_for_base = '';
}
$app_root_path_relative_for_base = rtrim($app_root_path_relative_for_base, '/');

define('CONTROLLER_API_BASE_URL', $protocol . '://' . $host . $app_root_path_relative_for_base . '/api');
$userApiUrl = CONTROLLER_API_BASE_URL . '/user_api.php';

$act = $_POST['act'] ?? $_GET['act'] ?? null;
$formErrors = [];
$redirectUrl = '../admin/user-management.php?view=list';

switch ($act) {
    case 'create':
    case 'update':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error_message'] = 'Phương thức không hợp lệ.';
            header('Location: ' . $redirectUrl);
            exit;
        }

        $userID = trim($_POST['userID'] ?? '');
        $firstName = trim($_POST['firstName'] ?? '');
        $lastName = trim($_POST['lastName'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $roleID = trim($_POST['roleID'] ?? '');
        $existingProfileImage = trim($_POST['profileImage'] ?? '');

        if (empty($firstName)) $formErrors[] = "Tên không được để trống.";
        if (empty($lastName)) $formErrors[] = "Họ không được để trống.";
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $formErrors[] = "Email không hợp lệ.";
        if (empty($roleID)) $formErrors[] = "Vai trò không được để trống.";
        if ($act === 'create' && empty($password)) $formErrors[] = "Mật khẩu không được để trống khi tạo mới.";

        $apiPayload = [
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'roleID' => $roleID,
        ];

        if ($act === 'update') {
            if (empty($userID)) {
                $formErrors[] = "UserID không được để trống khi cập nhật.";
            } else {
                $apiPayload['userID'] = $userID;
            }
            if (!empty($password)) {
                $apiPayload['password'] = $password;
            }
        } else {
            $apiPayload['password'] = $password;
        }

        $uploadedImageRelativePath = $existingProfileImage;

        if (isset($_FILES['profileImageFile']) && $_FILES['profileImageFile']['error'] === UPLOAD_ERR_OK) {
            if (empty($userID) && $act === 'update') {
                $formErrors[] = "Không thể tải ảnh lên mà không có User ID khi cập nhật.";
            } else {
                $idForPath = ($act === 'update') ? $userID : str_replace('.', '_', uniqid('temp_user_', true));
                $uploadedFile = $_FILES['profileImageFile'];
                $originalFileName = $uploadedFile['name'];
                $fileTmpName = $uploadedFile['tmp_name'];
                $fileSize = $uploadedFile['size'];
                $fileExtension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                $maxFileSize = 5 * 1024 * 1024;

                if (!in_array($fileExtension, $allowedExtensions, true)) {
                    $formErrors[] = "Định dạng file ảnh không hợp lệ. Cho phép: " . implode(', ', $allowedExtensions);
                }
                if ($fileSize > $maxFileSize) {
                    $formErrors[] = "Kích thước file ảnh quá lớn. Tối đa: " . ($maxFileSize / 1024 / 1024) . "MB.";
                }

                if (empty($formErrors)) {
                    $safeUserIDForPath = preg_replace('/[^a-zA-Z0-9_-]/', '_', (string)$idForPath);
                    $userUploadDirAbsolute = UPLOADS_BASE_DIR_ABSOLUTE . DIRECTORY_SEPARATOR . $safeUserIDForPath;
                    $userUploadDirRelative = USER_UPLOADS_SUBDIR_RELATIVE . '/' . $safeUserIDForPath;

                    if (ensureUploadDirectory($userUploadDirAbsolute)) {
                        $imageNameOnly = str_replace('.', '_', uniqid('avatar_', true)) . "." . $fileExtension;
                        $destinationPathAbsolute = $userUploadDirAbsolute . DIRECTORY_SEPARATOR . $imageNameOnly;
                        $i = 1;
                        while (file_exists($destinationPathAbsolute)) {
                            $imageNameOnly = str_replace('.', '_', uniqid('avatar_', true)) . "_($i)." . $fileExtension;
                            $destinationPathAbsolute = $userUploadDirAbsolute . DIRECTORY_SEPARATOR . $imageNameOnly;
                            $i++;
                        }

                        if (move_uploaded_file($fileTmpName, $destinationPathAbsolute)) {
                            $uploadedImageRelativePath = $userUploadDirRelative . '/' . $imageNameOnly;
                            if ($act === 'update' && !empty($existingProfileImage) && $existingProfileImage !== $uploadedImageRelativePath) {
                                $oldImagePathAbsolute = ROOT_APP_PATH . '/' . $existingProfileImage;
                                if (file_exists($oldImagePathAbsolute) && is_file($oldImagePathAbsolute)) {
                                    @unlink($oldImagePathAbsolute);
                                }
                            }
                        } else {
                            $formErrors[] = "Lỗi hệ thống: Không thể lưu ảnh đại diện.";
                            error_log("UPLOAD_ERROR_CONTROLLER: move_uploaded_file failed from {$fileTmpName} to {$destinationPathAbsolute}");
                        }
                    } else {
                        $formErrors[] = "Lỗi hệ thống: Không thể chuẩn bị thư mục lưu trữ.";
                    }
                }
            }
        } elseif (isset($_FILES['profileImageFile']) && $_FILES['profileImageFile']['error'] !== UPLOAD_ERR_OK && $_FILES['profileImageFile']['error'] !== UPLOAD_ERR_NO_FILE) {
            $formErrors[] = "Lỗi tải lên ảnh đại diện. Mã lỗi: " . $_FILES['profileImageFile']['error'];
        }

        $apiPayload['profileImage'] = $imageNameOnly;

        if (!empty($formErrors)) {
            $_SESSION['error_message'] = implode("<br>", $formErrors);
            $_SESSION['form_data'] = $_POST;
            if ($act === 'update' && !empty($userID)) {
                header('Location: ' . $redirectUrl . '&action=edit&id=' . urlencode($userID));
            } else {
                header('Location: ' . $redirectUrl . '&action=add');
            }
            exit;
        }

        $apiMethod = ($act === 'create') ? 'POST' : 'PUT';
        $apiResponse = callUserApi($userApiUrl, $apiMethod, $apiPayload);

        if (isset($apiResponse['success']) && $apiResponse['success'] === true) {
            $_SESSION['success_message'] = $apiResponse['message'] ?? ($act === 'create' ? 'Thêm người dùng thành công!' : 'Cập nhật người dùng thành công!');
        } else {
            $_SESSION['error_message'] = $apiResponse['message'] ?? 'Thao tác với người dùng thất bại.';
            if (isset($apiResponse['http_status_code']) && $apiResponse['http_status_code'] == 400 && isset($apiResponse['errors'])) {
                $_SESSION['error_message'] .= "<br>" . implode("<br>", $apiResponse['errors']);
            }
            $_SESSION['form_data'] = $_POST;
        }
        header('Location: ' . $redirectUrl);
        exit;

    case 'delete':
        $userIDToDelete = $_POST['userID'] ?? $_GET['userID'] ?? null;
        if (empty($userIDToDelete)) {
            $_SESSION['error_message'] = 'Thiếu UserID để xóa.';
            header('Location: ' . $redirectUrl);
            exit;
        }

        $apiPayload = ['userID' => $userIDToDelete];
        $apiResponse = callUserApi($userApiUrl, 'DELETE', $apiPayload);

        if (isset($apiResponse['success']) && $apiResponse['success'] === true) {
            $_SESSION['success_message'] = $apiResponse['message'] ?? 'Xóa người dùng thành công!';
        } else {
            $_SESSION['error_message'] = $apiResponse['message'] ?? 'Xóa người dùng thất bại.';
        }
        header('Location: ' . $redirectUrl);
        exit;

    default:
        $_SESSION['error_message'] = 'Hành động không xác định.';
        header('Location: ' . $redirectUrl);
        exit;
}