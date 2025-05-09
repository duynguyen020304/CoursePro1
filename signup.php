<?php include('template/head.php') ?>
<link rel="stylesheet" href="public/css/signup.css">
<?php include('template/header.php') ?>
<main>
    <div class="form-container">
        <h2 class="form-title">Sign Up</h2>
        <form method="POST" action="controller/c_signup.php">
            <div class="form-group">
                <label for="username">Email Address</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="firstname">First Name</label>
                <input type="text" id="firstname" name="firstname" required>
            </div>
            <div class="form-group">
                <label for="lastname">Last Name</label>
                <input type="text" id="lastname" name="lastname" required>
            </div>
            <button type="submit" class="btn">Sign Up</button>
        </form>
        <p class="message">Already have an account? <a href="signin.php">Sign in</a></p>
    </div>
</main>

<?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
    <div class="popup-overlay" id="popup">
        <div class="popup">
            <div class="checkmark">&#10004;</div>
            <p>Đăng ký thành công</p>
            <button class="popup-btn" onclick="closePopup()">OK</button>
        </div>
    </div>
    <script>
        function closePopup() {
            document.getElementById('popup').style.display = 'none';
        }
    </script>
<?php endif; ?>


<?php include('template/footer.php') ?>

