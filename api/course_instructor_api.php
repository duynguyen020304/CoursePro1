<?php
// api/course_instructor_api.php
$secretKey = '0196ce3e-ba28-7b47-8472-beded9ae0b5d';
require_once __DIR__ . '/../service/service_response.php';
require_once __DIR__ . '/../service/service_course_instructor.php';
require __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Content-Type: application/json; charset=utf-8');
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

$service = new CourseInstructorService();
$method  = $_SERVER['REQUEST_METHOD'];

// lấy params từ query string
parse_str($_SERVER['QUERY_STRING'], $query);

// đọc body JSON nếu có
$body = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        if (isset($query['courseID'])) {
            $res = $service->getByCourse($query['courseID']);
        } else {
            $res = new ServiceResponse(false, 'Thiếu parameter: courseID');
        }
        echo json_encode($res);
        break;

    case 'POST':
        $res = $service->add(
            $body['courseID']     ?? '',
            $body['instructorID'] ?? ''
        );
        echo json_encode($res);
        break;

    case 'PUT':
        $res = $service->update(
            $body['oldCourseID']     ?? '',
            $body['oldInstructorID'] ?? '',
            $body['newCourseID']     ?? '',
            $body['newInstructorID'] ?? ''
        );
        echo json_encode($res);
        break;

    case 'DELETE':
        $res = $service->delete(
            $body['courseID']     ?? '',
            $body['instructorID'] ?? ''
        );
        echo json_encode($res);
        break;

    default:
        http_response_code(405);
        echo json_encode(new ServiceResponse(false, 'Method Not Allowed'));
        break;
}
