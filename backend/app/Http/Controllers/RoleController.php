<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    /**
     * Display a listing of roles
     */
    public function index(Request $request)
    {
        $query = Role::with('permissions')->orderBy('role_name');

        // Include soft-deleted records
        if ($request->boolean('include_deleted', false)) {
            $query->withTrashed();
        }

        // Filter by is_active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $roles = $query->get();

        return $this->success($roles, 'Roles retrieved successfully');
    }

    /**
     * Display the specified role
     */
    public function show($id)
    {
        $role = Role::with(['users', 'permissions'])->findOrFail($id);

        return $this->success($role, 'Role retrieved successfully');
    }

    /**
     * Store a newly created role
     */
    public function store(Request $request)
    {
        $request->validate([
            'role_name' => 'required|string|max:255|unique:roles,role_name',
        ]);

        $role = Role::create([
            'role_id' => (string) Str::uuid(),
            'role_code' => 'role_' . Str::random(10),
            'role_name' => $request->role_name,
        ]);

        return $this->created($role, 'Role created successfully');
    }

    /**
     * Update the specified role
     */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'role_name' => 'sometimes|string|max:255|unique:roles,role_name,' . $id . ',role_id',
        ]);

        $role->update($request->only(['role_name', 'is_active']));

        return $this->success($role, 'Role updated successfully');
    }

    /**
     * Remove the specified role
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        // Prevent deleting core roles
        $role = Role::findOrFail($id);
        if (in_array($role->role_code, ['admin', 'student', 'instructor'], true)) {
            return $this->error('Cannot delete core system roles', 403);
        }

        $role->permissions()->detach();
        $role->delete();

        return $this->emptySuccess('Role deleted successfully');
    }

    /**
     * Get permissions for a specific role
     */
    public function getPermissions($id)
    {
        $role = Role::findOrFail($id);
        $permissions = $role->permissions;

        return $this->success($permissions, 'Permissions retrieved successfully');
    }

    /**
     * Assign permissions to a role
     */
    public function assignPermissions(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,permission_id',
        ]);

        $role->permissions()->syncWithoutDetaching($request->permissions);

        return $this->success($role->permissions, 'Permissions assigned successfully');
    }

    /**
     * Remove a permission from a role
     */
    public function removePermission($id, $permissionId)
    {
        $role = Role::findOrFail($id);
        $role->permissions()->detach($permissionId);

        return $this->emptySuccess('Permission removed successfully');
    }

    /**
     * Sync permissions for a role (replace all)
     */
    public function syncPermissions(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,permission_id',
        ]);

        $role->permissions()->sync($request->permissions);

        return $this->success($role->permissions, 'Permissions synced successfully');
    }

    /**
     * Get all available permissions
     */
    public function getAllPermissions()
    {
        $permissions = Permission::orderBy('name')->get();

        return $this->success($permissions, 'Permissions retrieved successfully');
    }
}
