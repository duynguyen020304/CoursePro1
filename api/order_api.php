<?php
$secretKey = '0196ce3e-ba28-7b47-8472-beded9ae0b5d';
require_once __DIR__ . '/../service/service_order.php';
require __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Content-Type: application/json');
$authHeader = apache_request_headers();
$token = null;

if ($authHeader['Authorization']) {
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

$service = new OrderService();
$method  = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // GET /order_api.php?orderID=... or /order_api.php?userID=...
        if (isset($_GET['orderID'])) {
            $resp = $service->get_order($_GET['orderID']);
            http_response_code($resp->success ? 200 : 404);
            echo json_encode(['success' => $resp->success, 'message' => $resp->message, 'data' => $resp->data]);
        } elseif (isset($_GET['userID'])) {
            $resp = $service->get_orders_by_user($_GET['userID']);
            http_response_code($resp->success ? 200 : 500);
            echo json_encode(['success' => $resp->success, 'message' => $resp->message, 'data' => $resp->data]);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu orderID hoặc userID']);
        }
        break;

    case 'POST':
        // Tạo order
        $data = json_decode(file_get_contents('php://input'), true);

        // Kiểm tra các trường bắt buộc
        if (empty($data['userID']) || empty($data['orderDate']) || !isset($data['totalAmount'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu dữ liệu: userID, orderDate, totalAmount']);
            exit;
        }

        // Tạo orderID tự động
        $orderID = uniqid('order_', true);

        // Kiểm tra định dạng orderDate
        try {
            $dt = new DateTime($data['orderDate']);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Định dạng orderDate không hợp lệ']);
            exit;
        }

        // Tạo đơn hàng
        $resp = $service->create_order(
            $orderID,        // Sử dụng orderID tự động tạo
            $data['userID'],
            $dt,
            floatval($data['totalAmount'])
        );

        // Phản hồi kết quả
        http_response_code($resp->success ? 201 : 500);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message, 'data' => $resp->data]);
        break;

    case 'PUT':
        // Update order
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['orderID']) || empty($data['userID']) || empty($data['orderDate']) || !isset($data['totalAmount'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu dữ liệu cần cập nhật']);
            exit;
        }
        try {
            $dt = new DateTime($data['orderDate']);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Định dạng orderDate không hợp lệ']);
            exit;
        }
        $resp = $service->update_order(
            $data['orderID'],
            $data['userID'],
            $dt,
            floatval($data['totalAmount'])
        );
        http_response_code($resp->success ? 200 : 500);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message]);
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['orderID'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu orderID']);
            exit;
        }
        $resp = $service->delete_order($data['orderID']);
        http_response_code($resp->success ? 200 : 404);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Phương thức không hỗ trợ']);
        break;
}
