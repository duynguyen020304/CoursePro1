<?php

class CourseCategoryDTO
{
    public string $courseID;
    public string $categoryID;
    public ?string $created_at;
    public function __construct(string $courseID, string $categoryID, ?string $created_at=null)
    {
        $this->courseID   = $courseID;
        $this->categoryID = $categoryID;
        $this->created_at = $created_at;
    }
}
