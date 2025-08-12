<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/role_dto.php';

class RoleBLL extends Database
{
    /**
     * Tạo một vai trò mới.
     *
     * @param RoleDTO $role Đối tượng chứa thông tin vai trò.
     * @return bool Trả về true nếu tạo thành công, ngược lại false.
     */
    public function create_role(RoleDTO $role): bool
    {
        $sql = "INSERT INTO Role (RoleID, RoleName) VALUES (?, ?)";
        $params = [
            $role->roleID,
            $role->roleName,
        ];
        $result = $this->executePrepared($sql, $params);
        return ($result !== false) && ($this->getAffectedRows() === 1);
    }

    /**
     * Xóa một vai trò.
     *
     * @param string $roleID ID của vai trò cần xóa.
     * @return bool Trả về true nếu xóa thành công, ngược lại false.
     */
    public function delete_role(string $roleID): bool
    {
        $sql = "DELETE FROM Role WHERE RoleID = ?";
        $params = [$roleID];
        $result = $this->executePrepared($sql, $params);
        return ($result !== false) && ($this->getAffectedRows() === 1);
    }

    /**
     * Cập nhật thông tin một vai trò.
     *
     * @param RoleDTO $role Đối tượng chứa thông tin vai trò cần cập nhật.
     * @return bool Trả về true nếu cập nhật thành công, ngược lại false.
     */
    public function update_role(RoleDTO $role): bool
    {
        $sql = "UPDATE Role SET RoleName = ? WHERE RoleID = ?";
        $params = [
            $role->roleName,
            $role->roleID,
        ];
        $result = $this->executePrepared($sql, $params);
        return ($result !== false);
    }

    /**
     * Lấy thông tin vai trò bằng ID.
     *
     * @param string $roleID ID của vai trò.
     * @return RoleDTO|null Trả về đối tượng RoleDTO nếu tìm thấy, ngược lại null.
     */
    public function get_role_by_role_id(string $roleID): ?RoleDTO
    {
        $sql = "SELECT RoleID, RoleName, 
                       DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') AS created_at_formatted
                FROM Role 
                WHERE RoleID = ?";
        $params = [$roleID];
        $result = $this->executePrepared($sql, $params);
        $dto = null;

        if ($result instanceof mysqli_result) {
            if ($row = $result->fetch_assoc()) {
                $dto = new RoleDTO(
                    $row['RoleID'],
                    $row['RoleName'],
                    $row['created_at_formatted'] ?? null
                );
            }
            $result->free();
        }
        return $dto;
    }

    /**
     * Lấy tất cả các vai trò.
     *
     * @return array Mảng các đối tượng RoleDTO.
     */
    public function get_all_roles(): array
    {
        $sql = "SELECT RoleID, RoleName, 
                       DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') AS created_at_formatted
                FROM Role 
                ORDER BY RoleName ASC";
        $result = $this->execute($sql);
        $roles = [];

        if ($result instanceof mysqli_result) {
            while ($row = $result->fetch_assoc()) {
                $roles[] = new RoleDTO(
                    $row['RoleID'],
                    $row['RoleName'],
                    $row['created_at_formatted'] ?? null
                );
            }
            $result->free();
        }
        return $roles;
    }
}
