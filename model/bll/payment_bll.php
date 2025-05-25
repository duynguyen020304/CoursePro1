<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/payment_dto.php';

class PaymentBLL extends Database
{
    public function create_payment(PaymentDTO $p): bool
    {
        // For inserting, format the DateTime object to include fractional seconds for TIMESTAMP
        $sql = "INSERT INTO PAYMENT (PaymentID, OrderID, PaymentDate, PaymentMethod, PaymentStatus, Amount)
                VALUES (:paymentID, :orderID, TO_TIMESTAMP(:paymentDate, 'YYYY-MM-DD HH24:MI:SS.FF6'), :paymentMethod, :paymentStatus, :amount)";

        $bindParams = [
            ':paymentID'     => $p->paymentID,
            ':orderID'       => $p->orderID,
            // Format to include fractional seconds for TIMESTAMP
            ':paymentDate'   => $p->paymentDate instanceof DateTimeInterface ? $p->paymentDate->format('Y-m-d H:i:s.u') : null,
            ':paymentMethod' => $p->paymentMethod,
            ':paymentStatus'   => $p->paymentStatus,
            ':amount'        => is_numeric($p->amount) ? (float)$p->amount : 0,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false) && ($this->getAffectedRows() === 1);
    }

    public function get_payment_by_order(string $orderID): ?PaymentDTO
    {
        // Format PaymentDate and created_at using TO_CHAR for consistent retrieval
        $sql = "SELECT PaymentID, OrderID, 
                       TO_CHAR(PaymentDate, 'YYYY-MM-DD HH24:MI:SS.FF6') AS payment_date_formatted,
                       PaymentMethod, PaymentStatus, Amount, 
                       TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
                FROM PAYMENT
                WHERE OrderID = :orderID_param";
        $bindParams = [':orderID_param' => $orderID];

        $stid = $this->executePrepared($sql, $bindParams);
        $dto = null;

        if ($stid) {
            if (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $paymentDate = null;
                // Parse the formatted string back into a DateTime object
                if (!empty($row['PAYMENT_DATE_FORMATTED'])) {
                    try {
                        $paymentDate = new DateTime($row['PAYMENT_DATE_FORMATTED']);
                    } catch (Exception $e) {
                        error_log("Error parsing PAYMENT_DATE_FORMATTED from DB: " . $row['PAYMENT_DATE_FORMATTED'] . " - " . $e->getMessage());
                        $paymentDate = null;
                    }
                }
                $dto = new PaymentDTO(
                    $row['PAYMENTID'],
                    $row['ORDERID'],
                    $paymentDate, // Use the parsed DateTime object
                    $row['PAYMENTMETHOD'],
                    $row['PAYMENTSTATUS'],
                    isset($row['AMOUNT']) ? (float)$row['AMOUNT'] : 0.0,
                    $row['CREATED_AT_FORMATTED'] ?? null // Use the formatted alias
                );
            }
            @oci_free_statement($stid);
        }
        return $dto;
    }

    public function get_payment_by_id(string $paymentID): ?PaymentDTO
    {
        // Format PaymentDate and created_at using TO_CHAR for consistent retrieval
        $sql = "SELECT PaymentID, OrderID, 
                       TO_CHAR(PaymentDate, 'YYYY-MM-DD HH24:MI:SS.FF6') AS payment_date_formatted,
                       PaymentMethod, PaymentStatus, Amount, 
                       TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
                FROM PAYMENT
                WHERE PaymentID = :paymentID_param";
        $bindParams = [':paymentID_param' => $paymentID];

        $stid = $this->executePrepared($sql, $bindParams);
        $dto = null;

        if ($stid) {
            if (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $paymentDate = null;
                // Parse the formatted string back into a DateTime object
                if (!empty($row['PAYMENT_DATE_FORMATTED'])) {
                    try {
                        $paymentDate = new DateTime($row['PAYMENT_DATE_FORMATTED']);
                    } catch (Exception $e) {
                        error_log("Error parsing PAYMENT_DATE_FORMATTED from DB: " . $row['PAYMENT_DATE_FORMATTED'] . " - " . $e->getMessage());
                    }
                }
                $dto = new PaymentDTO(
                    $row['PAYMENTID'],
                    $row['ORDERID'],
                    $paymentDate, // Use the parsed DateTime object
                    $row['PAYMENTMETHOD'],
                    $row['PAYMENTSTATUS'],
                    isset($row['AMOUNT']) ? (float)$row['AMOUNT'] : 0.0,
                    $row['CREATED_AT_FORMATTED'] ?? null // Use the formatted alias
                );
            }
            @oci_free_statement($stid);
        }
        return $dto;
    }

    public function update_payment(PaymentDTO $p): bool
    {
        // For updating, format the DateTime object to include fractional seconds for TIMESTAMP
        $sql = "UPDATE PAYMENT SET
                OrderID = :orderID,
                PaymentDate = TO_TIMESTAMP(:paymentDate, 'YYYY-MM-DD HH24:MI:SS.FF6'),
                PaymentMethod = :paymentMethod,
                PaymentStatus = :paymentStatus,
                Amount = :amount
                WHERE PaymentID = :paymentID_where";

        $bindParams = [
            ':orderID'        => $p->orderID,
            // Format to include fractional seconds for TIMESTAMP
            ':paymentDate'    => $p->paymentDate instanceof DateTimeInterface ? $p->paymentDate->format('Y-m-d H:i:s.u') : null,
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