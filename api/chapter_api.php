// api/chapter_api.php
<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../service/service_chapter.php';

$method  = $_SERVER['REQUEST_METHOD'];
$service = new ChapterService();
$response = null;

switch ($method) {
    case 'GET':
        $response = null;
        if (isset($_GET['id'])) {
            $response = $service->get_chapter_by_id($_GET['id']);
        } else {
            $response = $service->get_all_chapters();
        }
        http_response_code($response->success ? 201 : 500);
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
        echo json_encode(['success' => $response->success, 'message' => $response->message]);
        if ($response)
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
        break;

    default:
        $response = new ServiceResponse(false, "Phương thức {$method} không được hỗ trợ");
}

echo json_encode([
    'success' => $response->success,
    'message' => $response->message,
    'data'    => $response->data ?? null
]);
