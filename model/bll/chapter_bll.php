<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/chapter_dto.php';

class ChapterBLL extends Database
{
    public function create_chapter(ChapterDTO $chapter): bool
    {
        $sql = "BEGIN COURSE_CHAPTER_PKG.CREATE_CHAPTER_PROC(:chapterID, :courseID, :title, :description, :sortOrder); END;";

        $bindParams = [
            ':chapterID'   => $chapter->chapterID,
            ':courseID'    => $chapter->courseID,
            ':title'       => $chapter->title,
            ':description' => ['value' => $chapter->description, 'type' => OCI_B_CLOB],
            ':sortOrder'   => $chapter->sortOrder ?? 0,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function update_chapter(ChapterDTO $chapter): bool
    {
        $sql = "BEGIN COURSE_CHAPTER_PKG.UPDATE_CHAPTER_PROC(:chapterID_where, :courseID, :title, :description, :sortOrder); END;";

        $bindParams = [
            ':chapterID_where' => $chapter->chapterID,
            ':courseID'    => $chapter->courseID,
            ':title'       => $chapter->title,
            ':description' => ['value' => $chapter->description, 'type' => OCI_B_CLOB],
            ':sortOrder'   => $chapter->sortOrder ?? 0,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function delete_chapter(string $chapterID): bool
    {
        $sql = "BEGIN COURSE_CHAPTER_PKG.DELETE_CHAPTER_PROC(:chapterID); END;";
        $bindParams = [':chapterID' => $chapterID];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function get_all_chapters(): array
    {
        $sql = "BEGIN :result_cursor := COURSE_CHAPTER_PKG.GET_ALL_CHAPTERS_FUNC(); END;";
        $list = [];

        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[ChapterBLL] Failed to create new cursor for GET_ALL_CHAPTERS_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return [];
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[ChapterBLL] OCI Parse failed for GET_ALL_CHAPTERS_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return [];
        }

        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[ChapterBLL] OCI Execute failed for GET_ALL_CHAPTERS_FUNC block. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[ChapterBLL] OCI Execute failed for result cursor of GET_ALL_CHAPTERS_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }
        $stid_cursor = $out_cursor;

        if ($stid_cursor) {
            while (($row = @oci_fetch_array($stid_cursor, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $description = null;
                if (is_object($row['DESCRIPTION']) && method_exists($row['DESCRIPTION'], 'read')) {
                    $description = $row['DESCRIPTION']->read($row['DESCRIPTION']->size());
                } elseif (isset($row['DESCRIPTION'])) {
                    $description = $row['DESCRIPTION'];
                }
                $list[] = new ChapterDTO(
                    $row['CHAPTERID'],
                    $row['COURSEID'],
                    $row['TITLE'],
                    $description,
                    isset($row['SORTORDER']) ? (int)$row['SORTORDER'] : 0,
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid_cursor);
        }
        @oci_free_statement($parsed_stid);

        return $list;
    }

    public function get_chapter_by_id(string $chapterID): ?ChapterDTO
    {
        $sql = "BEGIN :result_cursor := COURSE_CHAPTER_PKG.GET_CHAPTER_BY_ID_FUNC(:chapterID_param); END;";
        $bindParams = [
            ':chapterID_param' => $chapterID
        ];

        $dto = null;
        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[ChapterBLL] Failed to create new cursor for GET_CHAPTER_BY_ID_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return null;
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[ChapterBLL] OCI Parse failed for GET_CHAPTER_BY_ID_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return null;
        }

        @oci_bind_by_name($parsed_stid, ':chapterID_param', $bindParams[':chapterID_param']);
        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[ChapterBLL] OCI Execute failed for GET_CHAPTER_BY_ID_FUNC. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[ChapterBLL] OCI Execute failed for result cursor of GET_CHAPTER_BY_ID_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }
        $stid_cursor = $out_cursor;

        if ($stid_cursor) {
            if (($row = @oci_fetch_array($stid_cursor, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $description = null;
                if (is_object($row['DESCRIPTION']) && method_exists($row['DESCRIPTION'], 'read')) {
                    $description = $row['DESCRIPTION']->read($row['DESCRIPTION']->size());
                } elseif (isset($row['DESCRIPTION'])) {
                    $description = $row['DESCRIPTION'];
                }
                $dto = new ChapterDTO(
                    $row['CHAPTERID'],
                    $row['COURSEID'],
                    $row['TITLE'],
                    $description,
                    isset($row['SORTORDER']) ? (int)$row['SORTORDER'] : 0,
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid_cursor);
        }
        @oci_free_statement($parsed_stid);

        return $dto;
    }

    public function get_chapters_by_course_id(string $courseID): array
    {
        $sql = "BEGIN :result_cursor := COURSE_CHAPTER_PKG.GET_CHAPTERS_BY_COURSE_FUNC(:courseID_param); END;";
        $bindParams = [
            ':courseID_param' => $courseID
        ];

        $list = [];
        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[ChapterBLL] Failed to create new cursor for GET_CHAPTERS_BY_COURSE_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return [];
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[ChapterBLL] OCI Parse failed for GET_CHAPTERS_BY_COURSE_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return [];
        }

        @oci_bind_by_name($parsed_stid, ':courseID_param', $bindParams[':courseID_param']);
        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[ChapterBLL] OCI Execute failed for GET_CHAPTERS_BY_COURSE_FUNC. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[ChapterBLL] OCI Execute failed for result cursor of GET_CHAPTERS_BY_COURSE_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }
        $stid_cursor = $out_cursor;

        if ($stid_cursor) {
            while (($row = @oci_fetch_array($stid_cursor, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $description = null;
                if (is_object($row['DESCRIPTION']) && method_exists($row['DESCRIPTION'], 'read')) {
                    $description = $row['DESCRIPTION']->read($row['DESCRIPTION']->size());
                } elseif (isset($row['DESCRIPTION'])) {
                    $description = $row['DESCRIPTION'];
                }
                $list[] = new ChapterDTO(
                    $row['CHAPTERID'],
                    $row['COURSEID'],
                    $row['TITLE'],
                    $description,
                    isset($row['SORTORDER']) ? (int)$row['SORTORDER'] : 0,
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid_cursor);
        }
        @oci_free_statement($parsed_stid);

        return $list;
    }
}
?>