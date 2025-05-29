<?php
session_start();
require_once '../model/database.php';
require_once '../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once '../vendor/phpmailer/phpmailer/src/SMTP.php';
require_once '../vendor/phpmailer/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function randomToken($length = 6)
{
    return strtoupper(bin2hex(random_bytes($length / 2)));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);

    $db = new Database();
    if (!$db->isConnected()) {
        $_SESSION['error'] = 'Không kết nối được database.';
        header('Location: ../forgot-password.php');
        exit;
    }

    $emailSafe = addslashes($email);
    $user = $db->fetchRow("SELECT * FROM Users WHERE Email = '{$emailSafe}'");
    if (!$user) {
        $_SESSION['error'] = 'Email không tồn tại trong hệ thống.';
        header('Location: ../forgot-password.php');
        exit;
    }

    $token = randomToken(6);
    $created_at = date('Y-m-d H:i:s');

    $db->execute("DELETE FROM \"PASSWORD_RESETS\" WHERE email = '{$emailSafe}'");
    $result = $db->execute("INSERT INTO \"PASSWORD_RESETS\" (email, token) VALUES ('{$emailSafe}', '{$token}')");
    if (!$result) {
        $_SESSION['error'] = 'Không lưu được token đặt lại mật khẩu.';
        header('Location: ../forgot-password.php');
        exit;
    }

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'longly777666@gmail.com';
        $mail->Password = 'tcpfibzyyqevbvco';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        $mail->setFrom('longly777666@gmail.com', 'CoursePro - Quên mật khẩu');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Mã xác nhận quên mật khẩu';
        $mail->Body = "<p>Mã xác nhận của bạn là: <b>{$token}</b></p><p>Mã có hiệu lực trong 10 phút.</p>";
        $mail->send();

        $_SESSION['email_reset'] = $email;
        $_SESSION['success'] = 'Mã xác nhận đã được gửi về email. Vui lòng kiểm tra hộp thư.';
        header('Location: ../verify-code.php?email=' . urlencode($email));
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = 'Không gửi được email. Lỗi: ' . $mail->ErrorInfo;
        header('Location: ../forgot-password.php');
        exit;
    }
} else {
    header('Location: ../forgot-password.php');
    exit;
}
