<?php
// Thay đổi đường dẫn để trỏ đến tệp kết nối MySQL
require_once __DIR__ . '/../Database/database.php';
require_once __DIR__ . '/../DTO/course_category_dto.php';

class CourseCategoryBLL extends Database
{
    /**
     * Chuyển đổi một hàng dữ liệu từ database thành một đối tượng CourseCategoryDTO.
     * @param array $row Mảng kết hợp chứa dữ liệu của một liên kết.
     * @return CourseCategoryDTO Đối tượng DTO của liên kết.
     */
    private function _map_row_to_dto(array $row): CourseCategoryDTO
    {
        return new CourseCategoryDTO(
            $row['courseID'],
            isset($row['categoryID']) ? (int)$row['categoryID'] : 0,
            $row['createdAt_formatted'] ?? null
        );
    }

    /**
     * Tạo liên kết giữa một khóa học và một danh mục.
     * @param CourseCategoryDTO $cc Đối tượng chứa courseID và categoryID.
     * @return bool Trả về true nếu liên kết thành công, ngược lại false.
     */
    public function link_course_category(CourseCategoryDTO $cc): bool
    {
        // Câu lệnh INSERT chuẩn của MySQL cho bảng trung gian
        $sql = "INSERT INTO course_categories (courseID, categoryID) VALUES (?, ?)";

        $bindParams = [
            $cc->courseID,
            (int)$cc->categoryID,
        ];

        $result = $this->executePrepared($sql, $bindParams);
        return ($result !== false && $this->getAffectedRows() > 0);
    }

    /**
     * Xóa liên kết giữa một khóa học và một danh mục.
     * @param string $courseID ID của khóa học.
     * @param int|string $categoryID ID của danh mục.
     * @return bool Trả về true nếu xóa thành công, ngược lại false.
     */
    public function unlink_course_category(string $courseID, $categoryID): bool
    {
        // Câu lệnh DELETE chuẩn của MySQL
        $sql = "DELETE FROM course_categories WHERE courseID = ? AND categoryID = ?";

        $bindParams = [
            $courseID,
            (int)$categoryID,
        ];

        $result = $this->executePrepared($sql, $bindParams);
        return ($result !== false && $this->getAffectedRows() > 0);
    }

    /**
     * Lấy danh sách các danh mục của một khóa học.
     * @param string $courseID ID của khóa học.
     * @return array Danh sách các đối tượng CourseCategoryDTO.
     */
    public function get_categories_by_course_id(string $courseID): array
    {
        // Câu lệnh SELECT để lấy các liên kết danh mục cho một khóa học
        $sql = "SELECT courseID, categoryID, DATE_FORMAT(createdAt, '%d-%m-%Y %H:%i:%s') as createdAt_formatted FROM course_categories WHERE courseID = ?";
        $bindParams = [$courseID];
        $list = [];

        $result = $this->executePrepared($sql, $bindParams);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $list[] = $this->_map_row_to_dto($row);
            }
        }
        return $list;
    }

    /**
     * Lấy danh sách các khóa học thuộc một danh mục.
     * @param int|string $categoryID ID của danh mục.
     * @return array Danh sách các đối tượng CourseCategoryDTO.
     */
    public function get_courses_by_category_id($categoryID): array
    {
        // Câu lệnh SELECT để lấy các liên kết khóa học cho một danh mục
        $sql = "SELECT courseID, categoryID, DATE_FORMAT(createdAt, '%d-%m-%Y %H:%i:%s') as createdAt_formatted FROM course_categories WHERE categoryID = ?";
        $bindParams = [(int)$categoryID];
        $list = [];

        $result = $this->executePrepared($sql, $bindParams);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $list[] = $this->_map_row_to_dto($row);
            }
        }
        return $list;
    }

    /**
     * Kiểm tra xem một liên kết giữa khóa học và danh mục đã tồn tại chưa.
     * @param string $courseID ID của khóa học.
     * @param int|string $categoryID ID của danh mục.
     * @return bool Trả về true nếu liên kết đã tồn tại, ngược lại false.
     */
    public function link_exists(string $courseID, $categoryID): bool
    {
        // Câu lệnh SELECT để kiểm tra sự tồn tại
        $sql = "SELECT 1 FROM course_categories WHERE courseID = ? AND categoryID = ?";
        $bindParams = [
            $courseID,
            (int)$categoryID,
        ];

        $result = $this->executePrepared($sql, $bindParams);
        // Nếu query trả về kết quả và có ít nhất 1 hàng, nghĩa là liên kết tồn tại
        return ($result && $result->num_rows > 0);
    }
}
