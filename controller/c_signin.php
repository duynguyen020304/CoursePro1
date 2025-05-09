<?php
require_once __DIR__ . '/../model/bll/user_bll.php';
require_once __DIR__ . '/../model/dto/user_dto.php';
require_once __DIR__ . '/../service/service_signin.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

// Nhận dữ liệu từ form
$data = json_decode(file_get_contents("php://input"), true);

// Kiểm tra dữ liệu đầu vào
if (!isset($data['username']) || !isset($data['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing username or password']);
    exit;
}

// Khởi tạo service và xác thực người dùng
$service = new UserService();
$response = $service->authenticate($data['username'], $data['password']);

if ($response->success) {
    $_SESSION['user'] = $response->data; // lưu thông tin người dùng
    echo json_encode(['success' => true, 'message' => 'Đăng nhập thành công']);
} else {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => $response->message]);
}
