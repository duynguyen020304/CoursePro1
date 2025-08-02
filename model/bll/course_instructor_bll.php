<?php
require_once __DIR__ . '/../database_mysql.php';
require_once __DIR__ . '/../dto/course_instructor_dto.php';

class CourseInstructorBLL extends Database
{
    /**
     * Thêm một phân công giảng viên cho một khóa học.
     * @param string $courseID ID của khóa học.
     * @param string $instructorID ID của giảng viên.
     * @return bool Trả về true nếu thêm thành công, ngược lại false.
     */
    public function add(string $courseID, string $instructorID): bool
    {
        // Câu lệnh SQL để chèn dữ liệu vào bảng CourseInstructor
        $sql = "INSERT INTO CourseInstructor (CourseID, InstructorID) VALUES (?, ?)";

        // Mảng chứa các tham số để bind vào câu lệnh SQL
        $bindParams = [$courseID, $instructorID];

        // Thực thi câu lệnh đã chuẩn bị
        $result = $this->executePrepared($sql, $bindParams);
        
        // Trả về true nếu có ít nhất một hàng bị ảnh hưởng (tức là chèn thành công)
        return $this->getAffectedRows() > 0;
    }

    /**
     * Cập nhật thông tin phân công.
     * @param string $oldCourseID ID khóa học cũ.
     * @param string $oldInstructorID ID giảng viên cũ.
     * @param string $newCourseID ID khóa học mới.
     * @param string $newInstructorID ID giảng viên mới.
     * @return bool Trả về true nếu cập nhật thành công, ngược lại false.
     */
    public function update(string $oldCourseID, string $oldInstructorID, string $newCourseID, string $newInstructorID): bool
    {
        // Câu lệnh SQL để cập nhật bản ghi trong CourseInstructor
        $sql = "UPDATE CourseInstructor SET CourseID = ?, InstructorID = ? WHERE CourseID = ? AND InstructorID = ?";

        // Mảng chứa các tham số theo đúng thứ tự trong câu lệnh SQL
        $bindParams = [$newCourseID, $newInstructorID, $oldCourseID, $oldInstructorID];

        // Thực thi câu lệnh đã chuẩn bị
        $result = $this->executePrepared($sql, $bindParams);

        // Trả về true nếu có ít nhất một hàng bị ảnh hưởng (tức là cập nhật thành công)
        return $this->getAffectedRows() > 0;
    }

    /**
     * Hủy liên kết giữa một giảng viên và một khóa học.
     * @param string $courseID ID của khóa học.
     * @param string $instructorID ID của giảng viên.
     * @return bool Trả về true nếu xóa thành công, ngược lại false.
     */
    public function unlink_course_instructor(string $courseID, string $instructorID): bool
    {
        // Câu lệnh SQL để xóa một bản ghi khỏi CourseInstructor
        $sql = "DELETE FROM CourseInstructor WHERE CourseID = ? AND InstructorID = ?";

        // Mảng chứa các tham số để bind
        $bindParams = [$courseID, $instructorID];

        // Thực thi câu lệnh đã chuẩn bị
        $result = $this->executePrepared($sql, $bindParams);

        // Trả về true nếu có ít nhất một hàng bị ảnh hưởng (tức là xóa thành công)
        return $this->getAffectedRows() > 0;
    }

    /**
     * Lấy thông tin một phân công cụ thể.
     * @param string $courseID ID của khóa học.
     * @param string $instructorID ID của giảng viên.
     * @return CourseInstructorDTO|null Trả về đối tượng DTO nếu tìm thấy, ngược lại null.
     */
    public function get_assignment(string $courseID, string $instructorID): ?CourseInstructorDTO
    {
        // Câu lệnh SQL để lấy một phân công, định dạng lại cột created_at
        $sql = "SELECT CourseID, InstructorID, DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') AS created_at_formatted FROM CourseInstructor WHERE CourseID = ? AND InstructorID = ?";
        
        // Mảng chứa các tham số để bind
        $bindParams = [$courseID, $instructorID];

        // Thực thi câu lệnh và lấy kết quả
        $result = $this->executePrepared($sql, $bindParams);

        if ($result && $result->num_rows > 0) {
            // Lấy hàng dữ liệu đầu tiên dưới dạng mảng kết hợp
            $row = $result->fetch_assoc();
            // Tạo và trả về đối tượng DTO từ dữ liệu
            return new CourseInstructorDTO(
                $row['CourseID'],
                $row['InstructorID'],
                $row['created_at_formatted']
            );
        }

        return null;
    }

    /**
     * Lấy danh sách tất cả các giảng viên được phân công cho một khóa học.
     * @param string $courseID ID của khóa học.
     * @return array Mảng các đối tượng CourseInstructorDTO.
     */
    public function get_instructors_by_course_id(string $courseID): array
    {
        // Câu lệnh SQL để lấy tất cả phân công của một khóa học
        $sql = "SELECT CourseID, InstructorID, DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') AS created_at_formatted FROM CourseInstructor WHERE CourseID = ?";
        
        // Mảng chứa tham số để bind
        $bindParams = [$courseID];

        $list = [];
        // Thực thi câu lệnh và lấy kết quả
        $result = $this->executePrepared($sql, $bindParams);

        if ($result) {
            // Lặp qua tất cả các hàng trong kết quả
            while ($row = $result->fetch_assoc()) {
                // Tạo đối tượng DTO cho mỗi hàng và thêm vào danh sách
                $list[] = new CourseInstructorDTO(
                    $row['CourseID'],
                    $row['InstructorID'],
                    $row['created_at_formatted']
                );
            }
        }

        return $list;
    }
}
?>
