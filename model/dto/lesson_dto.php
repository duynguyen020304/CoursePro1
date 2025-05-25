<?php

class LessonDTO
{
    public string $lessonID;
    public string $courseID;
    public string $chapterID;
    public string $title;
    public ?string $content;
    public int $sortOrder;
    public ?string $created_at;

    public function __construct(string $lessonID, string $courseID, string $chapterID, string $title, ?string $content, int $sortOrder=0, ?string $created_at=null)
    {
        $this->lessonID  = $lessonID;
        $this->courseID  = $courseID;
        $this->chapterID = $chapterID;
        $this->title     = $title;
        $this->content   = $content;
        $this->sortOrder = $sortOrder;
        $this->created_at = $created_at;
    }
}
