<?php

class CourseObjectiveDTO
{
    public ?string $objectiveID;
    public string $courseID;
    public string $objective;

    public function __construct(?string $objectiveID, string $courseID, string $objective)
    {
        $this->objectiveID = $objectiveID;
        $this->courseID    = $courseID;
        $this->objective   = $objective;
    }
}