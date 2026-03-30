<?php
require_once __DIR__ . '/../database_mysql.php';
require_once __DIR__ . '/../dto/chapter_dto.php';

class ChapterBLL extends Database
{
    /**
     * Tạo một chương mới trong cơ sở dữ liệu.
     * @param ChapterDTO $chapter Đối tượng ChapterDTO chứa thông tin chương mới.
     * @return bool Trả về true nếu tạo thành công, ngược lại false.
     */
    public function create_chapter(ChapterDTO $chapter): bool
    {
        // Câu lệnh SQL để chèn một chương mới vào bảng 'chapters'.
        // Sử dụng các placeholder (?) để tránh tấn công SQL injection.
        $sql = "INSERT INTO chapters (chapterID, courseID, title, description, sortOrder) VALUES (?, ?, ?, ?, ?)";

        // Mảng chứa các giá trị để ràng buộc vào câu lệnh đã chuẩn bị.
        $bindParams = [
            $chapter->chapterID,
            $chapter->courseID,
            $chapter->title,
            $chapter->description,
            $chapter->sortOrder ?? 0,
        ];

        // Thực thi câu lệnh đã chuẩn bị.
        $result = $this->executePrepared($sql, $bindParams);
        
        // Trả về true nếu câu lệnh được thực thi thành công (không phải là false).
        return ($result !== false);
    }

    /**
     * Cập nhật thông tin của một chương đã có.
     * @param ChapterDTO $chapter Đối tượng ChapterDTO chứa thông tin cần cập nhật.
     * @return bool Trả về true nếu cập nhật thành công, ngược lại false.
     */
    public function update_chapter(ChapterDTO $chapter): bool
    {
        // Câu lệnh SQL để cập nhật một chương trong bảng 'chapters'.
        $sql = "UPDATE chapters SET courseID = ?, title = ?, description = ?, sortOrder = ? WHERE chapterID = ?";

        // Mảng chứa các giá trị để ràng buộc.
        $bindParams = [
            $chapter->courseID,
            $chapter->title,
            $chapter->description,
            $chapter->sortOrder ?? 0,
            $chapter->chapterID,
        ];

        // Thực thi câu lệnh.
        $result = $this->executePrepared($sql, $bindParams);
        return ($result !== false);
    }

    /**
     * Xóa một chương khỏi cơ sở dữ liệu.
     * @param string $chapterID ID của chương cần xóa.
     * @return bool Trả về true nếu xóa thành công, ngược lại false.
     */
    public function delete_chapter(string $chapterID): bool
    {
        // Câu lệnh SQL để xóa một chương.
        $sql = "DELETE FROM chapters WHERE chapterID = ?";
        $bindParams = [$chapterID];

        // Thực thi câu lệnh.
        $result = $this->executePrepared($sql, $bindParams);
        return ($result !== false);
    }

    /**
     * Lấy tất cả các chương từ cơ sở dữ liệu.
     * @return array Danh sách các đối tượng ChapterDTO.
     */
    public function get_all_chapters(): array
    {
        // Câu lệnh SQL để lấy tất cả các chương, sắp xếp theo thứ tự.
        $sql = "SELECT chapterID, courseID, title, description, sortOrder, created_at FROM chapters ORDER BY sortOrder ASC, created_at DESC";
        $list = [];

        // Thực thi câu lệnh và lấy kết quả.
        $result = $this->executePrepared($sql);

        // Kiểm tra nếu kết quả là một đối tượng mysqli_result.
        if ($result instanceof mysqli_result) {
            // Lặp qua từng dòng kết quả.
            while (($row = $result->fetch_assoc())) {
                // Tạo đối tượng ChapterDTO từ dữ liệu dòng và thêm vào danh sách.
                $list[] = new ChapterDTO(
                    $row['chapterID'],
                    $row['courseID'],
                    $row['title'],
                    $row['description'],
                    (int)$row['sortOrder'],
                    $row['created_at']
                );
            }
            // Giải phóng bộ nhớ của kết quả.
            $result->free();
        }

        return $list;
    }

    /**
     * Lấy thông tin một chương cụ thể bằng ID.
     * @param string $chapterID ID của chương cần lấy.
     * @return ?ChapterDTO Trả về đối tượng ChapterDTO nếu tìm thấy, ngược lại null.
     */
    public function get_chapter_by_id(string $chapterID): ?ChapterDTO
    {
        // Câu lệnh SQL để lấy một chương theo ID.
        $sql = "SELECT chapterID, courseID, title, description, sortOrder, created_at FROM chapters WHERE chapterID = ?";
        $bindParams = [$chapterID];
        $dto = null;

        // Thực thi câu lệnh.
        $result = $this->executePrepared($sql, $bindParams);

        if ($result instanceof mysqli_result) {
            // Lấy dòng kết quả đầu tiên.
            if (($row = $result->fetch_assoc())) {
                // Tạo đối tượng ChapterDTO từ dữ liệu.
                $dto = new ChapterDTO(
                    $row['chapterID'],
                    $row['courseID'],
                    $row['title'],
                    $row['description'],
                    (int)$row['sortOrder'],
                    $row['created_at']
                );
            }
            $result->free();
        }

        return $dto;
    }

    /**
     * Lấy tất cả các chương thuộc về một khóa học cụ thể.
     * @param string $courseID ID của khóa học.
     * @return array Danh sách các đối tượng ChapterDTO.
     */
    public function get_chapters_by_course_id(string $courseID): array
    {
        // Câu lệnh SQL để lấy các chương theo courseID, sắp xếp theo thứ tự.
        $sql = "SELECT chapterID, courseID, title, description, sortOrder, created_at FROM chapters WHERE courseID = ? ORDER BY sortOrder ASC, created_at DESC";
        $bindParams = [$courseID];
        $list = [];

        // Thực thi câu lệnh.
        $result = $this->executePrepared($sql, $bindParams);

        if ($result instanceof mysqli_result) {
            // Lặp qua các dòng kết quả.
            while (($row = $result->fetch_assoc())) {
                // Tạo đối tượng ChapterDTO và thêm vào danh sách.
                $list[] = new ChapterDTO(
                    $row['chapterID'],
                    $row['courseID'],
                    $row['title'],
                    $row['description'],
                    (int)$row['sortOrder'],
                    $row['created_at']
                );
            }
            $result->free();
        }

        return $list;
    }
}
?>
