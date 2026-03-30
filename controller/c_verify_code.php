<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
session_start();
require_once '../model/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && isset($_POST['code'])) {
    $email = trim($_POST['email']);
    $code = strtoupper(trim($_POST['code']));
    $db = new Database();
    $emailSafe = addslashes($email);
    $tokenSafe = addslashes($code);

    $reset = $db->fetchRow("SELECT EMAIL, TOKEN, TO_CHAR(CREATED_AT, 'YYYY-MM-DD HH24:MI:SS.FF6') AS CREATED_AT_FORMATTED FROM \"PASSWORD_RESETS\" WHERE email = '{$emailSafe}' AND UPPER(token) = '{$tokenSafe}'");
    if (!$reset) {
        $_SESSION['error'] = 'Mã xác nhận không đúng.';
        header("Location: ../verify-code.php?email=" . urlencode($email));
        exit;
    }

    $created_at = strtotime($reset['CREATED_AT_FORMATTED']);
    $now = time();
    if (($now - $created_at) > 600) {
        $_SESSION['error'] = 'Mã xác nhận đã hết hạn. Vui lòng thực hiện lại.';
        $db->execute("DELETE FROM \"PASSWORD_RESETS\" WHERE email = '{$emailSafe}'");
        header("Location: ../forgot-password.php");
        exit;
    }

    $_SESSION['verified_reset_email'] = $email;
    header("Location: ../reset-password.php?email=" . urlencode($email));
    exit;
} else {
    header('Location: ../forgot-password.php');
    exit;
}