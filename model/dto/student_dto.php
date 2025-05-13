<?php


class StudentDTO {
    public string $studentID;
    public string $userID;
    public DateTime $enrollmentDate;

    public function __construct(string $studentID, string $userID, DateTime $enrollmentDate) {
        $this->studentID        = $studentID;
        $this->userID           = $userID;
        $this->enrollmentDate   = $enrollmentDate;
    }
}