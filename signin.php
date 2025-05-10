<?php include('template/head.php') ?>
<link href="public/css/signin.css" rel="stylesheet">
<?php include('template/header.php') ?>
<main>
    <div class="form-container">
        <h2>Sign In</h2>
        <form method="POST" action="controller/c_signin.php">
            <div class="form-group">
                <label for="username">Email Address</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Sign In</button>
        </form>
        <p class="message">Don't have an account? <a href="signup.php">Sign up</a></p>
    </div>
</main>
<?php if(!empty($_GET['error'])): ?>
    <div class="popup-overlay" id="popup">
        <div class="popup popup-error">
            <div class="error-icon">&#10006;</div> <!-- Dấu X -->
            <p><?= htmlspecialchars($_GET['error']) ?></p>
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
