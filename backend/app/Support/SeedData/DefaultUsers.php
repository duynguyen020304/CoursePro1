<?php

namespace App\Support\SeedData;

/**
 * Static class containing default user seed data.
 * Natural key: email (for UserAccount lookups)
 */
class DefaultUsers
{
    /**
     * @return array<int, array{email: string, password: string, first_name: string, last_name: string, role_id: string}>
     */
    public static function getData(): array
    {
        return [
            [
                'email' => 'admin@example.com',
                'password' => 'password',
                'first_name' => 'Admin',
                'last_name' => 'User',
                'role_id' => 'admin',
            ],
            [
                'email' => 'student@example.com',
                'password' => 'password',
                'first_name' => 'Test',
                'last_name' => 'Student',
                'role_id' => 'student',
            ],
        ];
    }
}
