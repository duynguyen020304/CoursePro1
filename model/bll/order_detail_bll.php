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
        // Lấy giá trị từ OrderDetailDTO
        $orderID = $detail->orderID;
        $courseID = $detail->courseID;
        $price = is_numeric($detail->price) ? (float)$detail->price : 0;  // Đảm bảo giá trị price hợp lệ

        // Tạo câu lệnh SQL
        $sql = "INSERT INTO OrderDetail (OrderID, CourseID, Price) 
            VALUES ('{$orderID}', '{$courseID}', {$price})";

        // Thực thi câu lệnh SQL
        $result = $this->execute($sql);

        // Kiểm tra kết quả thực thi
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
        // Kiểm tra giá trị Price
        if (!is_numeric($detail->price) || $detail->price <= 0) {
            return false;  // Trả về false nếu price không hợp lệ
        }

        $sql = "UPDATE OrderDetail SET Price = {$detail->price}
            WHERE OrderID = '{$detail->orderID}' AND CourseID = '{$detail->courseID}'";

        $result = $this->execute($sql);
        return $result === true;
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
        // Kiểm tra xem bản ghi có tồn tại không
        $sql_check = "SELECT * FROM OrderDetail WHERE OrderID = '{$orderID}' AND CourseID = '{$courseID}'";
        $check_result = $this->execute($sql_check);
        if ($check_result->num_rows === 0) {
            return false;  // Trả về false nếu không có bản ghi nào
        }

        $sql = "DELETE FROM OrderDetail WHERE OrderID = '{$orderID}' AND CourseID = '{$courseID}'";
        $result = $this->execute($sql);
        return $result === true && $this->getAffectedRows() === 1;
    }
}
