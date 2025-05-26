<?php

require_once __DIR__ . '/../model/bll/role_bll.php';
require_once __DIR__ . '/../model/dto/role_dto.php';
require_once __DIR__ . '/service_response.php';

class RoleService
{
    private RoleBLL $bll;

    public function __construct()
    {
        $this->bll = new RoleBLL();
    }

    public function create_role(string $roleID, string $roleName): ServiceResponse
    {
        $dto = new RoleDTO($roleID, $roleName);
        $ok = $this->bll->create_role($dto);
        if ($ok) {
            return new ServiceResponse(true, 'Tạo vai trò thành công', $dto);
        }
        return new ServiceResponse(false, 'Tạo vai trò thất bại');
    }

    public function get_role_by_role_id(string $roleID): ServiceResponse
    {
        $dto = $this->bll->get_role_by_role_id($roleID);
        if ($dto) {
            return new ServiceResponse(true, 'Lấy vai trò thành công', $dto);
        }
        return new ServiceResponse(false, 'Vai trò không tồn tại');
    }

    public function get_all_roles(): ServiceResponse
    {
        $list = $this->bll->get_all_roles();
        return new ServiceResponse(true, 'Lấy danh sách vai trò thành công', $list);
    }

    public function update_role(string $roleID, string $roleName): ServiceResponse
    {
        $dto = new RoleDTO($roleID, $roleName);
        $ok = $this->bll->update_role($dto);
        if ($ok) {
            return new ServiceResponse(true, 'Cập nhật vai trò thành công');
        }
        return new ServiceResponse(false, 'Cập nhật vai trò thất bại');
    }

    public function delete_role(string $roleID): ServiceResponse
    {
        $ok = $this->bll->delete_role($roleID);
        if ($ok) {
            return new ServiceResponse(true, 'Xóa vai trò thành công');
        }
        return new ServiceResponse(false, 'Xóa vai trò thất bại hoặc không tồn tại');
    }
}