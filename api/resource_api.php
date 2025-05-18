<?php
$secretKey = '0196ce3e-ba28-7b47-8472-beded9ae0b5d';
header("Content-Type: application/json");
require_once __DIR__ . '/../service/service_resource.php';
require_once __DIR__ . '/../service/service_response.php';
require __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$method = $_SERVER['REQUEST_METHOD'];
$authHeader = apache_request_headers();
$token = null;

if (isset($authHeader['Authorization'])) {
    if (preg_match('/Bearer\s(\S+)/', $authHeader['Authorization'], $matches)) {
        $token = $matches[1];
    }
}

if (!$token) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy token xác thực.']);
    exit;
}

try {
    $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
} catch (Firebase\JWT\ExpiredException $e) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Token đã hết hạn.']);
    exit;
} catch (Firebase\JWT\SignatureInvalidException $e) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Chữ ký token không hợp lệ.']);
    exit;
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Token không hợp lệ hoặc có lỗi xảy ra: ' . $e->getMessage()]);
    exit;
}

$service = new ResourceService();
$response = null;

switch ($method) {
    case 'GET':
        if (isset($_GET['resourceID'])) {
            $response = $service->get_resource_by_id($_GET['resourceID']);
        } elseif (isset($_GET['lessonID'])) {
            $response = $service->get_resources_by_lesson($_GET['lessonID']);
        } else {
            $response = $service->get_all_resources();
        }
        http_response_code($response->success ? 200 : 500);
        echo json_encode(['success' => $response->success, 'message' => $response->message, 'data' => $response->data]);
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        if (!isset($input['lessonID']) || !isset($input['resourcePath'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu lessonID hoặc resourcePath']);
            exit;
        }
        $response = $service->create_resource(
            $input['lessonID'],
            $input['resourcePath'],
            $input['title'] ?? null,
            (int)($input['sortOrder'] ?? 0)
        );
        http_response_code($response->success ? 201 : 500);
        echo json_encode(['success' => $response->success, 'message' => $response->message, 'data' => $response->data ?? null]);
        break;

    case 'PUT':
        $input = json_decode(file_get_contents("php://input"), true);
        if (!isset($input['resourceID']) || !isset($input['lessonID']) || !isset($input['resourcePath'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu resourceID, lessonID hoặc resourcePath']);
            exit;
        }
        $response = $service->update_resource(
            $input['resourceID'],
            $input['lessonID'],
            $input['resourcePath'],
            $input['title'] ?? null,
            (int)($input['sortOrder'] ?? 0)
        );
        http_response_code($response->success ? 200 : 500);
        echo json_encode(['success' => $response->success, 'message' => $response->message]);
        break;

    case 'DELETE':
        // Prefer resourceID in query string for delete
        $resourceID = $_GET['id'] ?? null;
        if (!$resourceID) {
            $data = json_decode(file_get_contents("php://input"), true);
            $resourceID = $data['resourceID'] ?? null;
        }
        if (!$resourceID) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu resourceID để xóa']);
            exit;
        }
        $response = $service->delete_resource($resourceID);
        http_response_code($response->success ? 200 : 500);
        echo json_encode(['success' => $response->success, 'message' => $response->message]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => "Phương thức {$method} không được hỗ trợ"]);
        break;
}