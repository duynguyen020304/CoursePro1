<?php

require_once __DIR__ . '/../model/database.php';
require_once __DIR__ . '/../model/bll/user_bll.php';
require_once __DIR__ . '/../model/bll/instructor_bll.php';
require_once __DIR__ . '/../model/dto/user_dto.php';
require_once __DIR__ . '/../model/dto/instructor_dto.php';
require_once __DIR__ . '/../model/dto/student_dto.php';
require_once __DIR__ . '/../model/bll/student_bll.php';
require_once __DIR__ . '/../model/bll/role_bll.php';
require_once __DIR__ . '/../model/dto/role_dto.php';
require_once __DIR__ . '/service_response.php';  // service_response nằm cùng thư mục

class UserService
{
    private UserBLL $userBll;
    private InstructorBLL $instructorBll;
    private StudentBLL $studentBll;

    public function __construct()
    {
        $this->userBll = new UserBLL();
        $this->instructorBll = new InstructorBLL();
        $this->studentBll = new StudentBLL();
    }

    public function authenticate(string $email, string $password): ServiceResponse
    {
        $user = $this->userBll->authenticate($email, $password);
        if (!$user) {
            return new ServiceResponse(false, 'Email hoặc mật khẩu không đúng');
        }
        return new ServiceResponse(true, 'Đăng nhập thành công', $user);
    }

    public function create_user(string $email, string $password, string $firstName, string $lastName, string $role, ?string $profileImage = null): ServiceResponse
    {
        if ($role == "admin") {
            return new ServiceResponse(false, "Không cho phép tạo tài khoản có role admin");
        }
        else if ($role != "instructor" && $role != "student") {
            return new ServiceResponse(false, "Vai trò không có trên hệ thống");
        }
        $userBll = new UserBLL();
        $existing = $userBll->get_user_by_email($email);
        if ($existing) {
            return new ServiceResponse(false, "Email đã được sử dụng");
        }
        $userID =str_replace('.', '_', uniqid('user', true));
//        $fullName = trim($firstName . ' ' . $lastName);

        $dto = new UserDTO($userID, $firstName, $lastName, $email, $password, $role, $profileImage);
        if ($this->userBll->create_user($dto)) {
            if ($role == "instructor") {
                $instructorDto = new InstructorDTO(
                    str_replace('.', '_', uniqid('instructor_', true)),
                    $userID
                );
                if (!$this->instructorBll->create_instructor($instructorDto)) {
                    return new ServiceResponse(false, "Tạo tài khoản cho giảng viên thất bại");
                }
            }
            else if ($role == "student") {
                $studentDto = new StudentDTO(
                    str_replace('.', '_', uniqid('student', true)),
                    $userID
                );
                if (!$this->studentBll->create_student($studentDto)) {
                    return new ServiceResponse(false, "Tạo tài khoản cho học sinh thất bại");
                }
            }
            return new ServiceResponse(true, "Tạo tài khoản thành công", $dto);
        }
        return new ServiceResponse(false, "Tạo tài khoản thất bại");
    }

    public function get_user_by_id(string $userID): ServiceResponse
    {
        try {
            $user = $this->userBll->get_user_by_id($userID);
            if (!$user) {
                return new ServiceResponse(false, 'Người dùng không tồn tại');
            }
            return new ServiceResponse(true, 'Lấy thông tin người dùng thành công', $user);
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi: ' . $e->getMessage());
        }
    }
    public function get_all_users(): ServiceResponse
    {
        try {
            $users = $this->userBll->get_all_users();
            return new ServiceResponse(true, 'Lấy danh sách người dùng thành công', $users);
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi: ' . $e->getMessage());
        }
    }
    public function update_user_partial(array $data): ServiceResponse
    {
        try {
            if (empty($data['userID'])) {
                return new ServiceResponse(false, 'Thiếu userID');
            }

            $existing = $this->userBll->get_user_by_id($data['userID']);
            if (!$existing) {
                return new ServiceResponse(false, 'Người dùng không tồn tại');
            }

            // build từng trường: nếu client có thì dùng, không thì giữ nguyên
            $newPassword = isset($data['password'])
                ? password_hash($data['password'], PASSWORD_DEFAULT)
                : $existing->password;

            $newEmail = $data['email'] ?? $existing->email;

            $newRole = $data['role'] ?? $existing->roleID;

            $newFirstName = $data['firstName'] ?? $existing->firstName;

            $newLastName = $data['lasttName'] ?? $existing->lastName;

            $newProfileImage = $data['profileImage'] ?? $existing->profileImage;

            // Tạo DTO mới với đủ 6 tham số
            $updated = new UserDTO(
                $existing->userID,
                $newFirstName,
                $newLastName,
                $newEmail,
                $newPassword,
                $newRole,
                $newProfileImage
            );
            if ($this->userBll->update_user($updated)) {
                return new ServiceResponse(true, 'Cập nhật người dùng thành công');
            }
            return new ServiceResponse(false, 'Cập nhật người dùng thất bại');
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi cập nhật: ' . $e->getMessage());
        }
    }

    public function delete_user(string $userID): ServiceResponse
    {
        try {
            $exists = $this->userBll->get_user_by_id($userID);
            if (!$exists) {
                return new ServiceResponse(false, 'Người dùng không tồn tại');
            }

            if ($this->userBll->delete_user($userID)) {
                return new ServiceResponse(true, 'Xóa người dùng thành công');
            }
            return new ServiceResponse(true, 'Xóa người dùng thất bại');
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi xóa: ' . $e->getMessage());
        }
    }
}
