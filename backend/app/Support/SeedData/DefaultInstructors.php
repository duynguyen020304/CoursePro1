<?php

namespace App\Support\SeedData;

/**
 * Static class containing default instructor seed data.
 * Natural key: email (for UserAccount lookups)
 */
class DefaultInstructors
{
    /**
     * @return array<int, array{email: string, password: string, first_name: string, last_name: string, biography: string}>
     */
    public static function getData(): array
    {
        return [
            [
                'email' => 'nguyen.tuan@example.com',
                'password' => 'Instructor@123',
                'first_name' => 'Nguyen',
                'last_name' => 'Tuan',
                'biography' => 'Thầy Nguyễn Tuấn là một chuyên gia lập trình backend hàng đầu với hơn 18 năm kinh nghiệm trong ngành công nghiệp phần mềm. Chuyên môn: Java Enterprise Edition, Spring Framework, Python, Django, microservices.',
            ],
            [
                'email' => 'tran.mai@example.com',
                'password' => 'Instructor@123',
                'first_name' => 'Tran',
                'last_name' => 'Mai',
                'biography' => 'Cô Trần Mai là một nhà thiết kế UI/UX tài năng với hơn 12 năm kinh nghiệm. Chuyên môn: Human-Centered Design, Design Thinking, Interaction Design, Figma, Adobe XD.',
            ],
            [
                'email' => 'le.thanh@example.com',
                'password' => 'Instructor@123',
                'first_name' => 'Le',
                'last_name' => 'Thanh',
                'biography' => 'Thầy Lê Thanh là chuyên gia hàng đầu về AI và Machine Learning với Tiến sĩ từ Đại học Quốc gia Singapore. Chuyên môn: Deep Learning, NLP, Computer Vision, Reinforcement Learning.',
            ],
            [
                'email' => 'pham.huong@example.com',
                'password' => 'Instructor@123',
                'first_name' => 'Pham',
                'last_name' => 'Huong',
                'biography' => 'Cô Phạm Hương là kỹ sư Full-stack với hơn 10 năm kinh nghiệm. Chuyên môn: React, Angular, Vue.js, Node.js, Express, PHP, Laravel.',
            ],
            [
                'email' => 'hoang.duc@example.com',
                'password' => 'Instructor@123',
                'first_name' => 'Hoang',
                'last_name' => 'Duc',
                'biography' => 'Thầy Hoàng Đức là chuyên gia Cybersecurity với hơn 20 năm kinh nghiệm. Chuyên môn: Penetration Testing, Incident Response, Security Architecture.',
            ],
        ];
    }
}
