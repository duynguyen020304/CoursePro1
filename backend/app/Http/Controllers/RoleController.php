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

        return response()->json([
            'success' => true,
            'data' => $roles,
        ]);
    }

    /**
     * Display the specified role
     */
    public function show($id)
    {
        $role = Role::with(['users', 'permissions'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $role,
        ]);
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
            'role_id' => 'role_' . Str::random(10),
            'role_name' => $request->role_name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Role created successfully',
            'data' => $role,
        ], 201);
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

        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully',
            'data' => $role,
        ]);
    }

    /**
     * Remove the specified role
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        // Prevent deleting core roles
        if (in_array($id, ['admin', 'student', 'instructor'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete core system roles',
            ], 403);
        }

        $role->permissions()->detach();
        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully',
        ]);
    }

    /**
     * Get permissions for a specific role
     */
    public function getPermissions($id)
    {
        $role = Role::findOrFail($id);
        $permissions = $role->permissions;

        return response()->json([
            'success' => true,
            'data' => $permissions,
        ]);
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

        return response()->json([
            'success' => true,
            'message' => 'Permissions assigned successfully',
            'data' => $role->permissions,
        ]);
    }

    /**
     * Remove a permission from a role
     */
    public function removePermission($id, $permissionId)
    {
        $role = Role::findOrFail($id);
        $role->permissions()->detach($permissionId);

        return response()->json([
            'success' => true,
            'message' => 'Permission removed successfully',
        ]);
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

        return response()->json([
            'success' => true,
            'message' => 'Permissions synced successfully',
            'data' => $role->permissions,
        ]);
    }

    /**
     * Get all available permissions
     */
    public function getAllPermissions()
    {
        $permissions = Permission::orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $permissions,
        ]);
    }
}
