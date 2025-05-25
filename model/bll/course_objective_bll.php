<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/course_objective_dto.php';

class CourseObjectiveBLL extends Database
{
    public function create(CourseObjectiveDTO $obj): bool
    {
        $sql = "INSERT INTO COURSEOBJECTIVE (ObjectiveID, CourseID, Objective)
                VALUES (:objectiveID, :courseID, :objective)";

        $bindParams = [
            ':objectiveID' => $obj->objectiveID,
            ':courseID'    => $obj->courseID,
            ':objective'   => $obj->objective,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false) && ($this->getAffectedRows() === 1);
    }

    public function update(CourseObjectiveDTO $obj): bool
    {
        $sql = "UPDATE COURSEOBJECTIVE SET Objective = :objective
                WHERE ObjectiveID = :objectiveID_where AND CourseID = :courseID_where";

        $bindParams = [
            ':objective'         => $obj->objective,
            ':objectiveID_where' => $obj->objectiveID,
            ':courseID_where'    => $obj->courseID,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function delete(string $courseID, string $objectiveID): bool
    {
        $sql = "DELETE FROM COURSEOBJECTIVE
                WHERE ObjectiveID = :objectiveID AND CourseID = :courseID";

        $bindParams = [
            ':objectiveID' => $objectiveID,
            ':courseID'    => $courseID,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false) && ($this->getAffectedRows() === 1);
    }

    public function get_objective_by_ids(string $courseID, string $objectiveID): ?CourseObjectiveDTO
    {
        $sql = "SELECT ObjectiveID, CourseID, Objective,
                       TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
                FROM COURSEOBJECTIVE
                WHERE ObjectiveID = :objectiveID_param AND CourseID = :courseID_param";

        $bindParams = [
            ':objectiveID_param' => $objectiveID,
            ':courseID_param'    => $courseID,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        $dto = null;

        if ($stid) {
            if (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $dto = new CourseObjectiveDTO(
                    $row['OBJECTIVEID'],
                    $row['COURSEID'],
                    $row['OBJECTIVE'],
                    $row['CREATED_AT_FORMATTED'] ?? null // Use the formatted alias
                );
            }
            @oci_free_statement($stid);
        }
        return $dto;
    }

    public function get_objectives_by_course_id(string $courseID): array
    {
        $sql = "SELECT ObjectiveID, CourseID, Objective,
                       TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
                FROM COURSEOBJECTIVE
                WHERE CourseID = :courseID_param
                ORDER BY ObjectiveID ASC";

        $bindParams = [':courseID_param' => $courseID];

        $stid = $this->executePrepared($sql, $bindParams);
        $objectives = [];

        if ($stid) {
            while (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $objectives[] = new CourseObjectiveDTO(
                    $row['OBJECTIVEID'],
                    $row['COURSEID'],
                    $row['OBJECTIVE'],
                    $row['CREATED_AT_FORMATTED'] ?? null // Use the formatted alias
                );
            }
            @oci_free_statement($stid);
        }
        return $objectives;
    }
}