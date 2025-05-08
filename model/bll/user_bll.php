<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/user_dto.php';

class UserBLL extends Database
{
    public function create_user(UserDTO $user)
    {
        $hashedPassword = password_hash($user->password, PASSWORD_DEFAULT);
        // echo $user->userID . "\n";
        $sql = "INSERT INTO Users (UserID, Name, Email, Password, RoleID) VALUES ('{$user->userID}', '{$user->name}', '{$user->email}', '{$hashedPassword}', '{$user->roleID}')";
        $this->execute($sql);
        $this->close();
    }
    
    public function authenticate(string $email, string $password): ?UserDTO
    {
        $sql = "SELECT * FROM `Users` WHERE Email = '{$email}'";
        $result = $this->execute($sql);
        $dto = null;

        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['Password'])) {
                $dto = new UserDTO($row['UserID'], $row['Name'], $row['Email'], $row['Password'], $row['RoleID']);
            }
        }
        $this->close();
        return $dto;
    }

    public function delete_user(string $userID)
    {
        $sql = "DELETE FROM `Users` WHERE UserID = '{$userID}'";
        $this->execute($sql);
        $this->close();
    }

    public function update_user(UserDTO $user)
    {
        $sql = "UPDATE `Users` SET Name = '{$user->name}', Email = '{$user->email}', Password = '{$user->password}', RoleID = '{$user->roleID}' WHERE UserID = '{$user->userID}'";
        $this->execute($sql);
        $this->close();
    }

    public function get_user_by_id(string $userID): ?UserDTO
    {
        $sql = "SELECT * FROM `Users` WHERE UserID = '{$userID}'";
        $result = $this->execute($sql);
        $dto = null;
        if ($row = $result->fetch_assoc()) {
            $dto = new UserDTO($row['UserID'], $row['Name'], $row['Email'], $row['Password'], $row['RoleID']);
        }
        $this->close();
        return $dto;
    }

    public function get_user_by_email(string $email): ?UserDTO
    {
        $sql = "SELECT * FROM `Users` WHERE Email = '{$email}'";
        $result = $this->execute($sql);
        $dto = null;
        if ($row = $result->fetch_assoc()) {
            $dto = new UserDTO($row['UserID'], $row['Name'], $row['Email'], $row['Password'], $row['RoleID']);
        }
        $this->close();
        return $dto;
    }

    public function get_all_users(): array
    {
        $sql = "SELECT * FROM `Users`";
        $result = $this->execute($sql);
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = new UserDTO($row['UserID'], $row['Name'], $row['Email'], $row['Password'], $row['RoleID']);
        }
        $this->close();
        return $users;
    }
}
