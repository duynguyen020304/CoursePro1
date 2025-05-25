<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/student_dto.php';

class StudentBLL extends Database
{
    public function create_student(StudentDTO $stu): bool
    {
        $sql = "INSERT INTO STUDENT (StudentID, UserID)
                VALUES (:studentID, :userID)";
        $bindParams = [
            ':studentID' => $stu->studentID,
            ':userID'    => $stu->userID,
        ];
        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false) && ($this->getAffectedRows() === 1);
    }

    public function delete_student(string $studentID): bool
    {
        $sql = "DELETE FROM STUDENT WHERE StudentID = :studentID";
        $bindParams = [':studentID' => $studentID];
        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false) && ($this->getAffectedRows() === 1);
    }

    public function update_student(StudentDTO $stu): bool
    {
        $sql = "UPDATE STUDENT SET UserID = :userID WHERE StudentID = :studentID_where";
        $bindParams = [
            ':userID'          => $stu->userID,
            ':studentID_where' => $stu->studentID,
        ];
        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function get_student(string $studentID): ?StudentDTO
    {
        $sql = "SELECT StudentID, UserID, TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
                FROM STUDENT
                WHERE StudentID = :studentID_param";
        $bindParams = [':studentID_param' => $studentID];
        $stid = $this->executePrepared($sql, $bindParams);
        $dto = null;

        if ($stid) {
            if (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $dto = new StudentDTO(
                    $row['STUDENTID'],
                    $row['USERID'],
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid);
        }
        return $dto;
    }

    public function get_student_by_user_id(string $userID): ?StudentDTO
    {
        $sql = "SELECT StudentID, UserID, TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
                FROM STUDENT
                WHERE UserID = :userID_param";
        $bindParams = [':userID_param' => $userID];
        $stid = $this->executePrepared($sql, $bindParams);
        $dto = null;

        if ($stid) {
            if (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $dto = new StudentDTO(
                    $row['STUDENTID'],
                    $row['USERID'],
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid);
        }
        return $dto;
    }

    public function get_all_students(): array
    {
        $sql = "SELECT StudentID, UserID, TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted FROM STUDENT";
        $stid = $this->executePrepared($sql);
        $list = [];

        if ($stid) {
            while (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $list[] = new StudentDTO(
                    $row['STUDENTID'],
                    $row['USERID'],
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid);
        }
        return $list;
    }
}