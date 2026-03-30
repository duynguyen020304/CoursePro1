<?php
require_once __DIR__ . '/../Database/database.php';
require_once __DIR__ . '/../DTO/payment_dto.php';

class PaymentBLL extends Database
{
    /**
     * Tạo một thanh toán mới.
     *
     * @param PaymentDTO $p Đối tượng chứa thông tin thanh toán.
     * @return bool Trả về true nếu tạo thành công, ngược lại false.
     */
    public function create_payment(PaymentDTO $p): bool
    {
        $sql = "INSERT INTO PAYMENTS (PaymentID, OrderID, PaymentDate, PaymentMethod, PaymentStatus, Amount) 
                VALUES (?, ?, ?, ?, ?, ?)";

        $paymentDateString = null;
        if ($p->paymentDate instanceof DateTimeInterface) {
            $paymentDateString = $p->paymentDate->format('Y-m-d H:i:s');
        }

        $params = [
            $p->paymentID,
            $p->orderID,
            $paymentDateString,
            $p->paymentMethod,
            $p->paymentStatus,
            is_numeric($p->amount) ? (float)$p->amount : 0,
        ];

        $result = $this->executePrepared($sql, $params);
        return ($result !== false) && ($this->getAffectedRows() === 1);
    }

    /**
     * Lấy thông tin thanh toán theo ID đơn hàng.
     *
     * @param string $orderID ID của đơn hàng.
     * @return PaymentDTO|null Trả về đối tượng PaymentDTO nếu tìm thấy, ngược lại null.
     */
    public function get_payment_by_order_id(string $orderID): ?PaymentDTO
    {
        $sql = "SELECT PaymentID, OrderID, PaymentDate, PaymentMethod, PaymentStatus, Amount,
                       DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') AS created_at_formatted
                FROM PAYMENTS
                WHERE OrderID = ?";
        $params = [$orderID];
        $result = $this->executePrepared($sql, $params);
        $dto = null;

        if ($result instanceof mysqli_result) {
            if ($row = $result->fetch_assoc()) {
                $paymentDate = null;
                if (!empty($row['PaymentDate'])) {
                    try {
                        $paymentDate = new DateTime($row['PaymentDate']);
                    } catch (Exception $e) {
                        error_log("Lỗi phân tích cú pháp PaymentDate từ DB: " . $row['PaymentDate'] . " - " . $e->getMessage());
                    }
                }
                $dto = new PaymentDTO(
                    $row['PaymentID'],
                    $row['OrderID'],
                    $paymentDate,
                    $row['PaymentMethod'],
                    $row['PaymentStatus'],
                    isset($row['Amount']) ? (float)$row['Amount'] : 0.0,
                    $row['created_at_formatted'] ?? null
                );
            }
            $result->free();
        }
        return $dto;
    }

    /**
     * Lấy thông tin thanh toán theo ID của nó.
     *
     * @param string $paymentID ID của thanh toán.
     * @return PaymentDTO|null Trả về đối tượng PaymentDTO nếu tìm thấy, ngược lại null.
     */
    public function get_payment_by_id(string $paymentID): ?PaymentDTO
    {
        $sql = "SELECT PaymentID, OrderID, PaymentDate, PaymentMethod, PaymentStatus, Amount,
                       DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') AS created_at_formatted
                FROM PAYMENTS
                WHERE PaymentID = ?";
        $params = [$paymentID];
        $result = $this->executePrepared($sql, $params);
        $dto = null;

        if ($result instanceof mysqli_result) {
            if ($row = $result->fetch_assoc()) {
                $paymentDate = null;
                if (!empty($row['PaymentDate'])) {
                    try {
                        $paymentDate = new DateTime($row['PaymentDate']);
                    } catch (Exception $e) {
                        error_log("Lỗi phân tích cú pháp PaymentDate từ DB: " . $row['PaymentDate'] . " - " . $e->getMessage());
                    }
                }
                $dto = new PaymentDTO(
                    $row['PaymentID'],
                    $row['OrderID'],
                    $paymentDate,
                    $row['PaymentMethod'],
                    $row['PaymentStatus'],
                    isset($row['Amount']) ? (float)$row['Amount'] : 0.0,
                    $row['created_at_formatted'] ?? null
                );
            }
            $result->free();
        }
        return $dto;
    }

    /**
     * Cập nhật thông tin thanh toán.
     *
     * @param PaymentDTO $p Đối tượng chứa thông tin thanh toán cần cập nhật.
     * @return bool Trả về true nếu cập nhật thành công, ngược lại false.
     */
    public function update_payment(PaymentDTO $p): bool
    {
        $sql = "UPDATE PAYMENTS SET OrderID = ?, PaymentDate = ?, PaymentMethod = ?, PaymentStatus = ?, Amount = ? 
                WHERE PaymentID = ?";

        $paymentDateString = null;
        if ($p->paymentDate instanceof DateTimeInterface) {
            $paymentDateString = $p->paymentDate->format('Y-m-d H:i:s');
        }

        $params = [
            $p->orderID,
            $paymentDateString,
            $p->paymentMethod,
            $p->paymentStatus,
            is_numeric($p->amount) ? (float)$p->amount : 0,
            $p->paymentID,
        ];

        $result = $this->executePrepared($sql, $params);
        return ($result !== false);
    }

    /**
     * Xóa một thanh toán.
     *
     * @param string $paymentID ID của thanh toán cần xóa.
     * @return bool Trả về true nếu xóa thành công, ngược lại false.
     */
    public function delete_payment(string $paymentID): bool
    {
        $sql = "DELETE FROM PAYMENTS WHERE PaymentID = ?";
        $params = [$paymentID];
        $result = $this->executePrepared($sql, $params);
        return ($result !== false) && ($this->getAffectedRows() === 1);
    }
}
