<?php

use DateTime;

require_once("../database.php");

class CategoryDTO
{
    public string $categoryID;
    public string $name;

    public function __construct(string $categoryID, string $name)
    {
        $this->categoryID = $categoryID;
        $this->name       = $name;
    }
}
