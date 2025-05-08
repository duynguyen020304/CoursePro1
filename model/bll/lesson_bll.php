<?php
require_once("../database.php");
require_once("../dto/lesson_dto.php");
class LessonBLL extends Database
{
    public function create_lesson(LessonDTO $l)
    {
        $content = $l->content ? "'{$l->content}'" : 'NULL';
        $sql = "INSERT INTO Lesson (LessonID, CourseID, ChapterID, Title, Content, SortOrder) VALUES ('{$l->lessonID}', '{$l->courseID}', '{$l->chapterID}', '{$l->title}', {$content}, {$l->sortOrder})";
        $this->execute($sql);
        $this->close();
    }

    public function delete_lesson(string $lid)
    {
        $sql = "DELETE FROM Lesson WHERE LessonID = '{$lid}'";
        $this->execute($sql);
        $this->close();
    }

    public function update_lesson(LessonDTO $l)
    {
        $content = $l->content ? "Content = '{$l->content}'," : '';
        $sql = "UPDATE Lesson SET Title = '{$l->title}', {$content} SortOrder = {$l->sortOrder} WHERE LessonID = '{$l->lessonID}'";
        $this->execute($sql);
        $this->close();
    }

    public function get_lesson(string $lid): ?LessonDTO
    {
        $sql = "SELECT * FROM Lesson WHERE LessonID = '{$lid}'";
        $result = $this->execute($sql);
        $dto = null;
        if ($row = $result->fetch_assoc()) {
            $dto = new LessonDTO($row['LessonID'], $row['CourseID'], $row['ChapterID'], $row['Title'], $row['Content'], (int)$row['SortOrder']);
        }
        $this->close();
        return $dto;
    }

    public function get_lessons_by_chapter(string $chapterID): array
    {
        $sql = "SELECT * FROM Lesson WHERE ChapterID = '{$chapterID}' ORDER BY SortOrder";
        $result = $this->execute($sql);
        $lessons = [];
        while ($row = $result->fetch_assoc()) {
            $lessons[] = new LessonDTO($row['LessonID'], $row['CourseID'], $row['ChapterID'], $row['Title'], $row['Content'], (int)$row['SortOrder']);
        }
        $this->close();
        return $lessons;
    }
}
