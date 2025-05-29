<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/order_dto.php';

class OrderBLL extends Database
{
    public function create_order(OrderDTO $order): bool
    {
        // Calls ORDER_PKG.CREATE_ORDER_PROC
        // Explicitly convert the date string to TIMESTAMP within the anonymous PL/SQL block
        $sql = "BEGIN 
                    ORDER_PKG.CREATE_ORDER_PROC(
                        p_OrderID     => :orderID, 
                        p_UserID      => :userID, 
                        p_OrderDate   => TO_TIMESTAMP(:orderDate_str, 'YYYY-MM-DD HH24:MI:SS.FF6'), 
                        p_TotalAmount => :totalAmount
                    ); 
                END;";

        $orderDateString = null;
        if ($order->orderDate instanceof DateTimeInterface) {
            // Ensure microseconds are padded to 6 digits if necessary, though FF6 handles variable length.
            // Using 'u' format specifier which gives microseconds.
            $orderDateString = $order->orderDate->format('Y-m-d H:i:s.u');
        }

        $bindParams = [
            ':orderID'     => $order->orderID,
            ':userID'      => $order->userID,
            ':orderDate_str' => $orderDateString, // Bind the string
            ':totalAmount' => is_numeric($order->totalAmount) ? (float)$order->totalAmount : 0,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function get_order_by_order_id(string $orderID): ?OrderDTO
    {
        $sql = "BEGIN :result_cursor := ORDER_PKG.GET_ORDER_BY_ID_FUNC(:orderID_param); END;";
        $bindParams = [
            ':orderID_param' => $orderID
        ];

        $dto = null;
        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[OrderBLL] Failed to create new cursor for GET_ORDER_BY_ID_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return null;
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[OrderBLL] OCI Parse failed for GET_ORDER_BY_ID_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return null;
        }

        @oci_bind_by_name($parsed_stid, ':orderID_param', $bindParams[':orderID_param']);
        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[OrderBLL] OCI Execute failed for GET_ORDER_BY_ID_FUNC block. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[OrderBLL] OCI Execute failed for result cursor of GET_ORDER_BY_ID_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }
        $stid_cursor = $out_cursor;

        if ($stid_cursor) {
            if (($row = @oci_fetch_array($stid_cursor, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $orderDate = null;
                if (!empty($row['ORDER_DATE_FORMATTED'])) {
                    try {
                        $orderDate = new DateTime($row['ORDER_DATE_FORMATTED']);
                    } catch (Exception $e) {
                        error_log("Error parsing ORDER_DATE_FORMATTED from DB (via PL/SQL): " . $row['ORDER_DATE_FORMATTED'] . " - " . $e->getMessage());
                    }
                }
                $dto = new OrderDTO(
                    $row['ORDERID'],
                    $row['USERID'],
                    $orderDate,
                    isset($row['TOTALAMOUNT']) ? (float)$row['TOTALAMOUNT'] : 0.0,
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid_cursor);
        }
        @oci_free_statement($parsed_stid);

        return $dto;
    }

    public function get_orders_by_user_id(string $userID): array
    {
        $sql = "BEGIN :result_cursor := ORDER_PKG.GET_ORDERS_BY_USER_FUNC(:userID_param); END;";
        $bindParams = [
            ':userID_param' => $userID
        ];

        $orders = [];
        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[OrderBLL] Failed to create new cursor for GET_ORDERS_BY_USER_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return [];
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[OrderBLL] OCI Parse failed for GET_ORDERS_BY_USER_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return [];
        }

        @oci_bind_by_name($parsed_stid, ':userID_param', $bindParams[':userID_param']);
        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[OrderBLL] OCI Execute failed for GET_ORDERS_BY_USER_FUNC block. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[OrderBLL] OCI Execute failed for result cursor of GET_ORDERS_BY_USER_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }
        $stid_cursor = $out_cursor;

        if ($stid_cursor) {
            while (($row = @oci_fetch_array($stid_cursor, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $orderDate = null;
                if (!empty($row['ORDER_DATE_FORMATTED'])) {
                    try {
                        $orderDate = new DateTime($row['ORDER_DATE_FORMATTED']);
                    } catch (Exception $e) {
                        error_log("Error parsing ORDER_DATE_FORMATTED from DB in get_orders_by_user (via PL/SQL): " . $row['ORDER_DATE_FORMATTED'] . " - " . $e->getMessage());
                    }
                }
                $orders[] = new OrderDTO(
                    $row['ORDERID'],
                    $row['USERID'],
                    $orderDate,
                    isset($row['TOTALAMOUNT']) ? (float)$row['TOTALAMOUNT'] : 0.0,
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid_cursor);
        }
        @oci_free_statement($parsed_stid);

        return $orders;
    }

    public function update_order(OrderDTO $order): bool
    {
        // Calls ORDER_PKG.UPDATE_ORDER_PROC
        $sql = "BEGIN 
                    ORDER_PKG.UPDATE_ORDER_PROC(
                        p_OrderID     => :orderID_where, 
                        p_UserID      => :userID, 
                        p_OrderDate   => TO_TIMESTAMP(:orderDate_str, 'YYYY-MM-DD HH24:MI:SS.FF6'), 
                        p_TotalAmount => :totalAmount
                    ); 
                END;";

        $orderDateString = null;
        if ($order->orderDate instanceof DateTimeInterface) {
            $orderDateString = $order->orderDate->format('Y-m-d H:i:s.u');
        }

        $bindParams = [
            ':orderID_where' => $order->orderID,
            ':userID'       => $order->userID,
            ':orderDate_str'    => $orderDateString, // Bind the string
            ':totalAmount'  => is_numeric($order->totalAmount) ? (float)$order->totalAmount : 0,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function delete_order(string $orderID): bool
    {
        // Calls ORDER_PKG.DELETE_ORDER_PROC
        $sql = "BEGIN ORDER_PKG.DELETE_ORDER_PROC(:orderID); END;";
        $bindParams = [':orderID' => $orderID];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }
}
?>
