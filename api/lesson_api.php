<?php
require_once __DIR__ . '/../service/service_lesson.php';
header('Content-Type: application/json');

$service = new LessonService();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['lessonID'])) {
            $resp = $service->get_lesson($_GET['lessonID']);
        } elseif (isset($_GET['chapterID'])) {
            $resp = $service->get_lessons_by_chapter($_GET['chapterID']);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu lessonID hoặc chapterID']);
            exit;
        }
        http_response_code($resp->success ? 200 : 404);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message, 'data' => $resp->data]);
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['courseID'], $data['chapterID'], $data['title'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu dữ liệu bắt buộc']);
            exit;
        }
        $content   = $data['content'] ?? null;
        $sortOrder = isset($data['sortOrder']) ? intval($data['sortOrder']) : 0;
        $resp = $service->create_lesson(
            $data['courseID'],
            $data['chapterID'],
            $data['title'],
            $content,
            $sortOrder
        );
        http_response_code($resp->success ? 201 : 500);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message, 'data' => $resp->data]);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['lessonID'], $data['courseID'], $data['chapterID'], $data['title'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu dữ liệu cần cập nhật']);
            exit;
        }
        $content   = $data['content'] ?? null;
        $sortOrder = isset($data['sortOrder']) ? intval($data['sortOrder']) : 0;
        $resp = $service->update_lesson(
            $data['lessonID'],
            $data['courseID'],
            $data['chapterID'],
            $data['title'],
            $content,
            $sortOrder
        );
        http_response_code($resp->success ? 200 : 500);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message]);
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['lessonID'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu lessonID']);
            exit;
        }
        $resp = $service->delete_lesson($data['lessonID']);
        http_response_code($resp->success ? 200 : 404);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Phương thức không hỗ trợ']);
        break;
}
