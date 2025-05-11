<?php
// File: api/course_image_api.php

require_once __DIR__ . '/../service/service_course_image.php';

header('Content-Type: application/json');
$service = new CourseImageService();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        // GET /api/course_image_api.php?courseID=...
        if (!isset($_GET['courseID'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu courseID']);
            exit;
        }
        $resp = $service->get_images($_GET['courseID']);
        http_response_code($resp->success ? 200 : 500);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message, 'data' => $resp->data]);
        break;

    case 'POST':
        // POST body: { courseID, imagePath, caption?, sortOrder? }
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['courseID'], $data['imagePath'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu courseID hoặc imagePath']);
            exit;
        }
        $caption   = $data['caption'] ?? null;
        $sortOrder = isset($data['sortOrder']) ? intval($data['sortOrder']) : 0;
        $resp = $service->add_image($data['courseID'], $data['imagePath'], $caption, $sortOrder);
        http_response_code($resp->success ? 201 : 500);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message, 'data' => $resp->data]);
        break;

    case 'DELETE':
        // DELETE body: { imageID }
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['imageID'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu imageID']);
            exit;
        }
        $resp = $service->delete_image($data['imageID']);
        http_response_code($resp->success ? 200 : 404);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ']);
        break;
}
