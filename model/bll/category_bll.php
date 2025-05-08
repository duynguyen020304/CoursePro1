<?php
require_once("../database.php");
require_once("../dto.php");
class CategoryBLL extends Database
{
    public function create_category(CategoryDTO $cat)
    {
        $sql = "INSERT INTO `Category` (CategoryID, Name) VALUES ('{$cat->categoryID}', '{$cat->name}')";
        $this->execute($sql);
        $this->close();
    }

    public function delete_category(string $catID)
    {
        $sql = "DELETE FROM `Category` WHERE CategoryID = '{$catID}'";
        $this->execute($sql);
        $this->close();
    }

    public function update_category(CategoryDTO $cat)
    {
        $sql = "UPDATE `Category` SET Name = '{$cat->name}' WHERE CategoryID = '{$cat->categoryID}'";
        $this->execute($sql);
        $this->close();
    }

    public function get_category(string $catID): ?CategoryDTO
    {
        $sql = "SELECT * FROM `Category` WHERE CategoryID = '{$catID}'";
        $result = $this->execute($sql);
        $dto = null;
        if ($row = $result->fetch_assoc()) {
            $dto = new CategoryDTO($row['CategoryID'], $row['Name']);
        }
        $this->close();
        return $dto;
    }

    public function get_all_categories(): array
    {
        $sql = "SELECT * FROM `Category`";
        $result = $this->execute($sql);
        $list = [];
        while ($row = $result->fetch_assoc()) {
            $list[] = new CategoryDTO($row['CategoryID'], $row['Name']);
        }
        $this->close();
        return $list;
    }
}
