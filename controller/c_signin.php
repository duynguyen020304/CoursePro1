<?php
session_start();
require_once __DIR__ . '/../model/bll/user_bll.php';
require_once __DIR__ . '/../model/dto/user_dto.php';
require_once __DIR__ . '/../service/service_user.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

// Lấy dữ liệu từ form POST
$username = $_POST['username'] ?? null;
$password = $_POST['password'] ?? null;

// Kiểm tra dữ liệu đầu vào
if (!$username || !$password) {
    $_SESSION['error'] = 'Missing username or password';
    header('Location: ../signin.php');
    exit;
}

// Khởi tạo service và xác thực người dùng
$service = new UserService();
$response = $service->authenticate($username, $password);

if ($response->success) {
    $_SESSION['user'] = [
        'userID' => $response->data->userID,
        'email'  => $response->data->email,
        'roleID' => $response->data->roleID,
        'name'   => $response->data->name,
    ];
    header('Location: ../home.php');
    exit;
} else {
    $_SESSION['error'] = $response->message;
    header('Location: ../signin.php?error=Tên đăng nhập hoặc mật khẩu không đúng');
    exit;
}