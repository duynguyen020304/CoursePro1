<?php
require_once("database.php");
class Course extends Database
{
    public function create_1_course($title, $course_description, $price, $created_by)
    {
        $sql = "INSERT IGNORE INTO course (title, course_description, price, created_by)
                    VALUES ('{$title}', '{$course_description}', '{$price}', '{$created_by}')";
        $this->execute($sql);
        $this->close();
    }
}
