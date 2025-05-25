<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/order_dto.php';

class OrderBLL extends Database
{
    public function create_order(OrderDTO $order): bool
    {
        // For inserting, convert DateTime to a string format Oracle understands for TIMESTAMP
        $sql = "INSERT INTO ORDERS (OrderID, UserID, OrderDate, TotalAmount)
                VALUES (:orderID, :userID, TO_TIMESTAMP(:orderDate, 'YYYY-MM-DD HH24:MI:SS.FF6'), :totalAmount)";

        $bindParams = [
            ':orderID'     => $order->orderID,
            ':userID'      => $order->userID,
            // Format to include fractional seconds for TIMESTAMP
            ':orderDate'   => $order->orderDate instanceof DateTimeInterface ? $order->orderDate->format('Y-m-d H:i:s.u') : null,
            ':totalAmount' => is_numeric($order->totalAmount) ? (float)$order->totalAmount : 0,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false) && ($this->getAffectedRows() === 1);
    }

    public function get_order(string $orderID): ?OrderDTO
    {
        // Format OrderDate and created_at using TO_CHAR for consistent retrieval
        $sql = "SELECT OrderID, UserID, 
                       TO_CHAR(OrderDate, 'YYYY-MM-DD HH24:MI:SS.FF6') AS order_date_formatted,
                       TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
                FROM ORDERS
                WHERE OrderID = :orderID_param";
        $bindParams = [':orderID_param' => $orderID];

        $stid = $this->executePrepared($sql, $bindParams);
        $dto = null;

        if ($stid) {
            if (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $orderDate = null;
                // Parse the formatted string back into a DateTime object
                if (!empty($row['ORDER_DATE_FORMATTED'])) {
                    try {
                        $orderDate = new DateTime($row['ORDER_DATE_FORMATTED']);
                    } catch (Exception $e) {
                        error_log("Error parsing ORDER_DATE_FORMATTED from DB: " . $row['ORDER_DATE_FORMATTED'] . " - " . $e->getMessage());
                    }
                }
                $dto = new OrderDTO(
                    $row['ORDERID'],
                    $row['USERID'],
                    $orderDate, // Use the parsed DateTime object
                    isset($row['TOTALAMOUNT']) ? (float)$row['TOTALAMOUNT'] : 0.0,
                    $row['CREATED_AT_FORMATTED'] ?? null // Use the formatted alias
                );
            }
            @oci_free_statement($stid);
        }
        return $dto;
    }

    public function get_orders_by_user(string $userID): array
    {
        // Format OrderDate and created_at using TO_CHAR for consistent retrieval
        $sql = "SELECT OrderID, UserID, 
                       TO_CHAR(OrderDate, 'YYYY-MM-DD HH24:MI:SS.FF6') AS order_date_formatted,
                       TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
                FROM ORDERS
                WHERE UserID = :userID_param
                ORDER BY OrderDate DESC";

        $bindParams = [':userID_param' => $userID];

        $stid = $this->executePrepared($sql, $bindParams);
        $orders = [];

        if ($stid) {
            while (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $orderDate = null;
                // Parse the formatted string back into a DateTime object
                if (!empty($row['ORDER_DATE_FORMATTED'])) {
                    try {
                        $orderDate = new DateTime($row['ORDER_DATE_FORMATTED']);
                    } catch (Exception $e) {
                        error_log("Error parsing ORDER_DATE_FORMATTED from DB in get_orders_by_user: " . $row['ORDER_DATE_FORMATTED'] . " - " . $e->getMessage());
                    }
                }
                $orders[] = new OrderDTO(
                    $row['ORDERID'],
                    $row['USERID'],
                    $orderDate, // Use the parsed DateTime object
                    isset($row['TOTALAMOUNT']) ? (float)$row['TOTALAMOUNT'] : 0.0,
                    $row['CREATED_AT_FORMATTED'] ?? null // Use the formatted alias
                );
            }
            @oci_free_statement($stid);
        }
        return $orders;
    }

    public function update_order(OrderDTO $order): bool
    {
        // For updating, convert DateTime to a string format Oracle understands for TIMESTAMP
        $sql = "UPDATE ORDERS SET
                UserID = :userID,
                OrderDate = TO_TIMESTAMP(:orderDate, 'YYYY-MM-DD HH24:MI:SS.FF6'),
                TotalAmount = :totalAmount
                WHERE OrderID = :orderID_where";

        $bindParams = [
            ':userID'       => $order->userID,
            // Format to include fractional seconds for TIMESTAMP
            ':orderDate'    => $order->orderDate instanceof DateTimeInterface ? $order->orderDate->format('Y-m-d H:i:s.u') : null,
            ':totalAmount'  => is_numeric($order->totalAmount) ? (float)$order->totalAmount : 0,
            ':orderID_where' => $order->orderID,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false) && ($this->getAffectedRows() === 1);
    }

    public function delete_order(string $orderID): bool
    {
        $sql = "DELETE FROM ORDERS WHERE OrderID = :orderID";
        $bindParams = [':orderID' => $orderID];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false) && ($this->getAffectedRows() === 1);
    }
}