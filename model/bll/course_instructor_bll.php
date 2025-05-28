<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/course_instructor_dto.php';

class CourseInstructorBLL extends Database
{
    public function add(string $courseID, string $instructorID): bool
    {
        $sql = "BEGIN COURSE_INSTRUCTOR_PKG.ADD_COURSE_INSTRUCTOR_PROC(:courseID, :instructorID); END;";

        $bindParams = [
            ':courseID'     => $courseID,
            ':instructorID' => $instructorID,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function update(string $oldCourseID, string $oldInstructorID, string $newCourseID, string $newInstructorID): bool
    {
        $sql = "BEGIN COURSE_INSTRUCTOR_PKG.UPDATE_COURSE_INSTRUCTOR_PROC(:old_courseID, :old_instructorID, :new_courseID, :new_instructorID); END;";

        $bindParams = [
            ':old_courseID'     => $oldCourseID,
            ':old_instructorID' => $oldInstructorID,
            ':new_courseID'     => $newCourseID,
            ':new_instructorID' => $newInstructorID,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function unlink_course_instructor(string $courseID, string $instructorID): bool
    {
        $sql = "BEGIN COURSE_INSTRUCTOR_PKG.UNLINK_COURSE_INSTRUCTOR_PROC(:courseID, :instructorID); END;";

        $bindParams = [
            ':courseID'  => $courseID,
            ':instructorID' => $instructorID,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function get_assignment(string $courseID, string $instructorID): ?CourseInstructorDTO
    {
        $sql = "BEGIN :result_cursor := COURSE_INSTRUCTOR_PKG.GET_ASSIGNMENT_FUNC(:courseID_param, :instructorID_param); END;";
        $bindParams = [
            ':courseID_param'     => $courseID,
            ':instructorID_param' => $instructorID,
        ];

        $dto = null;
        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[CourseInstructorBLL] Failed to create new cursor for GET_ASSIGNMENT_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return null;
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[CourseInstructorBLL] OCI Parse failed for GET_ASSIGNMENT_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return null;
        }

        @oci_bind_by_name($parsed_stid, ':courseID_param', $bindParams[':courseID_param']);
        @oci_bind_by_name($parsed_stid, ':instructorID_param', $bindParams[':instructorID_param']);
        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[CourseInstructorBLL] OCI Execute failed for GET_ASSIGNMENT_FUNC block. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[CourseInstructorBLL] OCI Execute failed for result cursor of GET_ASSIGNMENT_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }
        $stid_cursor = $out_cursor;

        if ($stid_cursor) {
            if (($row = @oci_fetch_array($stid_cursor, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $dto = new CourseInstructorDTO(
                    $row['COURSEID'],
                    $row['INSTRUCTORID'],
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid_cursor);
        }
        @oci_free_statement($parsed_stid);

        return $dto;
    }

    public function get_instructors_by_course_id(string $courseID): array
    {
        $sql = "BEGIN :result_cursor := COURSE_INSTRUCTOR_PKG.GET_INSTR_BY_COURSE_FUNC(:courseID); END;";
        $bindParams = [
            ':courseID' => $courseID
        ];

        $list = [];
        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[CourseInstructorBLL] Failed to create new cursor for GET_INSTR_BY_COURSE_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return [];
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[CourseInstructorBLL] OCI Parse failed for GET_INSTR_BY_COURSE_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return [];
        }

        @oci_bind_by_name($parsed_stid, ':courseID', $bindParams[':courseID']);
        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[CourseInstructorBLL] OCI Execute failed for GET_INSTR_BY_COURSE_FUNC. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[CourseInstructorBLL] OCI Execute failed for result cursor of GET_INSTR_BY_COURSE_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }
        $stid_cursor = $out_cursor;

        if ($stid_cursor) {
            while (($row = @oci_fetch_array($stid_cursor, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $list[] = new CourseInstructorDTO(
                    $row['COURSEID'],
                    $row['INSTRUCTORID'],
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