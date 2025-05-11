<?php
// ----------------------------------------
// File: api/video_api.php

require_once __DIR__ . '/../service/service_video.php';
header('Content-Type: application/json');

$service = new VideoService();
$method  = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['videoID'])) {
            $resp = $service->get_video($_GET['videoID']);
        } elseif (isset($_GET['lessonID'])) {
            $resp = $service->get_videos_by_lesson($_GET['lessonID']);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu videoID hoặc lessonID']);
            exit;
        }
        http_response_code($resp->success ? 200 : 404);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message, 'data' => $resp->data]);
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['lessonID'], $data['url'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu lessonID hoặc url']);
            exit;
        }
        $title     = $data['title'] ?? null;
        $sortOrder = isset($data['sortOrder']) ? intval($data['sortOrder']) : 0;
        $resp = $service->create_video($data['lessonID'], $data['url'], $title, $sortOrder);
        http_response_code($resp->success ? 201 : 500);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message, 'data' => $resp->data]);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['videoID'], $data['lessonID'], $data['url'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu videoID, lessonID hoặc url']);
            exit;
        }
        $title     = $data['title'] ?? null;
        $sortOrder = isset($data['sortOrder']) ? intval($data['sortOrder']) : 0;
        $resp = $service->update_video($data['videoID'], $data['lessonID'], $data['url'], $title, $sortOrder);
        http_response_code($resp->success ? 200 : 500);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message]);
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['videoID'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu videoID']);
            exit;
        }
        $resp = $service->delete_video($data['videoID']);
        http_response_code($resp->success ? 200 : 404);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Phương thức không hỗ trợ']);
        break;
}
