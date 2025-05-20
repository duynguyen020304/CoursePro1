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
        $passwordAdmin = password_hash("duyadmin123", PASSWORD_DEFAULT);
        $adminID = str_replace('.', '_', uniqid('admin', true));
        $admin_sql = "INSERT INTO Users (UserID, FirstName, LastName, Email, Password, RoleID, ProfileImage) VALUES ('{$adminID}', 'Duy', 'Admin', 'duyadmin123@example.com', '{$passwordAdmin}', 'admin', 'null')";
        $this->db->execute($admin_sql);
        // Create 4 instructor accounts
        $instructors = [
            [
                'email' => 'instructor1@example.com',
                'password' => 'Instructor@123',
                'firstName' => 'Nguyen',
                'lastName' => 'Tuan',
                'role' => 'instructor',
                'profileImage' => 'default'
            ],
            [
                'email' => 'instructor2@example.com',
                'password' => 'Instructor@123',
                'firstName' => 'Tran',
                'lastName' => 'Mai',
                'role' => 'instructor',
                'profileImage' => 'default'
            ],
            [
                'email' => 'instructor3@example.com',
                'password' => 'Instructor@123',
                'firstName' => 'Le',
                'lastName' => 'Thanh',
                'role' => 'instructor',
                'profileImage' => 'default'
            ],
            [
                'email' => 'instructor4@example.com',
                'password' => 'Instructor@123',
                'firstName' => 'Pham',
                'lastName' => 'Huong',
                'role' => 'instructor',
                'profileImage' => 'default'
            ]
        ];

        // Create 10 student accounts
        $students = [
            [
                'email' => 'student1@example.com',
                'password' => 'Student@123',
                'firstName' => 'Hoang',
                'lastName' => 'Minh',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student2@example.com',
                'password' => 'Student@123',
                'firstName' => 'Phan',
                'lastName' => 'Anh',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student3@example.com',
                'password' => 'Student@123',
                'firstName' => 'Do',
                'lastName' => 'Linh',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student4@example.com',
                'password' => 'Student@123',
                'firstName' => 'Vu',
                'lastName' => 'Trang',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student5@example.com',
                'password' => 'Student@123',
                'firstName' => 'Bui',
                'lastName' => 'Hai',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student6@example.com',
                'password' => 'Student@123',
                'firstName' => 'Ngo',
                'lastName' => 'Thu',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student7@example.com',
                'password' => 'Student@123',
                'firstName' => 'Dao',
                'lastName' => 'Long',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student8@example.com',
                'password' => 'Student@123',
                'firstName' => 'Duong',
                'lastName' => 'Lan',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student9@example.com',
                'password' => 'Student@123',
                'firstName' => 'Dang',
                'lastName' => 'Quang',
                'role' => 'student',
                'profileImage' => 'default'
            ],
            [
                'email' => 'student10@example.com',
                'password' => 'Student@123',
                'firstName' => 'Dinh',
                'lastName' => 'Ha',
                'role' => 'student',
                'profileImage' => 'default'
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
                $instructor['role'],
                $instructor['profileImage']
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
                $student['role'],
                $student['profileImage']
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