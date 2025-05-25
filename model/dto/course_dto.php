<?php
class CourseDTO
{
    public string $courseID;
    public string $title;
    public ?string $description;
    public float $price;
    public string $createdBy;
    public ?string $created_at;

    public function __construct(string $courseID, string $title, ?string $description, float $price, string $createdBy, ?string $created_at=null)
    {
        $this->courseID    = $courseID;
        $this->title       = $title;
        $this->description = $description;
        $this->price       = $price;
        $this->createdBy   = $createdBy;
        $this->created_at   = $created_at;
    }
}
