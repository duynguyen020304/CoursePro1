<?php
require_once __DIR__ . '/../../Model/Config/auth_helper.php';
require_once __DIR__ . '/../../Service/service_cart.php';

header("Content-Type: application/json");

// Require authentication for all requests
$userData = AuthHelper::requireAuth();

$method = $_SERVER['REQUEST_METHOD'];
$service = new CartService();

$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        $cart = $service->get_cart_by_user($userData->userID);
        if (!$cart) {
            $create_cart = $service->create_cart($userData->userID);
            if ($create_cart['success']) {
                echo json_encode(["success" => $create_cart['success'], "cartID" => $create_cart['cartID']]);
                exit;
            }
        }
        echo json_encode(["success" => true, "cartID" => $cart->cartID]);
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