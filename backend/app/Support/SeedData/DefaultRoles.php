<?php

namespace App\Support\SeedData;

/**
 * Static class containing default role seed data.
 * Deterministic UUID primary keys are used for stable role identity.
 */
class DefaultRoles
{
    public const ADMIN_ID = '11111111-1111-1111-1111-111111111111';
    public const STUDENT_ID = '22222222-2222-2222-2222-222222222222';
    public const INSTRUCTOR_ID = '33333333-3333-3333-3333-333333333333';

    /**
     * @return array<int, array{role_id: string, role_code: string, role_name: string}>
     */
    public static function getData(): array
    {
        return [
            [
                'role_id' => self::ADMIN_ID,
                'role_code' => 'admin',
                'role_name' => 'Admin',
            ],
            [
                'role_id' => self::STUDENT_ID,
                'role_code' => 'student',
                'role_name' => 'Student',
            ],
            [
                'role_id' => self::INSTRUCTOR_ID,
                'role_code' => 'instructor',
                'role_name' => 'Instructor',
            ],
        ];
    }

    public static function idForCode(string $roleCode): string
    {
        $role = collect(self::getData())->firstWhere('role_code', $roleCode);

        if (! $role) {
            throw new \InvalidArgumentException("Unknown default role code [{$roleCode}].");
        }

        return $role['role_id'];
    }
}
