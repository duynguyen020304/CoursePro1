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
        echo json_encode([
            'success' => $resp->success,
            'message' => $resp->message,
            'data'    => $resp->data
        ]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ']);
        break;
}
