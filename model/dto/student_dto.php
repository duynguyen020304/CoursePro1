<?php


class StudentDTO {
    public string $studentID;
    public string $userID;

    public function __construct(string $studentID, string $userID) {
        $this->studentID        = $studentID;
        $this->userID           = $userID;
    }
}