<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$login_error = $_SESSION['login_error'] ?? null;
$submitted_username = $_SESSION['submitted_username'] ?? '';

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

        <?php if ($login_error): ?>
        document.addEventListener('DOMContentLoaded', function() {
            var popupOverlay = document.getElementById('errorPopupOverlay');
            if (popupOverlay) {
                popupOverlay.style.display = 'flex';
            }
        });
        <?php endif; ?>
    </script>

<?php include('template/footer.php'); ?>