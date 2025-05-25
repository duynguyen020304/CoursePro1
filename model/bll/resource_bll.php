<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/resource_dto.php';

class ResourceBLL extends Database
{
    public function create_resource(ResourceDTO $resource): bool
    {
        $sql = "INSERT INTO COURSERESOURCE (ResourceID, LessonID, ResourcePath, Title, SortOrder)
                VALUES (:resourceID, :lessonID, :resourcePath, :title, :sortOrder)";
        $bindParams = [
            ':resourceID'   => $resource->resourceID,
            ':lessonID'     => $resource->lessonID,
            ':resourcePath' => $resource->resourcePath,
            ':title'        => $resource->title,
            ':sortOrder'    => $resource->sortOrder ?? 0,
        ];
        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false) && ($this->getAffectedRows() === 1);
    }

    public function get_resource_by_id(string $resourceID): ?ResourceDTO
    {
        $sql = "SELECT ResourceID, LessonID, ResourcePath, Title, SortOrder,
                       TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
                FROM COURSERESOURCE
                WHERE ResourceID = :resourceID_param";
        $bindParams = [':resourceID_param' => $resourceID];
        $stid = $this->executePrepared($sql, $bindParams);
        $dto = null;

        if ($stid) {
            if (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $dto = new ResourceDTO(
                    $row['RESOURCEID'],
                    $row['LESSONID'],
                    $row['RESOURCEPATH'],
                    $row['TITLE'],
                    isset($row['SORTORDER']) ? (int)$row['SORTORDER'] : 0,
                    $row['CREATED_AT_FORMATTED'] ?? null // Use the formatted alias
                );
            }
            @oci_free_statement($stid);
        }
        return $dto;
    }

    public function get_resources_by_lesson(string $lessonID): array
    {
        $sql = "SELECT ResourceID, LessonID, ResourcePath, Title, SortOrder,
                       TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
                FROM COURSERESOURCE
                WHERE LessonID = :lessonID_param
                ORDER BY SortOrder ASC";
        $bindParams = [':lessonID_param' => $lessonID];
        $stid = $this->executePrepared($sql, $bindParams);
        $resources = [];

        if ($stid) {
            while (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $resources[] = new ResourceDTO(
                    $row['RESOURCEID'],
                    $row['LESSONID'],
                    $row['RESOURCEPATH'],
                    $row['TITLE'],
                    isset($row['SORTORDER']) ? (int)$row['SORTORDER'] : 0,
                    $row['CREATED_AT_FORMATTED'] ?? null // Use the formatted alias
                );
            }
            @oci_free_statement($stid);
        }
        return $resources;
    }

    public function get_all_resources(): array
    {
        $sql = "SELECT ResourceID, LessonID, ResourcePath, Title, SortOrder,
                       TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
                FROM COURSERESOURCE
                ORDER BY SortOrder ASC";
        $stid = $this->executePrepared($sql);
        $resources = [];

        if ($stid) {
            while (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $resources[] = new ResourceDTO(
                    $row['RESOURCEID'],
                    $row['LESSONID'],
                    $row['RESOURCEPATH'],
                    $row['TITLE'],
                    isset($row['SORTORDER']) ? (int)$row['SORTORDER'] : 0,
                    $row['CREATED_AT_FORMATTED'] ?? null // Use the formatted alias
                );
            }
            @oci_free_statement($stid);
        }
        return $resources;
    }

    public function update_resource(ResourceDTO $resource): bool
    {
        $sql = "UPDATE COURSERESOURCE SET
                    LessonID = :lessonID,
                    ResourcePath = :resourcePath,
                    Title = :title,
                    SortOrder = :sortOrder
                WHERE ResourceID = :resourceID_where";
        $bindParams = [
            ':lessonID'       => $resource->lessonID,
            ':resourcePath'   => $resource->resourcePath,
            ':title'          => $resource->title,
            ':sortOrder'      => $resource->sortOrder ?? 0,
            ':resourceID_where' => $resource->resourceID,
        ];
        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function delete_resource(string $resourceID): bool
    {
        $sql = "DELETE FROM COURSERESOURCE WHERE ResourceID = :resourceID";
        $bindParams = [':resourceID' => $resourceID];
        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false) && ($this->getAffectedRows() === 1);
    }
}