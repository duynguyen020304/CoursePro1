<?php
// Public API - no authentication required
header("Content-Type: application/json");
require_once __DIR__ . '/../../Service/service_chapter.php';

$method  = $_SERVER['REQUEST_METHOD'];
$service = new ChapterService();
$response = null;

switch ($method) {
    case 'GET':
        if (isset($_GET['courseID'])) {
            $response = $service->get_chapters_by_course_id($_GET['courseID']);
        } else {
            $response = $service->get_all_chapters();
        }
        http_response_code($response->success ? 200 : 500);
        echo json_encode(['success' => $response->success, 'message' => $response->message, 'data' => $response->data]);
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        $response = $service->create_chapter(
            $input['courseID']   ?? '',
            $input['title']      ?? '',
            $input['description'] ?? null,
            (int)($input['sortOrder'] ?? 0)
        );
        http_response_code($response->success ? 201 : 500);
        echo json_encode(['success' => $response->success, 'message' => $response->message, 'data' => $response->data]);
        break;

    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        $response = $service->update_chapter(
            $input['chapterID']  ?? '',
            $input['courseID']   ?? '',
            $input['title']      ?? '',
            $input['description'] ?? null,
            (int)($input['sortOrder'] ?? 0)
        );
        http_response_code($response->success ? 201 : 500);
        echo json_encode(['success' => $response->success, 'message' => $response->message]);
        break;

    case 'DELETE':
        if (isset($_GET['id'])) {
            $response = $service->delete_chapter($_GET['id']);
        } else {
            $response = new ServiceResponse(false, 'ChapterID không được bỏ trống');
        }
        http_response_code($response->success ? 200 : 500);
        echo json_encode(['success' => $response->success, 'message' => $response->message]);
        break;

    default:
        $response = new ServiceResponse(false, "Phương thức {$method} không được hỗ trợ");
        http_response_code(405);
        echo json_encode(['success' => $response->success, 'message' => $response->message]);
}