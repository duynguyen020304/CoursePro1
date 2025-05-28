<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/lesson_dto.php';

class LessonBLL extends Database
{
    public function create_lesson(LessonDTO $lesson_dto): bool
    {
        $sql = "BEGIN COURSE_LESSON_PKG.CREATE_LESSON_PROC(:lessonID, :courseID, :chapterID, :title, :content, :sortOrder); END;";

        $bindParams = [
            ':lessonID'  => $lesson_dto->lessonID,
            ':courseID'  => $lesson_dto->courseID,
            ':chapterID' => $lesson_dto->chapterID,
            ':title'     => $lesson_dto->title,
            ':content'   => ['value' => $lesson_dto->content, 'type' => OCI_B_CLOB],
            ':sortOrder' => $lesson_dto->sortOrder ?? 0,
        ];
        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function delete_lesson(string $lessonID): bool
    {
        $sql = "BEGIN COURSE_LESSON_PKG.DELETE_LESSON_PROC(:lessonID); END;";
        $bindParams = [':lessonID' => $lessonID];
        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function update_lesson(LessonDTO $lesson_dto): bool
    {
        $sql = "BEGIN COURSE_LESSON_PKG.UPDATE_LESSON_PROC(:lessonID_where, :courseID, :chapterID, :title, :content, :sortOrder); END;";

        $bindParams = [
            ':lessonID_where' => $lesson_dto->lessonID,
            ':courseID'      => $lesson_dto->courseID,
            ':chapterID'     => $lesson_dto->chapterID,
            ':title'         => $lesson_dto->title,
            ':content'       => ['value' => $lesson_dto->content, 'type' => OCI_B_CLOB],
            ':sortOrder'     => $lesson_dto->sortOrder ?? 0,
        ];
        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function get_lesson_by_lesson_id(string $lessonID): ?LessonDTO
    {
        $sql = "BEGIN :result_cursor := COURSE_LESSON_PKG.GET_LESSON_BY_ID_FUNC(:lessonID_param); END;";
        $bindParams = [
            ':lessonID_param' => $lessonID
        ];

        $dto = null;
        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[LessonBLL] Failed to create new cursor for GET_LESSON_BY_ID_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return null;
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[LessonBLL] OCI Parse failed for GET_LESSON_BY_ID_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return null;
        }

        @oci_bind_by_name($parsed_stid, ':lessonID_param', $bindParams[':lessonID_param']);
        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[LessonBLL] OCI Execute failed for GET_LESSON_BY_ID_FUNC block. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[LessonBLL] OCI Execute failed for result cursor of GET_LESSON_BY_ID_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }
        $stid_cursor = $out_cursor;

        if ($stid_cursor) {
            if (($row = @oci_fetch_array($stid_cursor, OCI_ASSOC + OCI_RETURN_NULLS))) {
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
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid_cursor);
        }
        @oci_free_statement($parsed_stid);
        return $dto;
    }

    public function get_lessons_by_chapter_id(string $chapterID): array
    {
        $sql = "BEGIN :result_cursor := COURSE_LESSON_PKG.GET_LESSONS_BY_CHAPTER_FUNC(:chapterID_param); END;";
        $bindParams = [
            ':chapterID' => $chapterID
        ];

        $lessons = [];
        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[LessonBLL] Failed to create new cursor for GET_LESSONS_BY_CHAPTER_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return [];
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[LessonBLL] OCI Parse failed for GET_LESSONS_BY_CHAPTER_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return [];
        }

        @oci_bind_by_name($parsed_stid, ':chapterID', $bindParams[':chapterID']);
        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[LessonBLL] OCI Execute failed for GET_LESSONS_BY_CHAPTER_FUNC block. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[LessonBLL] OCI Execute failed for result cursor of GET_LESSONS_BY_CHAPTER_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }
        $stid_cursor = $out_cursor;

        if ($stid_cursor) {
            while (($row = @oci_fetch_array($stid_cursor, OCI_ASSOC + OCI_RETURN_NULLS))) {
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
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid_cursor);
        }
        @oci_free_statement($parsed_stid);
        return $lessons;
    }

    public function get_lessons_by_course(string $courseID): array
    {
        $sql = "BEGIN :result_cursor := COURSE_LESSON_PKG.GET_LESSONS_BY_COURSE_FUNC(:courseID); END;";
        $bindParams = [
            ':courseID' => $courseID
        ];

        $lessons = [];
        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[LessonBLL] Failed to create new cursor for GET_LESSONS_BY_COURSE_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return [];
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[LessonBLL] OCI Parse failed for GET_LESSONS_BY_COURSE_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return [];
        }

        @oci_bind_by_name($parsed_stid, ':courseID', $bindParams[':courseID']);
        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[LessonBLL] OCI Execute failed for GET_LESSONS_BY_COURSE_FUNC. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[LessonBLL] OCI Execute failed for result cursor of GET_LESSONS_BY_COURSE_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }
        $stid_cursor = $out_cursor;

        if ($stid_cursor) {
            while (($row = @oci_fetch_array($stid_cursor, OCI_ASSOC + OCI_RETURN_NULLS))) {
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
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid_cursor);
        }
        @oci_free_statement($parsed_stid);
        return $lessons;
    }
}
?>