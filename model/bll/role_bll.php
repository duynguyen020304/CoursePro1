<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/role_dto.php';
class RoleBLL extends Database
{
    public function create_role(RoleDTO $role)
    {
        $sql = "INSERT INTO `Role` (RoleID, RoleName) VALUES ('{$role->roleID}', '{$role->roleName}')";
        $this->execute($sql);
        $this->close();
    }

    public function delete_role(string $roleID)
    {
        $sql = "DELETE FROM `Role` WHERE RoleID = '{$roleID}'";
        $this->execute($sql);
        $this->close();
    }

    public function update_role(RoleDTO $role)
    {
        $sql = "UPDATE `Role` SET RoleName = '{$role->roleName}' WHERE RoleID = '{$role->roleID}'";
        $this->execute($sql);
        $this->close();
    }

    public function get_role(string $roleID): ?RoleDTO
    {
        $sql = "SELECT * FROM `Role` WHERE RoleID = '{$roleID}'";
        $result = $this->execute($sql);
        $dto = null;
        if ($row = $result->fetch_assoc()) {
            $dto = new RoleDTO($row['RoleID'], $row['RoleName']);
        }
        $this->close();
        return $dto;
    }

    public function get_all_roles(): array
    {
        $sql = "SELECT * FROM `Role`";
        $result = $this->execute($sql);
        $roles = [];
        while ($row = $result->fetch_assoc()) {
            $roles[] = new RoleDTO($row['RoleID'], $row['RoleName']);
        }
        $this->close();
        return $roles;
    }
}
