<?php
require_once __DIR__ . '/../Database/database.php';
require_once __DIR__ . '/../DTO/course_requirement_dto.php';

class CourseRequirementBLL extends Database
{
    /**
     * Tạo một yêu cầu mới cho khóa học.
     * @param CourseRequirementDTO $req Đối tượng DTO chứa thông tin yêu cầu.
     * @return bool Trả về true nếu tạo thành công, ngược lại false.
     */
    public function create(CourseRequirementDTO $req): bool
    {
        // Câu lệnh SQL để chèn dữ liệu vào bảng CourseRequirement
        $sql = "INSERT INTO CourseRequirement (RequirementID, CourseID, Requirement) VALUES (?, ?, ?)";

        // Mảng chứa các tham số để bind vào câu lệnh SQL
        $bindParams = [$req->requirementID, $req->courseID, $req->requirement];

        // Thực thi câu lệnh đã chuẩn bị
        $this->executePrepared($sql, $bindParams);
        
        // Trả về true nếu có ít nhất một hàng bị ảnh hưởng
        return $this->getAffectedRows() > 0;
    }

    /**
     * Cập nhật thông tin một yêu cầu.
     * @param CourseRequirementDTO $req Đối tượng DTO chứa thông tin cần cập nhật.
     * @return bool Trả về true nếu cập nhật thành công, ngược lại false.
     */
    public function update(CourseRequirementDTO $req): bool
    {
        // Câu lệnh SQL để cập nhật bản ghi trong CourseRequirement
        $sql = "UPDATE CourseRequirement SET Requirement = ? WHERE RequirementID = ? AND CourseID = ?";

        // Mảng chứa các tham số theo đúng thứ tự trong câu lệnh SQL
        $bindParams = [$req->requirement, $req->requirementID, $req->courseID];

        // Thực thi câu lệnh đã chuẩn bị
        $this->executePrepared($sql, $bindParams);

        // Trả về true nếu có ít nhất một hàng bị ảnh hưởng
        return $this->getAffectedRows() > 0;
    }

    /**
     * Xóa một yêu cầu khỏi khóa học.
     * @param string $requirementID ID của yêu cầu cần xóa.
     * @return bool Trả về true nếu xóa thành công, ngược lại false.
     */
    public function delete(string $requirementID): bool
    {
        // Câu lệnh SQL để xóa một bản ghi khỏi CourseRequirement
        $sql = "DELETE FROM CourseRequirement WHERE RequirementID = ?";

        // Mảng chứa các tham số để bind
        $bindParams = [$requirementID];

        // Thực thi câu lệnh đã chuẩn bị
        $this->executePrepared($sql, $bindParams);

        // Trả về true nếu có ít nhất một hàng bị ảnh hưởng
        return $this->getAffectedRows() > 0;
    }

    /**
     * Lấy thông tin một yêu cầu cụ thể bằng ID của nó.
     * @param string $requirementID ID của yêu cầu.
     * @return CourseRequirementDTO|null Trả về đối tượng DTO nếu tìm thấy, ngược lại null.
     */
    public function get_requirement_by_requirement_id(string $requirementID): ?CourseRequirementDTO
    {
        // Câu lệnh SQL để lấy một yêu cầu, định dạng lại cột created_at
        $sql = "SELECT RequirementID, CourseID, Requirement, DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') AS created_at_formatted FROM CourseRequirement WHERE RequirementID = ?";
        
        // Mảng chứa các tham số để bind
        $bindParams = [$requirementID];

        // Thực thi câu lệnh và lấy kết quả
        $result = $this->executePrepared($sql, $bindParams);

        if ($result && $result->num_rows > 0) {
            // Lấy hàng dữ liệu đầu tiên dưới dạng mảng kết hợp
            $row = $result->fetch_assoc();
            // Tạo và trả về đối tượng DTO từ dữ liệu
            return new CourseRequirementDTO(
                $row['RequirementID'],
                $row['CourseID'],
                $row['Requirement'],
                $row['created_at_formatted']
            );
        }

        return null;
    }

    /**
     * Lấy danh sách tất cả các yêu cầu của một khóa học.
     * @param string $courseID ID của khóa học.
     * @return array Mảng các đối tượng CourseRequirementDTO.
     */
    public function get_requirements_by_course_id(string $courseID): array
    {
        // Câu lệnh SQL để lấy tất cả yêu cầu của một khóa học
        $sql = "SELECT RequirementID, CourseID, Requirement, DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') AS created_at_formatted FROM CourseRequirement WHERE CourseID = ?";
        
        // Mảng chứa tham số để bind
        $bindParams = [$courseID];

        $requirements = [];
        // Thực thi câu lệnh và lấy kết quả
        $result = $this->executePrepared($sql, $bindParams);

        if ($result) {
            // Lặp qua tất cả các hàng trong kết quả
            while ($row = $result->fetch_assoc()) {
                // Tạo đối tượng DTO cho mỗi hàng và thêm vào danh sách
                $requirements[] = new CourseRequirementDTO(
                    $row['RequirementID'],
                    $row['CourseID'],
                    $row['Requirement'],
                    $row['created_at_formatted']
                );
            }
        }

        return $requirements;
    }
}
?>
