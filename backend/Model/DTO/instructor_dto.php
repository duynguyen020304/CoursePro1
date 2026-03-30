<?php
class InstructorDTO
{
    public string $instructorID;
    public string $userID;
    public ?string $biography;
    public ?string $created_at;
    public function __construct(string $instructorID, string $userID, ?string $biography = null, ?string $created_at = null)
    {
        $this->instructorID = $instructorID;
        $this->userID       = $userID;
        $this->biography    = $biography;
        $this->created_at   = $created_at;
    }
}
