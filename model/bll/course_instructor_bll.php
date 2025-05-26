<?php
// bll/CourseInstructorBLL.php

require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/course_instructor_dto.php';

class CourseInstructorBLL extends Database
{
    public function get_instructors_by_course_id(string $courseID): array
    {
        $sql = "SELECT CourseID, InstructorID, TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
                FROM COURSEINSTRUCTOR 
                WHERE CourseID = :courseID_param 
                ORDER BY InstructorID ASC";

        $bindParams = [':courseID_param' => $courseID];

        $stid = $this->executePrepared($sql, $bindParams);
        $list = [];

        if ($stid) {
            while (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $list[] = new CourseInstructorDTO(
                    $row['COURSEID'],
                    $row['INSTRUCTORID'],
                    $row['CREATED_AT_FORMATTED'] ?? null // Use the formatted alias
                );
            }
            @oci_free_statement($stid);
        }
        return $list;
    }

    public function add(string $courseID, string $instructorID): bool
    {
        $sql = "INSERT INTO COURSEINSTRUCTOR (CourseID, InstructorID)
                VALUES (:courseID, :instructorID)";

        $bindParams = [
            ':courseID'     => $courseID,
            ':instructorID' => $instructorID,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false) && ($this->getAffectedRows() === 1);
    }

    public function update(string $oldCourseID, string $oldInstructorID, string $newCourseID, string $newInstructorID): bool
    {
        $sql = "UPDATE COURSEINSTRUCTOR
                SET CourseID = :newCourseID, InstructorID = :newInstructorID
                WHERE CourseID = :oldCourseID_where AND InstructorID = :oldInstructorID_where";

        $bindParams = [
            ':newCourseID'           => $newCourseID,
            ':newInstructorID'       => $newInstructorID,
            ':oldCourseID_where'     => $oldCourseID,
            ':oldInstructorID_where' => $oldInstructorID,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function unlink_course_instructor(string $courseID, string $instructorID): bool
    {
        $sql = "DELETE FROM COURSEINSTRUCTOR
                WHERE CourseID = :courseID AND InstructorID = :instructorID";

        $bindParams = [
            ':courseID'     => $courseID,
            ':instructorID' => $instructorID,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false) && ($this->getAffectedRows() === 1);
    }

    public function get_assignment(string $courseID, string $instructorID): ?CourseInstructorDTO
    {
        $sql = "SELECT CourseID, InstructorID, TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
                FROM COURSEINSTRUCTOR 
                WHERE CourseID = :courseID_param AND InstructorID = :instructorID_param";

        $bindParams = [
            ':courseID_param' => $courseID,
            ':instructorID_param' => $instructorID,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        $dto = null;

        if ($stid) {
            if (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $dto = new CourseInstructorDTO(
                    $row['COURSEID'],
                    $row['INSTRUCTORID'],
                    $row['CREATED_AT_FORMATTED'] ?? null // Use the formatted alias
                );
            }
            @oci_free_statement($stid);
        }
        return $dto;
    }
}