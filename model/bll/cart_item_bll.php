<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/cart_item_dto.php';

class CartItemBLL extends Database
{
    public function create_item(CartItemDTO $item): bool
    {
        $sql = "INSERT INTO CARTITEM (CartItemID, CartID, CourseID, Quantity) 
                VALUES (:cartItemID, :cartID, :courseID, :quantity)";

        $bindParams = [
            ':cartItemID' => $item->cartItemID,
            ':cartID'     => $item->cartID,
            ':courseID'   => $item->courseID,
            ':quantity'   => isset($item->quantity) ? (int)$item->quantity : 1,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false) && ($this->getAffectedRows() === 1);
    }

    public function get_items_by_cart(string $cartID): array
    {
        $sql = "SELECT CartItemID, CartID, CourseID, Quantity, created_at 
                FROM CARTITEM 
                WHERE CartID = :cartID_param 
                ORDER BY CourseID ASC";

        $bindParams = [':cartID_param' => $cartID];

        $stid = $this->executePrepared($sql, $bindParams);
        $items = [];

        if ($stid) {
            while (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $items[] = new CartItemDTO(
                    $row['CARTITEMID'],
                    $row['CARTID'],
                    $row['COURSEID'],
                    isset($row['QUANTITY']) ? (int)$row['QUANTITY'] : 0,
                    $row['CREATED_AT'] ?? null
                );
            }
            @oci_free_statement($stid);
        }
        return $items;
    }

    public function delete_item(string $cartItemID): bool
    {
        $sql = "DELETE FROM CARTITEM WHERE CartItemID = :cartItemID";
        $bindParams = [':cartItemID' => $cartItemID];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false) && ($this->getAffectedRows() === 1);
    }

    public function get_item_by_id(string $cartItemID): ?CartItemDTO
    {
        $sql = "SELECT CartItemID, CartID, CourseID, Quantity, created_at 
                FROM CARTITEM 
                WHERE CartItemID = :cartItemID_param";
        $bindParams = [':cartItemID_param' => $cartItemID];

        $stid = $this->executePrepared($sql, $bindParams);
        $dto = null;

        if ($stid) {
            if (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $dto = new CartItemDTO(
                    $row['CARTITEMID'],
                    $row['CARTID'],
                    $row['COURSEID'],
                    isset($row['QUANTITY']) ? (int)$row['QUANTITY'] : 0,
                    $row['CREATED_AT'] ?? null
                );
            }
            @oci_free_statement($stid);
        }
        return $dto;
    }

    public function update_item_quantity(string $cartItemID, int $quantity): bool
    {
        if ($quantity <= 0) {
            return $this->delete_item($cartItemID);
        }

        $sql = "UPDATE CARTITEM SET Quantity = :quantity 
                WHERE CartItemID = :cartItemID_where";

        $bindParams = [
            ':quantity'        => $quantity,
            ':cartItemID_where' => $cartItemID,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }


    public function clear_cart(string $cartID): bool
    {
        $sql = "DELETE FROM CARTITEM WHERE CartID = :cartID";
        $bindParams = [':cartID' => $cartID];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }
}
?>