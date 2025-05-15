<?php
$secretKey = '0196ce3e-ba28-7b47-8472-beded9ae0b5d';
require_once __DIR__ . '/../service/service_role.php';
require __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Content-Type: application/json');
$authHeader = apache_request_headers();
$token = null;

if (isset($authHeader['Authorization'])) {
    if (preg_match('/Bearer\s(\S+)/', $authHeader['Authorization'], $matches)) {
        $token = $matches[1];
    }
}

if (!$token) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy token xác thực.']);
    exit;
}

try {
    $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
} catch (Firebase\JWT\ExpiredException $e) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Token đã hết hạn.']);
    exit;
} catch (Firebase\JWT\SignatureInvalidException $e) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Chữ ký token không hợp lệ.']);
    exit;
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Token không hợp lệ hoặc có lỗi xảy ra: ' . $e->getMessage()]);
    exit;
}

$service = new RoleService();
$method  = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['roleID'])) {
            $resp = $service->get_role($_GET['roleID']);
            http_response_code($resp->success ? 200 : 404);
            echo json_encode(['success' => $resp->success, 'message' => $resp->message, 'data' => $resp->data]);
        } else {
            $resp = $service->get_all_roles();
            http_response_code($resp->success ? 200 : 500);
            echo json_encode(['success' => $resp->success, 'message' => $resp->message, 'data' => $resp->data]);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['roleID']) || empty($data['roleName'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu roleID hoặc roleName']);
            exit;
        }
        $resp = $service->create_role($data['roleID'], $data['roleName']);
        http_response_code($resp->success ? 201 : 500);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message, 'data' => $resp->data]);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['roleID']) || empty($data['roleName'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu roleID hoặc roleName']);
            exit;
        }
        $resp = $service->update_role($data['roleID'], $data['roleName']);
        http_response_code($resp->success ? 200 : 500);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message]);
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['roleID'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu roleID']);
            exit;
        }
        $resp = $service->delete_role($data['roleID']);
        http_response_code($resp->success ? 200 : 404);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ']);
}
