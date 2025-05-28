<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/role_dto.php';

class RoleBLL extends Database
{
    public function create_role(RoleDTO $role): bool
    {
        $sql = "BEGIN ROLE_PKG.CREATE_ROLE_PROC(:roleID, :roleName); END;";
        $bindParams = [
            ':roleID'   => $role->roleID,
            ':roleName' => $role->roleName,
        ];
        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function delete_role(string $roleID): bool
    {
        $sql = "BEGIN ROLE_PKG.DELETE_ROLE_PROC(:roleID); END;";
        $bindParams = [':roleID' => $roleID];
        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function update_role(RoleDTO $role): bool
    {
        $sql = "BEGIN ROLE_PKG.UPDATE_ROLE_PROC(:roleID_where, :roleName); END;";
        $bindParams = [
            ':roleID_where' => $role->roleID,
            ':roleName'    => $role->roleName,
        ];
        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function get_role_by_role_id(string $roleID): ?RoleDTO
    {
        $sql = "BEGIN :result_cursor := ROLE_PKG.GET_ROLE_BY_ID_FUNC(:roleID_param); END;";
        $bindParams = [
            ':roleID_param' => $roleID
        ];

        $dto = null;
        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[RoleBLL] Failed to create new cursor for GET_ROLE_BY_ID_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return null;
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[RoleBLL] OCI Parse failed for GET_ROLE_BY_ID_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return null;
        }

        @oci_bind_by_name($parsed_stid, ':roleID_param', $bindParams[':roleID_param']);
        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[RoleBLL] OCI Execute failed for GET_ROLE_BY_ID_FUNC block. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[RoleBLL] OCI Execute failed for result cursor of GET_ROLE_BY_ID_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }
        $stid_cursor = $out_cursor;

        if ($stid_cursor) {
            if (($row = @oci_fetch_array($stid_cursor, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $dto = new RoleDTO(
                    $row['ROLEID'],
                    $row['ROLENAME'],
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid_cursor);
        }
        @oci_free_statement($parsed_stid);

        return $dto;
    }

    public function get_all_roles(): array
    {
        $sql = "BEGIN :result_cursor := ROLE_PKG.GET_ALL_ROLES_FUNC(); END;";

        $roles = [];
        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[RoleBLL] Failed to create new cursor for GET_ALL_ROLES_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return [];
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[RoleBLL] OCI Parse failed for GET_ALL_ROLES_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return [];
        }

        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[RoleBLL] OCI Execute failed for GET_ALL_ROLES_FUNC block. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[RoleBLL] OCI Execute failed for result cursor of GET_ALL_ROLES_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }
        $stid_cursor = $out_cursor;

        if ($stid_cursor) {
            while (($row = @oci_fetch_array($stid_cursor, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $roles[] = new RoleDTO(
                    $row['ROLEID'],
                    $row['ROLENAME'],
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid_cursor);
        }
        @oci_free_statement($parsed_stid);

        return $roles;
    }
}
?>