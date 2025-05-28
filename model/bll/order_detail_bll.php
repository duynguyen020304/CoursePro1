<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/order_detail_dto.php';

class OrderDetailBLL extends Database
{
    public function add_detail(OrderDetailDTO $detail): bool
    {
        $sql = "BEGIN ORDER_DETAIL_PKG.ADD_DETAIL_PROC(:orderID, :courseID, :price); END;";

        $bindParams = [
            ':orderID'  => $detail->orderID,
            ':courseID' => $detail->courseID,
            ':price'    => is_numeric($detail->price) ? (float)$detail->price : 0,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function get_details_by_order(string $orderID): array
    {
        $sql = "BEGIN :result_cursor := ORDER_DETAIL_PKG.GET_DETAILS_BY_ORDER_FUNC(:orderID_param); END;";
        $bindParams = [
            ':orderID_param' => $orderID
        ];

        $details = [];
        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[OrderDetailBLL] Failed to create new cursor for GET_DETAILS_BY_ORDER_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return [];
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[OrderDetailBLL] OCI Parse failed for GET_DETAILS_BY_ORDER_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return [];
        }

        @oci_bind_by_name($parsed_stid, ':orderID_param', $bindParams[':orderID_param']);
        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[OrderDetailBLL] OCI Execute failed for GET_DETAILS_BY_ORDER_FUNC block. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[OrderDetailBLL] OCI Execute failed for result cursor of GET_DETAILS_BY_ORDER_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }
        $stid_cursor = $out_cursor;

        if ($stid_cursor) {
            while (($row = @oci_fetch_array($stid_cursor, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $details[] = new OrderDetailDTO(
                    $row['ORDERID'],
                    $row['COURSEID'],
                    isset($row['PRICE']) ? (float)$row['PRICE'] : 0.0,
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid_cursor);
        }
        @oci_free_statement($parsed_stid);

        return $details;
    }

    public function update_detail(OrderDetailDTO $detail): bool
    {
        if (!is_numeric($detail->price)) {
            error_log("Invalid price format for update_detail: OrderID {$detail->orderID}, CourseID {$detail->courseID}, Price {$detail->price}");
            return false;
        }

        $sql = "BEGIN ORDER_DETAIL_PKG.UPDATE_DETAIL_PROC(:orderID_where, :courseID_where, :price); END;";

        $bindParams = [
            ':orderID_where'  => $detail->orderID,
            ':courseID_where' => $detail->courseID,
            ':price'          => (float)$detail->price,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function get_detail(string $orderID, string $courseID): ?OrderDetailDTO
    {
        $sql = "BEGIN :result_cursor := ORDER_DETAIL_PKG.GET_DETAIL_FUNC(:orderID_param, :courseID_param); END;";
        $bindParams = [
            ':orderID_param'  => $orderID,
            ':courseID_param' => $courseID,
        ];

        $dto = null;
        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[OrderDetailBLL] Failed to create new cursor for GET_DETAIL_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return null;
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[OrderDetailBLL] OCI Parse failed for GET_DETAIL_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return null;
        }

        @oci_bind_by_name($parsed_stid, ':orderID_param', $bindParams[':orderID_param']);
        @oci_bind_by_name($parsed_stid, ':courseID_param', $bindParams[':courseID_param']);
        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[OrderDetailBLL] OCI Execute failed for GET_DETAIL_FUNC block. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[OrderDetailBLL] OCI Execute failed for result cursor of GET_DETAIL_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }
        $stid_cursor = $out_cursor;

        if ($stid_cursor) {
            if (($row = @oci_fetch_array($stid_cursor, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $dto = new OrderDetailDTO(
                    $row['ORDERID'],
                    $row['COURSEID'],
                    isset($row['PRICE']) ? (float)$row['PRICE'] : 0.0,
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid_cursor);
        }
        @oci_free_statement($parsed_stid);

        return $dto;
    }

    public function delete_detail(string $orderID, string $courseID): bool
    {
        $sql = "BEGIN ORDER_DETAIL_PKG.DELETE_DETAIL_PROC(:orderID, :courseID); END;";

        $bindParams = [
            ':orderID'  => $orderID,
            ':courseID' => $courseID,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }
}
?>