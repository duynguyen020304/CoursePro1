<?php
require_once __DIR__ . '/../service/service_cart.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$service = new CartService();

$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        if (isset($_GET['userID'])) {
            $userID = $_GET['userID'];
            $cart = $service->getCartByUser($userID);
            echo json_encode($cart ?? []);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Missing userID']);
        }
        break;

    case 'POST':
        if (isset($input['userID'])) {
            echo json_encode($service->createCart($input['userID']));
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Missing userID']);
        }
        break;

    case 'PUT':
        if (isset($input['cartID'], $input['userID'])) {
            $success = $service->updateCart($input['cartID'], $input['userID']);
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
            $success = $service->deleteCart($cartID);
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
