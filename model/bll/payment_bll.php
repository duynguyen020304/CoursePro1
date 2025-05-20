<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/payment_dto.php';

class PaymentBLL extends Database
{
    public function create_payment(PaymentDTO $p): bool
    {
        $sql = "INSERT INTO PAYMENT (PaymentID, OrderID, PaymentDate, PaymentMethod, PaymentStatus, Amount) 
                VALUES (:paymentID, :orderID, TO_TIMESTAMP(:paymentDate, 'YYYY-MM-DD HH24:MI:SS'), :paymentMethod, :paymentStatus, :amount)";

        $bindParams = [
            ':paymentID'     => $p->paymentID,
            ':orderID'       => $p->orderID,
            ':paymentDate'   => $p->paymentDate instanceof DateTimeInterface ? $p->paymentDate->format('Y-m-d H:i:s') : null,
            ':paymentMethod' => $p->paymentMethod,
            ':paymentStatus'   => $p->paymentStatus,
            ':amount'        => is_numeric($p->amount) ? (float)$p->amount : 0,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false) && ($this->getAffectedRows() === 1);
    }

    public function get_payment_by_order(string $orderID): ?PaymentDTO
    {
        $sql = "SELECT PaymentID, OrderID, PaymentDate, PaymentMethod, PaymentStatus, Amount, created_at 
                FROM PAYMENT 
                WHERE OrderID = :orderID_param";
        $bindParams = [':orderID_param' => $orderID];

        $stid = $this->executePrepared($sql, $bindParams);
        $dto = null;

        if ($stid) {
            if (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $paymentDate = null;
                if (!empty($row['PAYMENTDATE'])) {
                    try {
                        $paymentDate = new DateTime($row['PAYMENTDATE']);
                    } catch (Exception $e) {
                        error_log("Error parsing PAYMENTDATE from DB: " . $row['PAYMENTDATE'] . " - " . $e->getMessage());
                        $paymentDate = null;
                    }
                }
                $dto = new PaymentDTO(
                    $row['PAYMENTID'],
                    $row['ORDERID'],
                    $paymentDate,
                    $row['PAYMENTMETHOD'],
                    $row['PAYMENTSTATUS'],
                    isset($row['AMOUNT']) ? (float)$row['AMOUNT'] : 0.0,
                    $row['CREATED_AT'] ?? null
                );
            }
            @oci_free_statement($stid);
        }
        return $dto;
    }

    public function get_payment_by_id(string $paymentID): ?PaymentDTO
    {
        $sql = "SELECT PaymentID, OrderID, PaymentDate, PaymentMethod, PaymentStatus, Amount, created_at 
                FROM PAYMENT 
                WHERE PaymentID = :paymentID_param";
        $bindParams = [':paymentID_param' => $paymentID];

        $stid = $this->executePrepared($sql, $bindParams);
        $dto = null;

        if ($stid) {
            if (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $paymentDate = null;
                if (!empty($row['PAYMENTDATE'])) {
                    try {
                        $paymentDate = new DateTime($row['PAYMENTDATE']);
                    } catch (Exception $e) {
                        error_log("Error parsing PAYMENTDATE from DB: " . $row['PAYMENTDATE'] . " - " . $e->getMessage());
                    }
                }
                $dto = new PaymentDTO(
                    $row['PAYMENTID'],
                    $row['ORDERID'],
                    $paymentDate,
                    $row['PAYMENTMETHOD'],
                    $row['PAYMENTSTATUS'],
                    isset($row['AMOUNT']) ? (float)$row['AMOUNT'] : 0.0,
                    $row['CREATED_AT'] ?? null
                );
            }
            @oci_free_statement($stid);
        }
        return $dto;
    }

    public function update_payment(PaymentDTO $p): bool
    {
        $sql = "UPDATE PAYMENT SET 
                OrderID = :orderID, 
                PaymentDate = TO_TIMESTAMP(:paymentDate, 'YYYY-MM-DD HH24:MI:SS'), 
                PaymentMethod = :paymentMethod, 
                PaymentStatus = :paymentStatus, 
                Amount = :amount 
                WHERE PaymentID = :paymentID_where";

        $bindParams = [
            ':orderID'        => $p->orderID,
            ':paymentDate'    => $p->paymentDate instanceof DateTimeInterface ? $p->paymentDate->format('Y-m-d H:i:s') : null,
            ':paymentMethod'  => $p->paymentMethod,
            ':paymentStatus'  => $p->paymentStatus,
            ':amount'         => is_numeric($p->amount) ? (float)$p->amount : 0,
            ':paymentID_where' => $p->paymentID,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function delete_payment(string $paymentID): bool
    {
        $sql = "DELETE FROM PAYMENT WHERE PaymentID = :paymentID";
        $bindParams = [':paymentID' => $paymentID];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false) && ($this->getAffectedRows() === 1);
    }
}
?>