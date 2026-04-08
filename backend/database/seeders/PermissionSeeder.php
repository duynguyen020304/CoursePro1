<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Support\RbacPermissionMap;
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
            // Access permissions
            ['name' => RbacPermissionMap::ADMIN_ACCESS, 'display_name' => 'Admin Access', 'description' => 'Can access admin-only capabilities'],
            ['name' => RbacPermissionMap::INSTRUCTOR_ACCESS, 'display_name' => 'Instructor Access', 'description' => 'Can access instructor-only capabilities'],

            // Account permissions
            ['name' => 'profile.view', 'display_name' => 'View Profile', 'description' => 'Can view profile and account information'],
            ['name' => 'profile.edit', 'display_name' => 'Edit Profile', 'description' => 'Can edit profile and account information'],
            ['name' => 'profile.view.own', 'display_name' => 'View Own Profile', 'description' => 'Can view their own profile'],
            ['name' => 'profile.edit.own', 'display_name' => 'Edit Own Profile', 'description' => 'Can edit their own profile'],
            ['name' => 'password.change', 'display_name' => 'Change Password', 'description' => 'Can change their own password'],

            // Commerce / learner permissions
            ['name' => 'cart.view', 'display_name' => 'View Cart', 'description' => 'Can view cart contents'],
            ['name' => 'cart.manage', 'display_name' => 'Manage Cart', 'description' => 'Can add, remove, and clear cart items'],
            ['name' => 'cart.manage.own', 'display_name' => 'Manage Own Cart', 'description' => 'Can manage their own cart'],
            ['name' => 'checkout.create', 'display_name' => 'Create Checkout', 'description' => 'Can proceed to checkout'],
            ['name' => 'orders.create', 'display_name' => 'Create Orders', 'description' => 'Can create new orders'],
            ['name' => 'payments.complete', 'display_name' => 'Complete Payments', 'description' => 'Can complete payment for their own orders'],
            ['name' => 'my-courses.view', 'display_name' => 'View My Courses', 'description' => 'Can view their purchased courses and learning dashboard'],
            ['name' => 'courses.learn', 'display_name' => 'Learn Courses', 'description' => 'Can access course learning content'],
            ['name' => 'courses.consume.own', 'display_name' => 'Consume Owned Courses', 'description' => 'Can consume owned course lessons'],
            ['name' => 'certificates.view', 'display_name' => 'View Certificates', 'description' => 'Can view earned certificates'],
            ['name' => 'certificates.view.own', 'display_name' => 'View Own Certificates', 'description' => 'Can view their own earned certificates'],

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
            ['name' => 'courses.view.any', 'display_name' => 'View Any Course', 'description' => 'Can view any course in admin or backoffice surfaces'],
            ['name' => 'courses.view.own', 'display_name' => 'View Own Courses', 'description' => 'Can view owned instructor courses'],
            ['name' => 'courses.create', 'display_name' => 'Create Courses', 'description' => 'Can create new courses'],
            ['name' => 'courses.edit', 'display_name' => 'Edit Courses', 'description' => 'Can edit course information'],
            ['name' => 'courses.edit.any', 'display_name' => 'Edit Any Course', 'description' => 'Can edit any course regardless of ownership'],
            ['name' => 'courses.edit.own', 'display_name' => 'Edit Own Courses', 'description' => 'Can edit only owned courses'],
            ['name' => 'courses.delete', 'display_name' => 'Delete Courses', 'description' => 'Can delete courses'],
            ['name' => 'courses.manage', 'display_name' => 'Manage Courses', 'description' => 'Full course management'],
            ['name' => 'courses.manage.any', 'display_name' => 'Manage Any Course', 'description' => 'Can manage any course regardless of ownership'],
            ['name' => 'courses.manage.own', 'display_name' => 'Manage Own Courses', 'description' => 'Can manage only owned courses'],

            // Category permissions
            ['name' => 'categories.view', 'display_name' => 'View Categories', 'description' => 'Can view category list and details'],
            ['name' => 'categories.create', 'display_name' => 'Create Categories', 'description' => 'Can create new categories'],
            ['name' => 'categories.edit', 'display_name' => 'Edit Categories', 'description' => 'Can edit category information'],
            ['name' => 'categories.delete', 'display_name' => 'Delete Categories', 'description' => 'Can delete categories'],
            ['name' => 'categories.manage', 'display_name' => 'Manage Categories', 'description' => 'Full category management'],

            // Course content permissions
            ['name' => 'chapters.manage', 'display_name' => 'Manage Chapters', 'description' => 'Can create, edit, and delete course chapters'],
            ['name' => 'lessons.view', 'display_name' => 'View Lessons', 'description' => 'Can view lessons, lesson videos, and lesson resources'],
            ['name' => 'lessons.manage', 'display_name' => 'Manage Lessons', 'description' => 'Can create, edit, and delete lessons'],
            ['name' => 'lessons.manage.any', 'display_name' => 'Manage Any Lessons', 'description' => 'Can manage any lessons regardless of ownership'],
            ['name' => 'lessons.manage.own', 'display_name' => 'Manage Own Lessons', 'description' => 'Can manage lessons belonging to owned courses'],
            ['name' => 'videos.manage', 'display_name' => 'Manage Videos', 'description' => 'Can upload, edit, and delete lesson videos'],
            ['name' => 'videos.manage.any', 'display_name' => 'Manage Any Videos', 'description' => 'Can manage any lesson videos'],
            ['name' => 'videos.manage.own', 'display_name' => 'Manage Own Videos', 'description' => 'Can manage videos belonging to owned courses'],
            ['name' => 'resources.manage', 'display_name' => 'Manage Resources', 'description' => 'Can upload, edit, and delete lesson resources'],
            ['name' => 'resources.manage.any', 'display_name' => 'Manage Any Resources', 'description' => 'Can manage any lesson resources'],
            ['name' => 'resources.manage.own', 'display_name' => 'Manage Own Resources', 'description' => 'Can manage resources belonging to owned courses'],

            // Review permissions
            ['name' => 'reviews.view', 'display_name' => 'View Reviews', 'description' => 'Can view review list and details'],
            ['name' => 'reviews.create', 'display_name' => 'Create Reviews', 'description' => 'Can create reviews'],
            ['name' => 'reviews.edit', 'display_name' => 'Edit Reviews', 'description' => 'Can edit reviews'],
            ['name' => 'reviews.delete', 'display_name' => 'Delete Reviews', 'description' => 'Can delete reviews'],
            ['name' => 'reviews.manage', 'display_name' => 'Manage Reviews', 'description' => 'Full review management including moderation'],

            // Order permissions
            ['name' => 'orders.view', 'display_name' => 'View Orders', 'description' => 'Can view order list and details'],
            ['name' => 'orders.view.any', 'display_name' => 'View Any Orders', 'description' => 'Can view any order'],
            ['name' => 'orders.view.own', 'display_name' => 'View Own Orders', 'description' => 'Can view their own orders'],
            ['name' => 'orders.manage', 'display_name' => 'Manage Orders', 'description' => 'Full order management'],

            // Payment permissions
            ['name' => 'payments.view', 'display_name' => 'View Payments', 'description' => 'Can view payment details'],
            ['name' => 'payments.view.any', 'display_name' => 'View Any Payments', 'description' => 'Can view any payment details'],
            ['name' => 'payments.view.own', 'display_name' => 'View Own Payments', 'description' => 'Can view their own payment details'],
            ['name' => 'payments.manage', 'display_name' => 'Manage Payments', 'description' => 'Full payment management'],

            // Instructor permissions
            ['name' => 'instructors.view', 'display_name' => 'View Instructors', 'description' => 'Can view instructor list and details'],
            ['name' => 'instructors.manage', 'display_name' => 'Manage Instructors', 'description' => 'Full instructor management'],
            ['name' => 'instructor.profile.manage', 'display_name' => 'Manage Instructor Profile', 'description' => 'Can create and update instructor profiles'],

            // Student permissions
            ['name' => 'students.view', 'display_name' => 'View Students', 'description' => 'Can view student list and details'],
            ['name' => 'students.manage', 'display_name' => 'Manage Students', 'description' => 'Full student management'],

            // Dashboard/Analytics
            ['name' => 'dashboard.view', 'display_name' => 'View Dashboard', 'description' => 'Can view dashboard surfaces'],
            ['name' => 'dashboard.admin.view', 'display_name' => 'View Admin Dashboard', 'description' => 'Can view the admin dashboard'],
            ['name' => 'dashboard.instructor.view', 'display_name' => 'View Instructor Dashboard', 'description' => 'Can view the instructor dashboard'],
            ['name' => 'revenue.view', 'display_name' => 'View Revenue', 'description' => 'Can view revenue dashboards and reports'],
            ['name' => 'analytics.view', 'display_name' => 'View Analytics', 'description' => 'Can view detailed analytics and reports'],
        ];

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

        foreach (RbacPermissionMap::defaultRolePermissions() as $roleId => $permissionNames) {
            $role = Role::find($roleId);

            if (!$role) {
                continue;
            }

            $permissionIds = Permission::whereIn('name', $permissionNames)->pluck('permission_id')->toArray();
            $role->permissions()->syncWithoutDetaching($permissionIds);
        }
    }
}
