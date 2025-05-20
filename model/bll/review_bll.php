<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/review_dto.php';

class ReviewBLL extends Database
{
    public function create_review(ReviewDTO $r): bool
    {
        $sql = "INSERT INTO REVIEW (ReviewID, UserID, CourseID, Rating, REVIEW_TEXT)
                VALUES (:reviewID, :userID, :courseID, :rating, :review_text)";
        $bindParams = [
            ':reviewID'   => $r->reviewID,
            ':userID'     => $r->userID,
            ':courseID'   => $r->courseID,
            ':rating'     => $r->rating,
            ':review_text' => $r->comment,
        ];
        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false) && ($this->getAffectedRows() === 1);
    }

    public function update_review(ReviewDTO $r): bool
    {
        $sql = "UPDATE REVIEW SET 
                UserID = :userID, 
                CourseID = :courseID, 
                Rating   = :rating, 
                REVIEW_TEXT  = :review_text 
                WHERE ReviewID = :reviewID_where";
        $bindParams = [
            ':userID'     => $r->userID,
            ':courseID'   => $r->courseID,
            ':rating'     => $r->rating,
            ':review_text' => $r->comment,
            ':reviewID_where' => $r->reviewID,
        ];
        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function delete_review(string $reviewID): bool
    {
        $sql = "DELETE FROM REVIEW WHERE ReviewID = :reviewID";
        $bindParams = [':reviewID' => $reviewID];
        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false) && ($this->getAffectedRows() === 1);
    }

    public function get_reviews_by_course(string $courseID): array
    {
        $sql = "SELECT ReviewID, UserID, CourseID, Rating, REVIEW_TEXT, created_at 
                FROM REVIEW 
                WHERE CourseID = :courseID_param 
                ORDER BY created_at DESC";
        $bindParams = [':courseID_param' => $courseID];
        $stid = $this->executePrepared($sql, $bindParams);
        $reviews = [];

        if ($stid) {
            while (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $reviews[] = new ReviewDTO(
                    $row['REVIEWID'],
                    $row['USERID'],
                    $row['COURSEID'],
                    isset($row['RATING']) ? (int)$row['RATING'] : 0,
                    $row['REVIEW_TEXT'],
                    $row['CREATED_AT'] ?? null
                );
            }
            @oci_free_statement($stid);
        }
        return $reviews;
    }

    public function get_review_by_id(string $reviewID): ?ReviewDTO
    {
        $sql = "SELECT ReviewID, UserID, CourseID, Rating, REVIEW_TEXT, created_at 
                FROM REVIEW 
                WHERE ReviewID = :reviewID_param";
        $bindParams = [':reviewID_param' => $reviewID];
        $stid = $this->executePrepared($sql, $bindParams);
        $dto = null;

        if ($stid) {
            if (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $dto = new ReviewDTO(
                    $row['REVIEWID'],
                    $row['USERID'],
                    $row['COURSEID'],
                    isset($row['RATING']) ? (int)$row['RATING'] : 0,
                    $row['REVIEW_TEXT'],
                    $row['CREATED_AT'] ?? null
                );
            }
            @oci_free_statement($stid);
        }
        return $dto;
    }
}
?>