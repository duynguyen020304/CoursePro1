<?php
$secretKey = '0196ce3e-ba28-7b47-8472-beded9ae0b5d';
require_once __DIR__ . '/../service/service_course.php';
require __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("Content-Type: application/json");
$authHeader = apache_request_headers();
$token = null;

if (!isset($_GET['isGetAllCourse']) && !$_GET['isGetAllCourse'] == true) {
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


$service = new CourseService();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $response = $service->get_all_courses();
        http_response_code($response->success ? 200 : 500);
        echo json_encode([
            'success' => $response->success,
            'message' => $response->message,
            'data'    => $response->data
        ]);
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $requiredFields = ['title', 'price'];
        $requiredArrayFields = ['instructorsID', 'categoriesID'];
        $missingFields = [];
        $invalidArrayFields = [];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                $missingFields[] = $field;
            }
        }

        foreach ($requiredArrayFields as $field) {
            if (!isset($data[$field])) {
                $missingFields[] = $field;
            } elseif (!is_array($data[$field]) || empty($data[$field])) {
                $invalidArrayFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Thiếu các dữ liệu đầu vào bắt buộc: ' . implode(', ', $missingFields)
            ]);
            exit;
        }

        if (!empty($invalidArrayFields)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Các trường sau phải là mảng không rỗng: ' . implode(', ', $invalidArrayFields)
            ]);
            exit;
        }

        $description = $data['description'] ?? null;
        $response = $service->create_course(
            $data['title'],
            $description,
            floatval($data['price']),
            $data['instructorsID'],
            $data['categoriesID'],
            $data['createdBy']
        );

        http_response_code($response->success ? 201 : 500);
        echo json_encode([
            'success' => $response->success,
            'message' => $response->message,
            'course_id' => $response->data ?? null
        ]);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        $requiredFields = ['courseID', 'title', 'price'];
        $requiredArrayFields = ['instructorsID', 'categoriesID'];
        $missingFields = [];
        $invalidArrayFields = [];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                $missingFields[] = $field;
            }
        }

        foreach ($requiredArrayFields as $field) {
            if (!isset($data[$field])) {
                $missingFields[] = $field;
            } elseif (!is_array($data[$field]) || empty($data[$field])) {
                $invalidArrayFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Thiếu các dữ liệu cần cập nhật bắt buộc: ' . implode(', ', $missingFields)
            ]);
            exit;
        }

        if (!empty($invalidArrayFields)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Các trường sau phải là mảng không rỗng: ' . implode(', ', $invalidArrayFields)
            ]);
            exit;
        }

        $description = $data['description'] ?? null;
        $response = $service->update_course(
            $data['courseID'],
            $data['title'],
            $description,
            floatval($data['price']),
            $data['instructorsID'],
            $data['categoriesID'],
        );

        http_response_code($response->success ? 200 : 500);
        echo json_encode(['success' => $response->success, 'message' => $response->message]);
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['courseID'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu dữ liệu cần thiết để xóa: courseID']);
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
