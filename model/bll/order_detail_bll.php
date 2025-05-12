<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/order_detail_dto.php';

class OrderDetailBLL extends Database
{
    /**
     * Thêm chi tiết đơn hàng
     *
     * @param OrderDetailDTO $detail
     * @return bool
     */
    public function add_detail(OrderDetailDTO $detail): bool
    {
        $sql = "INSERT INTO OrderDetail (OrderID, CourseID, Price) \
                VALUES ('{$detail->orderID}', '{$detail->courseID}', {$detail->price})";
        $result = $this->execute($sql);
        return $result === true && $this->getAffectedRows() === 1;
    }

    /**
     * Lấy danh sách chi tiết cho một đơn hàng
     *
     * @param string $orderID
     * @return OrderDetailDTO[]
     */
    public function get_details_by_order(string $orderID): array
    {
        $sql = "SELECT * FROM OrderDetail WHERE OrderID = '{$orderID}'";
        $result = $this->execute($sql);
        $details = [];
        if ($result instanceof mysqli_result) {
            while ($row = $result->fetch_assoc()) {
                $details[] = new OrderDetailDTO(
                    $row['OrderID'],
                    $row['CourseID'],
                    (float)$row['Price']
                );
            }
        }
        return $details;
    }

    /**
     * Cập nhật chi tiết đơn hàng
     *
     * @param OrderDetailDTO $detail
     * @return bool
     */
    public function update_detail(OrderDetailDTO $detail): bool
    {
        $sql = "UPDATE OrderDetail SET \
                Price = {$detail->price} \
                WHERE OrderID = '{$detail->orderID}' AND CourseID = '{$detail->courseID}'";
        $result = $this->execute($sql);
        return $result === true && $this->getAffectedRows() === 1;
    }

    /**
     * Xóa chi tiết đơn hàng
     *
     * @param string $orderID
     * @param string $courseID
     * @return bool
     */
    public function delete_detail(string $orderID, string $courseID): bool
    {
        $sql = "DELETE FROM OrderDetail \
                WHERE OrderID = '{$orderID}' AND CourseID = '{$courseID}'";
        $result = $this->execute($sql);
        return $result === true && $this->getAffectedRows() === 1;
    }
}
