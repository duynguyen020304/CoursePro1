<?php
// dto/course_instructor_dto.php

class CourseInstructorDTO
{
    public string $courseID;
    public string $instructorID;
    public ?string $created_at;

    public function __construct(string $courseID, string $instructorID, ?string $created_at=null)
    {
        $this->courseID     = $courseID;
        $this->instructorID = $instructorID;
        $this->created_at   = $created_at;
    }
}
