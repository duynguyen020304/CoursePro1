<?php

class CourseRequirementDTO
{
    public ?string $requirementID;
    public string $courseID;
    public string $requirement;
    public ?string $created_at;
    public function __construct(?string $requirementID, string $courseID, string $requirement, ?string $created_at=null)
    {
        $this->requirementID = $requirementID;
        $this->courseID    = $courseID;
        $this->requirement   = $requirement;
        $this->created_at   = $created_at;
    }
}