<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use App\Models\UserAccount;
use App\Support\SeedData\DefaultRoles;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Skip if students already exist
        if (Student::count() > 0) {
            $this->command->info('Students already seeded. Skipping...');
            return;
        }

        $students = [
            ['email' => 'student1@example.com', 'first_name' => 'Hoang', 'last_name' => 'Minh'],
            ['email' => 'student2@example.com', 'first_name' => 'Phan', 'last_name' => 'Anh'],
            ['email' => 'student3@example.com', 'first_name' => 'Do', 'last_name' => 'Linh'],
            ['email' => 'student4@example.com', 'first_name' => 'Vu', 'last_name' => 'Trang'],
            ['email' => 'student5@example.com', 'first_name' => 'Bui', 'last_name' => 'Hai'],
            ['email' => 'student6@example.com', 'first_name' => 'Ngo', 'last_name' => 'Thu'],
            ['email' => 'student7@example.com', 'first_name' => 'Dao', 'last_name' => 'Long'],
            ['email' => 'student8@example.com', 'first_name' => 'Duong', 'last_name' => 'Lan'],
            ['email' => 'student9@example.com', 'first_name' => 'Dang', 'last_name' => 'Quang'],
            ['email' => 'student10@example.com', 'first_name' => 'Dinh', 'last_name' => 'Ha'],
        ];

        foreach ($students as $studentData) {
            $userId = Str::uuid();

            User::create([
                'user_id' => $userId,
                'first_name' => $studentData['first_name'],
                'last_name' => $studentData['last_name'],
                'role_id' => DefaultRoles::STUDENT_ID,
            ]);

            UserAccount::create([
                'user_id' => $userId,
                'email' => $studentData['email'],
                'password' => Hash::make('Student@123'),
                'provider' => 'email',
            ]);

            Student::create([
                'student_id' => Str::uuid(),
                'user_id' => $userId,
            ]);
        }
    }
}
