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
        if (isset($_SESSION['error_message'])) {
            echo '<div class="popup-overlay" id="popup" style="display: flex;">'; // Hiển thị popup
            echo '    <div class="popup popup-error">';
            echo '        <div class="error-icon">&#10006;</div>'; // Dấu X
            echo '        <p>' . htmlspecialchars($_SESSION['error_message']) . '</p>';
            echo '        <button class="popup-btn" onclick="closePopup()">OK</button>';
            echo '    </div>';
            echo '</div>';
            unset($_SESSION['error_message']); // Xóa thông báo lỗi sau khi hiển thị
        }
        ?>

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