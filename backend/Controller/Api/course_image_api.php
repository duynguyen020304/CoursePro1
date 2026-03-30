<?php
require_once __DIR__ . '/../../Model/Config/auth_helper.php';
require_once __DIR__ . '/../../Service/service_course_image.php';

header('Content-Type: application/json');

// Require authentication for all requests
$userData = AuthHelper::requireAuth();

$service = new CourseImageService();
$data = json_decode(file_get_contents("php://input"), true);

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if (!isset($_GET['courseID'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
            exit;
        }
        $resp = $service->get_images($_GET['courseID']);
        http_response_code($resp->success ? 200 : 500);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message, 'data' => $resp->data]);
        break;

    case 'POST':
        if (!isset($data['courseID']) || !isset($data['imageID']) || !isset($data['imagePath'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
            exit;
        }
        $courseID        = $data['courseID'];
        $imageID         = $data['imageID'];
        $imagePath       = $data['imagePath'];
        $caption         = $data['caption'] ?? null;
        $sortOrder       = isset($data['sortOrder']) ? intval($data['sortOrder']) : 0;
        $resp = $service->add_image($imageID, $courseID, $imagePath, $caption, $sortOrder);
        http_response_code($resp->success ? 201 : 500);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message]);
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['imageID']) || !isset($data['courseID'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu dữ liệu']);
            exit;
        }
        $resp = $service->unlink_image_course($data['imageID'], $data['courseID']);
        http_response_code($resp->success ? 200 : ($resp->message == 'Image not found' ? 404 : 500));
        echo json_encode(['success' => $resp->success, 'message' => $resp->message]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ']);
        break;
}