<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/cart_dto.php';

class CartBLL extends Database
{
    public function create_cart(CartDTO $cart): bool
    {
        $sql = "INSERT INTO CART (CartID, UserID) 
                VALUES (:cartID, :userID)";

        $bindParams = [
            ':cartID' => $cart->cartID,
            ':userID' => $cart->userID,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false) && ($this->getAffectedRows() === 1);
    }

    public function get_cart_by_user(string $userID): ?CartDTO
    {
        $sql = "SELECT CartID, UserID, TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM CART
            WHERE UserID = :userID_param";

        $bindParams = [':userID_param' => $userID];

        $stid = $this->executePrepared($sql, $bindParams);
        $dto = null;

        if ($stid) {
            if (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $dto = new CartDTO(
                    $row['CARTID'],
                    $row['USERID'],
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid);
        }
        return $dto;
    }

    public function get_cart_by_id(string $cartID): ?CartDTO
    {
        $sql = "SELECT CartID, UserID, TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM CART
            WHERE CartID = :cartID_param";

        $bindParams = [':cartID_param' => $cartID];

        $stid = $this->executePrepared($sql, $bindParams);
        $dto = null;

        if ($stid) {
            if (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $dto = new CartDTO(
                    $row['CARTID'],
                    $row['USERID'],
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid);
        }
        return $dto;
    }


    public function delete_cart(string $cartID): bool
    {
        $sql = "DELETE FROM CART WHERE CartID = :cartID";
        $bindParams = [':cartID' => $cartID];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false) && ($this->getAffectedRows() === 1);
    }
}
?>