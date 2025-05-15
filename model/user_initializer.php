<?php
require_once __DIR__ . '/../service/service_user.php';
require_once __DIR__ . '/../model/database.php';


class UserInitializer
{
    private UserService $userService;
    private Database $db;

    public function __construct()
    {
        $this->userService = new UserService();
        $this->db = new Database();
    }

    public function initialize(): void
    {
        echo "Starting user initialization...\n";
        $this->db.
        // Create 4 instructor accounts
        $instructors = [
            [
                'email' => 'instructor1@example.com',
                'password' => 'Instructor@123',
                'firstName' => 'Nguyen',
                'lastName' => 'Tuan',
                'role' => 'instructor'
            ],
            [
                'email' => 'instructor2@example.com',
                'password' => 'Instructor@123',
                'firstName' => 'Tran',
                'lastName' => 'Mai',
                'role' => 'instructor'
            ],
            [
                'email' => 'instructor3@example.com',
                'password' => 'Instructor@123',
                'firstName' => 'Le',
                'lastName' => 'Thanh',
                'role' => 'instructor'
            ],
            [
                'email' => 'instructor4@example.com',
                'password' => 'Instructor@123',
                'firstName' => 'Pham',
                'lastName' => 'Huong',
                'role' => 'instructor'
            ]
        ];

        // Create 10 student accounts
        $students = [
            [
                'email' => 'student1@example.com',
                'password' => 'Student@123',
                'firstName' => 'Hoang',
                'lastName' => 'Minh',
                'role' => 'student'
            ],
            [
                'email' => 'student2@example.com',
                'password' => 'Student@123',
                'firstName' => 'Phan',
                'lastName' => 'Anh',
                'role' => 'student'
            ],
            [
                'email' => 'student3@example.com',
                'password' => 'Student@123',
                'firstName' => 'Do',
                'lastName' => 'Linh',
                'role' => 'student'
            ],
            [
                'email' => 'student4@example.com',
                'password' => 'Student@123',
                'firstName' => 'Vu',
                'lastName' => 'Trang',
                'role' => 'student'
            ],
            [
                'email' => 'student5@example.com',
                'password' => 'Student@123',
                'firstName' => 'Bui',
                'lastName' => 'Hai',
                'role' => 'student'
            ],
            [
                'email' => 'student6@example.com',
                'password' => 'Student@123',
                'firstName' => 'Ngo',
                'lastName' => 'Thu',
                'role' => 'student'
            ],
            [
                'email' => 'student7@example.com',
                'password' => 'Student@123',
                'firstName' => 'Dao',
                'lastName' => 'Long',
                'role' => 'student'
            ],
            [
                'email' => 'student8@example.com',
                'password' => 'Student@123',
                'firstName' => 'Duong',
                'lastName' => 'Lan',
                'role' => 'student'
            ],
            [
                'email' => 'student9@example.com',
                'password' => 'Student@123',
                'firstName' => 'Dang',
                'lastName' => 'Quang',
                'role' => 'student'
            ],
            [
                'email' => 'student10@example.com',
                'password' => 'Student@123',
                'firstName' => 'Dinh',
                'lastName' => 'Ha',
                'role' => 'student'
            ]
        ];

        // Create instructors
        echo "Creating instructor accounts...\n";
        foreach ($instructors as $instructor) {
            $response = $this->userService->create_user(
                $instructor['email'],
                $instructor['password'],
                $instructor['firstName'],
                $instructor['lastName'],
                $instructor['role']
            );
            
            if ($response->success) {
                echo "Created instructor: {$instructor['firstName']} {$instructor['lastName']} ({$instructor['email']})\n";
            } else {
                echo "Failed to create instructor {$instructor['email']}: {$response->message}\n";
            }
        }

        // Create students
        echo "Creating student accounts...\n";
        foreach ($students as $student) {
            $response = $this->userService->create_user(
                $student['email'],
                $student['password'],
                $student['firstName'],
                $student['lastName'],
                $student['role']
            );
            
            if ($response->success) {
                echo "Created student: {$student['firstName']} {$student['lastName']} ({$student['email']})\n";
            } else {
                echo "Failed to create student {$student['email']}: {$response->message}\n";
            }
        }

        echo "User initialization completed!\n";
    }
}

// Run the initializer
$initializer = new UserInitializer();
$initializer->initialize();