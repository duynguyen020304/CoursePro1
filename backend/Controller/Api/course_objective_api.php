<?php
// Public API - no authentication required
require_once __DIR__ . '/../../Service/service_course_objective.php';
require_once __DIR__ . '/../../Service/service_response.php';

header('Content-Type: application/json');

$service = new CourseObjectiveService();
$method  = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['objectiveID'])) {
            $resp = $service->get_objective_by_objective_id($_GET['objectiveID']);
        } elseif (isset($_GET['courseID'])) {
            $resp = $service->get_objectives_by_course_id($_GET['courseID']);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu objectiveID hoặc courseID']);
            exit;
        }
        http_response_code($resp->success ? 200 : 404);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message, 'data' => $resp->data]);
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['courseID'], $data['objective'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu courseID hoặc objective']);
            exit;
        }
        $resp = $service->create($data['courseID'], $data['objective']);
        http_response_code($resp->success ? 201 : 500);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message, 'data' => $resp->data]);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['objectiveID'], $data['courseID'], $data['objective'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu objectiveID, courseID hoặc objective']);
            exit;
        }
        $resp = $service->update($data['objectiveID'], $data['courseID'], $data['objective']);
        http_response_code($resp->success ? 200 : 500);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message]);
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['objectiveID'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu objectiveID']);
            exit;
        }
        $resp = $service->delete($data['objectiveID']);
        http_response_code($resp->success ? 200 : 404);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Phương thức không hỗ trợ']);
        break;
}