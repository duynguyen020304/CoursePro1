<?php

use DateTime;

require_once("../database.php");
class InstructorDTO
{
    public string $instructorID;
    public string $userID;
    public ?string $biography;
    public ?string $profileImage;

    public function __construct(string $instructorID, string $userID, ?string $biography = null, ?string $profileImage = null)
    {
        $this->instructorID = $instructorID;
        $this->userID       = $userID;
        $this->biography    = $biography;
        $this->profileImage = $profileImage;
    }
}
