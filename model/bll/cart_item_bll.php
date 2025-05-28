<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/cart_item_dto.php';

class CartItemBLL extends Database
{
    public function create_item(CartItemDTO $item): bool
    {
        $sql = "BEGIN CART_ITEM_PKG.CREATE_ITEM_PROC(:cartItemID, :cartID, :courseID, :quantity); END;";

        $bindParams = [
            ':cartItemID' => $item->cartItemID,
            ':cartID'     => $item->cartID,
            ':courseID'   => $item->courseID,
            ':quantity'   => isset($item->quantity) ? (int)$item->quantity : 1,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function get_items_by_cart(string $cartID): array
    {
        $sql = "BEGIN :result_cursor := CART_ITEM_PKG.GET_ITEMS_BY_CART_FUNC(:cartID_param); END;";
        $bindParams = [
            ':cartID_param' => $cartID
        ];

        $items = [];
        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[CartItemBLL] Failed to create new cursor for GET_ITEMS_BY_CART_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return [];
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[CartItemBLL] OCI Parse failed for GET_ITEMS_BY_CART_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return [];
        }

        @oci_bind_by_name($parsed_stid, ':cartID_param', $bindParams[':cartID_param']);
        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[CartItemBLL] OCI Execute failed for GET_ITEMS_BY_CART_FUNC block. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[CartItemBLL] OCI Execute failed for result cursor of GET_ITEMS_BY_CART_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }
        $stid_cursor = $out_cursor;

        if ($stid_cursor) {
            while (($row = @oci_fetch_array($stid_cursor, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $items[] = new CartItemDTO(
                    $row['CARTITEMID'],
                    $row['CARTID'],
                    $row['COURSEID'],
                    isset($row['QUANTITY']) ? (int)$row['QUANTITY'] : 0,
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid_cursor);
        }
        @oci_free_statement($parsed_stid);

        return $items;
    }

    public function delete_item(string $cartItemID): bool
    {
        $sql = "BEGIN CART_ITEM_PKG.DELETE_ITEM_PROC(:cartItemID); END;";
        $bindParams = [':cartItemID' => $cartItemID];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function get_item_by_id(string $cartItemID): ?CartItemDTO
    {
        $sql = "BEGIN :result_cursor := CART_ITEM_PKG.GET_ITEM_BY_ID_FUNC(:cartItemID_param); END;";
        $bindParams = [
            ':cartItemID_param' => $cartItemID
        ];

        $dto = null;
        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[CartItemBLL] Failed to create new cursor for GET_ITEM_BY_ID_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return null;
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[CartItemBLL] OCI Parse failed for GET_ITEM_BY_ID_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return null;
        }

        @oci_bind_by_name($parsed_stid, ':cartItemID_param', $bindParams[':cartItemID_param']);
        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[CartItemBLL] OCI Execute failed for GET_ITEM_BY_ID_FUNC block. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[CartItemBLL] OCI Execute failed for result cursor of GET_ITEM_BY_ID_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }
        $stid_cursor = $out_cursor;

        if ($stid_cursor) {
            if (($row = @oci_fetch_array($stid_cursor, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $dto = new CartItemDTO(
                    $row['CARTITEMID'],
                    $row['CARTID'],
                    $row['COURSEID'],
                    isset($row['QUANTITY']) ? (int)$row['QUANTITY'] : 0,
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid_cursor);
        }
        @oci_free_statement($parsed_stid);

        return $dto;
    }

    public function update_item_quantity(string $cartItemID, int $quantity): bool
    {
        $sql = "BEGIN CART_ITEM_PKG.UPDATE_ITEM_QUANTITY_PROC(:cartItemID, :quantity); END;";

        $bindParams = [
            ':cartItemID' => $cartItemID,
            ':quantity'   => $quantity,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function clear_cart(string $cartID): bool
    {
        $sql = "BEGIN CART_ITEM_PKG.CLEAR_CART_PROC(:cartID); END;";
        $bindParams = [':cartID' => $cartID];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }
}
?>