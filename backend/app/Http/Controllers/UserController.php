<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserAccount;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::with(['role', 'student', 'instructor', 'userAccount']);

        // Include soft-deleted records
        if ($request->boolean('include_deleted', false)) {
            $query->withTrashed();
        }

        // Filter by is_active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        if ($request->filled('email')) {
            $query->whereHas('userAccount', function ($q) use ($request) {
                $q->where('email', 'like', '%' . $request->email . '%');
            });
        }

        $users = $query->paginate($request->get('per_page', 15));

        // Add email to response from userAccount
        $users->getCollection()->transform(function ($user) {
            $user->email = $user->userAccount?->email;
            return $user;
        });

        return $this->paginated($users, 'Users retrieved successfully');
    }

    /**
     * Display the authenticated user's profile
     */
    public function profile(Request $request)
    {
        // The authenticated user is UserAccount
        $userAccount = $request->user();
        $user = $userAccount->user->load(['role.permissions', 'student', 'instructor']);

        return $this->success([
            'user_id' => $user->user_id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $userAccount->email,
            'email_verified_at' => optional($userAccount->email_verified_at)?->toISOString(),
            'is_verified' => $userAccount->is_verified,
            'role_id' => $user->role_id,
            'profile_image' => $user->profile_image,
            'role' => $user->role,
            'student' => $user->student,
            'instructor' => $user->instructor,
        ], 'Profile retrieved successfully');
    }

    /**
     * Update the user's profile
     */
    public function updateProfile(Request $request)
    {
        $userAccount = $request->user();
        $user = $userAccount->user;

        $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'profile_image' => 'nullable|string|max:255',
        ]);

        $user->update($request->only(['first_name', 'last_name', 'profile_image']));

        return $this->success([
            'user_id' => $user->user_id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $userAccount->email,
            'email_verified_at' => optional($userAccount->email_verified_at)?->toISOString(),
            'is_verified' => $userAccount->is_verified,
            'role_id' => $user->role_id,
            'profile_image' => $user->profile_image,
        ], 'Profile updated successfully');
    }

    /**
     * Display the specified user (admin only)
     */
    public function show(Request $request, $id)
    {
        $query = User::with(['role', 'student', 'instructor', 'orders', 'reviews', 'userAccount']);

        // Include soft-deleted records
        if ($request->boolean('include_deleted', false)) {
            $query->withTrashed();
        }

        $user = $query->findOrFail($id);

        $user->email = $user->userAccount?->email;

        return $this->success($user, 'User retrieved successfully');
    }

    /**
     * Update the specified user (admin only)
     */
    public function update(Request $request, $id)
    {
        $user = User::with('userAccount')->findOrFail($id);

        $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:user_accounts,email,' . $user->user_id . ',user_id',
            'role_id' => 'sometimes|exists:roles,role_id',
            'profile_image' => 'nullable|string|max:255',
        ]);

        // Update profile fields in User
        $user->update($request->only(['first_name', 'last_name', 'role_id', 'profile_image', 'is_active']));

        // Update email in UserAccount if provided
        if ($request->has('email') && $user->userAccount) {
            $user->userAccount->update(['email' => $request->email]);
        }

        $user->load(['role', 'student', 'instructor', 'userAccount']);
        $user->email = $user->userAccount?->email;

        return $this->success($user, 'User updated successfully');
    }

    /**
     * Remove the specified user (admin only)
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        // Cascade delete will handle userAccount via foreign key
        $user->delete();

        return $this->emptySuccess('User deleted successfully');
    }

    /**
     * Assign a role to a user (admin only)
     */
    public function assignRole(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'role_id' => 'required|exists:roles,role_id',
        ]);

        $user->update(['role_id' => $request->role_id]);

        return $this->success(
            $user->fresh(['role', 'student', 'instructor', 'userAccount']),
            'Role assigned successfully'
        );
    }
}
