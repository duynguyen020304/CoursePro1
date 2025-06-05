<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/course_dto.php';

class CourseBLL extends Database
{
    public function create_course(CourseDTO $c): bool
    {
        $sql = "BEGIN COURSE_PKG.CREATE_COURSE_PROC(:courseID, :title, :description, :price, :difficulty, :language, :createdBy); END;";

        $bindParams = [
            ':courseID'     => $c->courseID,
            ':title'        => $c->title,
            ':description'  => ['value' => $c->description, 'type' => OCI_B_CLOB],
            ':price'        => is_numeric($c->price) ? (float)$c->price : 0,
            ':difficulty'   => $c->difficulty,
            ':language'     => $c->language,
            ':createdBy'    => $c->createdBy,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function delete_course(string $courseID): bool
    {
        $sql = "BEGIN COURSE_PKG.DELETE_COURSE_PROC(:courseID); END;";
        $bindParams = [':courseID' => $courseID];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function update_course(CourseDTO $c): bool
    {
        $sql = "BEGIN COURSE_PKG.UPDATE_COURSE_PROC(:courseID_where, :title, :description, :price, :difficulty, :language); END;";

        $bindParams = [
            ':courseID_where' => $c->courseID,
            ':title'        => $c->title,
            ':description'  => ['value' => $c->description, 'type' => OCI_B_CLOB],
            ':price'        => is_numeric($c->price) ? (float)$c->price : 0,
            ':difficulty'   => $c->difficulty,
            ':language'     => $c->language,
            ':createdBy'    => $c->createdBy,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function get_course(string $courseID): ?CourseDTO
    {
        $sql = "BEGIN :result_cursor := COURSE_PKG.GET_COURSE_BY_ID_FUNC(:courseID_param); END;";
        $bindParams = [
            ':courseID_param' => $courseID
        ];

        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[CourseBLL] Failed to create new cursor for GET_COURSE_BY_ID_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return null;
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[CourseBLL] OCI Parse failed for GET_COURSE_BY_ID_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return null;
        }

        @oci_bind_by_name($parsed_stid, ':courseID_param', $bindParams[':courseID_param']);
        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[CourseBLL] OCI Execute failed for GET_COURSE_BY_ID_FUNC block. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[CourseBLL] OCI Execute failed for result cursor of GET_COURSE_BY_ID_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }

        $stid = $out_cursor;
        $dto = null;
        if ($stid) {
            if (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $description = null;
                if (is_object($row['DESCRIPTION']) && method_exists($row['DESCRIPTION'], 'read')) {
                    $description = $row['DESCRIPTION']->read($row['DESCRIPTION']->size());
                } elseif (isset($row['DESCRIPTION'])) {
                    $description = $row['DESCRIPTION'];
                }

                $dto = new CourseDTO(
                    $row['COURSEID'],
                    $row['TITLE'],
                    $description,
                    isset($row['PRICE']) ? (float)$row['PRICE'] : 0.0,
                    $row['CREATEDBY'],
                    $row['DIFFICULTY'],
                    $row['LANGUAGE'],
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid);
        }
        @oci_free_statement($parsed_stid);
        return $dto;
    }

    public function get_all_courses(): array
    {
        $sql = "BEGIN :result_cursor := COURSE_PKG.GET_ALL_COURSES_FUNC(); END;";
        $list = [];
        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[CourseBLL] Failed to create new cursor for GET_ALL_COURSES_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return [];
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[CourseBLL] OCI Parse failed for GET_ALL_COURSES_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return [];
        }

        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[CourseBLL] OCI Execute failed for GET_ALL_COURSES_FUNC block. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[CourseBLL] OCI Execute failed for result cursor of GET_ALL_COURSES_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }

        $stid = $out_cursor;
        if ($stid) {
            while (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $description = null;
                if (is_object($row['DESCRIPTION']) && method_exists($row['DESCRIPTION'], 'read')) {
                    $description = $row['DESCRIPTION']->read($row['DESCRIPTION']->size());
                } elseif (isset($row['DESCRIPTION'])) {
                    $description = $row['DESCRIPTION'];
                }

                $list[] = new CourseDTO(
                    $row['COURSEID'],
                    $row['TITLE'],
                    $description,
                    isset($row['PRICE']) ? (float)$row['PRICE'] : 0.0,
                    $row['CREATEDBY'],
                    $row['DIFFICULTY'],
                    $row['LANGUAGE'],
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid);
        }
        @oci_free_statement($parsed_stid);
        return $list;
    }

    public function search_courses_by_title(string $title): array
    {
        $sql = "BEGIN :result_cursor := COURSE_PKG.GET_COURSES_BY_TITLE_FUNC(:title_param); END;";
        $list = [];
        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[CourseBLL] Failed to create new cursor for GET_COURSES_BY_TITLE_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return [];
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[CourseBLL] OCI Parse failed for GET_COURSES_BY_TITLE_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return [];
        }

        @oci_bind_by_name($parsed_stid, ':title_param', $title);
        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[CourseBLL] OCI Execute failed for GET_COURSES_BY_TITLE_FUNC block. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[CourseBLL] OCI Execute failed for result cursor of GET_COURSES_BY_TITLE_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }

        $stid = $out_cursor;
        if ($stid) {
            while (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $description = null;
                if (is_object($row['DESCRIPTION']) && method_exists($row['DESCRIPTION'], 'read')) {
                    $description = $row['DESCRIPTION']->read($row['DESCRIPTION']->size());
                } elseif (isset($row['DESCRIPTION'])) {
                    $description = $row['DESCRIPTION'];
                }

                $list[] = new CourseDTO(
                    $row['COURSEID'],
                    $row['TITLE'],
                    $description,
                    isset($row['PRICE']) ? (float)$row['PRICE'] : 0.0,
                    $row['CREATEDBY'],
                    $row['DIFFICULTY'],
                    $row['LANGUAGE'],
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid);
        }
        @oci_free_statement($parsed_stid);
        return $list;
    }

    public function get_courses_by_difficulty_lang(string $difficulty, string $language): array
    {
        $sql = "BEGIN :result_cursor := COURSE_PKG.GET_COURSES_BY_DIFFICULTY_LANG_FUNC(:p_difficulty, :p_language); END;";
        $list = [];

        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[CourseBLL] Failed to create new cursor for GET_COURSES_BY_DIFFICULTY_LANG_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return [];
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[CourseBLL] OCI Parse failed for GET_COURSES_BY_DIFFICULTY_LANG_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return [];
        }

        @oci_bind_by_name($parsed_stid, ':p_difficulty', $difficulty);
        @oci_bind_by_name($parsed_stid, ':p_language', $language);
        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[CourseBLL] OCI Execute failed for GET_COURSES_BY_DIFFICULTY_LANG_FUNC block. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[CourseBLL] OCI Execute failed for result cursor of GET_COURSES_BY_DIFFICULTY_LANG_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }

        $stid = $out_cursor;
        if ($stid) {
            while (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $description = null;
                if (is_object($row['DESCRIPTION']) && method_exists($row['DESCRIPTION'], 'read')) {
                    $description = $row['DESCRIPTION']->read($row['DESCRIPTION']->size());
                } elseif (isset($row['DESCRIPTION'])) {
                    $description = $row['DESCRIPTION'];
                }

                $list[] = new CourseDTO(
                    $row['COURSEID'],
                    $row['TITLE'],
                    $description,
                    isset($row['PRICE']) ? (float)$row['PRICE'] : 0.0,
                    $row['CREATEDBY'],
                    $row['DIFFICULTY'],
                    $row['LANGUAGE'],
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid);
        }
        @oci_free_statement($parsed_stid);
        return $list;
    }

    public function get_courses_by_language(string $language): array
    {
        $sql = "BEGIN :result_cursor := COURSE_PKG.GET_COURSES_BY_LANGUAGE_FUNC(:p_language); END;";
        $list = [];

        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[CourseBLL] Failed to create new cursor for GET_COURSES_BY_LANGUAGE_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return [];
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[CourseBLL] OCI Parse failed for GET_COURSES_BY_LANGUAGE_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return [];
        }

        @oci_bind_by_name($parsed_stid, ':p_language', $language);
        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[CourseBLL] OCI Execute failed for GET_COURSES_BY_LANGUAGE_FUNC block. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[CourseBLL] OCI Execute failed for result cursor of GET_COURSES_BY_LANGUAGE_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }

