<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/cart_item_dto.php';

class CartItemBLL extends Database
{
    /**
     * Thêm một sản phẩm mới vào giỏ hàng.
     *
     * @param CartItemDTO $item Đối tượng sản phẩm trong giỏ hàng.
     * @return bool True nếu thành công, ngược lại false.
     */
    public function create_item(CartItemDTO $item): bool
    {
        $sql = "INSERT INTO CartItem (cartItemID, cartID, courseID, quantity) VALUES (?, ?, ?, ?)";
        
        $bindParams = [
            $item->cartItemID,
            $item->cartID,
            $item->courseID,
            $item->quantity ?? 1,
        ];

        $result = $this->executePrepared($sql, $bindParams);
        return $result !== false;
    }

    /**
     * Lấy tất cả sản phẩm trong một giỏ hàng.
     *
     * @param string $cartID ID của giỏ hàng.
     * @return array Mảng các đối tượng CartItemDTO.
     */
    public function get_items_by_cart(string $cartID): array
    {
        $sql = "SELECT cartItemID, cartID, courseID, quantity, created_at 
                FROM CartItem 
                WHERE cartID = ? 
                ORDER BY courseID ASC";
        
        $bindParams = [$cartID];
        $result = $this->executePrepared($sql, $bindParams);
        $items = [];

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $items[] = new CartItemDTO(
                    $row['cartItemID'],
                    $row['cartID'],
                    $row['courseID'],
                    (int)$row['quantity'],
                    $row['created_at']
                );
            }
            $result->free();
        }
        return $items;
    }

    /**
     * Xóa một sản phẩm khỏi giỏ hàng.
     *
     * @param string $cartItemID ID của sản phẩm trong giỏ hàng.
     * @return bool True nếu thành công, ngược lại false.
     */
    public function delete_item(string $cartItemID): bool
    {
        $sql = "DELETE FROM cart_items WHERE cartItemID = ?";
        $bindParams = [$cartItemID];
        $result = $this->executePrepared($sql, $bindParams);
        return $result !== false;
    }

    /**
     * Lấy thông tin một sản phẩm trong giỏ hàng bằng ID của nó.
     *
     * @param string $cartItemID ID của sản phẩm.
     * @return ?CartItemDTO Đối tượng CartItemDTO hoặc null nếu không tìm thấy.
     */
    public function get_item_by_id(string $cartItemID): ?CartItemDTO
    {
        $sql = "SELECT cartItemID, cartID, courseID, quantity, created_at 
                FROM CartItem 
                WHERE cartItemID = ?";
        
        $bindParams = [$cartItemID];
        $result = $this->executePrepared($sql, $bindParams);
        $dto = null;

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $dto = new CartItemDTO(
                $row['cartItemID'],
                $row['cartID'],
                $row['courseID'],
                (int)$row['quantity'],
                $row['created_at']
            );
            $result->free();
        }
        return $dto;
    }

    /**
     * Cập nhật số lượng của một sản phẩm trong giỏ hàng.
     *
     * @param string $cartItemID ID của sản phẩm.
     * @param int $quantity Số lượng mới.
     * @return bool True nếu thành công, ngược lại false.
     */
    public function update_item_quantity(string $cartItemID, int $quantity): bool
    {
        if ($quantity <= 0) {
            return $this->delete_item($cartItemID);
        }

        $sql = "UPDATE CartItem SET quantity = ? WHERE cartItemID = ?";
        
        $bindParams = [
            $quantity,
            $cartItemID,
        ];

        $result = $this->executePrepared($sql, $bindParams);
        return $result !== false;
    }

    /**
     * Xóa tất cả sản phẩm khỏi một giỏ hàng.
     *
     * @param string $cartID ID của giỏ hàng.
     * @return bool True nếu thành công, ngược lại false.
     */
    public function clear_cart(string $cartID): bool
    {
        $sql = "DELETE FROM CartItem WHERE cartID = ?";
        $bindParams = [$cartID];
        $result = $this->executePrepared($sql, $bindParams);
        return $result !== false;
    }
}
?>
