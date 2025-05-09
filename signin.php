<?php include('template/head.php') ?>
<link href="public/css/signin.css" rel="stylesheet">
<?php include('template/header.php') ?>
<main>
    <div class="form-container">
        <h2>Sign In</h2>
        <form>
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

<?php include('template/footer.php') ?>
