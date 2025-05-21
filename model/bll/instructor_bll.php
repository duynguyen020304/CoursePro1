<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/instructor_dto.php';

class InstructorBLL extends Database
{
    public function create_instructor(InstructorDTO $inst): bool
    {
        $sql = "INSERT INTO INSTRUCTOR (InstructorID, UserID, Biography) 
                VALUES (:instructorID, :userID, :biography)";

        $bindParams = [
            ':instructorID' => $inst->instructorID,
            ':userID'       => $inst->userID,
            ':biography'    => $inst->biography,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false) && ($this->getAffectedRows() === 1);
    }

    public function delete_instructor(string $instructorID): bool
    {
        $sql = "DELETE FROM INSTRUCTOR WHERE InstructorID = :instructorID";
        $bindParams = [':instructorID' => $instructorID];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false) && ($this->getAffectedRows() === 1);
    }

    public function update_instructor(InstructorDTO $inst): bool
    {
        $sql = "UPDATE INSTRUCTOR SET 
                UserID = :userID, 
                Biography = :biography 
                WHERE InstructorID = :instructorID_where";

        $bindParams = [
            ':userID'       => $inst->userID,
            ':biography'    => ['value' => $inst->biography, 'type' => OCI_B_CLOB],
            ':instructorID_where' => $inst->instructorID,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function get_instructor(string $instructorID): ?InstructorDTO
    {
        $sql = "SELECT InstructorID, UserID, Biography, created_at 
                FROM INSTRUCTOR 
                WHERE InstructorID = :instructorID_param";
        $bindParams = [':instructorID_param' => $instructorID];

        $stid = $this->executePrepared($sql, $bindParams);
        $dto = null;

        if ($stid) {
            if (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $biography = null;
                if (is_object($row['BIOGRAPHY']) && method_exists($row['BIOGRAPHY'], 'read')) {
                    $biography = $row['BIOGRAPHY']->read($row['BIOGRAPHY']->size());
                } elseif (isset($row['BIOGRAPHY'])) {
                    $biography = $row['BIOGRAPHY'];
                }

                $dto = new InstructorDTO(
                    $row['INSTRUCTORID'],
                    $row['USERID'],
                    $biography,
                    $row['CREATED_AT'] ?? null
                );
            }
            @oci_free_statement($stid);
        }
        return $dto;
    }

    public function get_instructor_by_user_id(string $userID): ?InstructorDTO
    {
        $sql = "SELECT InstructorID, UserID, Biography, created_at 
                FROM INSTRUCTOR 
                WHERE UserID = :userID_param";
        $bindParams = [':userID_param' => $userID];

        $stid = $this->executePrepared($sql, $bindParams);
        $dto = null;

        if ($stid) {
            if (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $biography = null;
                if (is_object($row['BIOGRAPHY']) && method_exists($row['BIOGRAPHY'], 'read')) {
                    $biography = $row['BIOGRAPHY']->read($row['BIOGRAPHY']->size());
                } elseif (isset($row['BIOGRAPHY'])) {
                    $biography = $row['BIOGRAPHY'];
                }

                $dto = new InstructorDTO(
                    $row['INSTRUCTORID'],
                    $row['USERID'],
                    $biography,
                    $row['CREATED_AT'] ?? null
                );
            }
            @oci_free_statement($stid);
        }
        return $dto;
    }

    public function get_all_instructors(): array
    {
        $sql = "SELECT InstructorID, UserID, Biography, created_at 
                FROM INSTRUCTOR ORDER BY InstructorID ASC";

        $stid = $this->executePrepared($sql);
        $list = [];

        if ($stid) {
            while (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $biography = null;
                if (is_object($row['BIOGRAPHY']) && method_exists($row['BIOGRAPHY'], 'read')) {
                    $biography = $row['BIOGRAPHY']->read($row['BIOGRAPHY']->size());
                } elseif (isset($row['BIOGRAPHY'])) {
                    $biography = $row['BIOGRAPHY'];
                }

                $list[] = new InstructorDTO(
                    $row['INSTRUCTORID'],
                    $row['USERID'],
                    $biography,
                    $row['CREATED_AT'] ?? null
                );
            }
            @oci_free_statement($stid);
        }
        return $list;
    }
}
?>