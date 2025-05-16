<?php
// Bắt buộc phải có session_start() ở đầu file để đọc/ghi $_SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Lấy thông báo lỗi và username đã nhập (nếu có) từ session
$login_error = $_SESSION['login_error'] ?? null;
$submitted_username = $_SESSION['submitted_username'] ?? '';

// Xóa thông báo lỗi khỏi session sau khi đã lấy để nó không hiển thị lại
// Username đã nhập sẽ được controller xóa khi đăng nhập thành công
if ($login_error) {
    unset($_SESSION['login_error']);
}

include('template/head.php');
?>
<link href="public/css/signin.css" rel="stylesheet">
<?php include('template/header.php'); ?>
<main>
    <div class="form-container">
        <h2>Sign In</h2>

        <?php // Pop-up hiển thị lỗi sẽ được kích hoạt bằng JavaScript nếu có lỗi ?>

        <form method="POST" action="controller/c_signin.php">
            <div class="form-group">
                <label for="username">Email Address</label>
                <input type="text" id="username" name="username" required value="<?= htmlspecialchars($submitted_username) ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Sign In</button>
        </form>
        <p class="message"><a href="forgot-password.php">Quên mật khẩu?</a></p>
        <p class="message">Don't have an account? <a href="signup.php">Sign up</a></p>
    </div>
</main>

<?php // HTML cho Pop-up lỗi - sẽ được hiển thị bằng JavaScript ?>
<?php if ($login_error): ?>
<div id="errorPopupOverlay" class="error-popup-overlay">
    <div class="error-popup">
        <div class="error-popup-icon">&times;</div> <div class="error-popup-message">
            <p><?= htmlspecialchars($login_error) ?></p>
        </div>
        <button onclick="closeErrorPopup()" class="error-popup-close-btn">Đóng</button>
    </div>
</div>
<?php endif; ?>

<script>
    function closeErrorPopup() {
        var popupOverlay = document.getElementById('errorPopupOverlay');
        if (popupOverlay) {
            popupOverlay.style.display = 'none';
        }
    }

    // Hiển thị pop-up nếu có lỗi được truyền từ PHP
    <?php if ($login_error): ?>
    document.addEventListener('DOMContentLoaded', function() {
        var popupOverlay = document.getElementById('errorPopupOverlay');
        if (popupOverlay) {
            popupOverlay.style.display = 'flex'; // Hiển thị pop-up
        }
    });
    <?php endif; ?>
</script>

<?php include('template/footer.php'); ?>
