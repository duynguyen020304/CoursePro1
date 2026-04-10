<?php

namespace App\Support\SeedData;

/**
 * Static class containing default student seed data.
 * Natural key: email (for UserAccount lookups)
 */
class DefaultStudents
{
    /**
     * @return array<int, array{email: string, password: string, first_name: string, last_name: string}>
     */
    public static function getData(): array
    {
        return [
            ['email' => 'student1@example.com', 'password' => 'Student@123', 'first_name' => 'Hoang', 'last_name' => 'Minh'],
            ['email' => 'student2@example.com', 'password' => 'Student@123', 'first_name' => 'Phan', 'last_name' => 'Anh'],
            ['email' => 'student3@example.com', 'password' => 'Student@123', 'first_name' => 'Do', 'last_name' => 'Linh'],
            ['email' => 'student4@example.com', 'password' => 'Student@123', 'first_name' => 'Vu', 'last_name' => 'Trang'],
            ['email' => 'student5@example.com', 'password' => 'Student@123', 'first_name' => 'Bui', 'last_name' => 'Hai'],
            ['email' => 'student6@example.com', 'password' => 'Student@123', 'first_name' => 'Ngo', 'last_name' => 'Thu'],
            ['email' => 'student7@example.com', 'password' => 'Student@123', 'first_name' => 'Dao', 'last_name' => 'Long'],
            ['email' => 'student8@example.com', 'password' => 'Student@123', 'first_name' => 'Duong', 'last_name' => 'Lan'],
            ['email' => 'student9@example.com', 'password' => 'Student@123', 'first_name' => 'Dang', 'last_name' => 'Quang'],
            ['email' => 'student10@example.com', 'password' => 'Student@123', 'first_name' => 'Dinh', 'last_name' => 'Ha'],
        ];
    }
}
