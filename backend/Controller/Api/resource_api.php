<?php
require_once __DIR__ . '/../../Model/Config/auth_helper.php';
require_once __DIR__ . '/../../Service/service_resource.php';
require_once __DIR__ . '/../../Service/service_response.php';

header("Content-Type: application/json");

// Require authentication for all requests
$userData = AuthHelper::requireAuth();

$method = $_SERVER['REQUEST_METHOD'];
$service = new ResourceService();
$response = null;

switch ($method) {
    case 'GET':
        if (isset($_GET['resourceID'])) {
            $response = $service->get_resource_by_resource_id($_GET['resourceID']);
        } elseif (isset($_GET['lessonID'])) {
            $response = $service->get_resources_by_lesson_id($_GET['lessonID']);
        } else {
            $response = $service->get_all_resources();
        }
        http_response_code($response->success ? 200 : 500);
        echo json_encode(['success' => $response->success, 'message' => $response->message, 'data' => $response->data]);
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        if (!isset($input['lessonID']) || !isset($input['resourcePath'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu lessonID hoặc resourcePath']);
            exit;
        }
        $response = $service->create_resource(
            $input['lessonID'],
            $input['resourcePath'],
            $input['title'] ?? null,
            (int)($input['sortOrder'] ?? 0)
        );
        http_response_code($response->success ? 201 : 500);
        echo json_encode(['success' => $response->success, 'message' => $response->message, 'data' => $response->data ?? null]);
        break;

    case 'PUT':
        $input = json_decode(file_get_contents("php://input"), true);
        if (!isset($input['resourceID']) || !isset($input['lessonID']) || !isset($input['resourcePath'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu resourceID, lessonID hoặc resourcePath']);
            exit;
        }
        $response = $service->update_resource(
            $input['resourceID'],
            $input['lessonID'],
            $input['resourcePath'],
            $input['title'] ?? null,
            (int)($input['sortOrder'] ?? 0)
        );
        http_response_code($response->success ? 200 : 500);
        echo json_encode(['success' => $response->success, 'message' => $response->message]);
        break;

    case 'DELETE':
        $resourceID = $_GET['id'] ?? null;
        if (!$resourceID) {
            $data = json_decode(file_get_contents("php://input"), true);
            $resourceID = $data['resourceID'] ?? null;
        }
        if (!$resourceID) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu resourceID để xóa']);
            exit;
        }
        $response = $service->delete_resource($resourceID);
        http_response_code($response->success ? 200 : 500);
        echo json_encode(['success' => $response->success, 'message' => $response->message]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => "Phương thức {$method} không được hỗ trợ"]);
        break;
}