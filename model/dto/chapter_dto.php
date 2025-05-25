<?php
class ChapterDTO
{
    public string $chapterID;
    public string $courseID;
    public string $title;
    public ?string $description;
    public int $sortOrder;
    public ?string $created_at;

    public function __construct(string $chapterID, string $courseID, string $title, ?string $description, int $sortOrder, ?string $created_at=null)
    {
        $this->chapterID   = $chapterID;
        $this->courseID    = $courseID;
        $this->title       = $title;
        $this->description = $description;
        $this->sortOrder   = $sortOrder;
        $this->created_at  = $created_at;
    }
}