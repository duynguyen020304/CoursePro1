<?php
// bll/CourseInstructorBLL.php

require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/course_instructor_dto.php';

class CourseInstructorBLL extends Database
{
    public function get_by_course($courseID)
    {
        $sql = "SELECT * FROM CourseInstructor WHERE CourseID = '{$courseID}'";
        $result = $this->execute($sql);

        $list = [];
        while ($row = $result->fetch_assoc()) {
            $list[] = new CourseInstructorDTO($row['CourseID'], $row['InstructorID']);
        }
        return $list;
    }

    public function add($courseID, $instructorID)
    {
        $sql = "INSERT INTO CourseInstructor (CourseID, InstructorID)
                VALUES ('{$courseID}', '{$instructorID}')";
        $result = $this->execute($sql);
        return $result === true && $this->getAffectedRows() === 1;
    }

    public function update($oldCourseID, $oldInstructorID, $newCourseID, $newInstructorID)
    {
        $sql = "UPDATE CourseInstructor
                SET CourseID = '{$newCourseID}', InstructorID = '{$newInstructorID}'
                WHERE CourseID = '{$oldCourseID}' AND InstructorID = '{$oldInstructorID}'";
        return $this->execute($sql) !== false;
    }

    public function delete($courseID, $instructorID)
    {
        $sql = "DELETE FROM CourseInstructor
                WHERE CourseID = '{$courseID}' AND InstructorID = '{$instructorID}'";
        $result = $this->execute($sql);
        return $result === true && $this->getAffectedRows() === 1;
    }
}
