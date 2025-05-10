<?php
require_once __DIR__ . '/../service/service_category.php';
require_once __DIR__ . '/../model/dto/category_dto.php';

header("Content-Type: application/json");

$service = new CategoryService();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $isTree = isset($_GET['tree']) && $_GET['tree'] === '1';
        $response = $isTree ? $service->get_tree() : $service->get_all();
        echo json_encode(['success' => $response->success, 'message' => $response->message, 'data' => $response->data]);
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['name'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu tên danh mục']);
            exit;
        }

        $dto = new CategoryDTO(
            0,
            $data['name'],
            $data['parent_id'] ?? null,
            $data['sort_order'] ?? 0
        );

        $response = $service->create($dto);
        http_response_code($response->success ? 200 : 500);
        echo json_encode(['success' => $response->success, 'message' => $response->message]);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['id'], $data['name'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu dữ liệu cập nhật']);
            exit;
        }

        $dto = new CategoryDTO(
            (int)$data['id'],
            $data['name'],
            $data['parent_id'] ?? null,
            $data['sort_order'] ?? 0
        );

        $response = $service->update($dto);
        http_response_code($response->success ? 200 : 500);
        echo json_encode(['success' => $response->success, 'message' => $response->message]);
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu ID để xóa']);
            exit;
        }

        $response = $service->delete((int)$data['id']);
        http_response_code($response->success ? 200 : 500);
        echo json_encode(['success' => $response->success, 'message' => $response->message]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Phương thức không hỗ trợ']);
        break;
}
