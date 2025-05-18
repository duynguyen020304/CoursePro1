<?php

require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/resource_dto.php';

class ResourceBLL extends Database
{
    public function create_resource(ResourceDTO $resource): bool
    {
        $title = $resource->title !== null ? "'" . $this->conn->real_escape_string($resource->title) . "'" : "NULL";
        $sql = "INSERT INTO `CourseResource` (ResourceID, LessonID, ResourcePath, Title, SortOrder)
                VALUES (
                    '{$resource->resourceID}',
                    '{$resource->lessonID}',
                    '{$resource->resourcePath}',
                    {$title},
                    {$resource->sortOrder}
                )";
        $result = $this->execute($sql);
        return $result === true && $this->getAffectedRows() === 1;
    }

    public function get_resource_by_id(string $resourceID): ?ResourceDTO
    {
        $sql = "SELECT * FROM `CourseResource` WHERE ResourceID = '{$resourceID}'";
        $result = $this->execute($sql);
        if ($result && $row = $result->fetch_assoc()) {
            return new ResourceDTO(
                $row['ResourceID'],
                $row['LessonID'],
                $row['ResourcePath'],
                $row['Title'],
                (int)$row['SortOrder'],
                $row['created_at']
            );
        }
        return null;
    }

    public function get_resources_by_lesson(string $lessonID): array
    {
        $sql = "SELECT * FROM `CourseResource` WHERE LessonID = '{$lessonID}' ORDER BY SortOrder ASC";
        $result = $this->execute($sql);
        $resources = [];
        while ($row = $result->fetch_assoc()) {
            $resources[] = new ResourceDTO(
                $row['ResourceID'],
                $row['LessonID'],
                $row['ResourcePath'],
                $row['Title'],
                (int)$row['SortOrder'],
                $row['created_at']
            );
        }
        return $resources;
    }

    public function get_all_resources(): array
    {
        $sql = "SELECT * FROM `CourseResource` ORDER BY SortOrder ASC";
        $result = $this->execute($sql);
        $resources = [];
        while ($row = $result->fetch_assoc()) {
            $resources[] = new ResourceDTO(
                $row['ResourceID'],
                $row['LessonID'],
                $row['ResourcePath'],
                $row['Title'],
                (int)$row['SortOrder'],
                $row['created_at']
            );
        }
        return $resources;
    }

    public function update_resource(ResourceDTO $resource): bool
    {
        $title = $resource->title !== null ? "'" . $this->conn->real_escape_string($resource->title) . "'" : "NULL";
        $sql = "UPDATE `CourseResource` SET
                    LessonID = '{$resource->lessonID}',
                    ResourcePath = '{$resource->resourcePath}',
                    Title = {$title},
                    SortOrder = {$resource->sortOrder}
                WHERE ResourceID = '{$resource->resourceID}'";
        $result = $this->execute($sql);
        return $result === true;
    }

    public function delete_resource(string $resourceID): bool
    {
        $sql = "DELETE FROM `CourseResource` WHERE ResourceID = '{$resourceID}'";
        $result = $this->execute($sql);
        return $result === true && $this->getAffectedRows() === 1;
    }
}