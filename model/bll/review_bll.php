<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/review_dto.php';

class ReviewBLL extends Database
{
    /**
     * Tạo một đánh giá mới.
     *
     * @param ReviewDTO $r Đối tượng chứa thông tin đánh giá.
     * @return bool Trả về true nếu tạo thành công, ngược lại false.
     */
    public function create_review(ReviewDTO $r): bool
    {
        $sql = "INSERT INTO Review (ReviewID, UserID, CourseID, Rating, REVIEW_TEXT) VALUES (?, ?, ?, ?, ?)";
        
        $params = [
            $r->reviewID,
            $r->userID,
            $r->courseID,
            $r->rating,
            $r->comment,
        ];

        $result = $this->executePrepared($sql, $params);
        return ($result !== false) && ($this->getAffectedRows() === 1);
    }

    /**
     * Cập nhật một đánh giá.
     *
     * @param ReviewDTO $r Đối tượng chứa thông tin đánh giá cần cập nhật.
     * @return bool Trả về true nếu cập nhật thành công, ngược lại false.
     */
    public function update_review(ReviewDTO $r): bool
    {
        $sql = "UPDATE Review SET UserID = ?, CourseID = ?, Rating = ?, REVIEW_TEXT = ? WHERE ReviewID = ?";
        
        $params = [
            $r->userID,
            $r->courseID,
            $r->rating,
            $r->comment,
            $r->reviewID,
        ];

        $result = $this->executePrepared($sql, $params);
        return ($result !== false);
    }

    /**
     * Xóa một đánh giá.
     *
     * @param string $reviewID ID của đánh giá cần xóa.
     * @return bool Trả về true nếu xóa thành công, ngược lại false.
     */
    public function delete_review(string $reviewID): bool
    {
        $sql = "DELETE FROM Review WHERE ReviewID = ?";
        $params = [$reviewID];
        $result = $this->executePrepared($sql, $params);
        return ($result !== false) && ($this->getAffectedRows() === 1);
    }

    /**
     * Lấy danh sách đánh giá theo ID khóa học.
     *
     * @param string $courseID ID của khóa học.
     * @return array Mảng các đối tượng ReviewDTO.
     */
    public function get_reviews_by_course(string $courseID): array
    {
        $sql = "SELECT ReviewID, UserID, CourseID, Rating, REVIEW_TEXT,
                       DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') AS created_at_formatted
                FROM Review
                WHERE CourseID = ?
                ORDER BY created_at DESC";
        $params = [$courseID];
        $result = $this->executePrepared($sql, $params);
        $reviews = [];

        if ($result instanceof mysqli_result) {
            while ($row = $result->fetch_assoc()) {
                $reviews[] = new ReviewDTO(
                    $row['ReviewID'],
                    $row['UserID'],
                    $row['CourseID'],
                    isset($row['Rating']) ? (int)$row['Rating'] : 0,
                    $row['REVIEW_TEXT'],
                    $row['created_at_formatted'] ?? null
                );
            }
            $result->free();
        }
        return $reviews;
    }

    /**
     * Lấy đánh giá bằng ID của nó.
     *
     * @param string $reviewID ID của đánh giá.
     * @return ReviewDTO|null Trả về đối tượng ReviewDTO nếu tìm thấy, ngược lại null.
     */
    public function get_review_by_id(string $reviewID): ?ReviewDTO
    {
        $sql = "SELECT ReviewID, UserID, CourseID, Rating, REVIEW_TEXT,
                       DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') AS created_at_formatted
                FROM Review
                WHERE ReviewID = ?";
        $params = [$reviewID];
        $result = $this->executePrepared($sql, $params);
        $dto = null;

        if ($result instanceof mysqli_result) {
            if ($row = $result->fetch_assoc()) {
                $dto = new ReviewDTO(
                    $row['ReviewID'],
                    $row['UserID'],
                    $row['CourseID'],
                    isset($row['Rating']) ? (int)$row['Rating'] : 0,
                    $row['REVIEW_TEXT'],
                    $row['created_at_formatted'] ?? null
                );
            }
            $result->free();
        }
        return $dto;
    }
}
