<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/student_dto.php';

class StudentBLL extends Database
{
    public function create_student(StudentDTO $stu): bool
    {
        $sql = "BEGIN STUDENT_PKG.CREATE_STUDENT_PROC(:studentID, :userID); END;";
        $bindParams = [
            ':studentID' => $stu->studentID,
            ':userID'    => $stu->userID,
        ];
        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function delete_student(string $studentID): bool
    {
        $sql = "BEGIN STUDENT_PKG.DELETE_STUDENT_PROC(:studentID); END;";
        $bindParams = [':studentID' => $studentID];
        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function update_student(StudentDTO $stu): bool
    {
        $sql = "BEGIN STUDENT_PKG.UPDATE_STUDENT_PROC(:studentID_where, :userID); END;";
        $bindParams = [
            ':studentID_where' => $stu->studentID,
            ':userID'          => $stu->userID,
        ];
        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function get_student_by_student_id(string $studentID): ?StudentDTO
    {
        $sql = "BEGIN :result_cursor := STUDENT_PKG.GET_STUDENT_BY_ID_FUNC(:studentID_param); END;";
        $bindParams = [
            ':studentID_param' => $studentID
        ];

        $dto = null;
        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[StudentBLL] Failed to create new cursor for GET_STUDENT_BY_ID_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return null;
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[StudentBLL] OCI Parse failed for GET_STUDENT_BY_ID_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return null;
        }

        @oci_bind_by_name($parsed_stid, ':studentID_param', $bindParams[':studentID_param']);
        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[StudentBLL] OCI Execute failed for GET_STUDENT_BY_ID_FUNC block. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[StudentBLL] OCI Execute failed for result cursor of GET_STUDENT_BY_ID_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }
        $stid_cursor = $out_cursor;

        if ($stid_cursor) {
            if (($row = @oci_fetch_array($stid_cursor, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $dto = new StudentDTO(
                    $row['STUDENTID'],
                    $row['USERID'],
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid_cursor);
        }
        @oci_free_statement($parsed_stid);

        return $dto;
    }

    public function get_student_by_user_id(string $userID): ?StudentDTO
    {
        $sql = "BEGIN :result_cursor := STUDENT_PKG.GET_STUDENT_BY_USER_ID_FUNC(:userID_param); END;";
        $bindParams = [
            ':userID_param' => $userID
        ];

        $dto = null;
        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[StudentBLL] Failed to create new cursor for GET_STUDENT_BY_USER_ID_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return null;
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[StudentBLL] OCI Parse failed for GET_STUDENT_BY_USER_ID_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return null;
        }

        @oci_bind_by_name($parsed_stid, ':userID_param', $bindParams[':userID_param']);
        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[StudentBLL] OCI Execute failed for GET_STUDENT_BY_USER_ID_FUNC. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[StudentBLL] OCI Execute failed for result cursor of GET_STUDENT_BY_USER_ID_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }
        $stid_cursor = $out_cursor;

        if ($stid_cursor) {
            if (($row = @oci_fetch_array($stid_cursor, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $dto = new StudentDTO(
                    $row['STUDENTID'],
                    $row['USERID'],
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid_cursor);
        }
        @oci_free_statement($parsed_stid);

        return $dto;
    }

    public function get_all_students(): array
    {
        $sql = "BEGIN :result_cursor := STUDENT_PKG.GET_ALL_STUDENTS_FUNC(); END;";
        $list = [];
        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[StudentBLL] Failed to create new cursor for GET_ALL_STUDENTS_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return [];
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[StudentBLL] OCI Parse failed for GET_ALL_STUDENTS_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return [];
        }

        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[StudentBLL] OCI Execute failed for GET_ALL_STUDENTS_FUNC block. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[StudentBLL] OCI Execute failed for result cursor of GET_ALL_STUDENTS_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }
        $stid_cursor = $out_cursor;

        if ($stid_cursor) {
            while (($row = @oci_fetch_array($stid_cursor, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $list[] = new StudentDTO(
                    $row['STUDENTID'],
                    $row['USERID'],
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