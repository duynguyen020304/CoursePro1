<?php
require_once __DIR__ . '/../model/bll/cart_item_bll.php';
require_once __DIR__ . '/../model/dto/cart_item_dto.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        create_item();
        break;
    case 'GET':
        get_items_by_cart();
        break;
    case 'DELETE':
        // Xác định là delete 1 item hay clear toàn bộ dựa vào dữ liệu gửi lên
        $data = json_decode(file_get_contents('php://input'), true);
        if (!empty($data['cartItemID'])) {
            delete_item($data['cartItemID']);
        } elseif (!empty($data['cartID'])) {
            clear_cart($data['cartID']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Missing cartItemID or cartID']);
        }
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
        break;
}

function create_item()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        // Lấy tham số từ input hoặc gán giá trị mặc định
        $cartItemID = $data['cartItemID'] ?? uniqid("cartItem_", true);
        $cartID = $data['cartID'] ?? '';
        $courseID = $data['courseID'] ?? '';
        $quantity = $data['quantity'] ?? 0;

        // Kiểm tra các tham số đầu vào
        if (empty($cartItemID) || empty($cartID) || empty($courseID) || $quantity <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid input data']);
            return;
        }

        // Tạo đối tượng DTO và BLL
        $itemDTO = new CartItemDTO($cartItemID, $cartID, $courseID, $quantity);
        $cartItemBLL = new CartItemBLL();

        // Gọi phương thức create_item trong BLL
        $result = $cartItemBLL->create_item($itemDTO);
        if ($result) {
            echo json_encode(['status' => 'success', 'message' => 'Item created successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to create item']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    }
}

function get_items_by_cart()
{
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $cartID = $_GET['cartID'] ?? '';

        // Kiểm tra CartID có được cung cấp không
        if (empty($cartID)) {
            echo json_encode(['status' => 'error', 'message' => 'Cart ID is required']);
            return;
        }

        $cartItemBLL = new CartItemBLL();
        $items = $cartItemBLL->get_items_by_cart($cartID);

        echo json_encode(['status' => 'success', 'data' => $items]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    }
}

function delete_item()
{
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $data = json_decode(file_get_contents('php://input'), true);
        $cartItemID = $data['cartItemID'] ?? '';

        // Kiểm tra CartItemID có được cung cấp không
        if (empty($cartItemID)) {
            echo json_encode(['status' => 'error', 'message' => 'CartItemID is required']);
            return;
        }

        $cartItemBLL = new CartItemBLL();
        $result = $cartItemBLL->delete_item($cartItemID);

        if ($result) {
            echo json_encode(['status' => 'success', 'message' => 'Item deleted successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete item']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    }
}

function clear_cart()
{
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $data = json_decode(file_get_contents('php://input'), true);
        $cartID = $data['cartID'] ?? '';

        // Kiểm tra CartID có được cung cấp không
        if (empty($cartID)) {
            echo json_encode(['status' => 'error', 'message' => 'CartID is required']);
            return;
        }

        $cartItemBLL = new CartItemBLL();
        $result = $cartItemBLL->clear_cart($cartID);

        if ($result) {
            echo json_encode(['status' => 'success', 'message' => 'Cart cleared successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to clear cart']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    }
}
?>
