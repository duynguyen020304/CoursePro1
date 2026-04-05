<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserAccount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

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

        // 2. Create admin user - skip if exists by email
        $adminAccount = UserAccount::where('email', 'admin@example.com')->first();
        if ($adminAccount) {
            // User exists, ensure User record is complete
            User::firstOrCreate(
                ['user_id' => $adminAccount->user_id],
                [
                    'first_name' => 'Admin',
                    'last_name' => 'User',
                    'role_id' => 'admin',
                ]
            );
        } else {
            // Create new admin user
            $adminUserId = Str::uuid();
            User::firstOrCreate(
                ['user_id' => $adminUserId],
                [
                    'first_name' => 'Admin',
                    'last_name' => 'User',
                    'role_id' => 'admin',
                ]
            );
            UserAccount::firstOrCreate(
                ['user_id' => $adminUserId],
                [
                    'email' => 'admin@example.com',
                    'password' => Hash::make('password'),
                    'provider' => 'email',
                ]
            );
        }

        // 3. Create a test student user for testing - skip if exists by email
        $testStudentAccount = UserAccount::where('email', 'student@example.com')->first();
        if ($testStudentAccount) {
            // User exists, ensure User record is complete
            User::firstOrCreate(
                ['user_id' => $testStudentAccount->user_id],
                [
                    'first_name' => 'Test',
                    'last_name' => 'Student',
                    'role_id' => 'student',
                ]
            );
        } else {
            // Create new test student user
            $testStudentUserId = Str::uuid();
            User::firstOrCreate(
                ['user_id' => $testStudentUserId],
                [
                    'first_name' => 'Test',
                    'last_name' => 'Student',
                    'role_id' => 'student',
                ]
            );
            UserAccount::firstOrCreate(
                ['user_id' => $testStudentUserId],
                [
                    'email' => 'student@example.com',
                    'password' => Hash::make('password'),
                    'provider' => 'email',
                ]
            );
        }

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