<?php
require_once __DIR__ . '/../Database/database.php';
require_once __DIR__ . '/../DTO/resource_dto.php';

class ResourceBLL extends Database
{
    /**
     * Tạo một tài nguyên mới.
     *
     * @param ResourceDTO $resource Đối tượng chứa thông tin tài nguyên.
     * @return bool Trả về true nếu tạo thành công, ngược lại false.
     */
    public function create_resource(ResourceDTO $resource): bool
    {
        $sql = "INSERT INTO COURSERESOURCES (ResourceID, LessonID, ResourcePath, Title, SortOrder) VALUES (?, ?, ?, ?, ?)";
        
        $params = [
            $resource->resourceID,
            $resource->lessonID,
            $resource->resourcePath,
            $resource->title,
            $resource->sortOrder ?? 0,
        ];

        $result = $this->executePrepared($sql, $params);
        return ($result !== false) && ($this->getAffectedRows() === 1);
    }

    /**
     * Lấy tài nguyên bằng ID của nó.
     *
     * @param string $resourceID ID của tài nguyên.
     * @return ResourceDTO|null Trả về đối tượng ResourceDTO nếu tìm thấy, ngược lại null.
     */
    public function get_resource_by_resource_id(string $resourceID): ?ResourceDTO
    {
        $sql = "SELECT ResourceID, LessonID, ResourcePath, Title, SortOrder,
                       DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') AS created_at_formatted
                FROM COURSERESOURCES
                WHERE ResourceID = ?";
        $params = [$resourceID];
        $result = $this->executePrepared($sql, $params);
        $dto = null;

        if ($result instanceof mysqli_result) {
            if ($row = $result->fetch_assoc()) {
                $dto = new ResourceDTO(
                    $row['ResourceID'],
                    $row['LessonID'],
                    $row['ResourcePath'],
                    $row['Title'],
                    isset($row['SortOrder']) ? (int)$row['SortOrder'] : 0,
                    $row['created_at_formatted'] ?? null
                );
            }
            $result->free();
        }
        return $dto;
    }

    /**
     * Lấy danh sách tài nguyên theo ID bài học.
     *
     * @param string $lessonID ID của bài học.
     * @return array Mảng các đối tượng ResourceDTO.
     */
    public function get_resources_by_lesson_id(string $lessonID): array
    {
        $sql = "SELECT ResourceID, LessonID, ResourcePath, Title, SortOrder,
                       DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') AS created_at_formatted
                FROM COURSERESOURCES
                WHERE LessonID = ?
                ORDER BY SortOrder ASC";
        $params = [$lessonID];
        $result = $this->executePrepared($sql, $params);
        $resources = [];

        if ($result instanceof mysqli_result) {
            while ($row = $result->fetch_assoc()) {
                $resources[] = new ResourceDTO(
                    $row['ResourceID'],
                    $row['LessonID'],
                    $row['ResourcePath'],
                    $row['Title'],
                    isset($row['SortOrder']) ? (int)$row['SortOrder'] : 0,
                    $row['created_at_formatted'] ?? null
                );
            }
            $result->free();
        }
        return $resources;
    }

    /**
     * Lấy tất cả các tài nguyên.
     *
     * @return array Mảng các đối tượng ResourceDTO.
     */
    public function get_all_resources(): array
    {
        $sql = "SELECT ResourceID, LessonID, ResourcePath, Title, SortOrder,
                       DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') AS created_at_formatted
                FROM COURSERESOURCES
                ORDER BY LessonID, SortOrder ASC";
        $result = $this->execute($sql);
        $resources = [];

        if ($result instanceof mysqli_result) {
            while ($row = $result->fetch_assoc()) {
                $resources[] = new ResourceDTO(
                    $row['ResourceID'],
                    $row['LessonID'],
                    $row['ResourcePath'],
                    $row['Title'],
                    isset($row['SortOrder']) ? (int)$row['SortOrder'] : 0,
                    $row['created_at_formatted'] ?? null
                );
            }
            $result->free();
        }
        return $resources;
    }

    /**
     * Cập nhật thông tin một tài nguyên.
     *
     * @param ResourceDTO $resource Đối tượng chứa thông tin tài nguyên cần cập nhật.
     * @return bool Trả về true nếu cập nhật thành công, ngược lại false.
     */
    public function update_resource(ResourceDTO $resource): bool
    {
        $sql = "UPDATE COURSERESOURCES SET LessonID = ?, ResourcePath = ?, Title = ?, SortOrder = ? 
                WHERE ResourceID = ?";
        
        $params = [
            $resource->lessonID,
            $resource->resourcePath,
            $resource->title,
            $resource->sortOrder ?? 0,
            $resource->resourceID,
        ];

        $result = $this->executePrepared($sql, $params);
        return ($result !== false);
    }

    /**
     * Xóa một tài nguyên.
     *
     * @param string $resourceID ID của tài nguyên cần xóa.
     * @return bool Trả về true nếu xóa thành công, ngược lại false.
     */
    public function delete_resource(string $resourceID): bool
    {
        $sql = "DELETE FROM COURSERESOURCES WHERE ResourceID = ?";
        $params = [$resourceID];
        $result = $this->executePrepared($sql, $params);
        return ($result !== false) && ($this->getAffectedRows() === 1);
    }
}
