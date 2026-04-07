<?php

namespace App\Support;

final class RbacPermissionMap
{
    public const ADMIN_ACCESS = 'admin.access';
    public const INSTRUCTOR_ACCESS = 'instructor.access';

    /**
     * @return array<string, array<int, string>>
     */
    public static function defaultRolePermissions(): array
    {
        return [
            'admin' => [
                self::ADMIN_ACCESS,
                self::INSTRUCTOR_ACCESS,
                'profile.view', 'profile.edit', 'profile.view.own', 'profile.edit.own', 'password.change',
                'cart.view', 'cart.manage', 'cart.manage.own', 'checkout.create',
                'users.view', 'users.create', 'users.edit', 'users.delete', 'users.manage',
                'roles.view', 'roles.create', 'roles.edit', 'roles.delete', 'roles.manage',
                'courses.view', 'courses.view.any', 'courses.view.own', 'courses.create', 'courses.edit', 'courses.edit.any', 'courses.edit.own', 'courses.delete', 'courses.manage', 'courses.manage.any', 'courses.manage.own', 'courses.learn', 'courses.consume.own',
                'categories.view', 'categories.create', 'categories.edit', 'categories.delete', 'categories.manage',
                'chapters.manage',
                'lessons.view', 'lessons.manage', 'lessons.manage.any', 'lessons.manage.own',
                'videos.manage', 'videos.manage.any', 'videos.manage.own',
                'resources.manage', 'resources.manage.any', 'resources.manage.own',
                'reviews.view', 'reviews.create', 'reviews.edit', 'reviews.delete', 'reviews.manage',
                'orders.view', 'orders.view.any', 'orders.view.own', 'orders.create', 'orders.manage',
                'payments.view', 'payments.view.any', 'payments.view.own', 'payments.manage', 'payments.complete',
                'instructors.view', 'instructors.manage', 'instructor.profile.manage',
                'students.view', 'students.manage',
                'dashboard.view', 'dashboard.admin.view', 'dashboard.instructor.view', 'revenue.view', 'analytics.view',
                'certificates.view', 'certificates.view.own',
            ],
            'instructor' => [
                self::INSTRUCTOR_ACCESS,
                'profile.view', 'profile.edit', 'profile.view.own', 'profile.edit.own', 'password.change',
                'courses.view', 'courses.view.own', 'courses.create', 'courses.edit', 'courses.edit.own', 'courses.manage', 'courses.manage.own', 'courses.learn', 'courses.consume.own',
                'categories.view',
                'chapters.manage',
                'lessons.view', 'lessons.manage', 'lessons.manage.own',
                'videos.manage', 'videos.manage.own',
                'resources.manage', 'resources.manage.own',
                'reviews.view', 'reviews.create',
                'orders.view', 'orders.view.own',
                'payments.view.own', 'payments.complete',
                'instructors.view', 'instructor.profile.manage',
                'students.view',
                'dashboard.view', 'dashboard.instructor.view',
                'certificates.view', 'certificates.view.own',
            ],
            'student' => [
                'profile.view', 'profile.edit', 'profile.view.own', 'profile.edit.own', 'password.change',
                'cart.view', 'cart.manage', 'cart.manage.own', 'checkout.create',
                'courses.view', 'courses.learn', 'courses.consume.own',
                'categories.view',
                'reviews.view', 'reviews.create', 'reviews.edit',
                'orders.view', 'orders.view.own', 'orders.create',
                'payments.view.own', 'payments.complete',
                'students.view',
                'lessons.view',
                'certificates.view', 'certificates.view.own',
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function permissionsForRole(?string $roleId): array
    {
        if (!$roleId) {
            return [];
        }

        return self::defaultRolePermissions()[$roleId] ?? [];
    }

    public static function roleHasPermission(?string $roleId, string $permission): bool
    {
        return in_array($permission, self::permissionsForRole($roleId), true);
    }
}
