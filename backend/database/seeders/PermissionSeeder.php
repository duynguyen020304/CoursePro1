<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // User permissions
            ['name' => 'users.view', 'display_name' => 'View Users', 'description' => 'Can view user list and details'],
            ['name' => 'users.create', 'display_name' => 'Create Users', 'description' => 'Can create new users'],
            ['name' => 'users.edit', 'display_name' => 'Edit Users', 'description' => 'Can edit user information'],
            ['name' => 'users.delete', 'display_name' => 'Delete Users', 'description' => 'Can delete users'],
            ['name' => 'users.manage', 'display_name' => 'Manage Users', 'description' => 'Full user management including role assignment'],

            // Role permissions
            ['name' => 'roles.view', 'display_name' => 'View Roles', 'description' => 'Can view role list and details'],
            ['name' => 'roles.create', 'display_name' => 'Create Roles', 'description' => 'Can create new roles'],
            ['name' => 'roles.edit', 'display_name' => 'Edit Roles', 'description' => 'Can edit role information'],
            ['name' => 'roles.delete', 'display_name' => 'Delete Roles', 'description' => 'Can delete roles'],
            ['name' => 'roles.manage', 'display_name' => 'Manage Roles', 'description' => 'Full role management including permission assignment'],

            // Course permissions
            ['name' => 'courses.view', 'display_name' => 'View Courses', 'description' => 'Can view course list and details'],
            ['name' => 'courses.create', 'display_name' => 'Create Courses', 'description' => 'Can create new courses'],
            ['name' => 'courses.edit', 'display_name' => 'Edit Courses', 'description' => 'Can edit course information'],
            ['name' => 'courses.delete', 'display_name' => 'Delete Courses', 'description' => 'Can delete courses'],
            ['name' => 'courses.manage', 'display_name' => 'Manage Courses', 'description' => 'Full course management'],

            // Category permissions
            ['name' => 'categories.view', 'display_name' => 'View Categories', 'description' => 'Can view category list and details'],
            ['name' => 'categories.create', 'display_name' => 'Create Categories', 'description' => 'Can create new categories'],
            ['name' => 'categories.edit', 'display_name' => 'Edit Categories', 'description' => 'Can edit category information'],
            ['name' => 'categories.delete', 'display_name' => 'Delete Categories', 'description' => 'Can delete categories'],
            ['name' => 'categories.manage', 'display_name' => 'Manage Categories', 'description' => 'Full category management'],

            // Review permissions
            ['name' => 'reviews.view', 'display_name' => 'View Reviews', 'description' => 'Can view review list and details'],
            ['name' => 'reviews.create', 'display_name' => 'Create Reviews', 'description' => 'Can create reviews'],
            ['name' => 'reviews.edit', 'display_name' => 'Edit Reviews', 'description' => 'Can edit reviews'],
            ['name' => 'reviews.delete', 'display_name' => 'Delete Reviews', 'description' => 'Can delete reviews'],
            ['name' => 'reviews.manage', 'display_name' => 'Manage Reviews', 'description' => 'Full review management including moderation'],

            // Order permissions
            ['name' => 'orders.view', 'display_name' => 'View Orders', 'description' => 'Can view order list and details'],
            ['name' => 'orders.manage', 'display_name' => 'Manage Orders', 'description' => 'Full order management'],

            // Payment permissions
            ['name' => 'payments.view', 'display_name' => 'View Payments', 'description' => 'Can view payment details'],
            ['name' => 'payments.manage', 'display_name' => 'Manage Payments', 'description' => 'Full payment management'],

            // Instructor permissions
            ['name' => 'instructors.view', 'display_name' => 'View Instructors', 'description' => 'Can view instructor list and details'],
            ['name' => 'instructors.manage', 'display_name' => 'Manage Instructors', 'description' => 'Full instructor management'],

            // Student permissions
            ['name' => 'students.view', 'display_name' => 'View Students', 'description' => 'Can view student list and details'],
            ['name' => 'students.manage', 'display_name' => 'Manage Students', 'description' => 'Full student management'],

            // Dashboard/Analytics
            ['name' => 'dashboard.view', 'display_name' => 'View Dashboard', 'description' => 'Can view admin dashboard and analytics'],
            ['name' => 'analytics.view', 'display_name' => 'View Analytics', 'description' => 'Can view detailed analytics and reports'],
        ];

        // Create permissions
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(
                ['name' => $perm['name']],
                [
                    'permission_id' => (string) Str::uuid(),
                    'display_name' => $perm['display_name'],
                    'description' => $perm['description'],
                ]
            );
        }

        // Assign all permissions to admin role
        $adminRole = Role::find('admin');
        if ($adminRole) {
            $allPermissions = Permission::all()->pluck('permission_id')->toArray();
            $adminRole->permissions()->syncWithoutDetaching($allPermissions);
        }

        // Assign limited permissions to instructor role
        $instructorRole = Role::find('instructor');
        if ($instructorRole) {
            $instructorPermissions = [
                'courses.view', 'courses.create', 'courses.edit', 'courses.manage',
                'categories.view',
                'reviews.view', 'reviews.create',
                'instructors.view',
                'students.view',
                'dashboard.view',
            ];
            $permIds = Permission::whereIn('name', $instructorPermissions)->pluck('permission_id')->toArray();
            $instructorRole->permissions()->syncWithoutDetaching($permIds);
        }

        // Assign basic permissions to student role
        $studentRole = Role::find('student');
        if ($studentRole) {
            $studentPermissions = [
                'courses.view',
                'categories.view',
                'reviews.view', 'reviews.create', 'reviews.edit',
                'students.view',
            ];
            $permIds = Permission::whereIn('name', $studentPermissions)->pluck('permission_id')->toArray();
            $studentRole->permissions()->syncWithoutDetaching($permIds);
        }
    }
}
