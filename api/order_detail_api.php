<?php
// File: api/order_detail_api.php
$secretKey = '0196ce3e-ba28-7b47-8472-beded9ae0b5d';
require_once __DIR__ . '/../service/service_order_detail.php';
require __DIR__ . '/../vendor/autoload.php';

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

$service = new OrderDetailService();
$method  = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // GET /.../order_detail_api.php?orderID=...
        if (!isset($_GET['orderID'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu orderID']);
            exit;
        }
        $resp = $service->get_details_by_order($_GET['orderID']);
        http_response_code($resp->success ? 200 : 500);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message, 'data' => $resp->data]);
        break;

    case 'POST':
        // Thêm mới
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['orderID']) || empty($data['courseID']) || !isset($data['price'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu orderID, courseID hoặc price']);
            exit;
        }
        $resp = $service->add_detail($data['orderID'], $data['courseID'], floatval($data['price']));
        http_response_code($resp->success ? 201 : 500);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message, 'data' => $resp->data]);
        break;

    case 'PUT':
        // Cập nhật
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['orderID']) || empty($data['courseID']) || !isset($data['price'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu orderID, courseID hoặc price']);
            exit;
        }
        $resp = $service->update_detail($data['orderID'], $data['courseID'], floatval($data['price']));
        http_response_code($resp->success ? 200 : 500);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message]);
        break;

    case 'DELETE':
        // Xóa
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['orderID']) || empty($data['courseID'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu orderID hoặc courseID']);
            exit;
        }
        $resp = $service->delete_detail($data['orderID'], $data['courseID']);
        http_response_code($resp->success ? 200 : 500);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Phương thức không hỗ trợ']);
        break;
}
