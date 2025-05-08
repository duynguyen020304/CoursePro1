<?php

use DateTime;

require_once("../database.php");
class ChapterDTO
{
    public string $chapterID;
    public string $courseID;
    public string $title;
    public ?string $description;
    public int $sortOrder;

    public function __construct(string $chapterID, string $courseID, string $title, ?string $description, int $sortOrder)
    {
        $this->chapterID   = $chapterID;
        $this->courseID    = $courseID;
        $this->title       = $title;
        $this->description = $description;
        $this->sortOrder   = $sortOrder;
    }
}