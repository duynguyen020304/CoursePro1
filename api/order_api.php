<?php
// File: api/order_api.php

require_once __DIR__ . '/../service/service_order.php';
header('Content-Type: application/json');

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
        // Create order
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['orderID']) || empty($data['userID']) || empty($data['orderDate']) || !isset($data['totalAmount'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu dữ liệu: orderID,userID,orderDate,totalAmount']);
            exit;
        }
        try {
            $dt = new DateTime($data['orderDate']);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Định dạng orderDate không hợp lệ']);
            exit;
        }
        $resp = $service->create_order(
            $data['orderID'],
            $data['userID'],
            $dt,
            floatval($data['totalAmount'])
        );
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
