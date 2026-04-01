<?php

namespace Database\Seeders;

use App\Models\Instructor;
use App\Models\User;
use App\Models\UserAccount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class InstructorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Skip if instructors already exist
        if (Instructor::count() > 0) {
            $this->command->info('Instructors already seeded. Skipping...');
            return;
        }

        $instructors = [
            [
                'email' => 'nguyen.tuan@example.com',
                'password' => 'Instructor@123',
                'first_name' => 'Nguyen',
                'last_name' => 'Tuan',
                'role_id' => 'instructor',
                'biography' => 'Thầy Nguyễn Tuấn là một chuyên gia lập trình backend hàng đầu với hơn 18 năm kinh nghiệm trong ngành công nghiệp phần mềm. Chuyên môn: Java Enterprise Edition, Spring Framework, Python, Django, microservices.',
            ],
            [
                'email' => 'tran.mai@example.com',
                'password' => 'Instructor@123',
                'first_name' => 'Tran',
                'last_name' => 'Mai',
                'role_id' => 'instructor',
                'biography' => 'Cô Trần Mai là một nhà thiết kế UI/UX tài năng với hơn 12 năm kinh nghiệm. Chuyên môn: Human-Centered Design, Design Thinking, Interaction Design, Figma, Adobe XD.',
            ],
            [
                'email' => 'le.thanh@example.com',
                'password' => 'Instructor@123',
                'first_name' => 'Le',
                'last_name' => 'Thanh',
                'role_id' => 'instructor',
                'biography' => 'Thầy Lê Thanh là chuyên gia hàng đầu về AI và Machine Learning với Tiến sĩ từ Đại học Quốc gia Singapore. Chuyên môn: Deep Learning, NLP, Computer Vision, Reinforcement Learning.',
            ],
            [
                'email' => 'pham.huong@example.com',
                'password' => 'Instructor@123',
                'first_name' => 'Pham',
                'last_name' => 'Huong',
                'role_id' => 'instructor',
                'biography' => 'Cô Phạm Hương là kỹ sư Full-stack với hơn 10 năm kinh nghiệm. Chuyên môn: React, Angular, Vue.js, Node.js, Express, PHP, Laravel.',
            ],
            [
                'email' => 'hoang.duc@example.com',
                'password' => 'Instructor@123',
                'first_name' => 'Hoang',
                'last_name' => 'Duc',
                'role_id' => 'instructor',
                'biography' => 'Thầy Hoàng Đức là chuyên gia Cybersecurity với hơn 20 năm kinh nghiệm. Chuyên môn: Penetration Testing, Incident Response, Security Architecture.',
            ],
        ];

        foreach ($instructors as $instructorData) {
            $userId = Str::uuid();

            $user = User::create([
                'user_id' => $userId,
                'first_name' => $instructorData['first_name'],
                'last_name' => $instructorData['last_name'],
                'role_id' => $instructorData['role_id'],
            ]);

            UserAccount::create([
                'user_id' => $userId,
                'email' => $instructorData['email'],
                'password' => Hash::make($instructorData['password']),
                'provider' => 'email',
            ]);

            Instructor::create([
                'instructor_id' => Str::uuid(),
                'user_id' => $userId,
                'biography' => $instructorData['biography'],
            ]);
        }
    }
}