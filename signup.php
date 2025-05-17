<?php
// QUAN TRỌNG: Khởi tạo session để truy cập các biến session.
// Phải được gọi trước bất kỳ output HTML nào.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Lấy các lỗi cụ thể và email đã nhập từ session (do controller gốc thiết lập)
$session_error_email = $_SESSION['error1'] ?? null;        // Thông báo lỗi cho trường email
$session_error_password = $_SESSION['error2'] ?? null;    // Thông báo lỗi cho trường mật khẩu
$session_error_confirm_password = $_SESSION['error_confirm_password'] ?? null; // Thông báo lỗi cho trường xác nhận mật khẩu
$session_error_name = $_SESSION['error3'] ?? null;        // Thông báo lỗi cho trường họ và tên
// $_SESSION['error4'] là lỗi kết nối API cụ thể, sẽ được hiển thị trong lỗi chung nếu có.

// Lấy giá trị email đã nhập trước đó để điền lại vào form
$submitted_email_value = $_SESSION['email'] ?? '';
$submitted_firstname_value = $_SESSION['firstname'] ?? ''; // Giữ lại giá trị tên
$submitted_lastname_value = $_SESSION['lastname'] ?? '';   // Giữ lại giá trị họ

// Xử lý hiển thị các lỗi chung từ $_SESSION['signup_errors']
// Loại bỏ các thông báo đã được hiển thị dưới dạng lỗi cụ thể cho từng trường
$handled_field_errors_messages = [];
if ($session_error_email) $handled_field_errors_messages[] = $session_error_email;
if ($session_error_password) $handled_field_errors_messages[] = $session_error_password;
if ($session_error_confirm_password) $handled_field_errors_messages[] = $session_error_confirm_password;
if ($session_error_name) $handled_field_errors_messages[] = $session_error_name;

$general_errors_to_display = [];
$all_signup_errors_from_session = $_SESSION['signup_errors'] ?? [];

if (!empty($all_signup_errors_from_session)) {
    foreach ($all_signup_errors_from_session as $error_message) {
        // Nếu thông báo lỗi này chưa được xử lý (chưa hiển thị dưới trường cụ thể)
        // thì thêm vào danh sách lỗi chung để hiển thị
        if (!in_array($error_message, $handled_field_errors_messages)) {
            $general_errors_to_display[] = $error_message;
        }
    }
}

// Xóa các biến session lỗi sau khi đã lấy giá trị để chúng không hiển thị lại ở lần tải trang sau
unset($_SESSION['error1'], $_SESSION['error2'], $_SESSION['error3'], $_SESSION['error4'], $_SESSION['error_confirm_password']);
unset($_SESSION['signup_errors']);
// Các giá trị đã nhập như email, firstname, lastname có thể được giữ lại hoặc xóa tùy theo logic mong muốn
// unset($_SESSION['email'], $_SESSION['firstname'], $_SESSION['lastname']); // Bỏ comment nếu muốn xóa sau mỗi lần submit

// Include header và head (đảm bảo chúng không có output trước session_start())
include('template/header.php');
include('template/head.php');
?>
<link rel="stylesheet" href="public/css/signup.css">
<style>
    /* CSS cho thông báo lỗi dưới trường input */
    .error-message {
        color: red;
        font-size: 0.875em; /* Tương đương 14px nếu font cơ sở là 16px */
        margin-top: 5px;
    }
    /* CSS cho khu vực hiển thị lỗi chung */
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

        <?php // Hiển thị các lỗi chung (ví dụ: lỗi API, hoặc các lỗi không thuộc trường cụ thể) ?>
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
                <?php if ($session_error_name): // Lỗi "Họ và tên không được để trống" sẽ hiển thị ở đây ?>
                    <p class="error-message"><?= htmlspecialchars($session_error_name) ?></p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="lastname">Last Name</label>
                <input type="text" id="lastname" name="lastname" value="<?= htmlspecialchars($submitted_lastname_value) ?>" required>
                <?php // Thông báo lỗi tên đã hiển thị ở trên, không cần lặp lại trừ khi muốn tách riêng ?>
            </div>

            <button type="submit" class="btn">Sign Up</button>
        </form>
        <p class="message">Already have an account? <a href="signin.php">Sign in</a></p>
    </div>
</main>
<?php // Chú ý: Phần hiển thị lỗi form_errors['email'] ở đây có thể không cần thiết nếu đã xử lý ở trên ?>
<?php /* if (!empty($form_errors['email'])): ?>
    <p class="error-message"><?= htmlspecialchars($form_errors['email']) ?></p>
<?php endif; */ ?>

<?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
    <div class="popup-overlay" id="popup" style="display: flex; justify-content: center; align-items: center;"> <?php // Đảm bảo popup hiển thị ?>
        <div class="popup">
            <div class="checkmark">&#10004;</div>
            <p>Đăng ký thành công</p>
            <button class="popup-btn" onclick="closePopupAndRedirect()">OK</button>
        </div>
    </div>
    <script>
        // Hàm đóng popup và chuyển hướng để xóa tham số 'success' khỏi URL
        function closePopupAndRedirect() {
            const popup = document.getElementById('popup');
            if (popup) {
                popup.style.display = 'none';
            }
            // Chuyển hướng về trang signup.php không có tham số success
            window.location.href = 'signup.php';
        }
        // Nếu bạn muốn popup tự động ẩn sau một khoảng thời gian, bạn có thể thêm setTimeout ở đây.
    </script>
<?php endif; ?>

<?php include('template/footer.php') ?>
