<?php
require_once __DIR__ . '/../service/service_course.php';

header("Content-Type: application/json");
$service = new CourseService();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $response = $service->get_all_courses();
        echo json_encode([
            'success' => $response->success,
            'message' => $response->message,
            'data'    => $response->data
        ]);
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['title'], $data['price'], $data['instructorID'], $data['categoriesID'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu dữ liệu đầu vào']);
            exit;
        }
        $description = $data['description'] ?? null;
        $response = $service->create_course(
            $data['title'],
            $description,
            floatval($data['price']),
            $data['instructorID'],
            $data['categoriesID']
        );

        http_response_code($response->success ? 200 : 500);
        echo json_encode(['success' => $response->success, 'message' => $response->message]);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['courseID'], $data['title'], $data['price'], $data['instructorID'], $data['categories'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu dữ liệu cần cập nhật']);
            exit;
        }

        $description = $data['description'] ?? null;
        $response = $service->update_course(
            $data['courseID'],
            $data['title'],
            $description,
            floatval($data['price']),
            $data['categories'],
            $data['instructorID'],
            $data['createdBy']
        );

        http_response_code($response->success ? 200 : 500);
        echo json_encode(['success' => $response->success, 'message' => $response->message]);
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['courseID'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu courseID để xóa']);
            exit;
        }

        $response = $service->delete_course($data['courseID']);
        http_response_code($response->success ? 200 : 500);
        echo json_encode(['success' => $response->success, 'message' => $response->message]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ']);
        break;
}
