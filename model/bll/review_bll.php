<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/review_dto.php';

class ReviewBLL extends Database
{
    /**
     * Thêm đánh giá
     *
     * @param ReviewDTO $r
     * @return bool
     */
    public function create_review(ReviewDTO $r): bool
    {
        $comment = $r->comment !== null ? "'{$r->comment}'" : 'NULL';
        $sql = "INSERT INTO Review (ReviewID, UserID, CourseID, Rating, Comment)\
                VALUES ('{$r->reviewID}', '{$r->userID}', '{$r->courseID}', {$r->rating}, {$comment})";
        $result = $this->execute($sql);
        return $result === true && $this->getAffectedRows() === 1;
    }

    /**
     * Cập nhật đánh giá
     *
     * @param ReviewDTO $r
     * @return bool
     */
    public function update_review(ReviewDTO $r): bool
    {
        $comment = $r->comment !== null ? "'{$r->comment}'" : 'NULL';
        $sql = "UPDATE Review SET \
                UserID = '{$r->userID}', \
                CourseID = '{$r->courseID}', \
                Rating   = {$r->rating}, \
                Comment  = {$comment} \
                WHERE ReviewID = '{$r->reviewID}'";
        $result = $this->execute($sql);
        return $result === true && $this->getAffectedRows() === 1;
    }

    /**
     * Xóa đánh giá
     *
     * @param string $reviewID
     * @return bool
     */
    public function delete_review(string $reviewID): bool
    {
        $sql = "DELETE FROM Review WHERE ReviewID = '{$reviewID}'";
        $result = $this->execute($sql);
        return $result === true && $this->getAffectedRows() === 1;
    }

    /**
     * Lấy đánh giá theo khóa học
     *
     * @param string $courseID
     * @return ReviewDTO[]
     */
    public function get_reviews_by_course(string $courseID): array
    {
        $sql = "SELECT * FROM Review WHERE CourseID = '{$courseID}'";
        $result = $this->execute($sql);
        $reviews = [];
        if ($result instanceof mysqli_result) {
            while ($row = $result->fetch_assoc()) {
                $reviews[] = new ReviewDTO(
                    $row['ReviewID'],
                    $row['UserID'],
                    $row['CourseID'],
                    (int)$row['Rating'],
                    $row['Comment']
                );
            }
        }
        return $reviews;
    }
}
