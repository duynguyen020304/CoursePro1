<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/order_detail_dto.php';
class OrderDetailBLL extends Database
{
    public function add_detail(OrderDetailDTO $detail)
    {
        $sql = "INSERT INTO OrderDetail (OrderID, CourseID, Price) VALUES ('{$detail->orderID}', '{$detail->courseID}', {$detail->price})";
        $result = $this->execute($sql);
        // $this->close();
        return $result === true && $this->getAffectedRows() === 1;
    }

    public function get_details_by_order(string $orderID): array
    {
        $sql = "SELECT * FROM OrderDetail WHERE OrderID = '{$orderID}'";
        $result = $this->execute($sql);
        $details = [];
        while ($row = $result->fetch_assoc()) {
            $details[] = new OrderDetailDTO($row['OrderID'], $row['CourseID'], (float)$row['Price']);
        }
        $this->close();
        return $details;
    }
}
