<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/cart_item_dto.php';
class CartItemBLL extends Database
{
    public function create_item(CartItemDTO $item)
    {
        $sql = "INSERT INTO CartItem (CartItemID, CartID, CourseID, Quantity) VALUES ('{$item->cartItemID}', '{$item->cartID}', '{$item->courseID}', {$item->quantity})";
        $this->execute($sql);
        $this->close();
    }

    public function get_items_by_cart(string $cartID): array
    {
        $sql = "SELECT * FROM CartItem WHERE CartID = '{$cartID}'";
        $result = $this->execute($sql);
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = new CartItemDTO($row['CartItemID'], $row['CartID'], $row['CourseID'], (int)$row['Quantity']);
        }
        $this->close();
        return $items;
    }

    public function delete_item(string $cartItemID)
    {
        $sql = "DELETE FROM CartItem WHERE CartItemID = '{$cartItemID}'";
        $this->execute($sql);
        $this->close();
    }

    public function clear_cart(string $cartID)
    {
        $sql = "DELETE FROM CartItem WHERE CartID = '{$cartID}'";
        $this->execute($sql);
        $this->close();
    }
}
