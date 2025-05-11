<?php
// // Ensure session is started at the very beginning of your script, before any output.
// if (session_status() === PHP_SESSION_NONE) {
//     session_start();
// }



// // Display general signup errors if any
// if (!empty($_SESSION['signup_errors'])) {
//     echo '<div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; margin-bottom: 15px; border-radius: 5px;">';
//     echo '<h4>Signup Errors:</h4>';
//     foreach ($_SESSION['signup_errors'] as $err) {
//         echo '<p style="margin: 5px 0;">' . htmlspecialchars($err) . '</p>';
//     }
//     echo '</div>';
//     unset($_SESSION['signup_errors']); // Clear after displaying
// }

// // Display detailed debug messages
// if (!empty($_SESSION['debug_messages']) && is_array($_SESSION['debug_messages'])) {
//     echo '<div class="alert alert-info" style="background-color: #d1ecf1; color: #0c5460; padding: 10px; border: 1px solid #bee5eb; margin-bottom: 15px; border-radius: 5px;">';
//     echo '<h4>Debug Information:</h4>';
//     echo '<pre style="white-space: pre-wrap; word-wrap: break-word;">'; // pre for formatting, with wrap
//     foreach ($_SESSION['debug_messages'] as $message) {
//         echo htmlspecialchars($message) . "\n";
//     }
//     echo '</pre>';
//     echo '</div>';
//     // unset($_SESSION['debug_messages']); // Optionally clear debug messages after displaying
// }

// // Display other specific session messages you had (if still needed)
// // It's generally better to consolidate errors/messages into the arrays above

// // Example of how you were trying to display other messages:
// // if (isset($_SESSION['email'])) {
// //     echo '<p>Current Email in Session: ' . htmlspecialchars($_SESSION['email']) . '</p>';
// // }
// // if (isset($_SESSION['m1'])) {
// //     echo '<p>Message m1: ' . htmlspecialchars($_SESSION['m1']) . '</p>';
// // }

// // Display payload if it exists (useful for debugging form submissions)
// if (!empty($_SESSION['payload']) && is_array($_SESSION['payload'])) {
//     echo '<div class="alert alert-warning" style="background-color: #fff3cd; color: #856404; padding: 10px; border: 1px solid #ffeeba; margin-bottom: 15px; border-radius: 5px;">';
//     echo '<h4>Last Submitted Payload:</h4>';
//     echo '<pre style="white-space: pre-wrap; word-wrap: break-word;">';
//     // Be careful about printing sensitive data like passwords from the payload
//     $displayPayload = $_SESSION['payload'];
//     if (isset($displayPayload['password'])) {
//         $displayPayload['password'] = '(hidden for display)';
//     }
//     echo htmlspecialchars(print_r($displayPayload, true));
//     echo '</pre>';
//     echo '</div>';
//     // unset($_SESSION['payload']); // Optionally clear payload after displaying
// }
?>

<?php
include('template/header.php');
include('template/head.php'); // Assuming this file doesn't output anything before session_start()
?>
<link rel="stylesheet" href="public/css/signup.css">
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
<?php if (!empty($_GET['error'])): ?>
    <p style="color:red;"><?= htmlspecialchars($_GET['error']) ?></p>
<?php endif; ?>
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