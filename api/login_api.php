<?php
require_once __DIR__ . '/../model/bll/user_bll.php';
require_once __DIR__ . '/../model/dto/user_dto.php';
require_once __DIR__ . '/../service/service_signup.php';


header("Content-Type: application/json");
session_start();

// Đảm bảo chỉ xử lý POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

// Kiểm tra trường bắt buộc
if (!isset($data['email']) || !isset($data['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing email or password']);
    exit;
}

$service = new UserService();

// Trường hợp đăng ký
if (isset($data['isSignup']) && $data['isSignup'] === true) {
    if (!isset($data['firstname']) || !isset($data['lastname'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing first or last name']);
        exit;
    }

    $registerResult = $service->create_user(
        $data['email'],
        $data['password'],
        $data['firstname'],
        $data['lastname']
    );

    if ($registerResult->success) {
        http_response_code(201);
        echo json_encode(['success' => true, 'message' => 'Account created successfully']);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $registerResult->message]);
    }

    exit;
}

// Trường hợp đăng nhập
$response = $service->authenticate($data['email'], $data['password']);

if ($response->success) {
    $_SESSION['user'] = [
        'userID' => $response->data->userID,
        'email'  => $response->data->email,
        'roleID' => $response->data->roleID,
    ];
    echo json_encode(['success' => true, 'message' => 'Login successful']);
} else {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => $response->message]);
}
