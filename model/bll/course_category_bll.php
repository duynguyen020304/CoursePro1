<?php
require_once("../database.php");
require_once("../dto.php");
class CourseCategoryBLL extends Database
{
    public function link_course_category(CourseCategoryDTO $cc)
    {
        $sql = "INSERT INTO `CourseCategory` (CourseID, CategoryID) VALUES ('{$cc->courseID}', '{$cc->categoryID}')";
        $this->execute($sql);
        $this->close();
    }

    public function unlink_course_category(string $courseID, string $categoryID)
    {
        $sql = "DELETE FROM `CourseCategory` WHERE CourseID = '{$courseID}' AND CategoryID = '{$categoryID}'";
        $this->execute($sql);
        $this->close();
    }

    public function get_categories_by_course(string $courseID): array
    {
        $sql = "SELECT * FROM `CourseCategory` WHERE CourseID = '{$courseID}'";
        $result = $this->execute($sql);
        $list = [];
        while ($row = $result->fetch_assoc()) {
            $list[] = new CourseCategoryDTO($row['CourseID'], $row['CategoryID']);
        }
        $this->close();
        return $list;
    }
}
