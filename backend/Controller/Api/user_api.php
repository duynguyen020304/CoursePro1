<?php
require_once __DIR__ . '/../../Model/Config/auth_helper.php';
require_once __DIR__ . '/../../Service/service_user.php';
require_once __DIR__ . '/../../Model/DTO/user_dto.php';

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

// Only require auth for non-GET requests
$userData = null;
if ($method !== 'GET') {
    $userData = AuthHelper::requireAuth();
}

$service = new UserService();

switch ($method) {
    case 'GET':
        $response = null;

        if (isset($_GET['id'])) {
            $response = $service->get_user_by_user_id($_GET['id']);
        } else {
            $response = $service->get_all_users();
        }

        $data_to_encode = null;

        if ($response && $response->success && !empty($response->data)) {
            $data_to_encode = $response->data;

            if (isset($_GET['id'])) {
                if (is_object($data_to_encode)) {
                    unset($data_to_encode->password);
                } elseif (is_array($data_to_encode)) {
                    unset($data_to_encode['password']);
                }
            } else {
                if (is_array($data_to_encode)) {
                    foreach ($data_to_encode as $key => $user_data) {
                        if (is_object($user_data)) {
                            unset($data_to_encode[$key]->password);
                        } elseif (is_array($user_data)) {
                            unset($data_to_encode[$key]['password']);
                        }
                    }
                }
            }
        } else {
            $data_to_encode = $response->data ?? null;
        }

        echo json_encode([
            'success' => $response->success ?? false,
            'message' => $response->message ?? 'An error occurred.',
            'data'    => $data_to_encode
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        if ($userData !== null && $userData->roleID !== "admin") {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Không đủ quyền đủ xóa']);
            exit;
        }
        if (
            !isset($data['email']) ||
            !isset($data['password']) ||
            !isset($data['firstName']) ||
            !isset($data['lastName']) ||
            !isset($data['role'])
        ) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin đăng ký']);
            exit;
        }
        if (!isset($data['profileImage'])) {
            $data['profileImage'] = null;
        }
        $biography = $data['biography'] ?? "NOT_SET";
        $registerResult = $service->create_user(
            $data['email'],
            $data['password'],
            $data['firstName'],
            $data['lastName'],
            $data['role'],
            $biography,
            $data['profileImage']
        );
        http_response_code($registerResult->success ? 200 : 500);
        echo json_encode(['success' => $registerResult->success, 'message' => $registerResult->message]);
        break;


    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['userID'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu userID']);
            exit;
        }
        if (isset($data['password']) && $userData !== null && $userData->roleID !== "admin") {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Không đủ quyền đủ chỉnh sửa']);
            exit;
        }
        $response = $service->update_user_partial($data, $userData->userID);
        http_response_code($response->success ? 200 : 500);
        echo json_encode(['success' => $response->success, 'message' => $response->message]);
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['userID'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu userID để xóa']);
            exit;
        }
        if ($userData !== null && $userData->roleID !== "admin") {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Không đủ quyền đủ xóa']);
            exit;
        }
        $response = $service->delete_user($data['userID'], $userData->userID);
        http_response_code($response->success ? 200 : 500);
        echo json_encode(['success' => $response->success, 'message' => $response->message]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ']);
        break;
}