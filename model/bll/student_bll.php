<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/student_dto.php';

class StudentBLL extends Database
{
    /**
     * Tạo một hồ sơ sinh viên mới.
     *
     * @param StudentDTO $stu Đối tượng chứa thông tin sinh viên.
     * @return bool Trả về true nếu tạo thành công, ngược lại false.
     */
    public function create_student(StudentDTO $stu): bool
    {
        $sql = "INSERT INTO STUDENTS (StudentID, UserID) VALUES (?, ?)";
        $params = [
            $stu->studentID,
            $stu->userID,
        ];
        $result = $this->executePrepared($sql, $params);
        return ($result !== false) && ($this->getAffectedRows() === 1);
    }

    /**
     * Xóa một hồ sơ sinh viên.
     *
     * @param string $studentID ID của sinh viên cần xóa.
     * @return bool Trả về true nếu xóa thành công, ngược lại false.
     */
    public function delete_student(string $studentID): bool
    {
        $sql = "DELETE FROM STUDENTS WHERE StudentID = ?";
        $params = [$studentID];
        $result = $this->executePrepared($sql, $params);
        return ($result !== false) && ($this->getAffectedRows() === 1);
    }

    /**
     * Cập nhật thông tin một sinh viên.
     *
     * @param StudentDTO $stu Đối tượng chứa thông tin sinh viên cần cập nhật.
     * @return bool Trả về true nếu cập nhật thành công, ngược lại false.
     */
    public function update_student(StudentDTO $stu): bool
    {
        $sql = "UPDATE STUDENTS SET UserID = ? WHERE StudentID = ?";
        $params = [
            $stu->userID,
            $stu->studentID,
        ];
        $result = $this->executePrepared($sql, $params);
        return ($result !== false);
    }

    /**
     * Lấy thông tin sinh viên bằng ID sinh viên.
     *
     * @param string $studentID ID của sinh viên.
     * @return StudentDTO|null Trả về đối tượng StudentDTO nếu tìm thấy, ngược lại null.
     */
    public function get_student_by_student_id(string $studentID): ?StudentDTO
    {
        $sql = "SELECT StudentID, UserID,
                       DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') AS created_at_formatted
                FROM STUDENTS
                WHERE StudentID = ?";
        $params = [$studentID];
        $result = $this->executePrepared($sql, $params);
        $dto = null;

        if ($result instanceof mysqli_result) {
            if ($row = $result->fetch_assoc()) {
                $dto = new StudentDTO(
                    $row['StudentID'],
                    $row['UserID'],
                    $row['created_at_formatted'] ?? null
                );
            }
            $result->free();
        }
        return $dto;
    }

    /**
     * Lấy thông tin sinh viên bằng ID người dùng.
     *
     * @param string $userID ID của người dùng.
     * @return StudentDTO|null Trả về đối tượng StudentDTO nếu tìm thấy, ngược lại null.
     */
    public function get_student_by_user_id(string $userID): ?StudentDTO
    {
        $sql = "SELECT StudentID, UserID,
                       DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') AS created_at_formatted
                FROM STUDENTS
                WHERE UserID = ?";
        $params = [$userID];
        $result = $this->executePrepared($sql, $params);
        $dto = null;

        if ($result instanceof mysqli_result) {
            if ($row = $result->fetch_assoc()) {
                $dto = new StudentDTO(
                    $row['StudentID'],
                    $row['UserID'],
                    $row['created_at_formatted'] ?? null
                );
            }
            $result->free();
        }
        return $dto;
    }

    /**
     * Lấy tất cả các sinh viên.
     *
     * @return array Mảng các đối tượng StudentDTO.
     */
    public function get_all_students(): array
    {
        $sql = "SELECT StudentID, UserID,
                       DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') AS created_at_formatted
                FROM STUDENTS
                ORDER BY created_at DESC";
        $result = $this->execute($sql);
        $list = [];

        if ($result instanceof mysqli_result) {
            while ($row = $result->fetch_assoc()) {
                $list[] = new StudentDTO(
                    $row['StudentID'],
                    $row['UserID'],
                    $row['created_at_formatted'] ?? null
                );
            }
            $result->free();
        }
        return $list;
    }
}
