<?php

require_once __DIR__ . '/../service/service_course_category.php';
header('Content-Type: application/json');

$service = new CourseCategoryService();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if (!isset($_GET['courseID'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu courseID']);
            exit;
        }
        $resp = $service->get_categories_by_course($_GET['courseID']);
        http_response_code($resp->success ? 200 : 500);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message, 'data' => $resp->data]);
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['courseID'], $data['categoryID'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu courseID hoặc categoryID']);
            exit;
        }

        $resp = $service->add_category_to_course($data['courseID'], $data['categoryID']);
        http_response_code($resp->success ? 201 : 500);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message]);
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['courseID'], $data['categoryID'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu courseID hoặc categoryID']);
            exit;
        }

        $resp = $service->remove_category_from_course($data['courseID'], $data['categoryID']);
        http_response_code($resp->success ? 200 : 500);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ']);
        break;
}
