<?php

use DateTime;

require_once __DIR__ . '/../database.php';

class UserDTO
{
    public string $userID;
    public string $name;
    public string $email;
    public string $password;
    public string $roleID;

    public function __construct(string $userID, string $name, string $email, string $password, string $roleID)
    {
        $this->userID   = $userID;
        $this->name     = $name;
        $this->email    = $email;
        $this->password = $password;
        $this->roleID   = $roleID;
    }
}
