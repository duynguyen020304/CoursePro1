<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/instructor_dto.php';
class InstructorBLL extends Database
{
    public function create_instructor(InstructorDTO $inst)
    {
        $bio = $inst->biography ? "'{$inst->biography}'" : 'NULL';
        $sql = "INSERT INTO `Instructor` (InstructorID, UserID, Biography) VALUES ('{$inst->instructorID}', '{$inst->userID}', '{$bio}')";
        $result = $this->execute($sql);
        // $this->close();
        return $result === true && $this->getAffectedRows() === 1;
    }

    public function delete_instructor(string $instID)
    {
        $sql = "DELETE FROM `Instructor` WHERE InstructorID = '{$instID}'";
        $result = $this->execute($sql);
        // $this->close();
        return $result === true && $this->getAffectedRows() === 1;
    }

    public function update_instructor(InstructorDTO $inst)
    {
        $bio = $inst->biography ? "Biography = '{$inst->biography}'," : '';
        $img = $inst->profileImage ? "ProfileImage = '{$inst->profileImage}'," : '';
        $sql = "UPDATE `Instructor` SET {$bio} {$img} UserID = '{$inst->userID}' WHERE InstructorID = '{$inst->instructorID}'";
        $result = $this->execute($sql);
        // $this->close();
        return $result === true && $this->getAffectedRows() === 1;
    }

    public function get_instructor(string $instID): ?InstructorDTO
    {
        $sql = "SELECT * FROM `Instructor` WHERE InstructorID = '{$instID}'";
        $result = $this->execute($sql);
        $dto = null;
        if ($row = $result->fetch_assoc()) {
            $dto = new InstructorDTO($row['InstructorID'], $row['UserID'], $row['Biography']);
        }
        // $this->close();
        return $dto;
    }

    public function get_all_instructors(): array
    {
        $sql = "SELECT * FROM `Instructor`";
        $result = $this->execute($sql);
        $list = [];
        while ($row = $result->fetch_assoc()) {
            $list[] = new InstructorDTO($row['InstructorID'], $row['UserID'], $row['Biography']);
        }
        // $this->close();
        return $list;
    }
  

}
