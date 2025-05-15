<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/cart_dto.php';

class CartBLL extends Database
{
    public function create_cart(CartDTO $cart)
    {
        $sql = "INSERT INTO Cart (CartID, UserID) VALUES ('{$cart->cartID}', '{$cart->userID}')";
        $result = $this->execute($sql);
        // $this->close();
        return $result === true && $this->getAffectedRows() === 1;
    }

    public function get_cart_by_user(string $userID): ?CartDTO
    {
        $sql = "SELECT * FROM Cart WHERE UserID = '{$userID}'";
        $result = $this->execute($sql);

        // Kiểm tra kết quả truy vấn
        if (!$result || !($result instanceof mysqli_result)) {
            error_log("Lỗi truy vấn Cart: $sql");
            return null;
        }

        $dto = null;
        if ($row = $result->fetch_assoc()) {
            $dto = new CartDTO($row['CartID'], $row['UserID']);
        }

        return $dto;
    }

    public function delete_cart(string $cartID): bool
    {
        // Escape dữ liệu để tránh SQL Injection (có thể đã xử lý ở Database::execute)
        $escapedID = $this->conn->real_escape_string($cartID);
        $sql = "DELETE FROM Cart WHERE CartID = '{$escapedID}'";

        $result = $this->execute($sql);
        return $result === true && $this->getAffectedRows() === 1;
    }
}
