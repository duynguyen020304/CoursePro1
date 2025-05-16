<?php
class RoleDTO
{
    public string $roleID;
    public string $roleName;
    public function __construct(string $roleID, string $roleName)
    {
        $this->roleID   = $roleID;
        $this->roleName = $roleName;
    }
}
