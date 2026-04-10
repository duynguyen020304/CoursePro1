<?php

namespace App\Support\SeedData;

/**
 * Static class containing default role seed data.
 * Natural key: role_id (e.g., 'admin', 'student', 'instructor')
 */
class DefaultRoles
{
    /**
     * @return array<int, array{role_id: string, role_name: string}>
     */
    public static function getData(): array
    {
        return [
            [
                'role_id' => 'admin',
                'role_name' => 'Admin',
            ],
            [
                'role_id' => 'student',
                'role_name' => 'Student',
            ],
            [
                'role_id' => 'instructor',
                'role_name' => 'Instructor',
            ],
        ];
    }
}
