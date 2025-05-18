<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/course_objective_dto.php';

class CourseObjectiveBLL extends Database
{
    public function create(CourseObjectiveDTO $obj): bool
    {
        $sql = "INSERT INTO `CourseObjective` (ObjectiveID ,CourseID, Objective) VALUES ('{$obj->objectiveID}','{$obj->courseID}', '{$obj->objective}')";
        $result = $this->execute($sql);
        return $result === true && $this->getAffectedRows() === 1;
    }

    public function update(CourseObjectiveDTO $obj): bool
    {

        $sql = "UPDATE `CourseObjective` SET Objective = '{$obj->objective}' WHERE ObjectiveID = '{$obj->objectiveID}'";
        $result = $this->execute($sql);
        return $result === true && $this->getAffectedRows() === 1;
    }

    public function delete(string $objectiveID): bool
    {
        $sql = "DELETE FROM `CourseObjective` WHERE ObjectiveID = '{$objectiveID}'";
        $result = $this->execute($sql);
        return $result === true && $this->getAffectedRows() === 1;
    }

    public function get_by_id(string $objectivetID): array
    {
        $sql = "SELECT * FROM `CourseObjective` WHERE ObjectiveID = '{$objectivetID}'";
        $result = $this->execute($sql);
        $objectives=[];
        if ($result instanceof mysqli_result) {
            while ($row = $result->fetch_assoc()) {
                $objectives[] = new CourseObjectiveDTO(
                    $row['ObjectiveID'],
                    $row['CourseID'],
                    $row['Objective']
                );
            }
        }
        return $objectives;
    }

    public function get_by_course_id(string $courseID): array
    {
        $sql = "SELECT * FROM `CourseObjective` WHERE CourseID = '{$courseID}'";
        $result = $this->execute($sql);
        $objectives=[];
        if ($result instanceof mysqli_result) {
            while ($row = $result->fetch_assoc()) {
                $objectives[] = new CourseObjectiveDTO(
                    $row['ObjectiveID'],
                    $row['CourseID'],
                    $row['Objective']
                );
            }
        }
        return $objectives;
    }


    public function get_all_by_course(string $courseID): array
    {
        $sql = "SELECT * FROM `CourseObjective` WHERE CourseID = '{$courseID}'";
        $result = $this->execute($sql);
        $objs = [];
        while ($row = $result->fetch_assoc()) {
            $objs[] = new CourseObjectiveDTO(
                $row['ObjectiveID'],
                $row['CourseID'],
                $row['Objective']
            );
        }
        return $objs;
    }
}