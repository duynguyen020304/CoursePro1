<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/course_requirement_dto.php';

class CourseRequirementBLL extends Database
{
    public function create(CourseRequirementDTO $req): bool
    {
        $sql = "INSERT INTO `CourseRequirement`(RequirementID, CourseID, Requirement) VALUES ('{$req->requirementID}','{$req->courseID}', '{$req->requirement}')";
        $result = $this->execute($sql);
        return $result === true && $this->getAffectedRows() === 1;
    }

    public function update(CourseRequirementDTO $req): bool
    {
        $sql = "UPDATE `CourseRequirement` SET Requirement = '{$req->requirement}' WHERE RequirementID = '{$req->requirementID}'";
        $result = $this->execute($sql);
        return $result === true && $this->getAffectedRows() === 1;
    }

    public function delete(string $requirementID): bool
    {
        $sql = "DELETE FROM `CourseRequirement` WHERE RequirementID = '{$requirementID}'";
        $result = $this->execute($sql);
        return $result === true && $this->getAffectedRows() === 1;
    }

    public function get_by_id(string $requirementID): array
    {
        $sql = "SELECT * FROM `CourseRequirement` WHERE RequirementID = '{$requirementID}'";
        $result = $this->execute($sql);
        $requirements=[];
        if ($result instanceof mysqli_result) {
            while ($row = $result->fetch_assoc()) {
                $requirements[] = new CourseRequirementDTO(
                    $row['RequirementID'],
                    $row['CourseID'],
                    $row['Requirement']
                );
            }
        }
        return $requirements;
    }

    public function get_by_course_id(string $CourseID): array
    {
        $sql = "SELECT * FROM `CourseRequirement` WHERE CourseID = '{$CourseID}'";
        $result = $this->execute($sql);
        $requirements=[];
        if ($result instanceof mysqli_result) {
            while ($row = $result->fetch_assoc()) {
                $requirements[] = new CourseRequirementDTO(
                    $row['RequirementID'],
                    $row['CourseID'],
                    $row['Requirement']
                );
            }
        }
        return $requirements;
    }

    public function get_all_by_course(string $courseID): array
    {
        $sql = "SELECT * FROM `CourseRequirement` WHERE CourseID = '{$courseID}'";
        $result = $this->execute($sql);
        $objs = [];
        while ($row = $result->fetch_assoc()) {
            $objs[] = new CourseRequirementDTO(
                $row['RequirementID'],
                $row['CourseID'],
                $row['Requirement']
            );
        }
        return $objs;
    }
}