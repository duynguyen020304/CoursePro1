<?php
require_once __DIR__ . '/../../Model/Config/auth_helper.php';
date_default_timezone_set('Asia/Ho_Chi_Minh');
require_once __DIR__ . '/../../Service/service_payment.php';

header('Content-Type: application/json');

// Require authentication for all requests
$userData = AuthHelper::requireAuth();

$service = new PaymentService();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['orderID'], $data['amount'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu dữ liệu bắt buộc: orderID, paymentDate hoặc amount']);
            exit;
        }

        $paymentDate = new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
        $method = $data['paymentMethod'] ?? null;
        $status = $data['paymentStatus'] ?? null;
        $amount = floatval($data['amount']);

        $response = $service->create_payment($data['orderID'], $paymentDate, $method, $status, $amount);
        http_response_code($response->success ? 201 : 500);
        echo json_encode(['success' => $response->success, 'message' => $response->message, 'data' => $response->data]);
        break;

    case 'GET':
        if (!isset($_GET['orderID'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu orderID để truy vấn']);
            exit;
        }
        $response = $service->get_payment_by_order_id($_GET['orderID']);
        http_response_code($response->success ? 200 : 404);
        echo json_encode(['success' => $response->success, 'message' => $response->message, 'data' => $response->data]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ']);
        break;
}