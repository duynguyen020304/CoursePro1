<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/resource_dto.php';

class ResourceBLL extends Database
{
    public function create_resource(ResourceDTO $resource): bool
    {
        $sql = "BEGIN COURSE_RESOURCE_PKG.CREATE_RESOURCE_PROC(:resourceID, :lessonID, :resourcePath, :title, :sortOrder); END;";
        $bindParams = [
            ':resourceID'   => $resource->resourceID,
            ':lessonID'     => $resource->lessonID,
            ':resourcePath' => $resource->resourcePath,
            ':title'        => $resource->title,
            ':sortOrder'    => $resource->sortOrder ?? 0,
        ];
        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function get_resource_by_resource_id(string $resourceID): ?ResourceDTO
    {
        $sql = "BEGIN :result_cursor := COURSE_RESOURCE_PKG.GET_RESOURCE_BY_ID_FUNC(:resourceID_param); END;";
        $bindParams = [
            ':resourceID_param' => $resourceID
        ];

        $dto = null;
        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[ResourceBLL] Failed to create new cursor for GET_RESOURCE_BY_ID_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return null;
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[ResourceBLL] OCI Parse failed for GET_RESOURCE_BY_ID_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return null;
        }

        @oci_bind_by_name($parsed_stid, ':resourceID_param', $bindParams[':resourceID_param']);
        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[ResourceBLL] OCI Execute failed for GET_RESOURCE_BY_ID_FUNC block. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[ResourceBLL] OCI Execute failed for result cursor of GET_RESOURCE_BY_ID_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }
        $stid_cursor = $out_cursor;

        if ($stid_cursor) {
            if (($row = @oci_fetch_array($stid_cursor, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $dto = new ResourceDTO(
                    $row['RESOURCEID'],
                    $row['LESSONID'],
                    $row['RESOURCEPATH'],
                    $row['TITLE'],
                    isset($row['SORTORDER']) ? (int)$row['SORTORDER'] : 0,
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid_cursor);
        }
        @oci_free_statement($parsed_stid);

        return $dto;
    }

    public function get_resources_by_lesson_id(string $lessonID): array
    {
        $sql = "BEGIN :result_cursor := COURSE_RESOURCE_PKG.GET_RESOURCES_BY_LESSON_FUNC(:lessonID_param); END;";
        $bindParams = [
            ':lessonID_param' => $lessonID
        ];
        $resources = [];
        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[ResourceBLL] Failed to create new cursor for GET_RESOURCES_BY_LESSON_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return [];
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[ResourceBLL] OCI Parse failed for GET_RESOURCES_BY_LESSON_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return [];
        }

        @oci_bind_by_name($parsed_stid, ':lessonID_param', $bindParams[':lessonID_param']);
        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[ResourceBLL] OCI Execute failed for GET_RESOURCES_BY_LESSON_FUNC block. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[ResourceBLL] OCI Execute failed for result cursor of GET_RESOURCES_BY_LESSON_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }
        $stid_cursor = $out_cursor;

        if ($stid_cursor) {
            while (($row = @oci_fetch_array($stid_cursor, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $resources[] = new ResourceDTO(
                    $row['RESOURCEID'],
                    $row['LESSONID'],
                    $row['RESOURCEPATH'],
                    $row['TITLE'],
                    isset($row['SORTORDER']) ? (int)$row['SORTORDER'] : 0,
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid_cursor);
        }
        @oci_free_statement($parsed_stid);

        return $resources;
    }

    public function get_all_resources(): array
    {
        $sql = "BEGIN :result_cursor := COURSE_RESOURCE_PKG.GET_ALL_RESOURCES_FUNC(); END;";
        $resources = [];
        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[ResourceBLL] Failed to create new cursor for GET_ALL_RESOURCES_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return [];
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[ResourceBLL] OCI Parse failed for GET_ALL_RESOURCES_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return [];
        }

        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[ResourceBLL] OCI Execute failed for GET_ALL_RESOURCES_FUNC block. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[ResourceBLL] OCI Execute failed for result cursor of GET_ALL_RESOURCES_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }
        $stid_cursor = $out_cursor;

        if ($stid_cursor) {
            while (($row = @oci_fetch_array($stid_cursor, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $resources[] = new ResourceDTO(
                    $row['RESOURCEID'],
                    $row['LESSONID'],
                    $row['RESOURCEPATH'],
                    $row['TITLE'],
                    isset($row['SORTORDER']) ? (int)$row['SORTORDER'] : 0,
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid_cursor);
        }
        @oci_free_statement($parsed_stid);

        return $resources;
    }

    public function update_resource(ResourceDTO $resource): bool
    {
        $sql = "BEGIN COURSE_RESOURCE_PKG.UPDATE_RESOURCE_PROC(:resourceID_where, :lessonID, :resourcePath, :title, :sortOrder); END;";
        $bindParams = [
            ':resourceID_where' => $resource->resourceID,
            ':lessonID'       => $resource->lessonID,
            ':resourcePath'   => $resource->resourcePath,
            ':title'          => $resource->title,
            ':sortOrder'      => $resource->sortOrder ?? 0,
        ];
        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function delete_resource(string $resourceID): bool
    {
        $sql = "BEGIN COURSE_RESOURCE_PKG.DELETE_RESOURCE_PROC(:resourceID); END;";
        $bindParams = [':resourceID' => $resourceID];
        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }
}
