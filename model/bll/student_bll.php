<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/student_dto.php';
class StudentBLL extends Database
{
    public function create_student(StudentDTO $stu)
    {
        $courses = $stu->completedCourses ? "'{$stu->completedCourses}'" : "''";
        $date = $stu->enrollmentDate->format('Y-m-d H:i:s');
        $sql = "INSERT INTO `Student` (StudentID, UserID, EnrollmentDate, CompletedCourses) VALUES ('{$stu->studentID}', '{$stu->userID}', '{$date}', {$courses})";
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
        $courses = $stu->completedCourses ? "CompletedCourses = '{$stu->completedCourses}'," : '';
        $date = $stu->enrollmentDate->format('Y-m-d H:i:s');
        $sql = "UPDATE `Student` SET UserID = '{$stu->userID}', EnrollmentDate = '{$date}', {$courses} WHERE StudentID = '{$stu->studentID}'";
        $result = $this->execute($sql);
        // $this->close();
        return $result === true && $this->getAffectedRows() === 1;
    }

    public function get_student(string $stuID): ?StudentDTO
    {
        $sql = "SELECT * FROM `Student` WHERE StudentID = '{$stuID}'";
        $result = $this->execute($sql);
        $dto = null;
        if ($row = $result->fetch_assoc()) {
            $dto = new StudentDTO($row['StudentID'], $row['UserID'], new DateTime($row['EnrollmentDate']), $row['CompletedCourses']);
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
            $list[] = new StudentDTO($row['StudentID'], $row['UserID'], new DateTime($row['EnrollmentDate']), $row['CompletedCourses']);
        }
        // $this->close();
        return $list;
    }
}
