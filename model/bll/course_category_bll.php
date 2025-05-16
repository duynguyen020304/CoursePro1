<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/course_category_dto.php';
class CourseCategoryBLL extends Database
{
    public function link_course_category(CourseCategoryDTO $cc)
    {
        $sql = "INSERT INTO `CourseCategory` (CourseID, CategoryID) VALUES ('{$cc->courseID}', {$cc->categoryID})";
        $result = $this->execute($sql);
        // $this->close();
        return $result === true && $this->getAffectedRows() === 1;
    }

    public function unlink_course_category(string $courseID, string $categoryID)
    {
        $sql = "DELETE FROM `CourseCategory` WHERE CourseID = '{$courseID}' AND CategoryID = {$categoryID}";
        $result = $this->execute($sql);
        // $this->close();
        return $result === true  && $this->getAffectedRows() === 1;
    }

    public function get_categories_by_course(string $courseID): array
    {
        $sql = "SELECT * FROM `CourseCategory` WHERE CourseID = '{$courseID}'";
        $result = $this->execute($sql);
        $list = [];
        while ($row = $result->fetch_assoc()) {
            $list[] = new CourseCategoryDTO($row['CourseID'], $row['CategoryID']);
        }
        // $this->close();
        return $list;
    }
}
