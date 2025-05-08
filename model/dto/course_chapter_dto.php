<?php

use DateTime;

require_once("../database.php");

class CourseCategoryDTO
{
    public string $courseID;
    public string $categoryID;

    public function __construct(string $courseID, string $categoryID)
    {
        $this->courseID   = $courseID;
        $this->categoryID = $categoryID;
    }
}
