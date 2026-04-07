<?php

namespace App\Models;

use App\Models\Traits\HasAuditColumns;
use App\Support\RbacPermissionMap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasAuditColumns;

    protected $primaryKey = 'role_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['role_id', 'role_name', 'is_active'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id', 'role_id');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role', 'role_id', 'permission_id');
    }

    public function hasPermission(string $permissionName): bool
    {
        if ($this->permissions()->where('name', $permissionName)->exists()) {
            return true;
        }

        return RbacPermissionMap::roleHasPermission($this->role_id, $permissionName);
    }

    /**
     * @return array<int, string>
     */
    public function permissionNames(): array
    {
        $persistedPermissions = $this->permissions()->pluck('name')->toArray();

        return array_values(array_unique([
            ...$persistedPermissions,
            ...RbacPermissionMap::permissionsForRole($this->role_id),
        ]));
    }

    public function hasAnyPermission(array $permissionNames): bool
    {
        foreach ($permissionNames as $permissionName) {
            if ($this->hasPermission($permissionName)) {
                return true;
            }
        }

        return false;
    }

    public function hasAllPermissions(array $permissionNames): bool
    {
        $availablePermissions = $this->permissionNames();

        return count(array_diff($permissionNames, $availablePermissions)) === 0;
    }
}
