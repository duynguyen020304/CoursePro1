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

    public function create_user(string $email, string $password, string $firstName, string $lastName): ServiceResponse
    {
        $userBll = new UserBLL();
        $existing = $userBll->get_user_by_email($email);

        if ($existing) {
            return new ServiceResponse(false, "Email đã được sử dụng");
        }

        $userID = uniqid('u_');
        // echo $userID;
        $fullName = trim($firstName . ' ' . $lastName);
        $dto = new UserDTO($userID, $fullName, $email, $password, 'student');
        $userBll->create_user($dto);
        // echo "Tạo tài khoản thành công";
        return new ServiceResponse(true, "Tạo tài khoản thành công", $dto);
    }
}
