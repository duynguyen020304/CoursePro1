<?php
require_once("../database.php");
require_once("../dto/order_detail_dto.php");
class OrderDetailBLL extends Database
{
    public function add_detail(OrderDetailDTO $detail)
    {
        $sql = "INSERT INTO OrderDetail (OrderID, CourseID, Price) VALUES ('{$detail->orderID}', '{$detail->courseID}', {$detail->price})";
        $this->execute($sql);
        $this->close();
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
