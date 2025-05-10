<?php include('template/head.php') ?>
<link href="public/css/signin.css" rel="stylesheet">
<?php include('template/header.php') ?>
<main>
    <div class="form-container">
        <h2>Tìm tài khoản của bạn</h2>
        <?php
            if (!empty($_SESSION['error'])) {
                echo '<div class="alert alert-danger">'.$_SESSION['error'].'</div>';
                unset($_SESSION['error']);
            }
            if (!empty($_SESSION['success'])) {
                echo '<div class="alert alert-success">'.$_SESSION['success'].'</div>';
                unset($_SESSION['success']);
            }
        ?>
        <form method="POST" action="controller/c_forgot_password.php">
            <div class="form-group">
                <label for="email">Nhập email của bạn</label>
                <input type="email" id="email" name="email" required>
            </div>
            <button type="submit" class="btn">Gửi yêu cầu</button>
        </form>
        <p class="message">
            Đã có tài khoản? <a href="signin.php">Đăng nhập</a>
        </p>
    </div>
</main>
