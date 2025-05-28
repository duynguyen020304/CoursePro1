<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/instructor_dto.php';

class InstructorBLL extends Database
{
    public function create_instructor(InstructorDTO $inst): bool
    {
        $sql = "BEGIN INSTRUCTOR_PKG.CREATE_INSTRUCTOR_PROC(:instructorID, :userID, :biography); END;";

        $bindParams = [
            ':instructorID' => $inst->instructorID,
            ':userID'       => $inst->userID,
            ':biography'    => ['value' => $inst->biography, 'type' => OCI_B_CLOB],
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function delete_instructor(string $instructorID): bool
    {
        $sql = "BEGIN INSTRUCTOR_PKG.DELETE_INSTRUCTOR_PROC(:instructorID); END;";
        $bindParams = [':instructorID' => $instructorID];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function update_instructor(InstructorDTO $inst): bool
    {
        $sql = "BEGIN INSTRUCTOR_PKG.UPDATE_INSTRUCTOR_PROC(:instructorID_where, :userID, :biography); END;";

        $bindParams = [
            ':instructorID_where' => $inst->instructorID,
            ':userID'             => $inst->userID,
            ':biography'          => ['value' => $inst->biography, 'type' => OCI_B_CLOB],
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function get_instructor(string $instructorID): ?InstructorDTO
    {
        $sql = "BEGIN :result_cursor := INSTRUCTOR_PKG.GET_INSTRUCTOR_BY_ID_FUNC(:instructorID_param); END;";
        $bindParams = [
            ':instructorID_param' => $instructorID
        ];

        $dto = null;
        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[InstructorBLL] Failed to create new cursor for GET_INSTRUCTOR_BY_ID_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return null;
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[InstructorBLL] OCI Parse failed for GET_INSTRUCTOR_BY_ID_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return null;
        }

        @oci_bind_by_name($parsed_stid, ':instructorID_param', $bindParams[':instructorID_param']);
        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[InstructorBLL] OCI Execute failed for GET_INSTRUCTOR_BY_ID_FUNC block. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[InstructorBLL] OCI Execute failed for result cursor of GET_INSTRUCTOR_BY_ID_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }
        $stid_cursor = $out_cursor;

        if ($stid_cursor) {
            if (($row = @oci_fetch_array($stid_cursor, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $biography = null;
                if (is_object($row['BIOGRAPHY']) && method_exists($row['BIOGRAPHY'], 'read')) {
                    $biography = $row['BIOGRAPHY']->read($row['BIOGRAPHY']->size());
                } elseif (isset($row['BIOGRAPHY'])) {
                    $biography = $row['BIOGRAPHY'];
                }

                $dto = new InstructorDTO(
                    $row['INSTRUCTORID'],
                    $row['USERID'],
                    $biography,
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid_cursor);
        }
        @oci_free_statement($parsed_stid);

        return $dto;
    }

    public function get_instructor_by_user_id(string $userID): ?InstructorDTO
    {
        $sql = "BEGIN :result_cursor := INSTRUCTOR_PKG.GET_INSTRUCTOR_BY_USER_ID_FUNC(:userID_param); END;";
        $bindParams = [
            ':userID_param' => $userID
        ];

        $dto = null;
        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[InstructorBLL] Failed to create new cursor for GET_INSTRUCTOR_BY_USER_ID_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return null;
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[InstructorBLL] OCI Parse failed for GET_INSTRUCTOR_BY_USER_ID_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return null;
        }

        @oci_bind_by_name($parsed_stid, ':userID_param', $bindParams[':userID_param']);
        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[InstructorBLL] OCI Execute failed for GET_INSTRUCTOR_BY_USER_ID_FUNC block. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[InstructorBLL] OCI Execute failed for result cursor of GET_INSTRUCTOR_BY_USER_ID_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }
        $stid_cursor = $out_cursor;

        if ($stid_cursor) {
            if (($row = @oci_fetch_array($stid_cursor, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $biography = null;
                if (is_object($row['BIOGRAPHY']) && method_exists($row['BIOGRAPHY'], 'read')) {
                    $biography = $row['BIOGRAPHY']->read($row['BIOGRAPHY']->size());
                } elseif (isset($row['BIOGRAPHY'])) {
                    $biography = $row['BIOGRAPHY'];
                }

                $dto = new InstructorDTO(
                    $row['INSTRUCTORID'],
                    $row['USERID'],
                    $biography,
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid_cursor);
        }
        @oci_free_statement($parsed_stid);

        return $dto;
    }

    public function get_all_instructors(): array
    {
        $sql = "BEGIN :result_cursor := INSTRUCTOR_PKG.GET_ALL_INSTRUCTORS_FUNC(); END;";

        $list = [];
        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[InstructorBLL] Failed to create new cursor for GET_ALL_INSTRUCTORS_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return [];
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[InstructorBLL] OCI Parse failed for GET_ALL_INSTRUCTORS_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return [];
        }

        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[InstructorBLL] OCI Execute failed for GET_ALL_INSTRUCTORS_FUNC block. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[InstructorBLL] OCI Execute failed for result cursor of GET_ALL_INSTRUCTORS_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }
        $stid_cursor = $out_cursor;

        if ($stid_cursor) {
            while (($row = @oci_fetch_array($stid_cursor, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $biography = null;
                if (is_object($row['BIOGRAPHY']) && method_exists($row['BIOGRAPHY'], 'read')) {
                    $biography = $row['BIOGRAPHY']->read($row['BIOGRAPHY']->size());
                } elseif (isset($row['BIOGRAPHY'])) {
                    $biography = $row['BIOGRAPHY'];
                }

                $list[] = new InstructorDTO(
                    $row['INSTRUCTORID'],
                    $row['USERID'],
                    $biography,
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