<?php
// Thay đổi đường dẫn để trỏ đến database_mysql.php
require_once __DIR__ . '/../database.php'; 
require_once __DIR__ . '/../dto/user_dto.php';

class UserBLL extends Database
{
    /**
     * Tạo một người dùng mới trong cơ sở dữ liệu.
     * @param UserDTO $user Đối tượng DTO chứa thông tin người dùng.
     * @return bool Trả về true nếu tạo thành công, ngược lại false.
     */
    public function create_user(UserDTO $user): bool
    {
        // Băm mật khẩu trước khi lưu
        $hashedPassword = password_hash($user->password, PASSWORD_DEFAULT);
        
        // Câu lệnh SQL INSERT cho MySQL
        $sql = "INSERT INTO users (userID, firstName, lastName, email, password, roleID, profileImage) VALUES (?, ?, ?, ?, ?, ?, ?)";

        // Tham số cho prepared statement
        $params = [
            $user->userID,
            $user->firstName,
            $user->lastName,
            $user->email,
            $hashedPassword,
            $user->roleID,
            $user->profileImage,
        ];

        // Thực thi câu lệnh và kiểm tra số dòng bị ảnh hưởng
        $this->executePrepared($sql, $params);
        return $this->getAffectedRows() > 0;
    }

    /**
     * Xác thực người dùng dựa trên email và mật khẩu.
     * @param string $email Email của người dùng.
     * @param string $password Mật khẩu của người dùng.
     * @return UserDTO|null Trả về đối tượng UserDTO nếu xác thực thành công, ngược lại null.
     */
    public function authenticate(string $email, string $password): ?UserDTO
    {
        // Câu lệnh SELECT để lấy thông tin người dùng cho việc xác thực
        $sql = "SELECT userID, firstName, lastName, email, password, roleID, profileImage, DATE_FORMAT(created_at, '%d-%m-%Y %H:%i:%s') AS created_at_formatted FROM users WHERE email = ?";
        $params = [$email];

        $result = $this->executePrepared($sql, $params);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            // Xác minh mật khẩu
            if (isset($row['password']) && password_verify($password, $row['password'])) {
                // Trả về DTO không bao gồm mật khẩu
                return new UserDTO(
                    $row['userID'],
                    $row['firstName'],
                    $row['lastName'],
                    $row['email'],
                    "", // Không trả về mật khẩu
                    $row['roleID'],
                    $row['profileImage'],
                    $row['created_at_formatted'] ?? null
                );
            }
        }
        return null;
    }

    /**
     * Xóa một người dùng khỏi cơ sở dữ liệu.
     * @param string $userID ID của người dùng cần xóa.
     * @return bool Trả về true nếu xóa thành công, ngược lại false.
     */
    public function delete_user(string $userID): bool
    {
        $sql = "DELETE FROM users WHERE userID = ?";
        $params = [$userID];
        $this->executePrepared($sql, $params);
        return $this->getAffectedRows() > 0;
    }

    /**
     * Cập nhật thông tin người dùng.
     * @param UserDTO $user Đối tượng DTO chứa thông tin cần cập nhật.
     * @return bool Trả về true nếu cập nhật thành công, ngược lại false.
     */
    public function update_user(UserDTO $user): bool
    {
        $params = [];
        // Chỉ cập nhật mật khẩu nếu một mật khẩu mới được cung cấp
        if (!empty($user->password)) {
            $hashedPassword = password_hash($user->password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET firstName = ?, lastName = ?, password = ?, roleID = ?, profileImage = ? WHERE userID = ?";
            $params = [
                $user->firstName,
                $user->lastName,
                $hashedPassword,
                $user->roleID,
                $user->profileImage,
                $user->userID
            ];
        } else {
            // Cập nhật không bao gồm mật khẩu
            $sql = "UPDATE users SET firstName = ?, lastName = ?, roleID = ?, profileImage = ? WHERE userID = ?";
            $params = [
                $user->firstName,
                $user->lastName,
                $user->roleID,
                $user->profileImage,
                $user->userID
            ];
        }

        $this->executePrepared($sql, $params);
        return $this->getAffectedRows() > 0;
    }

    /**
     * Lấy thông tin người dùng bằng userID.
     * @param string $userID ID của người dùng.
     * @param string $purpose Mục đích lấy thông tin ('get', 'update', 'internal_check').
     * @return UserDTO|null Trả về đối tượng UserDTO nếu tìm thấy, ngược lại null.
     */
    public function get_user_by_user_id(string $userID, string $purpose = "get"): ?UserDTO
    {
        $sql = "SELECT userID, firstName, lastName, email, password, roleID, profileImage, DATE_FORMAT(created_at, '%d-%m-%Y %H:%i:%s') AS created_at_formatted FROM users WHERE userID = ?";
        $params = [$userID];

        $result = $this->executePrepared($sql, $params);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            // Chỉ trả về mật khẩu (đã băm) cho các mục đích nội bộ
            $passwordForDTO = "";
            if ($purpose === "update" || $purpose === "internal_check") {
                $passwordForDTO = $row['password'];
            }

            return new UserDTO(
                $row['userID'],
                $row['firstName'],
                $row['lastName'],
                $row['email'],
                $passwordForDTO,
                $row['roleID'],
                $row['profileImage'],
                $row['created_at_formatted'] ?? null
            );
        }
        return null;
    }

    /**
     * Lấy thông tin người dùng bằng email.
     * @param string $email Email của người dùng.
     * @return UserDTO|null Trả về đối tượng UserDTO nếu tìm thấy, ngược lại null.
     */
    public function get_user_by_email(string $email): ?UserDTO
    {
        $sql = "SELECT userID, firstName, lastName, email, roleID, profileImage, DATE_FORMAT(created_at, '%d-%m-%Y %H:%i:%s') AS created_at_formatted FROM users WHERE email = ?";
        $params = [$email];

        $result = $this->executePrepared($sql, $params);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return new UserDTO(
                $row['userID'],
                $row['firstName'],
                $row['lastName'],
                $row['email'],
                "", // Không trả về mật khẩu
                $row['roleID'],
                $row['profileImage'],
                $row['created_at_formatted'] ?? null
            );
        }
        return null;
    }

    /**
     * Lấy danh sách tất cả người dùng.
     * @return array Mảng các đối tượng UserDTO.
     */
    public function get_all_users(): array
    {
        $sql = "SELECT userID, firstName, lastName, email, roleID, profileImage, DATE_FORMAT(created_at, '%d-%m-%Y %H:%i:%s') AS created_at_formatted FROM users ORDER BY created_at DESC";
        $users = [];
        
        $result = $this->executePrepared($sql);

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $users[] = new UserDTO(
                    $row['userID'],
                    $row['firstName'],
                    $row['lastName'],
                    $row['email'],
                    "", // Không trả về mật khẩu
                    $row['roleID'],
                    $row['profileImage'],
                    $row['created_at_formatted'] ?? null
                );
            }
        }
        return $users;
    }

    /**
     * Kiểm tra xem một email đã tồn tại trong hệ thống hay chưa.
     * @param string $email Email cần kiểm tra.
     * @param string|null $excludeUserID ID người dùng cần loại trừ khỏi việc kiểm tra (hữu ích khi cập nhật).
     * @return bool Trả về true nếu email đã tồn tại, ngược lại false.
     */
    public function email_exists(string $email, ?string $excludeUserID = null): bool
    {
        $params = [];
        if ($excludeUserID !== null) {
            $sql = "SELECT COUNT(*) as count FROM users WHERE email = ? AND userID != ?";
            $params = [$email, $excludeUserID];
        } else {
            $sql = "SELECT COUNT(*) as count FROM users WHERE email = ?";
            $params = [$email];
        }

        $result = $this->executePrepared($sql, $params);

        if ($result) {
            $row = $result->fetch_assoc();
            return isset($row['count']) && $row['count'] > 0;
        }

        error_log('[UserBLL-MySQL] Failed to check email existence for Email: ' . $email);
        return false;
    }
}
?>
