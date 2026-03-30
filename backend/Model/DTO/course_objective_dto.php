<?php

class CourseObjectiveDTO
{
    public ?string $objectiveID;
    public string $courseID;
    public string $objective;
    public ?string $created_at;
    public function __construct(?string $objectiveID, string $courseID, string $objective, ?string $created_at=null)
    {
        $this->objectiveID = $objectiveID;
        $this->courseID    = $courseID;
        $this->objective   = $objective;
        $this->created_at  = $created_at;
    }
}