<?php
class RoleDTO
{
    public string $roleID;
    public string $roleName;
    public ?string $created_at;
    public function __construct(string $roleID, string $roleName, ?string $created_at=null)
    {
        $this->roleID   = $roleID;
        $this->roleName = $roleName;
        $this->created_at = $created_at;
    }
}
