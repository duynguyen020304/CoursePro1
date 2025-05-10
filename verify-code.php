<?php include('template/head.php') ?>
<link href="public/css/signin.css" rel="stylesheet">
<?php include('template/header.php') ?>
<main>
    <div class="form-container">
        <h2>Xác nhận mã</h2>
        <form method="POST" action="controller/c_verify_code.php">
            <input type="hidden" name="email" value="<?php echo isset($_GET['email']) ? htmlspecialchars($_GET['email']) : ''; ?>">
            <div class="form-group">
                <label for="code">Mã xác nhận</label>
                <input type="text" id="code" name="code" required>
            </div>
            <button type="submit" class="btn">Xác nhận</button>
        </form>
        <p class="message">
            Bạn chưa nhận được mã? <a href="forgot-password.php">Gửi lại</a>
        </p>
    </div>
</main>
