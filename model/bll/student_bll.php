<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/student_dto.php';
class StudentBLL extends Database
{
    public function create_student(StudentDTO $stu)
    {
        $sql = "INSERT INTO `Student` (StudentID, UserID) VALUES ('{$stu->studentID}', '{$stu->userID}')";
        $result = $this->execute($sql);
        // $this->close();
        return $result === true && $this->getAffectedRows() === 1;
    }

    public function delete_student(string $stuID)
    {
        $sql = "DELETE FROM `Student` WHERE StudentID = '{$stuID}'";
        $result = $this->execute($sql);
        // $this->close();
        return $result === true && $this->getAffectedRows() === 1;
    }

    public function update_student(StudentDTO $stu)
    {
        $sql = "UPDATE `Student` SET UserID = '{$stu->userID}' WHERE StudentID = '{$stu->studentID}'";
        $result = $this->execute($sql);
        // $this->close();
        return $result === true;
    }

    public function get_student(string $stuID): ?StudentDTO
    {
        $sql = "SELECT * FROM `Student` WHERE StudentID = '{$stuID}'";
        $result = $this->execute($sql);
        $dto = null;
        if ($row = $result->fetch_assoc()) {
            $dto = new StudentDTO($row['StudentID'], $row['UserID']);
        }
        // $this->close();
        return $dto;
    }

    public function get_all_students(): array
    {
        $sql = "SELECT * FROM `Student`";
        $result = $this->execute($sql);
        $list = [];
        while ($row = $result->fetch_assoc()) {
            $list[] = new StudentDTO($row['StudentID'], $row['UserID']);
        }
        // $this->close();
        return $list;
    }
}
