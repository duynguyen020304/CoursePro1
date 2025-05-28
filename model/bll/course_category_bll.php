<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/course_category_dto.php';

class CourseCategoryBLL extends Database
{
    public function link_course_category(CourseCategoryDTO $cc): bool
    {
        $sql = "BEGIN COURSE_CATEGORY_PKG.LINK_COURSE_CATEGORY_PROC(:courseID, :categoryID); END;";

        $bindParams = [
            ':courseID'   => $cc->courseID,
            ':categoryID' => (int)$cc->categoryID,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function unlink_course_category(string $courseID, $categoryID): bool
    {
        $sql = "BEGIN COURSE_CATEGORY_PKG.UNLINK_COURSE_CATEGORY_PROC(:courseID, :categoryID); END;";

        $bindParams = [
            ':courseID'   => $courseID,
            ':categoryID' => (int)$categoryID,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function get_categories_by_course_id(string $courseID): array
    {
        $sql = "BEGIN :result_cursor := COURSE_CATEGORY_PKG.GET_CATEGORIES_BY_COURSE_FUNC(:courseID_param); END;";
        $bindParams = [
            ':courseID_param' => $courseID
        ];

        $list = [];
        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[CourseCategoryBLL] Failed to create new cursor for GET_CATEGORIES_BY_COURSE_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return [];
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[CourseCategoryBLL] OCI Parse failed for GET_CATEGORIES_BY_COURSE_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return [];
        }

        @oci_bind_by_name($parsed_stid, ':courseID_param', $bindParams[':courseID_param']);
        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[CourseCategoryBLL] OCI Execute failed for GET_CATEGORIES_BY_COURSE_FUNC block. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[CourseCategoryBLL] OCI Execute failed for result cursor of GET_CATEGORIES_BY_COURSE_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }
        $stid_cursor = $out_cursor;

        if ($stid_cursor) {
            while (($row = @oci_fetch_array($stid_cursor, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $list[] = new CourseCategoryDTO(
                    $row['COURSEID'],
                    isset($row['CATEGORYID']) ? (int)$row['CATEGORYID'] : 0,
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid_cursor);
        }
        @oci_free_statement($parsed_stid);

        return $list;
    }

    public function get_courses_by_category($categoryID): array
    {
        $sql = "BEGIN :result_cursor := COURSE_CATEGORY_PKG.GET_COURSES_BY_CATEGORY_FUNC(:categoryID_param); END;";
        $bindParams = [
            ':categoryID_param' => (int)$categoryID
        ];

        $list = [];
        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[CourseCategoryBLL] Failed to create new cursor for GET_COURSES_BY_CATEGORY_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return [];
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[CourseCategoryBLL] OCI Parse failed for GET_COURSES_BY_CATEGORY_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return [];
        }

        @oci_bind_by_name($parsed_stid, ':categoryID_param', $bindParams[':categoryID_param']);
        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[CourseCategoryBLL] OCI Execute failed for GET_COURSES_BY_CATEGORY_FUNC block. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[CourseCategoryBLL] OCI Execute failed for result cursor of GET_COURSES_BY_CATEGORY_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }
        $stid_cursor = $out_cursor;

        if ($stid_cursor) {
            while (($row = @oci_fetch_array($stid_cursor, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $list[] = new CourseCategoryDTO(
                    $row['COURSEID'],
                    isset($row['CATEGORYID']) ? (int)$row['CATEGORYID'] : 0,
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid_cursor);
        }
        @oci_free_statement($parsed_stid);

        return $list;
    }

    public function link_exists(string $courseID, $categoryID): bool
    {
        $sql = "SELECT COURSE_CATEGORY_PKG.LINK_EXISTS_FUNC(:courseID, :categoryID) AS LINK_EXISTS FROM DUAL";

        $bindParams = [
            ':courseID'   => $courseID,
            ':categoryID' => (int)$categoryID,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        if ($stid) {
            $row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS);
            @oci_free_statement($stid);
            if ($row && isset($row['LINK_EXISTS'])) {
                return (int)$row['LINK_EXISTS'] === 1;
            }
        }
        error_log('[CourseCategoryBLL] Failed to check link existence for CourseID: ' . $courseID . ', CategoryID: ' . $categoryID);
        return false;
    }
}
?>