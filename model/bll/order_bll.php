<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/order_dto.php';
class OrderBLL extends Database
{
    public function create_order(OrderDTO $order)
    {
        $date = $order->orderDate->format('Y-m-d H:i:s');
        $sql = "INSERT INTO Orders (OrderID, UserID, OrderDate, TotalAmount) VALUES ('{$order->orderID}', '{$order->userID}', '{$date}', {$order->totalAmount})";
        $result = $this->execute($sql);
        // $this->close();
        return $result === true && $this->getAffectedRows() === 1;
    }

    public function get_order(string $orderID): ?OrderDTO
    {
        $sql = "SELECT * FROM Orders WHERE OrderID = '{$orderID}'";
        $result = $this->execute($sql);
        $dto = null;
        if ($row = $result->fetch_assoc()) {
            $dto = new OrderDTO($row['OrderID'], $row['UserID'], new DateTime($row['OrderDate']), (float)$row['TotalAmount']);
        }
        $this->close();
        return $dto;
    }

    public function get_orders_by_user(string $userID): array
    {
        $sql = "SELECT * FROM Orders WHERE UserID = '{$userID}' ORDER BY OrderDate DESC";
        $result = $this->execute($sql);
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = new OrderDTO($row['OrderID'], $row['UserID'], new DateTime($row['OrderDate']), (float)$row['TotalAmount']);
        }
        $this->close();
        return $orders;
    }
}
