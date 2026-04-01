<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create roles first (no dependencies) - skip if exists
        \App\Models\Role::firstOrCreate(['role_id' => 'admin'], ['role_name' => 'Admin']);
        \App\Models\Role::firstOrCreate(['role_id' => 'student'], ['role_name' => 'Student']);
        \App\Models\Role::firstOrCreate(['role_id' => 'instructor'], ['role_name' => 'Instructor']);

        // 2. Create admin user - skip if exists
        \App\Models\User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'user_id' => \Str::uuid(),
                'first_name' => 'Admin',
                'last_name' => 'User',
                'password' => bcrypt('password'),
                'role_id' => 'admin',
            ]
        );

        // 3. Create a test student user for testing
        $testStudent = \App\Models\User::firstOrCreate(
            ['email' => 'student@example.com'],
            [
                'user_id' => \Str::uuid(),
                'first_name' => 'Test',
                'last_name' => 'Student',
                'password' => bcrypt('password'),
                'role_id' => 'student',
            ]
        );

        // 4. Seed permissions and assign to roles
        $this->call(PermissionSeeder::class);

        // 5. Seed categories (no dependencies)
        $this->call(CategorySeeder::class);

        // 6. Seed instructors (depends on users/roles)
        $this->call(InstructorSeeder::class);

        // 7. Seed students (depends on users/roles)
        $this->call(StudentSeeder::class);

        // 8. Seed courses (depends on instructors, categories)
        $this->call(CourseSeeder::class);

        // 9. Seed orders, cart items, and reviews (depends on students, courses)
        $this->call(OrderSeeder::class);
    }
}
