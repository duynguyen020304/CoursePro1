<?php
$secretKey = '0196ce3e-ba28-7b47-8472-beded9ae0b5d';
require_once __DIR__ . '/../model/dto/user_dto.php';
require_once __DIR__ . '/../service/service_user.php';
require_once __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("Content-Type: application/json");

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
     // << RẤT QUAN TRỌNG: Thay bằng một khóa bí mật mạnh và duy nhất của bạn
    $issuedAt   = time();
    $expire     = $issuedAt + (60 * 60 * 24);
    $serverName = "CoursePro1";

    $tokenPayload = [
        'iss' => $serverName,                       // Issuer: Người phát hành token
        'aud' => $serverName,                       // Audience: Đối tượng sử dụng token
        'iat' => $issuedAt,                         // Issued at: Thời điểm token được phát hành
        'nbf' => $issuedAt,                         // Not before: Token chưa hợp lệ trước thời điểm này
        'exp' => $expire,                           // Expiration time: Thời điểm token hết hạn
        'data' => [                                 // Dữ liệu người dùng bạn muốn lưu trong token
            'userID' => $response->data->userID,
            'email'  => $response->data->email,
            'roleID' => $response->data->roleID,
            'firstName' => $response->data->firstName,
            'lastName' => $response->data->lastName
        ]
    ];

    $jwt = JWT::encode($tokenPayload, $secretKey, 'HS256');

    echo json_encode([
        'success' => true,
        'message' => 'Đăng nhập thành công',
        'token' => $jwt, // Trả về token cho client
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