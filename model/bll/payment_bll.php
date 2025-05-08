<?php
require_once("../database.php");
require_once("../dto/payment_dto.php");
class PaymentBLL extends Database
{
    public function create_payment(PaymentDTO $p)
    {
        $method = $p->paymentMethod ? "'{$p->paymentMethod}'" : 'NULL';
        $status = $p->paymentStatus ? "'{$p->paymentStatus}'" : 'NULL';
        $date = $p->paymentDate->format('Y-m-d H:i:s');
        $sql = "INSERT INTO Payment (PaymentID, OrderID, PaymentDate, PaymentMethod, PaymentStatus, Amount) VALUES ('{$p->paymentID}', '{$p->orderID}', '{$date}', {$method}, {$status}, {$p->amount})";
        $this->execute($sql);
        $this->close();
    }

    public function get_payment_by_order(string $orderID): ?PaymentDTO
    {
        $sql = "SELECT * FROM Payment WHERE OrderID = '{$orderID}'";
        $result = $this->execute($sql);
        $dto = null;
        if ($row = $result->fetch_assoc()) {
            $dto = new PaymentDTO($row['PaymentID'], $row['OrderID'], new DateTime($row['PaymentDate']), $row['PaymentMethod'], $row['PaymentStatus'], (float)$row['Amount']);
        }
        $this->close();
        return $dto;
    }
}
