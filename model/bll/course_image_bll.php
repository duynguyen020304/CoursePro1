<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/course_image_dto.php';

class CourseImageBLL extends Database
{
    public function create_image(CourseImageDTO $img): bool
    {
        $sql = "INSERT INTO COURSEIMAGE (ImageID, CourseID, ImagePath, Caption, SortOrder) 
                VALUES (:imageID, :courseID, :imagePath, :caption, :sortOrder)";

        $bindParams = [
            ':imageID'    => $img->imageID,
            ':courseID'   => $img->courseID,
            ':imagePath'  => $img->imagePath,
            ':caption'    => $img->caption,
            ':sortOrder'  => $img->sortOrder ?? 0,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false) && ($this->getAffectedRows() === 1);
    }

    public function update_image(CourseImageDTO $img): bool
    {
        $sql = "UPDATE COURSEIMAGE
                SET CourseID = :courseID,
                    ImagePath = :imagePath,
                    Caption   = :caption,
                    SortOrder = :sortOrder
                WHERE ImageID = :imageID_where";

        $bindParams = [
            ':courseID'   => $img->courseID,
            ':imagePath'  => $img->imagePath,
            ':caption'    => $img->caption,
            ':sortOrder'  => $img->sortOrder ?? 0,
            ':imageID_where' => $img->imageID,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function delete_image(string $imageID, string $courseID): bool
    {
        $sql = "DELETE FROM COURSEIMAGE WHERE ImageID = :imageID AND CourseID = :courseID";

        $bindParams = [
            ':imageID'  => $imageID,
            ':courseID' => $courseID,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false) && ($this->getAffectedRows() === 1);
    }

    public function get_image_by_id(string $imageID): ?CourseImageDTO
    {
        $sql = "SELECT ImageID, CourseID, ImagePath, Caption, SortOrder, created_at 
                FROM COURSEIMAGE 
                WHERE ImageID = :imageID_param";
        $bindParams = [':imageID_param' => $imageID];

        $stid = $this->executePrepared($sql, $bindParams);
        $dto = null;

        if ($stid) {
            if (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $dto = new CourseImageDTO(
                    $row['IMAGEID'],
                    $row['COURSEID'],
                    $row['IMAGEPATH'],
                    $row['CAPTION'],
                    isset($row['SORTORDER']) ? (int)$row['SORTORDER'] : 0,
                    $row['CREATED_AT'] ?? null
                );
            }
            @oci_free_statement($stid);
        }
        return $dto;
    }


    public function get_images_by_course(string $courseID): array
    {
        $sql = "SELECT ImageID, CourseID, ImagePath, Caption, SortOrder, created_at 
                FROM COURSEIMAGE 
                WHERE CourseID = :courseID_param 
                ORDER BY SortOrder ASC";

        $bindParams = [':courseID_param' => $courseID];

        $stid = $this->executePrepared($sql, $bindParams);
        $images = [];

        if ($stid) {
            while (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $images[] = new CourseImageDTO(
                    $row['IMAGEID'],
                    $row['COURSEID'],
                    $row['IMAGEPATH'],
                    $row['CAPTION'],
                    isset($row['SORTORDER']) ? (int)$row['SORTORDER'] : 0,
                    $row['CREATED_AT'] ?? null
                );
            }
            @oci_free_statement($stid);
        }
        return $images;
    }
}
?>