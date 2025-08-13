<?php
// Thay đổi đường dẫn để trỏ đến tệp kết nối MySQL
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/course_image_dto.php';

class CourseImageBLL extends Database
{
    /**
     * Chuyển đổi một hàng dữ liệu từ database thành một đối tượng CourseImageDTO.
     * @param array $row Mảng kết hợp chứa dữ liệu của một hình ảnh.
     * @return CourseImageDTO Đối tượng DTO của hình ảnh.
     */
    private function _map_row_to_dto(array $row): CourseImageDTO
    {
        return new CourseImageDTO(
            $row['imageID'],
            $row['courseID'],
            $row['imagePath'],
            $row['caption'],
            isset($row['sortOrder']) ? (int)$row['sortOrder'] : 0,
            $row['createdAt_formatted'] ?? null
        );
    }

    /**
     * Tạo một bản ghi hình ảnh mới cho khóa học.
     * @param CourseImageDTO $img Đối tượng chứa thông tin hình ảnh.
     * @return bool Trả về true nếu tạo thành công, ngược lại false.
     */
    public function create_image(CourseImageDTO $img): bool
    {
        // Câu lệnh INSERT chuẩn của MySQL
        $sql = "INSERT INTO CourseImage (imageID, courseID, imagePath, caption, sortOrder) VALUES (?, ?, ?, ?, ?)";

        $bindParams = [
            $img->imageID,
            $img->courseID,
            $img->imagePath,
            $img->caption,
            $img->sortOrder ?? 0,
        ];

        $result = $this->executePrepared($sql, $bindParams);
        return ($result !== false && $this->getAffectedRows() > 0);
    }

    /**
     * Cập nhật thông tin của một hình ảnh.
     * @param CourseImageDTO $img Đối tượng chứa thông tin cần cập nhật.
     * @return bool Trả về true nếu cập nhật thành công, ngược lại false.
     */
    public function update_image(CourseImageDTO $img): bool
    {
        // Câu lệnh UPDATE chuẩn của MySQL
        $sql = "UPDATE CourseImage SET courseID = ?, imagePath = ?, caption = ?, sortOrder = ? WHERE imageID = ?";

        $bindParams = [
            $img->courseID,
            $img->imagePath,
            $img->caption,
            $img->sortOrder ?? 0,
            $img->imageID, // Tham số cho mệnh đề WHERE
        ];

        $result = $this->executePrepared($sql, $bindParams);
        return ($result !== false);
    }

    /**
     * Xóa một hình ảnh khỏi khóa học (xóa bản ghi).
     * @param string $imageID ID của hình ảnh.
     * @param string $courseID ID của khóa học để đảm bảo xóa đúng.
     * @return bool Trả về true nếu xóa thành công, ngược lại false.
     */
    public function unlink_image_course(string $imageID, string $courseID): bool
    {
        // Câu lệnh DELETE chuẩn của MySQL
        $sql = "DELETE FROM CourseImage WHERE imageID = ? AND courseID = ?";

        $bindParams = [
            $imageID,
            $courseID,
        ];

        $result = $this->executePrepared($sql, $bindParams);
        return ($result !== false && $this->getAffectedRows() > 0);
    }

    /**
     * Lấy thông tin chi tiết của một hình ảnh bằng ID của nó.
     * @param string $imageID ID của hình ảnh.
     * @return ?CourseImageDTO Trả về đối tượng DTO nếu tìm thấy, ngược lại null.
     */
    public function get_image_by_image_id(string $imageID): ?CourseImageDTO
    {
        // Câu lệnh SELECT với định dạng ngày tháng
        $sql = "SELECT imageID, courseID, imagePath, caption, sortOrder, DATE_FORMAT(created_at, '%d-%m-%Y %H:%i:%s') as createdAt_formatted FROM CourseImage WHERE imageID = ?";
        $bindParams = [$imageID];

        $result = $this->executePrepared($sql, $bindParams);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $this->_map_row_to_dto($row);
        }

        return null;
    }

    /**
     * Lấy tất cả hình ảnh của một khóa học, sắp xếp theo thứ tự.
     * @param string $courseID ID của khóa học.
     * @return array Danh sách các đối tượng CourseImageDTO.
     */
    public function get_images_by_course_id(string $courseID): array
    {
        // Câu lệnh SELECT, sắp xếp theo sortOrder để hiển thị đúng thứ tự
        $sql = "SELECT imageID, courseID, imagePath, caption, sortOrder, DATE_FORMAT(created_at, '%d-%m-%Y %H:%i:%s') as createdAt_formatted FROM CourseImage WHERE courseID = ? ORDER BY sortOrder ASC, created_at ASC";
        $bindParams = [$courseID];
        $images = [];

        $result = $this->executePrepared($sql, $bindParams);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $images[] = $this->_map_row_to_dto($row);
            }
        }
        return $images;
    }
}
