<?php
require_once __DIR__ . '/../Database/database.php';
require_once __DIR__ . '/../DTO/instructor_dto.php';

class InstructorBLL extends Database
{
    /**
     * Tạo một hồ sơ giảng viên mới.
     * @param InstructorDTO $inst Đối tượng DTO chứa thông tin giảng viên.
     * @return bool Trả về true nếu tạo thành công, ngược lại false.
     */
    public function create_instructor(InstructorDTO $inst): bool
    {
        // Câu lệnh SQL để chèn dữ liệu vào bảng Instructor
        $sql = "INSERT INTO Instructor (InstructorID, UserID, Biography) VALUES (?, ?, ?)";

        // Mảng chứa các tham số để bind vào câu lệnh SQL
        $bindParams = [$inst->instructorID, $inst->userID, $inst->biography];

        // Thực thi câu lệnh đã chuẩn bị
        $this->executePrepared($sql, $bindParams);
        
        // Trả về true nếu có ít nhất một hàng bị ảnh hưởng
        return $this->getAffectedRows() > 0;
    }

    /**
     * Xóa một hồ sơ giảng viên.
     * @param string $instructorID ID của giảng viên cần xóa.
     * @return bool Trả về true nếu xóa thành công, ngược lại false.
     */
    public function delete_instructor(string $instructorID): bool
    {
        // Câu lệnh SQL để xóa một bản ghi khỏi Instructor
        $sql = "DELETE FROM Instructor WHERE InstructorID = ?";

        // Mảng chứa các tham số để bind
        $bindParams = [$instructorID];

        // Thực thi câu lệnh đã chuẩn bị
        $this->executePrepared($sql, $bindParams);

        // Trả về true nếu có ít nhất một hàng bị ảnh hưởng
        return $this->getAffectedRows() > 0;
    }

    /**
     * Cập nhật thông tin hồ sơ giảng viên.
     * @param InstructorDTO $inst Đối tượng DTO chứa thông tin cần cập nhật.
     * @return bool Trả về true nếu cập nhật thành công, ngược lại false.
     */
    public function update_instructor(InstructorDTO $inst): bool
    {
        // Câu lệnh SQL để cập nhật bản ghi trong Instructor
        $sql = "UPDATE Instructor SET UserID = ?, Biography = ? WHERE InstructorID = ?";

        // Mảng chứa các tham số theo đúng thứ tự trong câu lệnh SQL
        $bindParams = [$inst->userID, $inst->biography, $inst->instructorID];

        // Thực thi câu lệnh đã chuẩn bị
        $this->executePrepared($sql, $bindParams);

        // Trả về true nếu có ít nhất một hàng bị ảnh hưởng
        return $this->getAffectedRows() > 0;
    }

    /**
     * Lấy thông tin giảng viên bằng ID giảng viên.
     * @param string $instructorID ID của giảng viên.
     * @return InstructorDTO|null Trả về đối tượng DTO nếu tìm thấy, ngược lại null.
     */
    public function get_instructor(string $instructorID): ?InstructorDTO
    {
        // Câu lệnh SQL để lấy thông tin giảng viên
        $sql = "SELECT InstructorID, UserID, Biography, DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') AS created_at_formatted FROM Instructor WHERE InstructorID = ?";
        
        $bindParams = [$instructorID];
        $result = $this->executePrepared($sql, $bindParams);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return new InstructorDTO(
                $row['InstructorID'],
                $row['UserID'],
                $row['Biography'],
                $row['created_at_formatted']
            );
        }

        return null;
    }

    /**
     * Lấy thông tin giảng viên bằng ID người dùng.
     * @param string $userID ID của người dùng.
     * @return InstructorDTO|null Trả về đối tượng DTO nếu tìm thấy, ngược lại null.
     */
    public function get_instructor_by_user_id(string $userID): ?InstructorDTO
    {
        // Câu lệnh SQL để lấy thông tin giảng viên theo UserID
        $sql = "SELECT InstructorID, UserID, Biography, DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') AS created_at_formatted FROM Instructor WHERE UserID = ?";
        
        $bindParams = [$userID];
        $result = $this->executePrepared($sql, $bindParams);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return new InstructorDTO(
                $row['InstructorID'],
                $row['UserID'],
                $row['Biography'],
                $row['created_at_formatted']
            );
        }

        return null;
    }

    /**
     * Lấy danh sách tất cả các giảng viên.
     * @return array Mảng các đối tượng InstructorDTO.
     */
    public function get_all_instructors(): array
    {
        // Câu lệnh SQL để lấy tất cả giảng viên
        $sql = "SELECT InstructorID, UserID, Biography, DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') AS created_at_formatted FROM Instructor";
        
        $list = [];
        $result = $this->executePrepared($sql);

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $list[] = new InstructorDTO(
                    $row['InstructorID'],
                    $row['UserID'],
                    $row['Biography'],
                    $row['created_at_formatted']
                );
            }
        }

        return $list;
    }
}
?>
