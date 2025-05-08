<?php

use DateTime;
require_once("../database.php");

class StudentDTO {
    public string $studentID;
    public string $userID;
    public DateTime $enrollmentDate;
    public string $completedCourses;

    public function __construct(string $studentID, string $userID, DateTime $enrollmentDate, string $completedCourses = '') {
        $this->studentID        = $studentID;
        $this->userID           = $userID;
        $this->enrollmentDate   = $enrollmentDate;
        $this->completedCourses = $completedCourses;
    }
}