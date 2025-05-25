<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/order_detail_dto.php';

class OrderDetailBLL extends Database
{
    public function add_detail(OrderDetailDTO $detail): bool
    {
        $sql = "INSERT INTO ORDERDETAIL (OrderID, CourseID, Price)
                VALUES (:orderID, :courseID, :price)";

        $bindParams = [
            ':orderID'  => $detail->orderID,
            ':courseID' => $detail->courseID,
            ':price'    => is_numeric($detail->price) ? (float)$detail->price : 0,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false) && ($this->getAffectedRows() === 1);
    }

    public function get_details_by_order(string $orderID): array
    {
        $sql = "SELECT OrderID, CourseID, Price, TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
                FROM ORDERDETAIL
                WHERE OrderID = :orderID_param
                ORDER BY CourseID";

        $bindParams = [':orderID_param' => $orderID];

        $stid = $this->executePrepared($sql, $bindParams);
        $details = [];

        if ($stid) {
            while (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $details[] = new OrderDetailDTO(
                    $row['ORDERID'],
                    $row['COURSEID'],
                    isset($row['PRICE']) ? (float)$row['PRICE'] : 0.0,
                    $row['CREATED_AT_FORMATTED'] ?? null // Use the formatted alias
                );
            }
            oci_free_statement($stid);
        }
        return $details;
    }

    public function update_detail(OrderDetailDTO $detail): bool
    {
        if (!is_numeric($detail->price) || (float)$detail->price < 0) {
            error_log("Invalid price for update_detail: OrderID {$detail->orderID}, CourseID {$detail->courseID}, Price {$detail->price}");
            return false;
        }

        $sql = "UPDATE ORDERDETAIL SET Price = :price
                WHERE OrderID = :orderID_where AND CourseID = :courseID_where";

        $bindParams = [
            ':price'          => (float)$detail->price,
            ':orderID_where'  => $detail->orderID,
            ':courseID_where' => $detail->courseID,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function get_detail(string $orderID, string $courseID): ?OrderDetailDTO
    {
        $sql = "SELECT OrderID, CourseID, Price, TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
                FROM ORDERDETAIL
                WHERE OrderID = :orderID_param AND CourseID = :courseID_param";
        $bindParams = [
            ':orderID_param' => $orderID,
            ':courseID_param' => $courseID,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        $dto = null;

        if ($stid) {
            if (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $dto = new OrderDetailDTO(
                    $row['ORDERID'],
                    $row['COURSEID'],
                    isset($row['PRICE']) ? (float)$row['PRICE'] : 0.0,
                    $row['CREATED_AT_FORMATTED'] ?? null // Use the formatted alias
                );
            }
            oci_free_statement($stid);
        }
        return $dto;
    }


    public function delete_detail(string $orderID, string $courseID): bool
    {
        $sql = "DELETE FROM ORDERDETAIL
                WHERE OrderID = :orderID AND CourseID = :courseID";

        $bindParams = [
            ':orderID'  => $orderID,
            ':courseID' => $courseID,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false) && ($this->getAffectedRows() === 1);
    }
}