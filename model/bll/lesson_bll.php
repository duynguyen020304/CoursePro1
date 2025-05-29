<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/lesson_dto.php';

class LessonBLL extends Database
{
    public function create_lesson(LessonDTO $lesson_dto): bool
    {
        $sql = "INSERT INTO COURSELESSON (LessonID, CourseID, ChapterID, Title, Content, SortOrder)
                VALUES (:lessonID, :courseID, :chapterID, :title, :content, :sortOrder)";
        if ($lesson_dto->content === null) { // Changed == to === for strict comparison
            $lesson_dto->content = "null"; // Consider handling actual NULL values if appropriate for your DB schema
        }
        $bindParams = [
            ':lessonID'  => $lesson_dto->lessonID,
            ':courseID'  => $lesson_dto->courseID,
            ':chapterID' => $lesson_dto->chapterID,
            ':title'     => $lesson_dto->title,
            ':content'   => $lesson_dto->content,
            ':sortOrder' => $lesson_dto->sortOrder ?? 0,
        ];
        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false) && ($this->getAffectedRows() === 1);
    }

    public function delete_lesson(string $lessonID): bool
    {
        $sql = "DELETE FROM COURSELESSON WHERE LessonID = :lessonID";
        $bindParams = [':lessonID' => $lessonID];
        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false) && ($this->getAffectedRows() === 1);
    }

    public function update_lesson(LessonDTO $lesson_dto): bool
    {
        $sql = "UPDATE COURSELESSON SET
                CourseID = :courseID,
                ChapterID = :chapterID,
                Title = :title,
                Content = :content,
                SortOrder = :sortOrder
                WHERE LessonID = :lessonID_where";
        $bindParams = [
            ':courseID'  => $lesson_dto->courseID,
            ':chapterID' => $lesson_dto->chapterID,
            ':title'     => $lesson_dto->title,
            ':content'   => $lesson_dto->content,
            ':sortOrder' => $lesson_dto->sortOrder ?? 0,
            ':lessonID_where' => $lesson_dto->lessonID,
        ];
        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function get_lesson_by_lesson_id(string $lessonID): ?LessonDTO
    {
        $sql = "SELECT LessonID, CourseID, ChapterID, Title, Content, SortOrder,
                       TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
                FROM COURSELESSON
                WHERE LessonID = :lessonID_param";
        $bindParams = [':lessonID_param' => $lessonID];
        $stid = $this->executePrepared($sql, $bindParams);
        $dto = null;

        if ($stid) {
            if (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $content = null;
                // Handle CLOB content if it's an OCI-Lob object
                if (is_object($row['CONTENT']) && method_exists($row['CONTENT'], 'read')) {
                    $content = $row['CONTENT']->read($row['CONTENT']->size());
                } elseif (isset($row['CONTENT'])) {
                    // Fallback for non-object content, though typically CLOBs are objects
                    $content = $row['CONTENT'];
                }
                $dto = new LessonDTO(
                    $row['LESSONID'],
                    $row['COURSEID'],
                    $row['CHAPTERID'],
                    $row['TITLE'],
                    $content,
                    isset($row['SORTORDER']) ? (int)$row['SORTORDER'] : 0,
                    $row['CREATED_AT_FORMATTED'] ?? null // Use the formatted alias
                );
            }
            @oci_free_statement($stid);
        }
        return $dto;
    }

    public function get_lessons_by_chapter_id(string $chapterID): array
    {
        $sql = "SELECT LessonID, CourseID, ChapterID, Title, Content, SortOrder,
                       TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
                FROM COURSELESSON
                WHERE ChapterID = :chapterID_param
                ORDER BY SortOrder ASC";
        $bindParams = [':chapterID_param' => $chapterID];
        $stid = $this->executePrepared($sql, $bindParams);
        $lessons = [];

        if ($stid) {
            while (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $content = null;
                // Handle CLOB content
                if (is_object($row['CONTENT']) && method_exists($row['CONTENT'], 'read')) {
                    $content = $row['CONTENT']->read($row['CONTENT']->size());
                } elseif (isset($row['CONTENT'])) {
                    $content = $row['CONTENT'];
                }
                $lessons[] = new LessonDTO(
                    $row['LESSONID'],
                    $row['COURSEID'],
                    $row['CHAPTERID'],
                    $row['TITLE'],
                    $content,
                    isset($row['SORTORDER']) ? (int)$row['SORTORDER'] : 0,
                    $row['CREATED_AT_FORMATTED'] ?? null // Use the formatted alias
                );
            }
            @oci_free_statement($stid);
        }
        return $lessons;
    }

    public function get_lessons_by_course(string $courseID): array
    {
        $sql = "SELECT LessonID, CourseID, ChapterID, Title, Content, SortOrder,
                       TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
                FROM COURSELESSON
                WHERE CourseID = :courseID_param
                ORDER BY ChapterID ASC, SortOrder ASC";
        $bindParams = [':courseID_param' => $courseID];
        $stid = $this->executePrepared($sql, $bindParams);
        $lessons = [];

        if ($stid) {
            while (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $content = null;
                // Handle CLOB content
                if (is_object($row['CONTENT']) && method_exists($row['CONTENT'], 'read')) {
                    $content = $row['CONTENT']->read($row['CONTENT']->size());
                } elseif (isset($row['CONTENT'])) {
                    $content = $row['CONTENT'];
                }
                $lessons[] = new LessonDTO(
                    $row['LESSONID'],
                    $row['COURSEID'],
                    $row['CHAPTERID'],
                    $row['TITLE'],
                    $content,
                    isset($row['SORTORDER']) ? (int)$row['SORTORDER'] : 0,
                    $row['CREATED_AT_FORMATTED'] ?? null // Use the formatted alias
                );
            }
            @oci_free_statement($stid);
        }
        return $lessons;
    }
}