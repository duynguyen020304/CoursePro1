<?php
$secretKey = '0196ce3e-ba28-7b47-8472-beded9ae0b5d';
require_once __DIR__ . '/../service/service_cart.php';
require __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("Content-Type: application/json");
$authHeader = apache_request_headers();
$token = null;
$decode = null;


if (isset($authHeader['Authorization'])) {
    if (preg_match('/Bearer\s(\S+)/', $authHeader['Authorization'], $matches)) {
        $token = $matches[1];
        $decode = JWT::decode($token, new Key($secretKey, 'HS256'));
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

$method = $_SERVER['REQUEST_METHOD'];
$service = new CartService();

$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        $cart = $service->get_cart_by_user($decode->data->userID);
        if (!$cart) {
            $create_cart = $service->create_cart($decode->data->userID);
            if ($create_cart['success']) {
                echo json_encode(["sucesss" => $create_cart['successs'], "cartID" => $create_cart['cartID']]);
                exit;
            }
        }
        echo json_encode(["sucesss" => true, "cartID" => $cart->cartID]);
        exit;
        break;

    case 'POST':
        if (isset($input['userID'])) {
            echo json_encode($service->create_cart($input['userID']));
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Missing userID']);
        }
        break;

    case 'PUT':
        if (isset($input['cartID'], $input['userID'])) {
            $success = $service->update_cart($input['cartID'], $input['userID']);
            echo json_encode(['success' => $success]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Missing cartID or userID']);
        }
        break;

    case 'DELETE':
        if (isset($_GET['cartID'])) {
            $cartID = $_GET['cartID'];
            error_log("Received cartID to delete: $cartID");
            $success = $service->delete_cart($cartID);
            echo json_encode(['success' => $success]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Missing cartID']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
