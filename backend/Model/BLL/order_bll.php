<?php
require_once __DIR__ . '/../Database/database.php';
require_once __DIR__ . '/../DTO/order_dto.php';

class OrderBLL extends Database
{
    /**
     * Tạo một đơn hàng mới.
     *
     * @param OrderDTO $order Đối tượng chứa thông tin đơn hàng.
     * @return bool Trả về true nếu tạo thành công, ngược lại false.
     */
    public function create_order(OrderDTO $order): bool
    {
        $sql = "INSERT INTO ORDERS (OrderID, UserID, OrderDate, TotalAmount) VALUES (?, ?, ?, ?)";

        $orderDateString = null;
        if ($order->orderDate instanceof DateTimeInterface) {
            $orderDateString = $order->orderDate->format('Y-m-d H:i:s');
        }

        $params = [
            $order->orderID,
            $order->userID,
            $orderDateString,
            is_numeric($order->totalAmount) ? (float)$order->totalAmount : 0,
        ];

        $result = $this->executePrepared($sql, $params);
        return ($result !== false) && ($this->getAffectedRows() === 1);
    }

    /**
     * Lấy thông tin đơn hàng bằng ID.
     *
     * @param string $orderID ID của đơn hàng.
     * @return OrderDTO|null Trả về đối tượng OrderDTO nếu tìm thấy, ngược lại null.
     */
    public function get_order_by_order_id(string $orderID): ?OrderDTO
    {
        $sql = "SELECT OrderID, UserID, OrderDate, TotalAmount,
                       DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') AS created_at_formatted
                FROM ORDERS
                WHERE OrderID = ?";
        $params = [$orderID];
        $result = $this->executePrepared($sql, $params);
        $dto = null;

        if ($result instanceof mysqli_result) {
            if ($row = $result->fetch_assoc()) {
                $orderDate = null;
                if (!empty($row['OrderDate'])) {
                    try {
                        $orderDate = new DateTime($row['OrderDate']);
                    } catch (Exception $e) {
                        error_log("Lỗi phân tích cú pháp OrderDate từ DB: " . $row['OrderDate'] . " - " . $e->getMessage());
                    }
                }
                $dto = new OrderDTO(
                    $row['OrderID'],
                    $row['UserID'],
                    $orderDate,
                    isset($row['TotalAmount']) ? (float)$row['TotalAmount'] : 0.0,
                    $row['created_at_formatted'] ?? null
                );
            }
            $result->free();
        }
        return $dto;
    }

    /**
     * Lấy danh sách đơn hàng theo ID người dùng.
     *
     * @param string $userID ID của người dùng.
     * @return array Mảng các đối tượng OrderDTO.
     */
    public function get_orders_by_user_id(string $userID): array
    {
        $sql = "SELECT OrderID, UserID, OrderDate, TotalAmount,
                       DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') AS created_at_formatted
                FROM ORDERS
                WHERE UserID = ?";
        $params = [$userID];
        $result = $this->executePrepared($sql, $params);
        $orders = [];

        if ($result instanceof mysqli_result) {
            while ($row = $result->fetch_assoc()) {
                $orderDate = null;
                if (!empty($row['OrderDate'])) {
                    try {
                        $orderDate = new DateTime($row['OrderDate']);
                    } catch (Exception $e) {
                        error_log("Lỗi phân tích cú pháp OrderDate từ DB trong get_orders_by_user: " . $row['OrderDate'] . " - " . $e->getMessage());
                    }
                }
                $orders[] = new OrderDTO(
                    $row['OrderID'],
                    $row['UserID'],
                    $orderDate,
                    isset($row['TotalAmount']) ? (float)$row['TotalAmount'] : 0.0,
                    $row['created_at_formatted'] ?? null
                );
            }
            $result->free();
        }
        return $orders;
    }

    /**
     * Cập nhật thông tin một đơn hàng.
     *
     * @param OrderDTO $order Đối tượng chứa thông tin đơn hàng cần cập nhật.
     * @return bool Trả về true nếu cập nhật thành công, ngược lại false.
     */
    public function update_order(OrderDTO $order): bool
    {
        $sql = "UPDATE ORDERS SET UserID = ?, OrderDate = ?, TotalAmount = ? WHERE OrderID = ?";

        $orderDateString = null;
        if ($order->orderDate instanceof DateTimeInterface) {
            $orderDateString = $order->orderDate->format('Y-m-d H:i:s');
        }

        $params = [
            $order->userID,
            $orderDateString,
            is_numeric($order->totalAmount) ? (float)$order->totalAmount : 0,
            $order->orderID,
        ];

        $result = $this->executePrepared($sql, $params);
        return ($result !== false);
    }

    /**
     * Xóa một đơn hàng.
     *
     * @param string $orderID ID của đơn hàng cần xóa.
     * @return bool Trả về true nếu xóa thành công, ngược lại false.
     */
    public function delete_order(string $orderID): bool
    {
        $sql = "DELETE FROM ORDERS WHERE OrderID = ?";
        $params = [$orderID];
        $result = $this->executePrepared($sql, $params);
        return ($result !== false) && ($this->getAffectedRows() === 1);
    }
}
