<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/course_chapter_dto.php';
class CourseChapterBLL extends Database
{
    public function get_chapters_by_course(string $courseID): array
    {
        $sql = "SELECT * FROM Chapter WHERE CourseID = '{$courseID}' ORDER BY SortOrder";
        $result = $this->execute($sql);
        $chapters = [];
        while ($row = $result->fetch_assoc()) {
            $chapters[] = new ChapterDTO($row['ChapterID'], $row['CourseID'], $row['Title'], $row['Description'], (int)$row['SortOrder']);
        }
        $this->close();
        return $chapters;
    }
}
