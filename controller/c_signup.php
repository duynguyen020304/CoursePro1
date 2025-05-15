<?php
session_start();

// Debug messages initialization removed

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Debug message removed
    header('Location: ../signup.php');
    exit;
}
// Debug message removed

$email     = trim($_POST['username'] ?? '');
$password  = trim($_POST['password'] ?? '');
$firstName = trim($_POST['firstname'] ?? '');
$lastName  = trim($_POST['lastname'] ?? '');
$_SESSION["email"] = $email;

// Debug messages removed

$errors = [];
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Email không hợp lệ.';
    $_SESSION['error1'] = 'Email không hợp lệ.';
    // Debug message removed
}
if (strlen($password) < 6) {
    $errors[] = 'Mật khẩu phải ít nhất 6 ký tự.';
    $_SESSION['error2'] = 'Mật khẩu phải ít nhất 6 ký tự.';
    // Debug message removed
}
if ($firstName === '' || $lastName === '') {
    $errors[] = 'Họ và tên không được để trống.';
    $_SESSION['error3'] = 'Họ và tên không được để trống.';
    // Debug message removed
}

if (!empty($errors)) {
    $_SESSION['signup_errors'] = $errors;
    // Debug message removed
    header('Location: ../signup.php');
    exit;
}
// Debug message removed

$payload = [
    'email'     => $email,
    'password'  => $password,
    'firstname' => $firstName,
    'lastname'  => $lastName,
    'role'      => 'student',
    'isSignup'  => true,
];

// Debug message removed

$apiUrl = 'http://localhost/CoursePro1/api/login_api.php';
// Debug message removed

$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_TIMEOUT        => 10,
]);

// Debug message removed
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
// Debug messages removed

if (curl_errno($ch)) {
    $curlError = curl_error($ch);
    $errors[] = 'Lỗi kết nối API: ' . $curlError;
    $_SESSION['error4'] = 'Lỗi kết nối API: ' . $curlError;
    // Debug message removed
    curl_close($ch);
    $_SESSION['signup_errors'] = $errors;
    // Debug message removed
    header('Location: ../signup.php');
    exit;
}
curl_close($ch);
// Debug message removed

$result = json_decode($response, true);
// Debug message removed

if ($result === null && json_last_error() !== JSON_ERROR_NONE) {
    // Debug message removed
    $errors[] = 'Lỗi xử lý phản hồi từ server.';
    $_SESSION['signup_errors'] = $errors;
    header('Location: ../signup.php');
    exit;
}

if (($httpCode === 201 || ($httpCode === 200 && isset($result['success']) && $result['success'] === true)) && empty($errors)) {
    // Debug message removed
    unset($_SESSION['signup_errors']);
    unset($_SESSION['payload']);
    unset($_SESSION['email']);
    header('Location: ../signup.php?success=1');
    exit;
}

$errorMsg = 'Đăng ký không thành công. Vui lòng thử lại.';
if (isset($result['message']) && is_string($result['message'])) {
    $errorMsg = $result['message'];
} elseif (isset($result['error']) && is_string($result['error'])) {
    $errorMsg = $result['error'];
}

$_SESSION['signup_errors'] = [$errorMsg];
// Debug message removed
header('Location: ../signup.php');
exit;