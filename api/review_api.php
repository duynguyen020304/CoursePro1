<?php

require_once __DIR__ . '/../service/service_review.php';
header('Content-Type: application/json');

$service = new ReviewService();
$method  = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (!isset($_GET['courseID'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu courseID']);
            exit;
        }
        $resp = $service->get_reviews_by_course($_GET['courseID']);
        http_response_code($resp->success ? 200 : 500);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message, 'data' => $resp->data]);
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['reviewID']) || empty($data['userID']) || empty($data['courseID']) || !isset($data['rating'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu dữ liệu']);
            exit;
        }
        $resp = $service->create_review(
            $data['reviewID'],
            $data['userID'],
            $data['courseID'],
            intval($data['rating']),
            $data['comment'] ?? null
        );
        http_response_code($resp->success ? 201 : 500);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message, 'data' => $resp->data]);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['reviewID']) || empty($data['userID']) || empty($data['courseID']) || !isset($data['rating'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu dữ liệu']);
            exit;
        }
        $resp = $service->update_review(
            $data['reviewID'],
            $data['userID'],
            $data['courseID'],
            intval($data['rating']),
            $data['comment'] ?? null
        );
        http_response_code($resp->success ? 200 : 500);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message]);
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['reviewID'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu reviewID']);
            exit;
        }
        $resp = $service->delete_review($data['reviewID']);
        http_response_code($resp->success ? 200 : 404);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Phương thức không hỗ trợ']);
}
