<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/review_dto.php';
class ReviewBLL extends Database
{
    public function create_review(ReviewDTO $r)
    {
        $comment = $r->comment ? "'{$r->comment}'" : 'NULL';
        $sql = "INSERT INTO Review (ReviewID, UserID, CourseID, Rating, Comment) VALUES ('{$r->reviewID}', '{$r->userID}', '{$r->courseID}', {$r->rating}, {$comment})";
        $result = $this->execute($sql);
        // $this->close();
        return $result === true && $this->getAffectedRows() === 1;
    }

    public function delete_review(string $reviewID)
    {
        $sql = "DELETE FROM Review WHERE ReviewID = '{$reviewID}'";
        $result = $this->execute($sql);
        // $this->close();
        return $result === true && $this->getAffectedRows() === 1;
    }

    public function get_reviews_by_course(string $courseID): array
    {
        $sql = "SELECT * FROM Review WHERE CourseID = '{$courseID}'";
        $result = $this->execute($sql);
        $reviews = [];
        while ($row = $result->fetch_assoc()) {
            $reviews[] = new ReviewDTO($row['ReviewID'], $row['UserID'], $row['CourseID'], (int)$row['Rating'], $row['Comment']);
        }
        $this->close();
        return $reviews;
    }
}
