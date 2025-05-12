<?php
class CourseDTO
{
    public string $courseID;
    public string $title;
    public ?string $description;
    public float $price;
    public string $instructorID;
    public string $createdBy;

    public function __construct(string $courseID, string $title, ?string $description, float $price, string $instructorID, string $createdBy)
    {
        $this->courseID    = $courseID;
        $this->title       = $title;
        $this->description = $description;
        $this->price       = $price;
        $this->instructorID = $instructorID;
        $this->createdBy   = $createdBy;
    }
}
