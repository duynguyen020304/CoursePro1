<?php
require_once __DIR__ . '/../../Model/DTO/user_dto.php';
require_once __DIR__ . '/../../Service/service_user.php';
require_once __DIR__ . '/../../Model/Config/config.php';

header("Content-Type: application/json");

// Đảm bảo chỉ xử lý POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ']);
    exit;
}

// Use Config for JWT secret key
$secretKey = Config::getJwtSecretKey();

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
    $biography = "NOT_SET";
    if (isset($data['biography'])) {
        $biography = $data['biography'];
    }
    $registerResult = $service->create_user(
        $data['email'],
        $data['password'],
        $data['firstname'],
        $data['lastname'],
        $data['role'],
        $biography,
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
} else if (isset($data['isChangePassword']) && $data['isChangePassword'] === true) {
    if (
        !isset($data['email']) ||
        !isset($data['password'])
    ) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Thiếu thông tin đổi mật khẩu']);
        exit;
    }
    $response = $service->update_user_partial(
        [
            'email' => $data['email'],
            'password' => $data['password']
        ]
    );
    if ($response->success) {
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Đổi mật khẩu thành công']);
    }
    exit;
}

// Trường hợp đăng nhập
$response = $service->authenticate($data['email'], $data['password']);

if ($response->success) {
    $issuedAt   = time();
    $expire     = $issuedAt + (60 * 60 * 24);
    $serverName = "CoursePro1";

    $tokenPayload = [
        'iss' => $serverName,
        'aud' => $serverName,
        'iat' => $issuedAt,
        'nbf' => $issuedAt,
        'exp' => $expire,
        'data' => [
            'userID' => $response->data->userID,
            'email'  => $response->data->email,
            'roleID' => $response->data->roleID,
            'firstName' => $response->data->firstName,
            'lastName' => $response->data->lastName
        ]
    ];

    // Use AuthHelper for token generation
    require_once __DIR__ . '/../../Model/Config/auth_helper.php';
    $jwt = AuthHelper::generateToken($response->data);

    echo json_encode([
        'success' => true,
        'message' => 'Đăng nhập thành công',
        'token' => $jwt,
        'userID' => $response->data->userID,
        'firstName' => $response->data->firstName,
        'lastName' => $response->data->lastName,
        'email' => $response->data->email,
        'roleID' => $response->data->roleID,
        'profileImage' => $response->data->profileImage
    ]);
} else {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => $response->message]);
}