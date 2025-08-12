<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/lesson_dto.php';

class LessonBLL extends Database
{
    /**
     * Tạo một bài học mới trong cơ sở dữ liệu.
     *
     * @param LessonDTO $lesson_dto Đối tượng chứa thông tin bài học.
     * @return bool Trả về true nếu tạo thành công, ngược lại false.
     */
    public function create_lesson(LessonDTO $lesson_dto): bool
    {
        $sql = "INSERT INTO CourseLesson (LessonID, CourseID, ChapterID, Title, Content, SortOrder)
                VALUES (?, ?, ?, ?, ?, ?)";

        $params = [
            $lesson_dto->lessonID,
            $lesson_dto->courseID,
            $lesson_dto->chapterID,
            $lesson_dto->title,
            $lesson_dto->content,
            $lesson_dto->sortOrder ?? 0,
        ];

        $result = $this->executePrepared($sql, $params);
        return ($result !== false) && ($this->getAffectedRows() === 1);
    }

    /**
     * Xóa một bài học khỏi cơ sở dữ liệu.
     *
     * @param string $lessonID ID của bài học cần xóa.
     * @return bool Trả về true nếu xóa thành công, ngược lại false.
     */
    public function delete_lesson(string $lessonID): bool
    {
        $sql = "DELETE FROM CourseLesson WHERE LessonID = ?";
        $params = [$lessonID];
        $result = $this->executePrepared($sql, $params);
        return ($result !== false) && ($this->getAffectedRows() === 1);
    }

    /**
     * Cập nhật thông tin một bài học.
     *
     * @param LessonDTO $lesson_dto Đối tượng chứa thông tin bài học cần cập nhật.
     * @return bool Trả về true nếu cập nhật thành công, ngược lại false.
     */
    public function update_lesson(LessonDTO $lesson_dto): bool
    {
        $sql = "UPDATE CourseLesson SET
                    CourseID = ?,
                    ChapterID = ?,
                    Title = ?,
                    Content = ?,
                    SortOrder = ?
                WHERE LessonID = ?";

        $params = [
            $lesson_dto->courseID,
            $lesson_dto->chapterID,
            $lesson_dto->title,
            $lesson_dto->content,
            $lesson_dto->sortOrder ?? 0,
            $lesson_dto->lessonID,
        ];

        $result = $this->executePrepared($sql, $params);
        return ($result !== false);
    }

    /**
     * Lấy thông tin một bài học bằng ID của nó.
     *
     * @param string $lessonID ID của bài học.
     * @return LessonDTO|null Trả về đối tượng LessonDTO nếu tìm thấy, ngược lại null.
     */
    public function get_lesson_by_lesson_id(string $lessonID): ?LessonDTO
    {
        $sql = "SELECT LessonID, CourseID, ChapterID, Title, Content, SortOrder,
                       DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') AS created_at_formatted
                FROM CourseLesson
                WHERE LessonID = ?";
        $params = [$lessonID];
        $result = $this->executePrepared($sql, $params);
        $dto = null;

        if ($result instanceof mysqli_result) {
            if ($row = $result->fetch_assoc()) {
                $dto = new LessonDTO(
                    $row['LessonID'],
                    $row['CourseID'],
                    $row['ChapterID'],
                    $row['Title'],
                    $row['Content'],
                    isset($row['SortOrder']) ? (int)$row['SortOrder'] : 0,
                    $row['created_at_formatted'] ?? null
                );
            }
            $result->free();
        }
        return $dto;
    }

    /**
     * Lấy danh sách các bài học theo ID của chương.
     *
     * @param string $chapterID ID của chương.
     * @return array Mảng các đối tượng LessonDTO.
     */
    public function get_lessons_by_chapter_id(string $chapterID): array
    {
        $sql = "SELECT LessonID, CourseID, ChapterID, Title, Content, SortOrder,
                       DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') AS created_at_formatted
                FROM CourseLesson
                WHERE ChapterID = ?
                ORDER BY SortOrder ASC";
        $params = [$chapterID];
        $result = $this->executePrepared($sql, $params);
        $lessons = [];

        if ($result instanceof mysqli_result) {
            while ($row = $result->fetch_assoc()) {
                $lessons[] = new LessonDTO(
                    $row['LessonID'],
                    $row['CourseID'],
                    $row['ChapterID'],
                    $row['Title'],
                    $row['Content'],
                    isset($row['SortOrder']) ? (int)$row['SortOrder'] : 0,
                    $row['created_at_formatted'] ?? null
                );
            }
            $result->free();
        }
        return $lessons;
    }

    /**
     * Lấy tất cả các bài học của một khóa học.
     *
     * @param string $courseID ID của khóa học.
     * @return array Mảng các đối tượng LessonDTO.
     */
    public function get_lessons_by_course(string $courseID): array
    {
        $sql = "SELECT LessonID, CourseID, ChapterID, Title, Content, SortOrder,
                       DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') AS created_at_formatted
                FROM CourseLesson
                WHERE CourseID = ?
                ORDER BY ChapterID ASC, SortOrder ASC";
        $params = [$courseID];
        $result = $this->executePrepared($sql, $params);
        $lessons = [];

        if ($result instanceof mysqli_result) {
            while ($row = $result->fetch_assoc()) {
                $lessons[] = new LessonDTO(
                    $row['LessonID'],
                    $row['CourseID'],
                    $row['ChapterID'],
                    $row['Title'],
                    $row['Content'],
                    isset($row['SortOrder']) ? (int)$row['SortOrder'] : 0,
                    $row['created_at_formatted'] ?? null
                );
            }
            $result->free();
        }
        return $lessons;
    }
}
