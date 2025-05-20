<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/user_dto.php';

class UserBLL extends Database
{
    public function create_user(UserDTO $user): bool
    {
        $hashedPassword = password_hash($user->password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO USERS (UserID, FirstName, LastName, Email, Password, RoleID, ProfileImage)
                VALUES (:userID, :firstName, :lastName, :email, :password, :roleID, :profileImage)";
        $bindParams = [
            ':userID'       => $user->userID,
            ':firstName'    => $user->firstName,
            ':lastName'     => $user->lastName,
            ':email'        => $user->email,
            ':password'     => $hashedPassword,
            ':roleID'       => $user->roleID,
            ':profileImage' => $user->profileImage,
        ];
        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false) && ($this->getAffectedRows() === 1);
    }

    public function authenticate(string $email, string $password): ?UserDTO
    {
        $sql = "SELECT UserID, FirstName, LastName, Email, Password, RoleID, ProfileImage, created_at 
                FROM USERS 
                WHERE Email = :email";
        $bindParams = [':email' => $email];
        $stid = $this->executePrepared($sql, $bindParams);
        $dto = null;

        if ($stid) {
            if (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                if (password_verify($password, $row['PASSWORD'])) {
                    $dto = new UserDTO(
                        $row['USERID'],
                        $row['FIRSTNAME'],
                        $row['LASTNAME'],
                        $row['EMAIL'],
                        "",
                        $row['ROLEID'],
                        $row['PROFILEIMAGE'],
                        $row['CREATED_AT'] ?? null
                    );
                }
            }
            @oci_free_statement($stid);
        }
        return $dto;
    }

    public function delete_user(string $userID): bool
    {
        $sql = "DELETE FROM USERS WHERE UserID = :userID";
        $bindParams = [':userID' => $userID];
        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false) && ($this->getAffectedRows() === 1);
    }

    public function update_user(UserDTO $user): bool
    {
        $setClauses = [];
        $bindParams = [];

        if ($user->firstName !== null) {
            $setClauses[] = "FirstName = :firstName";
            $bindParams[':firstName'] = $user->firstName;
        }
        if ($user->lastName !== null) {
            $setClauses[] = "LastName = :lastName";
            $bindParams[':lastName'] = $user->lastName;
        }
        if (!empty($user->password)) {
            $setClauses[] = "Password = :password";
            $bindParams[':password'] = password_hash($user->password, PASSWORD_DEFAULT);
        }
        if ($user->roleID !== null) {
            $setClauses[] = "RoleID = :roleID";
            $bindParams[':roleID'] = $user->roleID;
        }
        $setClauses[] = "ProfileImage = :profileImage";
        $bindParams[':profileImage'] = $user->profileImage;

        if (empty($setClauses)) {
            return true;
        }

        $sql = "UPDATE USERS SET " . implode(', ', $setClauses) . " WHERE UserID = :userID";
        $bindParams[':userID'] = $user->userID;

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function get_user_by_id(string $userID, string $purpose = "get"): ?UserDTO
    {
        $sql = "SELECT UserID, FirstName, LastName, Email, Password, RoleID, ProfileImage, created_at 
                FROM USERS WHERE UserID = :userID";
        $bindParams = [':userID' => $userID];
        $stid = $this->executePrepared($sql, $bindParams);
        $dto = null;

        if ($stid) {
            if (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $passwordForDTO = "";
                if ($purpose === "update" || $purpose === "internal_check") {
                    $passwordForDTO = $row['PASSWORD'];
                }
                $dto = new UserDTO(
                    $row['USERID'],
                    $row['FIRSTNAME'],
                    $row['LASTNAME'],
                    $row['EMAIL'],
                    $passwordForDTO,
                    $row['ROLEID'],
                    $row['PROFILEIMAGE'],
                    $row['CREATED_AT'] ?? null
                );
            }
            @oci_free_statement($stid);
        }
        return $dto;
    }

    public function get_user_by_email(string $email): ?UserDTO
    {
        $sql = "SELECT UserID, FirstName, LastName, Email, Password, RoleID, ProfileImage, created_at 
                FROM USERS WHERE Email = :email";
        $bindParams = [':email' => $email];
        $stid = $this->executePrepared($sql, $bindParams);
        $dto = null;

        if ($stid) {
            if (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $dto = new UserDTO(
                    $row['USERID'],
                    $row['FIRSTNAME'],
                    $row['LASTNAME'],
                    $row['EMAIL'],
                    "",
                    $row['ROLEID'],
                    $row['PROFILEIMAGE'],
                    $row['CREATED_AT'] ?? null
                );
            }
            @oci_free_statement($stid);
        }
        return $dto;
    }

    public function get_all_users(): array
    {
        $sql = "SELECT UserID, FirstName, LastName, Email, RoleID, ProfileImage, created_at FROM USERS";
        $stid = $this->executePrepared($sql);
        $users = [];

        if ($stid) {
            while (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $users[] = new UserDTO(
                    $row['USERID'],
                    $row['FIRSTNAME'],
                    $row['LASTNAME'],
                    $row['EMAIL'],
                    "",
                    $row['ROLEID'],
                    $row['PROFILEIMAGE'],
                    $row['CREATED_AT'] ?? null
                );
            }
            @oci_free_statement($stid);
        }
        return $users;
    }

    public function email_exists(string $email, ?string $excludeUserID = null): bool
    {
        $sql = "SELECT COUNT(UserID) AS EMAIL_COUNT FROM USERS WHERE Email = :email";
        $bindParams = [':email' => $email];

        if ($excludeUserID !== null) {
            $sql .= " AND UserID != :excludeUserID";
            $bindParams[':excludeUserID'] = $excludeUserID;
        }

        $stid = $this->executePrepared($sql, $bindParams);
        if ($stid) {
            $row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS);
            @oci_free_statement($stid);
            if ($row && isset($row['EMAIL_COUNT'])) {
                return (int)$row['EMAIL_COUNT'] > 0;
            }
        }
        return false;
    }
}
?>