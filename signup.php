<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$session_error_email = $_SESSION['error1'] ?? null;
$session_error_password = $_SESSION['error2'] ?? null;
$session_error_confirm_password = $_SESSION['error_confirm_password'] ?? null;
$session_error_name = $_SESSION['error3'] ?? null;

$submitted_email_value = $_SESSION['email'] ?? '';
$submitted_firstname_value = $_SESSION['firstname'] ?? '';
$submitted_lastname_value = $_SESSION['lastname'] ?? '';

$handled_field_errors_messages = [];
if ($session_error_email) $handled_field_errors_messages[] = $session_error_email;
if ($session_error_password) $handled_field_errors_messages[] = $session_error_password;
if ($session_error_confirm_password) $handled_field_errors_messages[] = $session_error_confirm_password;
if ($session_error_name) $handled_field_errors_messages[] = $session_error_name;

$general_errors_to_display = [];
$all_signup_errors_from_session = $_SESSION['signup_errors'] ?? [];

if (!empty($all_signup_errors_from_session)) {
    foreach ($all_signup_errors_from_session as $error_message) {
        if (!in_array($error_message, $handled_field_errors_messages)) {
            $general_errors_to_display[] = $error_message;
        }
    }
}

unset($_SESSION['error1'], $_SESSION['error2'], $_SESSION['error3'], $_SESSION['error4'], $_SESSION['error_confirm_password']);
unset($_SESSION['signup_errors']);

include('template/header.php');
include('template/head.php');
?>
    <link rel="stylesheet" href="public/css/signup.css">
    <style>
        .error-message {
            color: red;
            font-size: 0.875em;
            margin-top: 5px;
        }
        .general-errors {
            color: red;
            margin-bottom: 15px;
            border: 1px solid red;
            padding: 10px;
            border-radius: 5px;
            background-color: #ffebeb;
        }
    </style>
    <main>
        <div class="form-container">
            <h2 class="form-title">Sign Up</h2>

            <?php if (!empty($general_errors_to_display)): ?>
                <div class="general-errors">
                    <?php foreach ($general_errors_to_display as $err_msg): ?>
                        <p><?= htmlspecialchars($err_msg) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="controller/c_signup.php">
                <div class="form-group">
                    <label for="username">Email Address</label>
                    <input type="text" id="username" name="username" value="<?= htmlspecialchars($submitted_email_value) ?>" required>
                    <?php if ($session_error_email): ?>
                        <p class="error-message"><?= htmlspecialchars($session_error_email) ?></p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    <?php if ($session_error_password): ?>
                        <p class="error-message"><?= htmlspecialchars($session_error_password) ?></p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                    <?php if ($session_error_confirm_password): ?>
                        <p class="error-message"><?= htmlspecialchars($session_error_confirm_password) ?></p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="firstname">First Name</label>
                    <input type="text" id="firstname" name="firstname" value="<?= htmlspecialchars($submitted_firstname_value) ?>" required>
                    <?php if ($session_error_name): ?>
                        <p class="error-message"><?= htmlspecialchars($session_error_name) ?></p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="lastname">Last Name</label>
                    <input type="text" id="lastname" name="lastname" value="<?= htmlspecialchars($submitted_lastname_value) ?>" required>
                </div>

                <button type="submit" class="btn">Sign Up</button>
            </form>
            <p class="message">Already have an account? <a href="signin.php">Sign in</a></p>
        </div>
    </main>

<?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
    <div class="popup-overlay" id="popup" style="display: flex; justify-content: center; align-items: center;">
        <div class="popup">
            <div class="checkmark">&#10004;</div>
            <p>Đăng ký thành công</p>
            <button class="popup-btn" onclick="closePopupAndRedirect()">OK</button>
        </div>
    </div>
    <script>
        function closePopupAndRedirect() {
            const popup = document.getElementById('popup');
            if (popup) {
                popup.style.display = 'none';
            }
            window.location.href = 'signup.php';
        }
    </script>
<?php endif; ?>

<?php include('template/footer.php') ?>