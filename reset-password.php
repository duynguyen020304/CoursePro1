<?php

?>
<?php include('template/head.php') ?>
<link href="public/css/signin.css" rel="stylesheet">
<?php include('template/header.php') ?>
<main>
    <div class="form-container">
        <h2>Đặt lại mật khẩu</h2>
        <form method="POST" action="controller/c_reset_password.php">
            <input type="hidden" name="token" value="<?php echo isset($_GET['token']) ? htmlspecialchars($_GET['token']) : ''; ?>">
            <div class="form-group">
                <label for="password">Mật khẩu mới</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Xác nhận mật khẩu mới</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn">Xác nhận</button>
        </form>
    </div>
</main>
