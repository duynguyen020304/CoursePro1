<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/course_requirement_dto.php';

class CourseRequirementBLL extends Database
{
    public function create(CourseRequirementDTO $req): bool
    {
        $sql = "BEGIN COURSE_REQUIREMENT_PKG.CREATE_REQUIREMENT_PROC(:requirementID, :courseID, :requirement); END;";

        $bindParams = [
            ':requirementID' => $req->requirementID,
            ':courseID'      => $req->courseID,
            ':requirement'   => $req->requirement,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function update(CourseRequirementDTO $req): bool
    {
        $sql = "BEGIN COURSE_REQUIREMENT_PKG.UPDATE_REQUIREMENT_PROC(:requirementID_where, :courseID_where, :requirement); END;";

        $bindParams = [
            ':requirementID_where' => $req->requirementID,
            ':courseID_where'    => $req->courseID,
            ':requirement'       => $req->requirement,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function delete(string $requirementID): bool
    {
        $sql = "BEGIN COURSE_REQUIREMENT_PKG.DELETE_REQUIREMENT_PROC(:requirementID); END;";

        $bindParams = [
            ':requirementID' => $requirementID,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function get_requirement_by_requirement_id(string $requirementID): ?CourseRequirementDTO
    {
        $sql = "BEGIN :result_cursor := COURSE_REQUIREMENT_PKG.GET_REQ_BY_REQ_ID_FUNC(:requirementID_param); END;";
        $bindParams = [
            ':requirementID_param' => $requirementID,
        ];

        $dto = null;
        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[CourseRequirementBLL] Failed to create new cursor for GET_REQ_BY_REQ_ID_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return null;
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[CourseRequirementBLL] OCI Parse failed for GET_REQ_BY_REQ_ID_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return null;
        }

        @oci_bind_by_name($parsed_stid, ':requirementID_param', $bindParams[':requirementID_param']);
        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[CourseRequirementBLL] OCI Execute failed for GET_REQ_BY_REQ_ID_FUNC block. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[CourseRequirementBLL] OCI Execute failed for result cursor of GET_REQ_BY_REQ_ID_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }
        $stid_cursor = $out_cursor;

        if ($stid_cursor) {
            if (($row = @oci_fetch_array($stid_cursor, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $dto = new CourseRequirementDTO(
                    $row['REQUIREMENTID'],
                    $row['COURSEID'],
                    $row['REQUIREMENT'],
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid_cursor);
        }
        @oci_free_statement($parsed_stid);

        return $dto;
    }

    public function get_requirements_by_course_id(string $courseID): array
    {
        $sql = "BEGIN :result_cursor := COURSE_REQUIREMENT_PKG.GET_REQS_BY_COURSE_ID_FUNC(:courseID); END;";
        $bindParams = [
            ':courseID' => $courseID
        ];

        $requirements = [];
        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[CourseRequirementBLL] Failed to create new cursor for GET_REQS_BY_COURSE_ID_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return [];
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[CourseRequirementBLL] OCI Parse failed for GET_REQS_BY_COURSE_ID_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return [];
        }

        @oci_bind_by_name($parsed_stid, ':courseID', $bindParams[':courseID']);
        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[CourseRequirementBLL] OCI Execute failed for GET_REQS_BY_COURSE_ID_FUNC. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[CourseRequirementBLL] OCI Execute failed for result cursor of GET_REQS_BY_COURSE_ID_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }
        $stid_cursor = $out_cursor;

        if ($stid_cursor) {
            while (($row = @oci_fetch_array($stid_cursor, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $requirements[] = new CourseRequirementDTO(
                    $row['REQUIREMENTID'],
                    $row['COURSEID'],
                    $row['REQUIREMENT'],
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid_cursor);
        }
        @oci_free_statement($parsed_stid);

        return $requirements;
    }
}
?>