<?php

use DateTime;

require_once("../database.php");
class CourseImageDTO
{
    public string $imageID;
    public string $courseID;
    public string $imagePath;
    public ?string $caption;
    public int $sortOrder;

    public function __construct(string $imageID, string $courseID, string $imagePath, ?string $caption, int $sortOrder)
    {
        $this->imageID    = $imageID;
        $this->courseID   = $courseID;
        $this->imagePath  = $imagePath;
        $this->caption    = $caption;
        $this->sortOrder  = $sortOrder;
    }
}
