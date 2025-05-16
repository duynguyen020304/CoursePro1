<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/course_image_dto.php';

class CourseImageBLL extends Database
{
    /**
     * Thêm ảnh cho khóa học
     *
     * @param CourseImageDTO $img
     * @return bool
     */
    public function create_image(CourseImageDTO $img): bool
    {
        $caption = $img->caption ? "{$img->caption}" : 'NULL';
        $sql = "INSERT INTO CourseImage (ImageID, CourseID, ImagePath, Caption, SortOrder) VALUES ('{$img->imageID}', '{$img->courseID}', '{$img->imagePath}', '{$caption}', {$img->sortOrder})";
        $result = $this->execute($sql);
        return $result === true && $this->getAffectedRows() === 1;
    }

    /**
     * Cập nhật ảnh của khóa học
     *
     * @param CourseImageDTO $img
     * @return bool
     */
    public function update_image(CourseImageDTO $img): bool
    {
        $caption = $img->caption ? "'{$img->caption}'" : 'NULL';
        $sql = "UPDATE CourseImage
                SET CourseID = '{$img->courseID}',
                    ImagePath = '{$img->imagePath}',
                    Caption   = {$caption},
                    SortOrder = {$img->sortOrder}
                WHERE ImageID = '{$img->imageID}'";
        $result = $this->execute($sql);
        return $result === true;
    }

    /**
     * Xóa ảnh theo ID
     *
     * @param string $imageID
     * @return bool
     */
    public function delete_image(string $imageID, string $courseID): bool
    {
        $sql = "DELETE FROM CourseImage WHERE ImageID = '{$imageID}' AND CourseID = '{$courseID}'";
        $result = $this->execute($sql);
        return $result === true && $this->getAffectedRows() === 1;
    }

    /**
     * Lấy danh sách ảnh theo khóa học
     *
     * @param string $courseID
     * @return CourseImageDTO[]
     */
    public function get_images_by_course(string $courseID): array
    {
        $sql = "SELECT * FROM CourseImage WHERE CourseID = '{$courseID}' ORDER BY SortOrder";
        $result = $this->execute($sql);
        $images = [];
        if ($result instanceof mysqli_result) {
            while ($row = $result->fetch_assoc()) {
                $images[] = new CourseImageDTO(
                    $row['ImageID'],
                    $row['CourseID'],
                    $row['ImagePath'],
                    $row['Caption'],
                    (int)$row['SortOrder']
                );
            }
        }
        return $images;
    }
}
