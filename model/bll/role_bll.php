<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/role_dto.php';

class RoleBLL extends Database
{
    public function create_role(RoleDTO $role): bool
    {
        $sql = "INSERT INTO ROLE (RoleID, RoleName) 
                VALUES (:roleID, :roleName)";
        $bindParams = [
            ':roleID'   => $role->roleID,
            ':roleName' => $role->roleName,
        ];
        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false) && ($this->getAffectedRows() === 1);
    }

    public function delete_role(string $roleID): bool
    {
        $sql = "DELETE FROM ROLE WHERE RoleID = :roleID";
        $bindParams = [':roleID' => $roleID];
        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false) && ($this->getAffectedRows() === 1);
    }

    public function update_role(RoleDTO $role): bool
    {
        $sql = "UPDATE ROLE SET RoleName = :roleName WHERE RoleID = :roleID_where";
        $bindParams = [
            ':roleName'    => $role->roleName,
            ':roleID_where' => $role->roleID,
        ];
        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function get_role(string $roleID): ?RoleDTO
    {
        $sql = "SELECT RoleID, RoleName, created_at 
                FROM ROLE 
                WHERE RoleID = :roleID_param";
        $bindParams = [':roleID_param' => $roleID];
        $stid = $this->executePrepared($sql, $bindParams);
        $dto = null;

        if ($stid) {
            if (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $dto = new RoleDTO(
                    $row['ROLEID'],
                    $row['ROLENAME'],
                    $row['CREATED_AT'] ?? null
                );
            }
            @oci_free_statement($stid);
        }
        return $dto;
    }

    public function get_all_roles(): array
    {
        $sql = "SELECT RoleID, RoleName, created_at 
                FROM ROLE ORDER BY RoleID";
        $stid = $this->executePrepared($sql);
        $roles = [];

        if ($stid) {
            while (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $roles[] = new RoleDTO(
                    $row['ROLEID'],
                    $row['ROLENAME'],
                    $row['CREATED_AT'] ?? null
                );
            }
            @oci_free_statement($stid);
        }
        return $roles;
    }
}
?>