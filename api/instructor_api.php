<?php
$secretKey = '0196ce3e-ba28-7b47-8472-beded9ae0b5d';
require_once __DIR__ . '/../service/service_instructor.php';
require __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Content-Type: application/json');
$authHeader = apache_request_headers();
$token = null;

if (!isset($_GET['isGetInstructorHomePage']) && !$_GET['isGetInstructorHomePage'] == true) {
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
}


$service = new InstructorService();
$method  = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':

        if (isset($_GET['instructorID'])) {
            $respCode = 200;

            $resp = $service->get_instructor($_GET['instructorID']);
            if (!$resp->success) {
                $respCode = 404;
            }
            http_response_code($respCode);
            echo json_encode([
                'success' => $resp->success,
                'message' => $resp->message,
                'data'    => $resp->data
            ]);
        } else {
            // Lấy tất cả giảng viên
            $resp = $service->get_all_instructors();
            http_response_code($resp->success ? 200 : 500);
            echo json_encode([
                'success' => $resp->success,
                'message' => $resp->message,
                'data'    => $resp->data
            ]);
        }
        break;

    case 'POST':
        // Thêm mới
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['instructorID']) || empty($data['userID'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu instructorID hoặc userID']);
            exit;
        }
        $resp = $service->create_instructor(
            $data['instructorID'],
            $data['userID'],
            $data['biography'] ?? null,
        );
        http_response_code($resp->success ? 201 : 500);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message, 'data' => $resp->data ?? null]);
        break;

    case 'PUT':
        // Cập nhật
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['instructorID']) || empty($data['userID'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu instructorID hoặc userID']);
            exit;
        }
        $resp = $service->update_instructor(
            $data['instructorID'],
            $data['userID'],
            $data['biography'] ?? null,
            $data['profileImage'] ?? null
        );
        http_response_code($resp->success ? 200 : 500);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message]);
        break;

    case 'DELETE':
        // Xóa
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['instructorID'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu instructorID để xóa']);
            exit;
        }
        $resp = $service->delete_instructor($data['instructorID']);
        http_response_code($resp->success ? 200 : 404);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ']);
        break;
}
