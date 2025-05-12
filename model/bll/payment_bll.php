<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/payment_dto.php';

class PaymentBLL extends Database
{
    /**
     * Tạo mới thanh toán
     *
     * @param PaymentDTO $p
     * @return bool
     */
    public function create_payment(PaymentDTO $p): bool
    {
        $method = $p->paymentMethod ? "'{$p->paymentMethod}'" : 'NULL';
        $status = $p->paymentStatus ? "'{$p->paymentStatus}'" : 'NULL';
        $date   = $p->paymentDate->format('Y-m-d H:i:s');
        $sql = "INSERT INTO Payment (PaymentID, OrderID, PaymentDate, PaymentMethod, PaymentStatus, Amount) \
                VALUES ('{$p->paymentID}', '{$p->orderID}', '{$date}', {$method}, {$status}, {$p->amount})";
        $res = $this->execute($sql);
        return $res === true && $this->getAffectedRows() === 1;
    }

    /**
     * Lấy thanh toán theo OrderID
     *
     * @param string $orderID
     * @return PaymentDTO|null
     */
    public function get_payment_by_order(string $orderID): ?PaymentDTO
    {
        $sql = "SELECT * FROM Payment WHERE OrderID = '{$orderID}'";
        $result = $this->execute($sql);
        $dto = null;
        if ($result instanceof mysqli_result && $row = $result->fetch_assoc()) {
            $dto = new PaymentDTO(
                $row['PaymentID'],
                $row['OrderID'],
                new DateTime($row['PaymentDate']),
                $row['PaymentMethod'],
                $row['PaymentStatus'],
                (float)$row['Amount']
            );
        }
        return $dto;
    }

    /**
     * Cập nhật thông tin thanh toán
     *
     * @param PaymentDTO $p
     * @return bool
     */
    public function update_payment(PaymentDTO $p): bool
    {
        $method = $p->paymentMethod ? "'{$p->paymentMethod}'" : 'NULL';
        $status = $p->paymentStatus ? "'{$p->paymentStatus}'" : 'NULL';
        $date   = $p->paymentDate->format('Y-m-d H:i:s');
        $sql = "UPDATE Payment SET \
                OrderID = '{$p->orderID}', \
                PaymentDate = '{$date}', \
                PaymentMethod = {$method}, \
                PaymentStatus = {$status}, \
                Amount = {$p->amount} \
                WHERE PaymentID = '{$p->paymentID}'";
        $res = $this->execute($sql);
        return $res === true && $this->getAffectedRows() === 1;
    }

    /**
     * Xóa thanh toán theo PaymentID
     *
     * @param string $paymentID
     * @return bool
     */
    public function delete_payment(string $paymentID): bool
    {
        $sql = "DELETE FROM Payment WHERE PaymentID = '{$paymentID}'";
        $res = $this->execute($sql);
        return $res === true && $this->getAffectedRows() === 1;
    }
}
