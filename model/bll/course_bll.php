<?php
require_once("../database.php");
require_once("../dto.php");
class CourseBLL extends Database
{
    public function create_course(CourseDTO $c)
    {
        $desc = $c->description ? "'{$c->description}'" : 'NULL';
        $sql = "INSERT INTO `Course` (CourseID, Title, Description, Price, CreatedBy) VALUES ('{$c->courseID}', '{$c->title}', {$desc}, {$c->price}, '{$c->createdBy}')";
        $this->execute($sql);
        $this->close();
    }

    public function delete_course(string $courseID)
    {
        $sql = "DELETE FROM `Course` WHERE CourseID = '{$courseID}'";
        $this->execute($sql);
        $this->close();
    }

    public function update_course(CourseDTO $c)
    {
        $desc = $c->description ? "Description = '{$c->description}'," : '';
        $sql = "UPDATE `Course` SET Title = '{$c->title}', {$desc} Price = {$c->price}, CreatedBy = '{$c->createdBy}' WHERE CourseID = '{$c->courseID}'";
        $this->execute($sql);
        $this->close();
    }

    public function get_course(string $courseID): ?CourseDTO
    {
        $sql = "SELECT * FROM `Course` WHERE CourseID = '{$courseID}'";
        $result = $this->execute($sql);
        $dto = null;
        if ($row = $result->fetch_assoc()) {
            $dto = new CourseDTO($row['CourseID'], $row['Title'], $row['Description'], (float)$row['Price'], $row['CreatedBy']);
        }
        $this->close();
        return $dto;
    }

    public function get_all_courses(): array
    {
        $sql = "SELECT * FROM `Course`";
        $result = $this->execute($sql);
        $list = [];
        while ($row = $result->fetch_assoc()) {
            $list[] = new CourseDTO($row['CourseID'], $row['Title'], $row['Description'], (float)$row['Price'], $row['CreatedBy']);
        }
        $this->close();
        return $list;
    }
}
