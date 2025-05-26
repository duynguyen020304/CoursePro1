<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/course_requirement_dto.php';

class CourseRequirementBLL extends Database
{
    public function create(CourseRequirementDTO $req): bool
    {
        $sql = "INSERT INTO COURSEREQUIREMENT (RequirementID, CourseID, Requirement)
                VALUES (:requirementID, :courseID, :requirement)";

        $bindParams = [
            ':requirementID' => $req->requirementID,
            ':courseID'      => $req->courseID,
            ':requirement'   => $req->requirement,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false) && ($this->getAffectedRows() === 1);
    }

    public function update(CourseRequirementDTO $req): bool
    {
        $sql = "UPDATE COURSEREQUIREMENT SET Requirement = :requirement
                WHERE RequirementID = :requirementID_where AND CourseID = :courseID_where";

        $bindParams = [
            ':requirement'        => $req->requirement,
            ':requirementID_where' => $req->requirementID,
            ':courseID_where'     => $req->courseID,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function delete(string $requirementID): bool
    {
        $sql = "DELETE FROM COURSEREQUIREMENT
                WHERE RequirementID = :requirementID";

        $bindParams = [
            ':requirementID' => $requirementID,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false) && ($this->getAffectedRows() === 1);
    }

    public function get_requirement_by_requirement_id(string $requirementID): ?CourseRequirementDTO
    {
        $sql = "SELECT RequirementID, CourseID, Requirement,
                       TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
                FROM COURSEREQUIREMENT
                WHERE RequirementID = :requirementID_param";

        $bindParams = [
            ':requirementID_param' => $requirementID,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        $dto = null;

        if ($stid) {
            if (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $dto = new CourseRequirementDTO(
                    $row['REQUIREMENTID'],
                    $row['COURSEID'],
                    $row['REQUIREMENT'],
                    $row['CREATED_AT_FORMATTED'] ?? null // Use the formatted alias
                );
            }
            @oci_free_statement($stid);
        }
        return $dto;
    }

    public function get_requirements_by_course_id(string $courseID): array
    {
        $sql = "SELECT RequirementID, CourseID, Requirement,
                       TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
                FROM COURSEREQUIREMENT
                WHERE CourseID = :courseID_param
                ORDER BY RequirementID ASC";

        $bindParams = [':courseID_param' => $courseID];

        $stid = $this->executePrepared($sql, $bindParams);
        $requirements = [];

        if ($stid) {
            while (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $requirements[] = new CourseRequirementDTO(
                    $row['REQUIREMENTID'],
                    $row['COURSEID'],
                    $row['REQUIREMENT'],
                    $row['CREATED_AT_FORMATTED'] ?? null // Use the formatted alias
                );
            }
            @oci_free_statement($stid);
        }
        return $requirements;
    }
}