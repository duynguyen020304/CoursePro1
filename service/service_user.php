<?php

require_once __DIR__ . '/../model/database.php';
require_once __DIR__ . '/../model/bll/user_bll.php';
require_once __DIR__ . '/../model/dto/user_dto.php';
require_once __DIR__ . '/service_response.php';  // service_response nằm cùng thư mục

class UserService
{
    private UserBLL $userBll;

    public function __construct()
    {
        $this->userBll = new UserBLL();
    }

    public function authenticate(string $email, string $password): ServiceResponse
    {
        $user = $this->userBll->authenticate($email, $password);
        if (!$user) {
            return new ServiceResponse(false, 'Email hoặc mật khẩu không đúng');
        }
        return new ServiceResponse(true, 'Đăng nhập thành công', $user);
    }

    public function create_user(string $email, string $password, string $firstName, string $lastName, string $role): ServiceResponse
    {
        // echo "đang ở phía trước BLL";
        $userBll = new UserBLL();
        $existing = $userBll->get_user_by_email($email);

        if ($existing) {
            return new ServiceResponse(false, "Email đã được sử dụng");
        }

        $userID = uniqid('u_');

        $fullName = trim($firstName . ' ' . $lastName);
        
        $dto = new UserDTO($userID, $fullName, $email, $password, $role);
        if ($userBll->create_user($dto)) {
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

        $newEmail     = isset($data['email'])
            ? $data['email']
            : $existing->email;

        $newRole      = isset($data['role'])
            ? $data['role']
            : $existing->roleID;

        $newName = isset($data['name'])
            ? $data['name']
            : $existing->name;


        // Tạo DTO mới với đủ 6 tham số
        $updated = new UserDTO(
            $existing->userID,
            $newName,
            $newEmail,
            $newPassword,
            $newRole
        );
        $this->userBll->update_user($updated);
        return new ServiceResponse(true, 'Cập nhật người dùng thành công');
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

            $this->userBll->delete_user($userID); // đảm bảo có phương thức này
            return new ServiceResponse(true, 'Xóa người dùng thành công');
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi xóa: ' . $e->getMessage());
        }
    }
}
