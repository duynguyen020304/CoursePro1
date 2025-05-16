<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http');
$host = $_SERVER['HTTP_HOST'];
$script_path = $_SERVER['SCRIPT_NAME'];

$path_parts = explode('/', dirname($script_path));
if (count($path_parts) > 1 && $path_parts[1] !== '') {
    $app_root_path_relative = implode('/', array_slice($path_parts, 0, count($path_parts)));
    if ($app_root_path_relative === '/' || $app_root_path_relative === '\\') $app_root_path_relative = '';
} else {
    $app_root_path_relative = '';
}
$app_root_path_relative = rtrim($app_root_path_relative, '/');

define('APP_BASE_URL', $protocol . '://' . $host . $app_root_path_relative);
const CONTROLLER_FILE_PATH = '/controller/c_edit_profile.php';

if (!isset($user)) {
    $user = isset($_SESSION['user']) ? (object)$_SESSION['user'] : null;

    if (!$user) {
        $user = (object) [
            'userID' => $_SESSION['user']['userID'] ?? '123',
            'firstName' => $_SESSION['user']['firstName'] ?? 'John',
            'lastName' => $_SESSION['user']['lastName'] ?? 'Doe',
            'email' => $_SESSION['user']['email'] ?? 'john.doe@example.com',
            'profileImage' => $_SESSION['user']['profileImage'] ?? null
        ];
    }
}

$current_page = basename($_SERVER['PHP_SELF']);

$updateMessage = $_SESSION['update_message'] ?? null;
$messageType = $_SESSION['message_type'] ?? 'info';
unset($_SESSION['update_message'], $_SESSION['message_type']);

define('USER_UPLOADS_WEB_PATH', '/uploads/');
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Chỉnh sửa Hồ sơ - Ecourse</title>
    <link href="public/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="public/css/edit-profile.css" rel="stylesheet" />
    <style>
        .profile-image-current {
            max-width: 150px;
            max-height: 150px;
            border-radius: 8px;
            object-fit: cover;
            border: 1px solid #dee2e6;
        }

        .profile-image-sm {
            max-width: 100px;
            max-height: 100px;
            border-radius: 6px;
            object-fit: cover;
        }

        .profile-nav .nav-link.active {
            background-color: #0d6efd;
            color: white !important;
        }

        .profile-nav .nav-link {
            color: #0d6efd;
        }
    </style>
</head>

<body>
    <?php include('template/user_sidebar.php'); ?>
    <div class="main-content">
        <header class="profile-header">
            <div class="container-xl px-0 px-lg-3">
                <h2 class="mb-3">Tài khoản của tôi</h2>
                <ul class="nav nav-pills profile-nav">
                    <li class="nav-item">
                        <a class="nav-link active" id="profile-tab-link" data-bs-toggle="tab" href="#profileAndSecurityContent" role="tab" aria-controls="profileAndSecurityContent" aria-selected="true"><i class="bi bi-person-badge me-1"></i> Hồ sơ & Bảo mật</a>
                    </li>
                </ul>
            </div>
        </header>
        <div class="profile-content-wrapper container-xl px-0 px-lg-3">
            <?php if ($updateMessage): ?>
                <div class="alert alert-<?= htmlspecialchars($messageType) ?> alert-dismissible fade show my-3" role="alert">
                    <?= nl2br(htmlspecialchars($updateMessage)) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <div class="tab-content py-3" id="profileTabContent">
                <div class="tab-pane fade show active" id="profileAndSecurityContent" role="tabpanel" aria-labelledby="profile-tab-link">
                    <section class="form-section">
                        <h4 class="mb-4">Thông tin cá nhân</h4>
                        <form action="controller/c_edit_profile.php" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="update_user_profile" />
                            <input type="hidden" name="userID" value="<?= htmlspecialchars($user->userID ?? '') ?>" />
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="firstName" class="form-label">Tên</label>
                                    <input type="text" class="form-control" id="firstName" name="firstName" value="<?= htmlspecialchars($user->firstName ?? '') ?>" />
                                </div>
                                <div class="col-md-6">
                                    <label for="lastName" class="form-label">Họ</label>
                                    <input type="text" class="form-control" id="lastName" name="lastName" value="<?= htmlspecialchars($user->lastName ?? '') ?>" />
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ảnh đại diện hiện tại</label>
                                <div>
                                    <?php
                                    $profileImagePath = 'public/img/avatar-user.png';
                                    if (!empty($user->profileImage) && !empty($user->userID)) {
                                        $profileImagePath = APP_BASE_URL . CONTROLLER_FILE_PATH . '?action=load_img_profile&user_id=' . urlencode($user->userID) . '&image=' . urlencode($user->profileImage);
                                    }
                                    ?>
                                    <img src="<?php echo htmlspecialchars($profileImagePath) ?>" alt="Ảnh đại diện hiện tại" class="profile-image-current mb-2" id="currentProfileImage" onerror="this.onerror=null; this.src='public/img/avatar-user.png';" />
                                </div>
                                <label for="profileImageFile" class="form-label">Tải ảnh mới (tùy chọn)</label>
                                <input class="form-control" type="file" id="profileImageFile" name="profileImageFile" accept="image/jpeg, image/png, image/gif, image/webp" />
                                <small class="form-text text-muted">Để có kết quả tốt nhất, hãy tải lên ảnh vuông. Tối đa 5MB. Định dạng: JPG, PNG, GIF, WEBP.</small>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Lưu thay đổi thông tin</button>
                        </form>
                    </section>
                    <hr class="my-4" />
                    <section class="form-section">
                        <h4 class="mb-4">Cài đặt tài khoản & Bảo mật</h4>
                        <div class="mb-3">
                            <label for="email" class="form-label">Địa chỉ email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user->email ?? '') ?>" readonly disabled />
                            <small class="form-text text-muted">Email không thể thay đổi qua giao diện này.</small>
                        </div>
                        <hr class="my-4" />
                        <h5 class="mb-3">Đổi mật khẩu</h5>
                        <form action="controller/c_edit_profile.php" method="post">
                            <input type="hidden" name="action" value="save_password" />
                            <input type="hidden" name="userID" value="<?= htmlspecialchars($user->userID ?? '') ?>" />
                            <div class="mb-3">
                                <label for="currentPassword" class="form-label">Mật khẩu hiện tại</label>
                                <input type="password" class="form-control" id="currentPassword" name="currentPassword" required />
                            </div>
                            <div class="mb-3">
                                <label for="newPassword" class="form-label">Mật khẩu mới</label>
                                <input type="password" class="form-control" id="newPassword" name="newPassword" required />
                            </div>
                            <div class="mb-3">
                                <label for="confirmPassword" class="form-label">Xác nhận mật khẩu mới</label>
                                <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required />
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-key-fill me-1"></i> Đổi mật khẩu</button>
                        </form>
                    </section>
                </div>
            </div>
        </div>
        <footer class="text-center text-muted mt-4 py-3 border-top">
            <small>© <?= date('Y') ?> Ecourse Platform. All Rights Reserved.</small>
        </footer>
    </div>
    <script src="public/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const profileImageFile = document.getElementById('profileImageFile');
            const currentProfileImage = document.getElementById('currentProfileImage');

            if (profileImageFile && currentProfileImage) {
                profileImageFile.addEventListener('change', function(event) {
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            currentProfileImage.src = e.target.result;
                        }
                        reader.readAsDataURL(file);
                    }
                });
            }

            if (window.location.hash) {
                const elementToScroll = document.querySelector(window.location.hash);
                if (elementToScroll) {}
            }
        });
    </script>
</body>

</html>