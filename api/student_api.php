<?php
$secretKey = '0196ce3e-ba28-7b47-8472-beded9ae0b5d';
require_once __DIR__ . '/../service/service_student.php';
require __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$authHeader = apache_request_headers();
$token = null;

if ($authHeader['Authorization']) {
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

$service = new StudentService();


switch ($method) {
    case 'GET':
        // GET /student_api.php?studentID=... or without to fetch all
        if (isset($_GET['studentID'])) {
            $resp = $service->get_student($_GET['studentID']);
            http_response_code($resp->success ? 200 : 404);
            echo json_encode([
                'success' => $resp->success,
                'message' => $resp->message,
                'data'    => $resp->data
            ]);
        } else {
            $resp = $service->get_all_students();
            http_response_code($resp->success ? 200 : 500);
            echo json_encode([
                'success' => $resp->success,
                'message' => $resp->message,
                'data'    => $resp->data
            ]);
        }
        break;

    case 'POST':
        // Create new student
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['studentID']) || empty($data['userID']) || empty($data['enrollmentDate'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu dữ liệu bắt buộc: studentID, userID hoặc enrollmentDate']);
            exit;
        }
        try {
            $enrollDate = new DateTime($data['enrollmentDate']);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Định dạng enrollmentDate không hợp lệ']);
            exit;
        }
        $resp = $service->create_student(
            $data['studentID'],
            $data['userID'],
            $enrollDate,
            $data['completedCourses'] ?? null
        );
        http_response_code($resp->success ? 201 : 500);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message, 'data' => $resp->data]);
        break;

    case 'PUT':
        // Update existing student
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['studentID']) || empty($data['userID']) || empty($data['enrollmentDate'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu dữ liệu cần cập nhật: studentID, userID hoặc enrollmentDate']);
            exit;
        }
        try {
            $enrollDate = new DateTime($data['enrollmentDate']);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Định dạng enrollmentDate không hợp lệ']);
            exit;
        }
        $resp = $service->update_student(
            $data['studentID'],
            $data['userID'],
            $enrollDate,
            $data['completedCourses'] ?? null
        );
        http_response_code($resp->success ? 200 : 500);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message]);
        break;

    case 'DELETE':
        // Delete a student
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['studentID'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu studentID để xóa']);
            exit;
        }
        $resp = $service->delete_student($data['studentID']);
        http_response_code($resp->success ? 200 : 404);
        echo json_encode(['success' => $resp->success, 'message' => $resp->message]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ']);
        break;
}
