<?php
class CourseDTO
{
    public string $courseID;
    public string $title;
    public ?string $description;
    public float $price;
    public string $createdBy;

    public ?string $difficulty;
    public ?string $language;
    public ?string $created_at;

    public function __construct(string $courseID, string $title, ?string $description, float $price, string $createdBy, ?string $difficulty=null, ?string $language=null, ?string $created_at=null)
    {
        $this->courseID    = $courseID;
        $this->title       = $title;
        $this->description = $description;
        $this->price       = $price;
        $this->createdBy   = $createdBy;
        $this->difficulty  = $difficulty;
        $this->language    = $language;
        $this->created_at  = $created_at;
    }
}
