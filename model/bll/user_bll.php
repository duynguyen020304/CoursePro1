<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/user_dto.php';

class UserBLL extends Database
{
    public function create_user(UserDTO $user): bool
    {
        $hashedPassword = password_hash($user->password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO `Users` (UserID, FirstName, LastName, Email, Password, RoleID, ProfileImage)
            VALUES ('{$user->userID}', '{$user->firstName}', '{$user->lastName}', '{$user->email}', '{$hashedPassword}', '{$user->roleID}', '{$user->profileImage}')";
        $result = $this->execute($sql);
        return $result === true && $this->getAffectedRows() === 1;
    }

    public function authenticate(string $email, string $password): ?UserDTO
    {
        $sql = "SELECT * FROM `Users` WHERE Email = '{$email}'";
        $result = $this->execute($sql);
        $dto = null;

        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['Password'])) {
                $dto = new UserDTO($row['UserID'], $row['FirstName'], $row['LastName'], $row['Email'], "", $row['RoleID'], $row['ProfileImage']);
            }
        }
        // $this->close();
        return $dto;
    }

    public function delete_user(string $userID)
    {
        $sql = "DELETE FROM `Users` WHERE UserID = '{$userID}'";
        $result = $this->execute($sql);
        return $result === true && $this->getAffectedRows() === 1;
        // $this->close();
    }

    public function update_user(UserDTO $user)
    {
        $sql = "UPDATE `Users` SET FirstName = '{$user->firstName}', LastName='{$user->lastName}', Email = '{$user->email}', Password = '{$user->password}', RoleID = '{$user->roleID}' WHERE UserID = '{$user->userID}'";
        $result = $this->execute($sql);
        return $result === true && $this->getAffectedRows() === 1;
        // $this->close();
    }

    public function get_user_by_id(string $userID): ?UserDTO
    {
        $sql = "SELECT * FROM `Users` WHERE UserID = '{$userID}'";
        $result = $this->execute($sql);
        if ($row = $result->fetch_assoc()) {
            $dto = new UserDTO($row['UserID'], $row['FirstName'], $row['LastName'], $row['Email'], "", $row['RoleID'], $row['ProfileImage'], $row['created_at']);
        }
        // $this->close();
        return $dto;
    }

    public function get_user_by_email(string $email): ?UserDTO
    {
        $sql = "SELECT UserID, Name, Email, RoleID, FROM `Users` WHERE Email = '{$email}'";
        $result = $this->execute($sql);
        $dto = null;
        if ($row = $result->fetch_assoc()) {
            $dto = new UserDTO($row['UserID'], $row['FirstName'], $row['LastName'], $row['Email'], "", $row['RoleID'], $row['ProfileImage'], $row['created_at']);
        }
        // $this->close();
        return $dto;
    }

    public function get_all_users(): array
    {
        $sql = "SELECT * FROM `Users`";
        $result = $this->execute($sql);
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = new UserDTO($row['UserID'], $row['FirstName'], $row['LastName'], $row['Email'], "", $row['RoleID'], $row['ProfileImage'], $row['created_at']);
        }
        // $this->close();
        return $users;
    }
}
