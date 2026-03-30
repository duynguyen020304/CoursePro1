<?php
// Public API - no authentication required
require_once __DIR__ . '/../../Service/service_course_requirement.php';
require_once __DIR__ . '/../../Service/service_response.php';

header('Content-Type: application/json');

$service = new CourseRequirementService();
$method  = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['requirementID'])) {
            $resp = $service->get_requirement_by_requirement_id($_GET['requirementID']);
        } elseif (isset($_GET['courseID'])) {
            $resp = $service->get_requirements_by_course_id($_GET['courseID']);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu requirementID hoặc courseID']);
            exit;
        }
        http_response_code($resp->success ? 200 : 404);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message, 'data' => $resp->data]);
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['courseID'], $data['requirement'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu courseID hoặc requirement']);
            exit;
        }
        $resp = $service->create($data['courseID'], $data['requirement']);
        http_response_code($resp->success ? 201 : 500);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message, 'data' => $resp->data]);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['requirementID'], $data['courseID'], $data['requirement'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu requirementID, courseID hoặc requirement']);
            exit;
        }
        $resp = $service->update($data['requirementID'], $data['courseID'], $data['requirement']);
        http_response_code($resp->success ? 200 : 500);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message]);
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['requirementID'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu requirementID']);
            exit;
        }
        $resp = $service->delete($data['requirementID']);
        http_response_code($resp->success ? 200 : 404);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Phương thức không hỗ trợ']);
        break;
}