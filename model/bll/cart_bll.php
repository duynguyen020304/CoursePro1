<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/cart_dto.php';

class CartBLL extends Database
{
    public function create_cart(CartDTO $cart): bool
    {
        $sql = "BEGIN CART_PKG.CREATE_CART_PROC(:cartID, :userID); END;";

        $bindParams = [
            ':cartID' => $cart->cartID,
            ':userID' => $cart->userID,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function get_cart_by_user(string $userID): ?CartDTO
    {
        $sql = "BEGIN :result_cursor := CART_PKG.GET_CART_BY_USER_FUNC(:userID_param); END;";
        $bindParams = [
            ':userID_param' => $userID
        ];

        $dto = null;
        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[CartBLL] Failed to create new cursor for GET_CART_BY_USER_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return null;
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[CartBLL] OCI Parse failed for GET_CART_BY_USER_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return null;
        }

        @oci_bind_by_name($parsed_stid, ':userID_param', $bindParams[':userID_param']);
        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[CartBLL] OCI Execute failed for GET_CART_BY_USER_FUNC block. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }
        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[CartBLL] OCI Execute failed for result cursor. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }
        $stid_cursor = $out_cursor;

        if ($stid_cursor) {
            if (($row = @oci_fetch_array($stid_cursor, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $dto = new CartDTO(
                    $row['CARTID'],
                    $row['USERID'],
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid_cursor);
        }
        @oci_free_statement($parsed_stid);
        return $dto;
    }

    public function get_cart_by_id(string $cartID): ?CartDTO
    {
        $sql = "BEGIN :result_cursor := CART_PKG.GET_CART_BY_ID_FUNC(:cartID_param); END;";
        $bindParams = [
            ':cartID_param' => $cartID
        ];

        $dto = null;
        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[CartBLL] Failed to create new cursor for GET_CART_BY_ID_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return null;
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[CartBLL] OCI Parse failed for GET_CART_BY_ID_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return null;
        }

        @oci_bind_by_name($parsed_stid, ':cartID_param', $bindParams[':cartID_param']);
        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[CartBLL] OCI Execute failed for GET_CART_BY_ID_FUNC. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }
        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[CartBLL] OCI Execute failed for result cursor. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }
        $stid_cursor = $out_cursor;

        if ($stid_cursor) {
            if (($row = @oci_fetch_array($stid_cursor, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $dto = new CartDTO(
                    $row['CARTID'],
                    $row['USERID'],
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid_cursor);
        }
        @oci_free_statement($parsed_stid);
        return $dto;
    }

    public function delete_cart(string $cartID): bool
    {
        $sql = "BEGIN CART_PKG.DELETE_CART_PROC(:cartID); END;";
        $bindParams = [':cartID' => $cartID];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }
}
?>