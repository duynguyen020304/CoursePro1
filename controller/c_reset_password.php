<?php
session_start();
require_once '../model/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $email = $_SESSION['verified_reset_email'] ?? null;
    $errors = [];

    // Kiểm tra xác thực
    if (!$email) {
        $_SESSION['error'] = 'Bạn không có quyền thực hiện thao tác này.';
        header('Location: ../forgot-password.php');
        exit;
    }
    // Kiểm tra điều kiện password
    if (strlen($password) < 6) {
        $errors[] = 'Mật khẩu phải ít nhất 6 ký tự.';
    }
    if ($password !== $confirm_password) {
        $errors[] = 'Mật khẩu xác nhận không khớp.';
    }
    if (!empty($errors)) {
        $_SESSION['error'] = implode('<br>', $errors);
        header('Location: ../reset-password.php');
        exit;
    }

    // Hash mật khẩu và cập nhật vào users
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $db = new Database();
    $emailSafe = addslashes($email);
    $result = $db->execute("UPDATE Users SET Password = '{$hash}' WHERE Email = '{$emailSafe}'");
    if (!$result) {
        $_SESSION['error'] = 'Không thể cập nhật mật khẩu. Vui lòng thử lại.';
        header('Location: ../reset-password.php');
        exit;
    }
    // Xóa token reset
    $db->execute("DELETE FROM password_resets WHERE email = '{$emailSafe}'");
    unset($_SESSION['verified_reset_email']);

    $_SESSION['success'] = 'Đặt lại mật khẩu thành công! Bạn có thể đăng nhập ngay.';
    header('Location: ../signin.php');
    exit;
} else {
    header('Location: ../forgot-password.php');
    exit;
}
