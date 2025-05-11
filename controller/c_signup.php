<?php
session_start();

$_SESSION['debug_messages'] = [];
$_SESSION['debug_messages'][] = "Script execution started.";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['debug_messages'][] = "Invalid request method: " . $_SERVER['REQUEST_METHOD'] . ". Redirecting to signup page.";
    header('Location: ../signup.php');
    exit;
}
$_SESSION['debug_messages'][] = "Request method is POST.";

$email     = trim($_POST['username'] ?? '');
$password  = trim($_POST['password'] ?? '');
$firstName = trim($_POST['firstname'] ?? '');
$lastName  = trim($_POST['lastname'] ?? '');
$_SESSION["email"] = $email;

$_SESSION['debug_messages'][] = "Raw POST data: username='{$_POST['username']}', password='(hidden)', firstname='{$_POST['firstname']}', lastname='{$_POST['lastname']}'.";
$_SESSION['debug_messages'][] = "Sanitized data: email='{$email}', password='(hidden)', firstName='{$firstName}', lastName='{$lastName}'.";

$errors = [];
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Email không hợp lệ.';
    $_SESSION['error1'] = 'Email không hợp lệ.';
    $_SESSION['debug_messages'][] = "Validation error: Invalid email format for '{$email}'.";
}
if (strlen($password) < 6) {
    $errors[] = 'Mật khẩu phải ít nhất 6 ký tự.';
    $_SESSION['error2'] = 'Mật khẩu phải ít nhất 6 ký tự.';
    $_SESSION['debug_messages'][] = "Validation error: Password too short (length: " . strlen($password) . ").";
}
if ($firstName === '' || $lastName === '') {
    $errors[] = 'Họ và tên không được để trống.';
    $_SESSION['error3'] = 'Họ và tên không được để trống.';
    $_SESSION['debug_messages'][] = "Validation error: First name or last name is empty.";
}

if (!empty($errors)) {
    $_SESSION['signup_errors'] = $errors;
    $_SESSION['debug_messages'][] = "Validation errors found: " . implode(", ", $errors) . ". Redirecting to signup page.";
    header('Location: ../signup.php');
    exit;
}
$_SESSION['debug_messages'][] = "Basic validation passed.";

$payload = [
    'email'     => $email,
    'password'  => $password,
    'firstname' => $firstName,
    'lastname'  => $lastName,
    'role'      => 'student',
    'isSignup'  => true,
];

$_SESSION['debug_messages'][] = "Payload prepared: " . json_encode($payload);

$apiUrl = 'http://localhost/CoursePro1/api/login_api.php';
$_SESSION['debug_messages'][] = "API URL: " . $apiUrl;

$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_TIMEOUT        => 10,
]);

$_SESSION['debug_messages'][] = "cURL initialized. Executing request to API.";
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$_SESSION['debug_messages'][] = "cURL request executed. HTTP Code: " . $httpCode;
$_SESSION['debug_messages'][] = "Raw API Response: " . $response;

if (curl_errno($ch)) {
    $curlError = curl_error($ch);
    $errors[] = 'Lỗi kết nối API: ' . $curlError;
    $_SESSION['error4'] = 'Lỗi kết nối API: ' . $curlError;
    $_SESSION['debug_messages'][] = "cURL error: " . $curlError;
    curl_close($ch);
    $_SESSION['signup_errors'] = $errors;
    $_SESSION['debug_messages'][] = "Redirecting to signup page due to cURL error.";
    header('Location: ../signup.php');
    exit;
}
curl_close($ch);
$_SESSION['debug_messages'][] = "cURL connection closed.";

$result = json_decode($response, true);
$_SESSION['debug_messages'][] = "API response JSON decoded. Result: " . var_export($result, true);

if ($result === null && json_last_error() !== JSON_ERROR_NONE) {
    $_SESSION['debug_messages'][] = "Error decoding JSON response: " . json_last_error_msg();
    $errors[] = 'Lỗi xử lý phản hồi từ server.';
    $_SESSION['signup_errors'] = $errors;
    header('Location: ../signup.php');
    exit;
}

if (($httpCode === 201 || ($httpCode === 200 && isset($result['success']) && $result['success'] === true)) && empty($errors)) {
    $_SESSION['debug_messages'][] = "Registration successful. HTTP Code: {$httpCode}. API Result: " . json_encode($result) . ". Redirecting to signup page with success=1.";
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
$_SESSION['debug_messages'][] = "Registration failed. HTTP Code: {$httpCode}. API Message: '{$errorMsg}'. API Result: " . json_encode($result) . ". Redirecting to signup page.";
header('Location: ../signup.php');
exit;
