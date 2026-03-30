<?php
require_once __DIR__ . '/../Database/database.php';
require_once __DIR__ . '/../DTO/order_detail_dto.php';

class OrderDetailBLL extends Database
{
    /**
     * Thêm một chi tiết đơn hàng mới.
     *
     * @param OrderDetailDTO $detail Đối tượng chứa thông tin chi tiết.
     * @return bool Trả về true nếu thêm thành công, ngược lại false.
     */
    public function add_detail(OrderDetailDTO $detail): bool
    {
        $sql = "INSERT INTO ORDERDETAILS (OrderID, CourseID, Price) VALUES (?, ?, ?)";

        $params = [
            $detail->orderID,
            $detail->courseID,
            is_numeric($detail->price) ? (float)$detail->price : 0,
        ];

        $result = $this->executePrepared($sql, $params);
        return ($result !== false) && ($this->getAffectedRows() === 1);
    }

    /**
     * Lấy danh sách chi tiết theo ID đơn hàng.
     *
     * @param string $orderID ID của đơn hàng.
     * @return array Mảng các đối tượng OrderDetailDTO.
     */
    public function get_details_by_order(string $orderID): array
    {
        $sql = "SELECT OrderID, CourseID, Price,
                       DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') AS created_at_formatted
                FROM ORDERDETAILS
                WHERE OrderID = ?";
        $params = [$orderID];
        $result = $this->executePrepared($sql, $params);
        $details = [];

        if ($result instanceof mysqli_result) {
            while ($row = $result->fetch_assoc()) {
                $details[] = new OrderDetailDTO(
                    $row['OrderID'],
                    $row['CourseID'],
                    isset($row['Price']) ? (float)$row['Price'] : 0.0,
                    $row['created_at_formatted'] ?? null
                );
            }
            $result->free();
        }
        return $details;
    }

    /**
     * Cập nhật một chi tiết đơn hàng.
     *
     * @param OrderDetailDTO $detail Đối tượng chứa thông tin chi tiết cần cập nhật.
     * @return bool Trả về true nếu cập nhật thành công, ngược lại false.
     */
    public function update_detail(OrderDetailDTO $detail): bool
    {
        if (!is_numeric($detail->price)) {
            error_log("Định dạng giá không hợp lệ cho update_detail: OrderID {$detail->orderID}, CourseID {$detail->courseID}, Price {$detail->price}");
            return false;
        }

        $sql = "UPDATE ORDERDETAILS SET Price = ? WHERE OrderID = ? AND CourseID = ?";

        $params = [
            (float)$detail->price,
            $detail->orderID,
            $detail->courseID,
        ];

        $result = $this->executePrepared($sql, $params);
        return ($result !== false);
    }

    /**
     * Lấy một chi tiết đơn hàng cụ thể.
     *
     * @param string $orderID ID của đơn hàng.
     * @param string $courseID ID của khóa học.
     * @return OrderDetailDTO|null Trả về đối tượng OrderDetailDTO nếu tìm thấy, ngược lại null.
     */
    public function get_detail(string $orderID, string $courseID): ?OrderDetailDTO
    {
        $sql = "SELECT OrderID, CourseID, Price,
                       DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') AS created_at_formatted
                FROM ORDERDETAILS
                WHERE OrderID = ? AND CourseID = ?";
        $params = [$orderID, $courseID];
        $result = $this->executePrepared($sql, $params);
        $dto = null;

        if ($result instanceof mysqli_result) {
            if ($row = $result->fetch_assoc()) {
                $dto = new OrderDetailDTO(
                    $row['OrderID'],
                    $row['CourseID'],
                    isset($row['Price']) ? (float)$row['Price'] : 0.0,
                    $row['created_at_formatted'] ?? null
                );
            }
            $result->free();
        }
        return $dto;
    }

    /**
     * Xóa một chi tiết đơn hàng.
     *
     * @param string $orderID ID của đơn hàng.
     * @param string $courseID ID của khóa học.
     * @return bool Trả về true nếu xóa thành công, ngược lại false.
     */
    public function delete_detail(string $orderID, string $courseID): bool
    {
        $sql = "DELETE FROM ORDERDETAILS WHERE OrderID = ? AND CourseID = ?";

        $params = [
            $orderID,
            $courseID,
        ];

        $result = $this->executePrepared($sql, $params);
        return ($result !== false) && ($this->getAffectedRows() === 1);
    }
}
