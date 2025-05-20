<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/course_category_dto.php';

class CourseCategoryBLL extends Database
{
    public function link_course_category(CourseCategoryDTO $cc): bool
    {
        $sql = "INSERT INTO COURSECATEGORY (CourseID, CategoryID) 
                VALUES (:courseID, :categoryID)";

        $bindParams = [
            ':courseID'   => $cc->courseID,
            ':categoryID' => (int)$cc->categoryID,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false) && ($this->getAffectedRows() === 1);
    }

    public function unlink_course_category(string $courseID, $categoryID): bool
    {
        $sql = "DELETE FROM COURSECATEGORY 
                WHERE CourseID = :courseID AND CategoryID = :categoryID";

        $bindParams = [
            ':courseID'   => $courseID,
            ':categoryID' => (int)$categoryID,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false) && ($this->getAffectedRows() === 1);
    }

    public function get_categories_by_course(string $courseID): array
    {
        $sql = "SELECT CourseID, CategoryID, created_at 
                FROM COURSECATEGORY 
                WHERE CourseID = :courseID_param 
                ORDER BY CategoryID ASC";

        $bindParams = [':courseID_param' => $courseID];

        $stid = $this->executePrepared($sql, $bindParams);
        $list = [];

        if ($stid) {
            while (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $list[] = new CourseCategoryDTO(
                    $row['COURSEID'],
                    isset($row['CATEGORYID']) ? (int)$row['CATEGORYID'] : 0,
                    $row['CREATED_AT'] ?? null
                );
            }
            @oci_free_statement($stid);
        }
        return $list;
    }

    public function get_courses_by_category($categoryID): array
    {
        $sql = "SELECT CourseID, CategoryID, created_at 
                FROM COURSECATEGORY 
                WHERE CategoryID = :categoryID_param 
                ORDER BY CourseID ASC";

        $bindParams = [':categoryID_param' => (int)$categoryID];

        $stid = $this->executePrepared($sql, $bindParams);
        $list = [];

        if ($stid) {
            while (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $list[] = new CourseCategoryDTO(
                    $row['COURSEID'],
                    isset($row['CATEGORYID']) ? (int)$row['CATEGORYID'] : 0,
                    $row['CREATED_AT'] ?? null
                );
            }
            @oci_free_statement($stid);
        }
        return $list;
    }

    public function link_exists(string $courseID, $categoryID): bool
    {
        $sql = "SELECT COUNT(*) AS LINK_COUNT 
                FROM COURSECATEGORY 
                WHERE CourseID = :courseID AND CategoryID = :categoryID";
        $bindParams = [
            ':courseID'   => $courseID,
            ':categoryID' => (int)$categoryID,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        if ($stid) {
            $row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS);
            @oci_free_statement($stid);
            if ($row && isset($row['LINK_COUNT'])) {
                return (int)$row['LINK_COUNT'] > 0;
            }
        }
        return false;
    }
}
?>