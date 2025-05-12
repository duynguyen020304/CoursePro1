<?php
require_once __DIR__ . '/../service/service_user.php';
require_once __DIR__ . '/../model/dto/user_dto.php';

header("Content-Type: application/json");

$service = new UserService();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $response = null;

        if (isset($_GET['id'])) {
            $response = $service->get_user_by_id($_GET['id']);
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

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['userID'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu userID']);
            exit;
        }

        $response = $service->update_user_partial($data);
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

        $response = $service->delete_user($data['userID']);
        http_response_code($response->success ? 200 : 500);
        echo json_encode(['success' => $response->success, 'message' => $response->message]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ']);
        break;
}
