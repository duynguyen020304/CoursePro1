<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/course_image_dto.php';

class CourseImageBLL extends Database
{
    public function create_image(CourseImageDTO $img): bool
    {
        $sql = "BEGIN COURSE_IMAGE_PKG.CREATE_IMAGE_PROC(:imageID, :courseID, :imagePath, :caption, :sortOrder); END;";

        $bindParams = [
            ':imageID'    => $img->imageID,
            ':courseID'   => $img->courseID,
            ':imagePath'  => $img->imagePath,
            ':caption'    => $img->caption,
            ':sortOrder'  => $img->sortOrder ?? 0,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function update_image(CourseImageDTO $img): bool
    {
        $sql = "BEGIN COURSE_IMAGE_PKG.UPDATE_IMAGE_PROC(:imageID_where, :courseID, :imagePath, :caption, :sortOrder); END;";

        $bindParams = [
            ':imageID_where' => $img->imageID,
            ':courseID'   => $img->courseID,
            ':imagePath'  => $img->imagePath,
            ':caption'    => $img->caption,
            ':sortOrder'  => $img->sortOrder ?? 0,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function unlink_image_course(string $imageID, string $courseID): bool
    {
        $sql = "BEGIN COURSE_IMAGE_PKG.UNLINK_IMAGE_COURSE_PROC(:imageID, :courseID); END;";

        $bindParams = [
            ':imageID'  => $imageID,
            ':courseID' => $courseID,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function get_image_by_image_id(string $imageID): ?CourseImageDTO
    {
        $sql = "BEGIN :result_cursor := COURSE_IMAGE_PKG.GET_IMAGE_BY_IMAGE_ID_FUNC(:imageID_param); END;";
        $bindParams = [
            ':imageID_param' => $imageID
        ];

        $dto = null;
        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[CourseImageBLL] Failed to create new cursor for GET_IMAGE_BY_IMAGE_ID_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return null;
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[CourseImageBLL] OCI Parse failed for GET_IMAGE_BY_IMAGE_ID_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return null;
        }

        @oci_bind_by_name($parsed_stid, ':imageID_param', $bindParams[':imageID_param']);
        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[CourseImageBLL] OCI Execute failed for GET_IMAGE_BY_IMAGE_ID_FUNC block. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[CourseImageBLL] OCI Execute failed for result cursor of GET_IMAGE_BY_IMAGE_ID_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }
        $stid_cursor = $out_cursor;

        if ($stid_cursor) {
            if (($row = @oci_fetch_array($stid_cursor, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $dto = new CourseImageDTO(
                    $row['IMAGEID'],
                    $row['COURSEID'],
                    $row['IMAGEPATH'],
                    $row['CAPTION'],
                    isset($row['SORTORDER']) ? (int)$row['SORTORDER'] : 0,
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid_cursor);
        }
        @oci_free_statement($parsed_stid);

        return $dto;
    }

    public function get_images_by_course_id(string $courseID): array
    {
        $sql = "BEGIN :result_cursor := COURSE_IMAGE_PKG.GET_IMAGES_BY_COURSE_ID_FUNC(:courseID_param); END;";
        $bindParams = [
            ':courseID_param' => $courseID
        ];

        $images = [];
        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[CourseImageBLL] Failed to create new cursor for GET_IMAGES_BY_COURSE_ID_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return [];
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[CourseImageBLL] OCI Parse failed for GET_IMAGES_BY_COURSE_ID_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return [];
        }

        @oci_bind_by_name($parsed_stid, ':courseID_param', $bindParams[':courseID_param']);
        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[CourseImageBLL] OCI Execute failed for GET_IMAGES_BY_COURSE_ID_FUNC block. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[CourseImageBLL] OCI Execute failed for result cursor of GET_IMAGES_BY_COURSE_ID_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }
        $stid_cursor = $out_cursor;

        if ($stid_cursor) {
            while (($row = @oci_fetch_array($stid_cursor, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $images[] = new CourseImageDTO(
                    $row['IMAGEID'],
                    $row['COURSEID'],
                    $row['IMAGEPATH'],
                    $row['CAPTION'],
                    isset($row['SORTORDER']) ? (int)$row['SORTORDER'] : 0,
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid_cursor);
        }
        @oci_free_statement($parsed_stid);

        return $images;
    }
}
?>