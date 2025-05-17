<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/lesson_dto.php';
class LessonBLL extends Database
{
    public function create_lesson(LessonDTO $l)
    {
        $content = $l->content ? "'{$l->content}'" : 'NULL';
        $sql = "INSERT INTO CourseLesson (LessonID, CourseID, ChapterID, Title, Content, SortOrder) VALUES ('{$l->lessonID}', '{$l->courseID}', '{$l->chapterID}', '{$l->title}', {$content}, {$l->sortOrder})";
        $result = $this->execute($sql);
        // $this->close();
        return $result === true && $this->getAffectedRows() === 1;
    }

    public function delete_lesson(string $lid)
    {
        $sql = "DELETE FROM CourseLesson WHERE LessonID = '{$lid}'";
        $result = $this->execute($sql);
        // $this->close();
        return $result === true && $this->getAffectedRows() === 1;
    }

    public function update_lesson(LessonDTO $l)
    {
        $content = $l->content ? "Content = '{$l->content}'," : '';
        $sql = "UPDATE CourseLesson SET Title = '{$l->title}', {$content} SortOrder = {$l->sortOrder} WHERE LessonID = '{$l->lessonID}'";
        $result = $this->execute($sql);
        // $this->close();
        return $result === true;
    }

    public function get_lesson(string $lid): ?LessonDTO
    {
        $sql = "SELECT * FROM CourseLesson WHERE LessonID = '{$lid}'";
        $result = $this->execute($sql);
        $dto = null;
        if ($row = $result->fetch_assoc()) {
            $dto = new LessonDTO($row['LessonID'], $row['CourseID'], $row['ChapterID'], $row['Title'], $row['Content'], (int)$row['SortOrder']);
        }
        // $this->close();
        return $dto;
    }

    public function get_lessons_by_chapter(string $chapterID): array
    {
        $sql = "SELECT * FROM CourseLesson WHERE ChapterID = '{$chapterID}' ORDER BY SortOrder";
        $result = $this->execute($sql);
        $lessons = [];
        while ($row = $result->fetch_assoc()) {
            $lessons[] = new LessonDTO($row['LessonID'], $row['CourseID'], $row['ChapterID'], $row['Title'], $row['Content'], (int)$row['SortOrder']);
        }
        // $this->close();
        return $lessons;
    }
}
