<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/order_dto.php';

class OrderBLL extends Database
{
    public function create_order(OrderDTO $order): bool
    {
        $sql = "INSERT INTO ORDERS (OrderID, UserID, OrderDate, TotalAmount)
                VALUES (:orderID, :userID, TO_TIMESTAMP(:orderDate, 'YYYY-MM-DD HH24:MI:SS'), :totalAmount)";

        $bindParams = [
            ':orderID'     => $order->orderID,
            ':userID'      => $order->userID,
            ':orderDate'   => $order->orderDate instanceof DateTimeInterface ? $order->orderDate->format('Y-m-d H:i:s') : null,
            ':totalAmount' => is_numeric($order->totalAmount) ? (float)$order->totalAmount : 0,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false) && ($this->getAffectedRows() === 1);
    }

    public function get_order(string $orderID): ?OrderDTO
    {
        $sql = "SELECT OrderID, UserID, OrderDate, TotalAmount, created_at 
                FROM ORDERS 
                WHERE OrderID = :orderID_param";
        $bindParams = [':orderID_param' => $orderID];

        $stid = $this->executePrepared($sql, $bindParams);
        $dto = null;

        if ($stid) {
            if (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $orderDate = null;
                if (!empty($row['ORDERDATE'])) {
                    try {
                        $orderDate = new DateTime($row['ORDERDATE']);
                    } catch (Exception $e) {
                        error_log("Error parsing ORDERDATE from DB: " . $row['ORDERDATE'] . " - " . $e->getMessage());
                    }
                }
                $dto = new OrderDTO(
                    $row['ORDERID'],
                    $row['USERID'],
                    $orderDate,
                    isset($row['TOTALAMOUNT']) ? (float)$row['TOTALAMOUNT'] : 0.0,
                    $row['CREATED_AT'] ?? null
                );
            }
            @oci_free_statement($stid);
        }
        return $dto;
    }

    public function get_orders_by_user(string $userID): array
    {
        $sql = "SELECT OrderID, UserID, OrderDate, TotalAmount, created_at 
                FROM ORDERS 
                WHERE UserID = :userID_param 
                ORDER BY OrderDate DESC";

        $bindParams = [':userID_param' => $userID];

        $stid = $this->executePrepared($sql, $bindParams);
        $orders = [];

        if ($stid) {
            while (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $orderDate = null;
                if (!empty($row['ORDERDATE'])) {
                    try {
                        $orderDate = new DateTime($row['ORDERDATE']);
                    } catch (Exception $e) {
                        error_log("Error parsing ORDERDATE from DB in get_orders_by_user: " . $row['ORDERDATE'] . " - " . $e->getMessage());
                    }
                }
                $orders[] = new OrderDTO(
                    $row['ORDERID'],
                    $row['USERID'],
                    $orderDate,
                    isset($row['TOTALAMOUNT']) ? (float)$row['TOTALAMOUNT'] : 0.0,
                    $row['CREATED_AT'] ?? null
                );
            }
            @oci_free_statement($stid);
        }
        return $orders;
    }

    public function update_order(OrderDTO $order): bool
    {
        $sql = "UPDATE ORDERS SET
                UserID = :userID,
                OrderDate = TO_TIMESTAMP(:orderDate, 'YYYY-MM-DD HH24:MI:SS'),
                TotalAmount = :totalAmount
                WHERE OrderID = :orderID_where";

        $bindParams = [
            ':userID'       => $order->userID,
            ':orderDate'    => $order->orderDate instanceof DateTimeInterface ? $order->orderDate->format('Y-m-d H:i:s') : null,
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
?>