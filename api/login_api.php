<?php
require_once __DIR__ . '/../model/dto/user_dto.php';
require_once __DIR__ . '/../service/service_user.php';

header("Content-Type: application/json");
session_start();

// Đảm bảo chỉ xử lý POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ']);
    exit;
}
$service = new UserService();

$data = json_decode(file_get_contents("php://input"), true);

// Kiểm tra trường bắt buộc
if (!isset($data['email']) || !isset($data['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Thiếu email hoặc mật khẩu']);
    exit;
}


// Trường hợp đăng ký
if (isset($data['isSignup']) && $data['isSignup'] === true) {
    if (
        !isset($data['email']) ||
        !isset($data['password']) ||
        !isset($data['firstname']) ||
        !isset($data['lastname']) ||
        !isset($data['role'])
    ) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Thiếu thông tin đăng ký']);
        exit;
    }
    if (!isset($data['profileImage'])) {
        $data['profileImage'] = null;
    }
    // echo "đang ở phía trước create user";
    $registerResult = $service->create_user(
        $data['email'],
        $data['password'],
        $data['firstname'],
        $data['lastname'],
        $data['role'],
        $data['profileImage']
    );

    if ($registerResult->success) {
        http_response_code(201);
        echo json_encode(['success' => true, 'message' => 'Tạo tài khoản thành công']);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $registerResult->message]);
    }

    exit;
}

// Trường hợp đăng nhập
$response = $service->authenticate($data['email'], $data['password']);

if ($response->success) {
    // $_SESSION['user'] = [
    //     'userID' => $response->data->userID,
    //     'email'  => $response->data->email,
    //     'roleID' => $response->data->roleID,
    //     'name'   => $response->data->name,
    // ];
    echo json_encode(['success' => true, 'message' => 'Đăng nhập thành công', 'userID' => $response->data->userID, 'firstName' => $response->data->firstName, 'lastName' => $response->data->lastName, 'email' => $response->data->email, 'roleID' => $response->data->roleID, 'profileImage' => $response->data->profileImage]);
} else {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => $response->message]);
}
