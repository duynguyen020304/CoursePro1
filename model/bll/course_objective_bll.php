<?php
require_once __DIR__ . '/../database_mysql.php';
require_once __DIR__ . '/../dto/course_objective_dto.php';

class CourseObjectiveBLL extends Database
{
    /**
     * Tạo một mục tiêu mới cho khóa học.
     * @param CourseObjectiveDTO $obj Đối tượng DTO chứa thông tin mục tiêu.
     * @return bool Trả về true nếu tạo thành công, ngược lại false.
     */
    public function create(CourseObjectiveDTO $obj): bool
    {
        // Câu lệnh SQL để chèn dữ liệu vào bảng CourseObjective
        $sql = "INSERT INTO CourseObjective (ObjectiveID, CourseID, Objective) VALUES (?, ?, ?)";

        // Mảng chứa các tham số để bind vào câu lệnh SQL
        $bindParams = [$obj->objectiveID, $obj->courseID, $obj->objective];

        // Thực thi câu lệnh đã chuẩn bị
        $this->executePrepared($sql, $bindParams);
        
        // Trả về true nếu có ít nhất một hàng bị ảnh hưởng
        return $this->getAffectedRows() > 0;
    }

    /**
     * Cập nhật thông tin một mục tiêu.
     * @param CourseObjectiveDTO $obj Đối tượng DTO chứa thông tin cần cập nhật.
     * @return bool Trả về true nếu cập nhật thành công, ngược lại false.
     */
    public function update(CourseObjectiveDTO $obj): bool
    {
        // Câu lệnh SQL để cập nhật bản ghi trong CourseObjective
        $sql = "UPDATE CourseObjective SET Objective = ? WHERE ObjectiveID = ? AND CourseID = ?";

        // Mảng chứa các tham số theo đúng thứ tự trong câu lệnh SQL
        $bindParams = [$obj->objective, $obj->objectiveID, $obj->courseID];

        // Thực thi câu lệnh đã chuẩn bị
        $this->executePrepared($sql, $bindParams);

        // Trả về true nếu có ít nhất một hàng bị ảnh hưởng
        return $this->getAffectedRows() > 0;
    }

    /**
     * Xóa một mục tiêu khỏi khóa học.
     * @param string $objectiveID ID của mục tiêu cần xóa.
     * @return bool Trả về true nếu xóa thành công, ngược lại false.
     */
    public function delete(string $objectiveID): bool
    {
        // Câu lệnh SQL để xóa một bản ghi khỏi CourseObjective
        $sql = "DELETE FROM CourseObjective WHERE ObjectiveID = ?";

        // Mảng chứa các tham số để bind
        $bindParams = [$objectiveID];

        // Thực thi câu lệnh đã chuẩn bị
        $this->executePrepared($sql, $bindParams);

        // Trả về true nếu có ít nhất một hàng bị ảnh hưởng
        return $this->getAffectedRows() > 0;
    }

    /**
     * Lấy thông tin một mục tiêu cụ thể bằng ID của nó.
     * @param string $objectiveID ID của mục tiêu.
     * @return CourseObjectiveDTO|null Trả về đối tượng DTO nếu tìm thấy, ngược lại null.
     */
    public function get_objective_by_objective_id(string $objectiveID): ?CourseObjectiveDTO
    {
        // Câu lệnh SQL để lấy một mục tiêu, định dạng lại cột created_at
        $sql = "SELECT ObjectiveID, CourseID, Objective, DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') AS created_at_formatted FROM CourseObjective WHERE ObjectiveID = ?";
        
        // Mảng chứa các tham số để bind
        $bindParams = [$objectiveID];

        // Thực thi câu lệnh và lấy kết quả
        $result = $this->executePrepared($sql, $bindParams);

        if ($result && $result->num_rows > 0) {
            // Lấy hàng dữ liệu đầu tiên dưới dạng mảng kết hợp
            $row = $result->fetch_assoc();
            // Tạo và trả về đối tượng DTO từ dữ liệu
            return new CourseObjectiveDTO(
                $row['ObjectiveID'],
                $row['CourseID'],
                $row['Objective'],
                $row['created_at_formatted']
            );
        }

        return null;
    }

    /**
     * Lấy danh sách tất cả các mục tiêu của một khóa học.
     * @param string $courseID ID của khóa học.
     * @return array Mảng các đối tượng CourseObjectiveDTO.
     */
    public function get_objectives_by_course_id(string $courseID): array
    {
        // Câu lệnh SQL để lấy tất cả mục tiêu của một khóa học
        $sql = "SELECT ObjectiveID, CourseID, Objective, DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') AS created_at_formatted FROM CourseObjective WHERE CourseID = ?";
        
        // Mảng chứa tham số để bind
        $bindParams = [$courseID];

        $objectives = [];
        // Thực thi câu lệnh và lấy kết quả
        $result = $this->executePrepared($sql, $bindParams);

        if ($result) {
            // Lặp qua tất cả các hàng trong kết quả
            while ($row = $result->fetch_assoc()) {
                // Tạo đối tượng DTO cho mỗi hàng và thêm vào danh sách
                $objectives[] = new CourseObjectiveDTO(
                    $row['ObjectiveID'],
                    $row['CourseID'],
                    $row['Objective'],
                    $row['created_at_formatted']
                );
            }
        }

        return $objectives;
    }
}
?>
