<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/cart_dto.php';

class CartBLL extends Database
{
    /**
     * Tạo một giỏ hàng mới trong database.
     *
     * @param CartDTO $cart Đối tượng giỏ hàng chứa thông tin cần tạo.
     * @return bool Trả về true nếu tạo thành công, ngược lại false.
     */
    public function create_cart(CartDTO $cart): bool
    {
        // Câu lệnh SQL INSERT chuẩn cho MySQL
        $sql = "INSERT INTO Cart (cartID, userID) VALUES (?, ?)";

        $bindParams = [
            $cart->cartID,
            $cart->userID,
        ];

        // Sử dụng executePrepared để thực thi an toàn
        $result = $this->executePrepared($sql, $bindParams);
        return ($result !== false);
    }

    /**
     * Lấy thông tin giỏ hàng dựa trên ID người dùng.
     *
     * @param string $userID ID của người dùng.
     * @return ?CartDTO Trả về đối tượng CartDTO nếu tìm thấy, ngược lại null.
     */
    public function get_cart_by_user(string $userID): ?CartDTO
    {
        // Câu lệnh SELECT chuẩn cho MySQL
        $sql = "SELECT cartID, userID, created_at FROM Cart WHERE userID = ? LIMIT 1";
        $bindParams = [$userID];

        $result = $this->executePrepared($sql, $bindParams);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $dto = new CartDTO(
                $row['cartID'],
                $row['userID'],
                $row['created_at']
            );
            $result->free();
            return $dto;
        }

        return null;
    }

    /**
     * Lấy thông tin giỏ hàng dựa trên ID giỏ hàng.
     *
     * @param string $cartID ID của giỏ hàng.
     * @return ?CartDTO Trả về đối tượng CartDTO nếu tìm thấy, ngược lại null.
     */
    public function get_cart_by_id(string $cartID): ?CartDTO
    {
        // Câu lệnh SELECT chuẩn cho MySQL
        $sql = "SELECT cartID, userID, created_at FROM Cart WHERE cartID = ? LIMIT 1";
        $bindParams = [$cartID];

        $result = $this->executePrepared($sql, $bindParams);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $dto = new CartDTO(
                $row['cartID'],
                $row['userID'],
                $row['created_at']
            );
            $result->free();
            return $dto;
        }

        return null;
    }

    /**
     * Xóa một giỏ hàng khỏi database.
     *
     * @param string $cartID ID của giỏ hàng cần xóa.
     * @return bool Trả về true nếu xóa thành công, ngược lại false.
     */
    public function delete_cart(string $cartID): bool
    {
        // Câu lệnh DELETE chuẩn cho MySQL
        $sql = "DELETE FROM Cart WHERE cartID = ?";
        $bindParams = [$cartID];

        $result = $this->executePrepared($sql, $bindParams);
        return ($result !== false);
    }
}
?>
