<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/order_dto.php';

class OrderBLL extends Database
{
    /**
     * Tạo mới đơn hàng
     *
     * @param OrderDTO $order
     * @return bool
     */
    public function create_order(OrderDTO $order): bool
    {
        $date = $order->orderDate->format('Y-m-d H:i:s');
        $sql = "INSERT INTO Orders
                (OrderID, UserID, OrderDate, TotalAmount)
                VALUES
                ('{$order->orderID}', '{$order->userID}', '{$date}', {$order->totalAmount})";
        $result = $this->execute($sql);
        return $result === true && $this->getAffectedRows() === 1;
    }

    /**
     * Lấy đơn hàng theo ID
     *
     * @param string $orderID
     * @return OrderDTO|null
     */
    public function get_order(string $orderID): ?OrderDTO
    {
        $sql = "SELECT * FROM Orders WHERE OrderID = '{$orderID}'";
        $result = $this->execute($sql);
        $dto = null;
        if ($result instanceof mysqli_result && $row = $result->fetch_assoc()) {
            $dto = new OrderDTO(
                $row['OrderID'],
                $row['UserID'],
                new DateTime($row['OrderDate']),
                (float)$row['TotalAmount']
            );
        }
        return $dto;
    }

    /**
     * Lấy danh sách đơn hàng của một user
     *
     * @param string $userID
     * @return OrderDTO[]
     */
    public function get_orders_by_user(string $userID): array
    {
        $sql = "SELECT * FROM Orders WHERE UserID = '{$userID}' ORDER BY OrderDate DESC";
        $result = $this->execute($sql);
        $orders = [];
        if ($result instanceof mysqli_result) {
            while ($row = $result->fetch_assoc()) {
                $orders[] = new OrderDTO(
                    $row['OrderID'],
                    $row['UserID'],
                    new DateTime($row['OrderDate']),
                    (float)$row['TotalAmount']
                );
            }
        }
        return $orders;
    }

    /**
     * Cập nhật đơn hàng
     *
     * @param OrderDTO $order
     * @return bool
     */
    public function update_order(OrderDTO $order): bool
    {
        $date = $order->orderDate->format('Y-m-d H:i:s');
        $sql = "UPDATE Orders SET
                UserID = '{$order->userID}',
                OrderDate = '{$date}',
                TotalAmount = {$order->totalAmount}
                WHERE OrderID = '{$order->orderID}'";
        $result = $this->execute($sql);
        return $result === true && $this->getAffectedRows() === 1;
    }

    /**
     * Xóa đơn hàng theo ID
     *
     * @param string $orderID
     * @return bool
     */
    public function delete_order(string $orderID): bool
    {
        $sql = "DELETE FROM Orders WHERE OrderID = '{$orderID}'";
        $result = $this->execute($sql);
        return $result === true && $this->getAffectedRows() === 1;
    }
}
