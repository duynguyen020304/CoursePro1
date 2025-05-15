<?php
// // Đảm bảo session được khởi động ở đầu script, trước bất kỳ output nào.
// if (session_status() === PHP_SESSION_NONE) {
//     session_start();
// }

// // Bao gồm file head nếu cần (ví dụ: để có styling chung)
// // include('template/head.php'); // Bỏ comment nếu bạn có file này và nó không output gì trước session_start()

// // Hiển thị thông báo lỗi chung cho người dùng (nếu có)
// if (!empty($_SESSION['error_message'])) {
//     echo '<div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; margin-bottom: 15px; border-radius: 5px;">';
//     echo '<h4>Thông báo lỗi:</h4>';
//     echo '<p style="margin: 5px 0;">' . htmlspecialchars($_SESSION['error_message']) . '</p>';
//     echo '</div>';
//     unset($_SESSION['error_message']); // Xóa thông báo lỗi sau khi hiển thị
// }

// // Hiển thị các thông điệp debug chi tiết (nếu có)
// if (!empty($_SESSION['debug_messages']) && is_array($_SESSION['debug_messages'])) {
//     echo '<div class="alert alert-info" style="background-color: #d1ecf1; color: #0c5460; padding: 10px; border: 1px solid #bee5eb; margin-bottom: 15px; border-radius: 5px;">';
//     echo '<h4>Thông tin Debug:</h4>';
//     // Sử dụng thẻ <pre> để giữ nguyên định dạng và xuống dòng
//     echo '<pre style="white-space: pre-wrap; word-wrap: break-word; max-height: 300px; overflow-y: auto;">';
//     foreach ($_SESSION['debug_messages'] as $message) {
//         // Sử dụng htmlspecialchars để tránh XSS nếu thông điệp debug chứa HTML/JS
//         echo htmlspecialchars($message) . "\n";
//     }
//     echo '</pre>';
//     echo '</div>';
//     // Tùy chọn: Xóa các thông điệp debug sau khi hiển thị nếu bạn chỉ muốn xem chúng một lần
//     // unset($_SESSION['debug_messages']);
// }

// /*
// // Các phần code khác bạn có thể muốn giữ lại hoặc điều chỉnh:

// // Ví dụ: Hiển thị email từ session (nếu được lưu trữ)
// if (isset($_SESSION['user']['email'])) {
//     echo '<p>Email người dùng hiện tại: ' . htmlspecialchars($_SESSION['user']['email']) . '</p>';
// }

// // Hiển thị payload đã gửi gần nhất (nếu được lưu trữ và cần thiết cho debug)
// if (!empty($_SESSION['payload_debug']) && is_array($_SESSION['payload_debug'])) {
//     echo '<div class="alert alert-warning" style="background-color: #fff3cd; color: #856404; padding: 10px; border: 1px solid #ffeeba; margin-bottom: 15px; border-radius: 5px;">';
//     echo '<h4>Payload đã gửi gần nhất (Debug):</h4>';
//     echo '<pre style="white-space: pre-wrap; word-wrap: break-word;">';
//     $displayPayload = $_SESSION['payload_debug'];
//     // Cẩn thận không hiển thị thông tin nhạy cảm như mật khẩu ở dạng rõ
//     if (isset($displayPayload['password'])) {
//         $displayPayload['password'] = '(ẩn vì lý do bảo mật)';
//     }
//     echo htmlspecialchars(print_r($displayPayload, true));
//     echo '</pre>';
//     echo '</div>';
//     // Tùy chọn: Xóa payload debug sau khi hiển thị
//     // unset($_SESSION['payload_debug']);
// }
// */
?>


<?php
session_start(); // Bắt buộc phải có để đọc $_SESSION
include('template/head.php');
?>
<link href="public/css/signin.css" rel="stylesheet">
<?php include('template/header.php'); ?>
<main>
    <div class="form-container">
        <h2>Sign In</h2>

        <?php
        // Hiển thị thông báo lỗi từ session nếu có
//        if (isset($_SESSION['error_message'])) {
//            echo '<div class="popup-overlay" id="popup" style="display: flex;">'; // Hiển thị popup
//            echo '    <div class="popup popup-error">';
//            echo '        <div class="error-icon">&#10006;</div>'; // Dấu X
//            echo '        <p>' . htmlspecialchars($_SESSION['error_message']) . '</p>';
//            echo '        <button class="popup-btn" onclick="closePopup()">OK</button>';
//            echo '    </div>';
//            echo '</div>';
//            unset($_SESSION['error_message']); // Xóa thông báo lỗi sau khi hiển thị
//        }
//        ?>

        <form method="POST" action="controller/c_signin.php">
            <div class="form-group">
                <label for="username">Email Address</label>
                <input type="text" id="username" name="username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"> </div>
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

<script>
    function closePopup() {
        var popup = document.getElementById('popup');
        if (popup) {
            popup.style.display = 'none';
        }
    }

    // Nếu popup được hiển thị bởi PHP, đảm bảo nó có thể được đóng
    document.addEventListener('DOMContentLoaded', (event) => {
        var popup = document.getElementById('popup');
   
    });
</script>

<?php include('template/footer.php'); ?>