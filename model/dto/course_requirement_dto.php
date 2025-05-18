<?php

class CourseRequirementDTO
{
    public ?string $requirementID;
    public string $courseID;
    public string $requirement;

    public function __construct(?string $requirementID, string $courseID, string $requirement)
    {
        $this->requirementID = $requirementID;
        $this->courseID    = $courseID;
        $this->requirement   = $requirement;
    }
}