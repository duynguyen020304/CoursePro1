<?php
// File: api/course_chapter_api.php

require_once __DIR__ . '/../service/service_course_chapter.php';
header('Content-Type: application/json');
$service = new CourseChapterService();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if (!isset($_GET['courseID'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu courseID']);
            exit;
        }
        $resp = $service->get_chapters_by_course($_GET['courseID']);
        http_response_code($resp->success ? 200 : 500);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message, 'data' => $resp->data]);
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['courseID'], $data['title'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu courseID hoặc title']);
            exit;
        }
        $description = $data['description'] ?? null;
        $sortOrder   = isset($data['sortOrder']) ? intval($data['sortOrder']) : 0;
        $resp = $service->create_chapter($data['courseID'], $data['title'], $description, $sortOrder);
        http_response_code($resp->success ? 201 : 500);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message, 'data' => $resp->data]);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['chapterID'], $data['title'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu chapterID hoặc title']);
            exit;
        }
        $description = $data['description'] ?? null;
        $sortOrder   = isset($data['sortOrder']) ? intval($data['sortOrder']) : 0;
        $resp = $service->update_chapter($data['chapterID'], $data['title'], $description, $sortOrder);
        http_response_code($resp->success ? 200 : 500);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message]);
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['chapterID'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu chapterID']);
            exit;
        }
        $resp = $service->delete_chapter($data['chapterID']);
        http_response_code($resp->success ? 200 : 500);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ']);
        break;
}
