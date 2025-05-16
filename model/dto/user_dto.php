<?php

class UserDTO
{
    public string $userID;
    public string $firstName;
    public string $lastName;
    public string $email;
    public string $password;
    public string $roleID;
    public ?string $profileImage;
    public ?string $created_at;

    public function __construct(string $userID, string $firstName, string $lastName, string $email, string $password, string $roleID,  ?string $profileImage = null, ?string $created_at = null)
    {
        $this->userID   = $userID;
        $this->firstName = $firstName;
        $this->lastName  = $lastName;
        $this->email    = $email;
        $this->password = $password;
        $this->roleID   = $roleID;
        $this->profileImage = $profileImage;
        $this->created_at = $created_at;
    }
}
