<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/course_dto.php';

class CourseBLL extends Database
{
    public function create_course(CourseDTO $c): bool
    {
        $sql = "INSERT INTO COURSE (CourseID, Title, Description, Price, CreatedBy) 
                VALUES (:courseID, :title, :description, :price, :createdBy)";

        $bindParams = [
            ':courseID'     => $c->courseID,
            ':title'        => $c->title,
            ':description'  => ['value' => $c->description, 'type' => OCI_B_CLOB],
            ':price'        => is_numeric($c->price) ? (float)$c->price : 0,
            ':createdBy'    => $c->createdBy,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false) && ($this->getAffectedRows() === 1);
    }

    public function delete_course(string $courseID): bool
    {
        $sql = "DELETE FROM COURSE WHERE CourseID = :courseID";
        $bindParams = [':courseID' => $courseID];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false) && ($this->getAffectedRows() === 1);
    }

    public function update_course(CourseDTO $c): bool
    {
        $sql = "UPDATE COURSE SET 
                Title = :title, 
                Description = :description, 
                Price = :price, 
                CreatedBy = :createdBy 
                WHERE CourseID = :courseID_where";

        $bindParams = [
            ':title'        => $c->title,
            ':description'  => ['value' => $c->description, 'type' => OCI_B_CLOB],
            ':price'        => is_numeric($c->price) ? (float)$c->price : 0,
            ':createdBy'    => $c->createdBy,
            ':courseID_where' => $c->courseID,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function get_course(string $courseID): ?CourseDTO
    {
        $sql = "SELECT CourseID, Title, Description, Price, CreatedBy, created_at 
                FROM COURSE 
                WHERE CourseID = :courseID_param";
        $bindParams = [':courseID_param' => $courseID];

        $stid = $this->executePrepared($sql, $bindParams);
        $dto = null;

        if ($stid) {
            if (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $description = null;
                if (is_object($row['DESCRIPTION']) && method_exists($row['DESCRIPTION'], 'read')) {
                    $description = $row['DESCRIPTION']->read($row['DESCRIPTION']->size());
                } elseif (isset($row['DESCRIPTION'])) {
                    $description = $row['DESCRIPTION'];
                }

                $dto = new CourseDTO(
                    $row['COURSEID'],
                    $row['TITLE'],
                    $description,
                    isset($row['PRICE']) ? (float)$row['PRICE'] : 0.0,
                    $row['CREATEDBY'],
                    $row['CREATED_AT'] ?? null
                );
            }
            @oci_free_statement($stid);
        }
        return $dto;
    }

    public function get_all_courses(): array
    {
        $sql = "SELECT CourseID, Title, Description, Price, CreatedBy, created_at 
                FROM COURSE 
                ORDER BY Title ASC";

        $stid = $this->executePrepared($sql);
        $list = [];

        if ($stid) {
            while (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $description = null;
                if (is_object($row['DESCRIPTION']) && method_exists($row['DESCRIPTION'], 'read')) {
                    $description = $row['DESCRIPTION']->read($row['DESCRIPTION']->size());
                } elseif (isset($row['DESCRIPTION'])) {
                    $description = $row['DESCRIPTION'];
                }

                $list[] = new CourseDTO(
                    $row['COURSEID'],
                    $row['TITLE'],
                    $description,
                    isset($row['PRICE']) ? (float)$row['PRICE'] : 0.0,
                    $row['CREATEDBY'],
                    $row['CREATED_AT'] ?? null
                );
            }
            @oci_free_statement($stid);
        }
        return $list;
    }
}
?>