<?php
require_once __DIR__ . '/../../Model/Config/auth_helper.php';
require_once __DIR__ . '/../../Service/service_instructor.php';

header('Content-Type: application/json');

// Conditional auth: skip if isGetInstructorHomePage=true
$isGetInstructorHomePage = isset($_GET['isGetInstructorHomePage']) && $_GET['isGetInstructorHomePage'] == true;
$userData = null;

if (!$isGetInstructorHomePage) {
    $userData = AuthHelper::requireAuth();
}

$service = new InstructorService();
$method  = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['instructorID'])) {
            $respCode = 200;
            $resp = $service->get_instructor($_GET['instructorID']);
            if (!$resp->success) {
                $respCode = 404;
            }
            http_response_code($respCode);
            echo json_encode([
                'success' => $resp->success,
                'message' => $resp->message,
                'data'    => $resp->data
            ]);
        } else {
            $resp = $service->get_all_instructors();
            http_response_code($resp->success ? 200 : 500);
            echo json_encode([
                'success' => $resp->success,
                'message' => $resp->message,
                'data'    => $resp->data
            ]);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['instructorID']) || empty($data['userID'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu instructorID hoặc userID']);
            exit;
        }
        $resp = $service->create_instructor(
            $data['instructorID'],
            $data['userID'],
            $data['biography'] ?? null,
        );
        http_response_code($resp->success ? 201 : 500);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message, 'data' => $resp->data ?? null]);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['instructorID']) || empty($data['userID'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu instructorID hoặc userID']);
            exit;
        }
        $resp = $service->update_instructor(
            $data['instructorID'],
            $data['userID'],
            $data['biography'] ?? null,
            $data['profileImage'] ?? null
        );
        http_response_code($resp->success ? 200 : 500);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message]);
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['instructorID'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu instructorID để xóa']);
            exit;
        }
        $resp = $service->delete_instructor($data['instructorID']);
        http_response_code($resp->success ? 200 : 404);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ']);
        break;
}