        $stid = $out_cursor;
        if ($stid) {
            while (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $description = null;
                if (is_object($row['DESCRIPTION']) && method_exists($row['DESCRIPTION'], 'read')) {
                    $description = $row['DESCRIPTION']->read($row['DESCRIPTION']->size());
                } elseif (isset($row['DESCRIPTION'])) {
                    $description = $row['DESCRIPTION'];
                }

                $list[] = new CourseDTO(
                    $row['COURSEID'],
                    $row['TITLE'],
                    $description,
                    isset($row['PRICE']) ? (float)$row['PRICE'] : 0.0,
                    $row['CREATEDBY'],
                    $row['DIFFICULTY'],
                    $row['LANGUAGE'],
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid);
        }
        @oci_free_statement($parsed_stid);
        return $list;
    }

    public function get_courses_by_difficulty(string $difficulty): array
    {
        $sql = "BEGIN :result_cursor := COURSE_PKG.GET_COURSES_BY_DIFFICULTY_FUNC(:p_difficulty); END;";
        $list = [];

        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[CourseBLL] Failed to create new cursor for GET_COURSES_BY_DIFFICULTY_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return [];
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[CourseBLL] OCI Parse failed for GET_COURSES_BY_DIFFICULTY_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return [];
        }

        @oci_bind_by_name($parsed_stid, ':p_difficulty', $difficulty);
        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[CourseBLL] OCI Execute failed for GET_COURSES_BY_DIFFICULTY_FUNC block. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[CourseBLL] OCI Execute failed for result cursor of GET_COURSES_BY_DIFFICULTY_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }

        $stid = $out_cursor;
        if ($stid) {
            while (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $description = null;
                if (is_object($row['DESCRIPTION']) && method_exists($row['DESCRIPTION'], 'read')) {
                    $description = $row['DESCRIPTION']->read($row['DESCRIPTION']->size());
                } elseif (isset($row['DESCRIPTION'])) {
                    $description = $row['DESCRIPTION'];
                }

                $list[] = new CourseDTO(
                    $row['COURSEID'],
                    $row['TITLE'],
                    $description,
                    isset($row['PRICE']) ? (float)$row['PRICE'] : 0.0,
                    $row['CREATEDBY'],
                    $row['DIFFICULTY'],
                    $row['LANGUAGE'],
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid);
        }
        @oci_free_statement($parsed_stid);
        return $list;
    }

    public function get_courses_paginated(int $pageNumber, int $pageSize = 10, ?string $filterDifficulty = null, ?string $filterLanguage = null): array
    {
        $sql = "BEGIN :result_cursor := COURSE_PKG.GET_COURSES_PAGINATED_FUNC(:p_page_number, :p_page_size, :p_filter_difficulty, :p_filter_language); END;";
        $list = [];

        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[CourseBLL] Failed to create new cursor for GET_COURSES_PAGINATED_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return [];
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[CourseBLL] OCI Parse failed for GET_COURSES_PAGINATED_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return [];
        }

        @oci_bind_by_name($parsed_stid, ':p_page_number', $pageNumber, -1, SQLT_INT);
        @oci_bind_by_name($parsed_stid, ':p_page_size', $pageSize, -1, SQLT_INT);
        @oci_bind_by_name($parsed_stid, ':p_filter_difficulty', $filterDifficulty);
        @oci_bind_by_name($parsed_stid, ':p_filter_language', $filterLanguage);
        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[CourseBLL] OCI Execute failed for GET_COURSES_PAGINATED_FUNC block. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[CourseBLL] OCI Execute failed for result cursor of GET_COURSES_PAGINATED_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }

        $stid = $out_cursor;
        if ($stid) {
            while (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $description = null;
                if (is_object($row['DESCRIPTION']) && method_exists($row['DESCRIPTION'], 'read')) {
                    $description = $row['DESCRIPTION']->read($row['DESCRIPTION']->size());
                } elseif (isset($row['DESCRIPTION'])) {
                    $description = $row['DESCRIPTION'];
                }

                $list[] = new CourseDTO(
                    $row['COURSEID'],
                    $row['TITLE'],
                    $description,
                    isset($row['PRICE']) ? (float)$row['PRICE'] : 0.0,
                    $row['CREATEDBY'],
                    $row['DIFFICULTY'],
                    $row['LANGUAGE'],
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid);
        }
        @oci_free_statement($parsed_stid);
        return $list;
    }
}