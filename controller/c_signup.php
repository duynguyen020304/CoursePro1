<?php
session_start();

// 1. Đảm bảo include đúng đường dẫn đến service xử lý signup
require_once __DIR__ . '/../service/service_user.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Nếu không phải POST thì chuyển về form đăng ký
    header('Location: ../signup.php');
    exit();
}

// 2. Lấy dữ liệu từ form
$email     = trim($_POST['username']   ?? '');
$password  = trim($_POST['password']   ?? '');
$firstName = trim($_POST['firstname']  ?? '');
$lastName  = trim($_POST['lastname']   ?? '');

// 3. Validate cơ bản (có thể mở rộng thêm)
$errors = [];
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Email không hợp lệ.';
}
if (strlen($password) < 6) {
    $errors[] = 'Mật khẩu phải ít nhất 6 ký tự.';
}
if ($firstName === '' || $lastName === '') {
    $errors[] = 'Họ và tên không được để trống.';
}

if (!empty($errors)) {
    $_SESSION['signup_errors'] = $errors;
    header('Location: ../signup.php');
    exit();
}

// 4. Tạo service và gọi hàm signup
$service = new UserService();
// Mặc định cho role ID = 1 (ví dụ: học viên)
$response = $service->create_user($email, $password, $firstName, $lastName, 'student');

if ($response->success) {
    // Đăng ký thành công
    header('Location: ../signup.php?success=1');
    exit();
} else {
    // Đăng ký thất bại, đọc thông báo lỗi từ service
    $_SESSION['signup_errors'] = [ $response->message ];
    header('Location: ../signup.php');
    exit();
}
