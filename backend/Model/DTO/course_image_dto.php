<?php
class CourseImageDTO
{
    public string $imageID;
    public string $courseID;
    public string $imagePath;
    public ?string $caption;
    public int $sortOrder;
    public ?string $created_at;

    public function __construct(string $imageID, string $courseID, string $imagePath, ?string $caption, int $sortOrder, ?string $created_at=null)
    {
        $this->imageID    = $imageID;
        $this->courseID   = $courseID;
        $this->imagePath  = $imagePath;
        $this->caption    = $caption;
        $this->sortOrder  = $sortOrder;
        $this->created_at = $created_at;
    }
}
