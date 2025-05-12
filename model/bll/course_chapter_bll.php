<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/course_chapter_dto.php';

class CourseChapterBLL extends Database
{
    /**
     * Lấy danh sách chương của một khóa học
     *
     * @param string $courseID
     * @return ChapterDTO[]
     */
    public function get_chapters_by_course(string $courseID): array
    {
        $sql = "SELECT * FROM Chapter WHERE CourseID = '{$courseID}' ORDER BY SortOrder";
        $result = $this->execute($sql);
        $chapters = [];
        if ($result instanceof mysqli_result) {
            while ($row = $result->fetch_assoc()) {
                $chapters[] = new ChapterDTO(
                    $row['ChapterID'],
                    $row['CourseID'],
                    $row['Title'],
                    $row['Description'],
                    (int)$row['SortOrder']
                );
            }
        }
        $this->close();
        return $chapters;
    }

    /**
     * Tạo mới chương cho một khóa học
     *
     * @param ChapterDTO $chapter
     * @return bool
     */
    public function create_chapter(ChapterDTO $chapter): bool
    {
        $desc = $chapter->description !== null ? "'{$chapter->description}'" : 'NULL';
        $sql = "INSERT INTO Chapter (ChapterID, CourseID, Title, Description, SortOrder)\
                VALUES ('{$chapter->chapterID}', '{$chapter->courseID}', '{$chapter->title}', {$desc}, {$chapter->sortOrder})";
        $result = $this->execute($sql);
        // $this->close();
        return $result === true && $this->getAffectedRows() === 1;
    }

    /**
     * Cập nhật thông tin một chương
     *
     * @param ChapterDTO $chapter
     * @return bool
     */
    public function update_chapter(ChapterDTO $chapter): bool
    {
        $descClause = $chapter->description !== null ? "Description = '{$chapter->description}'," : '';
        $sql = "UPDATE Chapter SET \
                Title = '{$chapter->title}', \
                {$descClause} \
                SortOrder = {$chapter->sortOrder} \
                WHERE ChapterID = '{$chapter->chapterID}'";
        $result = $this->execute($sql);
        // $this->close();
        return $result === true && $this->getAffectedRows() === 1;
    }

    /**
     * Xóa một chương theo ID
     *
     * @param string $chapterID
     * @return bool
     */
    public function delete_chapter(string $chapterID): bool
    {
        $sql = "DELETE FROM Chapter WHERE ChapterID = '{$chapterID}'";
        $result = $this->execute($sql);
        // $this->close();
        return $result === true && $this->getAffectedRows() === 1;
    }
}
