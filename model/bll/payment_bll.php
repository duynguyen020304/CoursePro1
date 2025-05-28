<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/payment_dto.php';

class PaymentBLL extends Database
{
    public function create_payment(PaymentDTO $p): bool
    {
        $sql = "BEGIN PAYMENT_PKG.CREATE_PAYMENT_PROC(:paymentID, :orderID, :paymentDate, :paymentMethod, :paymentStatus, :amount); END;";

        $bindParams = [
            ':paymentID'     => $p->paymentID,
            ':orderID'       => $p->orderID,
            ':paymentDate'   => $p->paymentDate instanceof DateTimeInterface ? $p->paymentDate->format('Y-m-d H:i:s.u') : null,
            ':paymentMethod' => $p->paymentMethod,
            ':paymentStatus' => $p->paymentStatus,
            ':amount'        => is_numeric($p->amount) ? (float)$p->amount : 0,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function get_payment_by_order_id(string $orderID): ?PaymentDTO
    {
        $sql = "BEGIN :result_cursor := PAYMENT_PKG.GET_PAYMENT_BY_ORDER_ID_FUNC(:orderID_param); END;";
        $bindParams = [
            ':orderID_param' => $orderID
        ];

        $dto = null;
        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[PaymentBLL] Failed to create new cursor for GET_PAYMENT_BY_ORDER_ID_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return null;
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[PaymentBLL] OCI Parse failed for GET_PAYMENT_BY_ORDER_ID_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return null;
        }

        @oci_bind_by_name($parsed_stid, ':orderID_param', $bindParams[':orderID_param']);
        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[PaymentBLL] OCI Execute failed for GET_PAYMENT_BY_ORDER_ID_FUNC block. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[PaymentBLL] OCI Execute failed for result cursor of GET_PAYMENT_BY_ORDER_ID_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }
        $stid_cursor = $out_cursor;

        if ($stid_cursor) {
            if (($row = @oci_fetch_array($stid_cursor, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $paymentDate = null;
                if (!empty($row['PAYMENT_DATE_FORMATTED'])) {
                    try {
                        $paymentDate = new DateTime($row['PAYMENT_DATE_FORMATTED']);
                    } catch (Exception $e) {
                        error_log("Error parsing PAYMENT_DATE_FORMATTED from DB (via PL/SQL): " . $row['PAYMENT_DATE_FORMATTED'] . " - " . $e->getMessage());
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
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid_cursor);
        }
        @oci_free_statement($parsed_stid);

        return $dto;
    }

    public function get_payment_by_id(string $paymentID): ?PaymentDTO
    {
        $sql = "BEGIN :result_cursor := PAYMENT_PKG.GET_PAYMENT_BY_ID_FUNC(:paymentID_param); END;";
        $bindParams = [
            ':paymentID_param' => $paymentID
        ];

        $dto = null;
        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[PaymentBLL] Failed to create new cursor for GET_PAYMENT_BY_ID_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return null;
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[PaymentBLL] OCI Parse failed for GET_PAYMENT_BY_ID_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return null;
        }

        @oci_bind_by_name($parsed_stid, ':paymentID_param', $bindParams[':paymentID_param']);
        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[PaymentBLL] OCI Execute failed for GET_PAYMENT_BY_ID_FUNC block. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[PaymentBLL] OCI Execute failed for result cursor of GET_PAYMENT_BY_ID_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }
        $stid_cursor = $out_cursor;

        if ($stid_cursor) {
            if (($row = @oci_fetch_array($stid_cursor, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $paymentDate = null;
                if (!empty($row['PAYMENT_DATE_FORMATTED'])) {
                    try {
                        $paymentDate = new DateTime($row['PAYMENT_DATE_FORMATTED']);
                    } catch (Exception $e) {
                        error_log("Error parsing PAYMENT_DATE_FORMATTED from DB (via PL/SQL): " . $row['PAYMENT_DATE_FORMATTED'] . " - " . $e->getMessage());
                    }
                }
                $dto = new PaymentDTO(
                    $row['PAYMENTID'],
                    $row['ORDERID'],
                    $paymentDate,
                    $row['PAYMENTMETHOD'],
                    $row['PAYMENTSTATUS'],
                    isset($row['AMOUNT']) ? (float)$row['AMOUNT'] : 0.0,
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid_cursor);
        }
        @oci_free_statement($parsed_stid);

        return $dto;
    }

    public function update_payment(PaymentDTO $p): bool
    {
        $sql = "BEGIN PAYMENT_PKG.UPDATE_PAYMENT_PROC(:paymentID_where, :orderID, :paymentDate, :paymentMethod, :paymentStatus, :amount); END;";

        $bindParams = [
            ':paymentID_where' => $p->paymentID,
            ':orderID'        => $p->orderID,
            ':paymentDate'    => $p->paymentDate instanceof DateTimeInterface ? $p->paymentDate->format('Y-m-d H:i:s.u') : null,
            ':paymentMethod'  => $p->paymentMethod,
            ':paymentStatus'  => $p->paymentStatus,
            ':amount'         => is_numeric($p->amount) ? (float)$p->amount : 0,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function delete_payment(string $paymentID): bool
    {
        $sql = "BEGIN PAYMENT_PKG.DELETE_PAYMENT_PROC(:paymentID); END;";
        $bindParams = [':paymentID' => $paymentID];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }
}
?>