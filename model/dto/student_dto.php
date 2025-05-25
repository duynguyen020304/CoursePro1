<?php


class StudentDTO {
    public string $studentID;
    public string $userID;
    public ?string $created_at;

    public function __construct(string $studentID, string $userID, ?string $created_at = null) {
        $this->studentID        = $studentID;
        $this->userID           = $userID;
        $this->created_at       = $created_at;
    }
}