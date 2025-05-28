<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/course_objective_dto.php';

class CourseObjectiveBLL extends Database
{
    public function create(CourseObjectiveDTO $obj): bool
    {
        $sql = "BEGIN COURSE_OBJECTIVE_PKG.CREATE_OBJECTIVE_PROC(:objectiveID, :courseID, :objective); END;";

        $bindParams = [
            ':objectiveID' => $obj->objectiveID,
            ':courseID'    => $obj->courseID,
            ':objective'   => $obj->objective,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function update(CourseObjectiveDTO $obj): bool
    {
        $sql = "BEGIN COURSE_OBJECTIVE_PKG.UPDATE_OBJECTIVE_PROC(:objectiveID_where, :courseID_where, :objective); END;";

        $bindParams = [
            ':objectiveID_where' => $obj->objectiveID,
            ':courseID_where'    => $obj->courseID,
            ':objective'         => $obj->objective,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function delete(string $objectiveID): bool
    {
        $sql = "BEGIN COURSE_OBJECTIVE_PKG.DELETE_OBJECTIVE_PROC(:objectiveID); END;";

        $bindParams = [
            ':objectiveID' => $objectiveID,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function get_objective_by_objective_id(string $objectiveID): ?CourseObjectiveDTO
    {
        $sql = "BEGIN :result_cursor := COURSE_OBJECTIVE_PKG.GET_OBJ_BY_OBJ_ID_FUNC(:objectiveID_param); END;";
        $bindParams = [
            ':objectiveID_param' => $objectiveID,
        ];

        $dto = null;
        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[CourseObjectiveBLL] Failed to create new cursor for GET_OBJ_BY_OBJ_ID_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return null;
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[CourseObjectiveBLL] OCI Parse failed for GET_OBJ_BY_OBJ_ID_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return null;
        }

        @oci_bind_by_name($parsed_stid, ':objectiveID_param', $bindParams[':objectiveID_param']);
        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[CourseObjectiveBLL] OCI Execute failed for GET_OBJ_BY_OBJ_ID_FUNC block. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[CourseObjectiveBLL] OCI Execute failed for result cursor of GET_OBJ_BY_OBJ_ID_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }
        $stid_cursor = $out_cursor;

        if ($stid_cursor) {
            if (($row = @oci_fetch_array($stid_cursor, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $dto = new CourseObjectiveDTO(
                    $row['OBJECTIVEID'],
                    $row['COURSEID'],
                    $row['OBJECTIVE'],
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid_cursor);
        }
        @oci_free_statement($parsed_stid);

        return $dto;
    }

    public function get_objectives_by_course_id(string $courseID): array
    {
        $sql = "BEGIN :result_cursor := COURSE_OBJECTIVE_PKG.GET_OBJS_BY_COURSE_ID_FUNC(:courseID_param); END;";
        $bindParams = [
            ':courseID_param' => $courseID
        ];

        $objectives = [];
        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[CourseObjectiveBLL] Failed to create new cursor for GET_OBJS_BY_COURSE_ID_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return [];
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[CourseObjectiveBLL] OCI Parse failed for GET_OBJS_BY_COURSE_ID_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return [];
        }

        @oci_bind_by_name($parsed_stid, ':courseID_param', $bindParams[':courseID_param']);
        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[CourseObjectiveBLL] OCI Execute failed for GET_OBJS_BY_COURSE_ID_FUNC block. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[CourseObjectiveBLL] OCI Execute failed for result cursor of GET_OBJS_BY_COURSE_ID_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }
        $stid_cursor = $out_cursor;

        if ($stid_cursor) {
            while (($row = @oci_fetch_array($stid_cursor, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $objectives[] = new CourseObjectiveDTO(
                    $row['OBJECTIVEID'],
                    $row['COURSEID'],
                    $row['OBJECTIVE'],
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid_cursor);
        }
        @oci_free_statement($parsed_stid);

        return $objectives;
    }
}
?>