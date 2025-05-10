<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/course_image_dto.php';
class CourseImageBLL extends Database
{
    public function create_image(CourseImageDTO $img)
    {
        $caption = $img->caption ? "'{$img->caption}'" : 'NULL';
        $sql = "INSERT INTO CourseImage (ImageID, CourseID, ImagePath, Caption, SortOrder) VALUES ('{$img->imageID}', '{$img->courseID}', '{$img->imagePath}', {$caption}, {$img->sortOrder})";
        $this->execute($sql);
        $this->close();
    }

    public function delete_image(string $imageID)
    {
        $sql = "DELETE FROM CourseImage WHERE ImageID = '{$imageID}'";
        $this->execute($sql);
        $this->close();
    }

    public function get_images_by_course(string $courseID): array
    {
        $sql = "SELECT * FROM CourseImage WHERE CourseID = '{$courseID}' ORDER BY SortOrder";
        $result = $this->execute($sql);
        $images = [];
        while ($row = $result->fetch_assoc()) {
            $images[] = new CourseImageDTO($row['ImageID'], $row['CourseID'], $row['ImagePath'], $row['Caption'], (int)$row['SortOrder']);
        }
        $this->close();
        return $images;
    }
}
