<?php
// dto/course_instructor_dto.php

class CourseInstructorDTO
{
    public string $courseID;
    public string $instructorID;

    public function __construct($courseID, $instructorID)
    {
        $this->courseID     = $courseID;
        $this->instructorID = $instructorID;
    }
}
