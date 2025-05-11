<?php
session_start();
require_once '../model/database.php';

// date_default_timezone_set('Asia/Ho_Chi_Minh'); // Múi giờ VN

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && isset($_POST['code'])) {
    $email = trim($_POST['email']);
    $code = strtoupper(trim($_POST['code'])); // Ép mã thành HOA và bỏ khoảng trắng
    $db = new Database();
    $emailSafe = addslashes($email);
    $tokenSafe = addslashes($code);

    // So khớp mã xác nhận KHÔNG phân biệt hoa thường
    $reset = $db->fetchRow("SELECT * FROM password_resets WHERE email = '{$emailSafe}' AND UPPER(token) = '{$tokenSafe}'");
    if (!$reset) {
        $_SESSION['error'] = 'Mã xác nhận không đúng.';
        header("Location: ../verify-code.php?email=" . urlencode($email));
        exit;
    }
    // Kiểm tra thời gian hiệu lực (10 phút)
    $created_at = strtotime($reset['created_at']);
    $now = time();
    if (($now - $created_at) > 600) { // 600s = 10 phút
        $_SESSION['error'] = 'Mã xác nhận đã hết hạn. Vui lòng thực hiện lại.';
        // Xóa token hết hạn
        $db->execute("DELETE FROM password_resets WHERE email = '{$emailSafe}'");
        header("Location: ../forgot-password.php");
        exit;
    }
    // Đúng mã, đúng hạn -> lưu quyền cho phép reset
    $_SESSION['verified_reset_email'] = $email;
    header("Location: ../reset-password.php?email=" . urlencode($email));
    exit;
} else {
    header('Location: ../forgot-password.php');
    exit;
}
