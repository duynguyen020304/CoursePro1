<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/lesson_dto.php';

class LessonBLL extends Database
{
    public function create_lesson(LessonDTO $l): bool
    {
        $sql = "INSERT INTO COURSELESSON (LessonID, CourseID, ChapterID, Title, Content, SortOrder) 
                VALUES (:lessonID, :courseID, :chapterID, :title, :content, :sortOrder)";
        $bindParams = [
            ':lessonID'  => $l->lessonID,
            ':courseID'  => $l->courseID,
            ':chapterID' => $l->chapterID,
            ':title'     => $l->title,
            ':content'   => ['value' => $l->content, 'type' => OCI_B_CLOB],
            ':sortOrder' => $l->sortOrder ?? 0,
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

    public function update_lesson(LessonDTO $l): bool
    {
        $sql = "UPDATE COURSELESSON SET 
                CourseID = :courseID, 
                ChapterID = :chapterID, 
                Title = :title, 
                Content = :content, 
                SortOrder = :sortOrder 
                WHERE LessonID = :lessonID_where";
        $bindParams = [
            ':courseID'  => $l->courseID,
            ':chapterID' => $l->chapterID,
            ':title'     => $l->title,
            ':content'   => ['value' => $l->content, 'type' => OCI_B_CLOB],
            ':sortOrder' => $l->sortOrder ?? 0,
            ':lessonID_where' => $l->lessonID,
        ];
        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function get_lesson(string $lessonID): ?LessonDTO
    {
        $sql = "SELECT LessonID, CourseID, ChapterID, Title, Content, SortOrder, created_at 
                FROM COURSELESSON 
                WHERE LessonID = :lessonID_param";
        $bindParams = [':lessonID_param' => $lessonID];
        $stid = $this->executePrepared($sql, $bindParams);
        $dto = null;

        if ($stid) {
            if (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $content = null;
                if (is_object($row['CONTENT']) && method_exists($row['CONTENT'], 'read')) {
                    $content = $row['CONTENT']->read($row['CONTENT']->size());
                } elseif (isset($row['CONTENT'])) {
                    $content = $row['CONTENT'];
                }
                $dto = new LessonDTO(
                    $row['LESSONID'],
                    $row['COURSEID'],
                    $row['CHAPTERID'],
                    $row['TITLE'],
                    $content,
                    isset($row['SORTORDER']) ? (int)$row['SORTORDER'] : 0,
                    $row['CREATED_AT'] ?? null
                );
            }
            @oci_free_statement($stid);
        }
        return $dto;
    }

    public function get_lessons_by_chapter(string $chapterID): array
    {
        $sql = "SELECT LessonID, CourseID, ChapterID, Title, Content, SortOrder, created_at 
                FROM COURSELESSON 
                WHERE ChapterID = :chapterID_param 
                ORDER BY SortOrder ASC";
        $bindParams = [':chapterID_param' => $chapterID];
        $stid = $this->executePrepared($sql, $bindParams);
        $lessons = [];

        if ($stid) {
            while (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $content = null;
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
                    $row['CREATED_AT'] ?? null
                );
            }
            @oci_free_statement($stid);
        }
        return $lessons;
    }

    public function get_lessons_by_course(string $courseID): array
    {
        $sql = "SELECT LessonID, CourseID, ChapterID, Title, Content, SortOrder, created_at 
                FROM COURSELESSON 
                WHERE CourseID = :courseID_param 
                ORDER BY ChapterID ASC, SortOrder ASC";
        $bindParams = [':courseID_param' => $courseID];
        $stid = $this->executePrepared($sql, $bindParams);
        $lessons = [];

        if ($stid) {
            while (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $content = null;
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
                    $row['CREATED_AT'] ?? null
                );
            }
            @oci_free_statement($stid);
        }
        return $lessons;
    }
}
?>