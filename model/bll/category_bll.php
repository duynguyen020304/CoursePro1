<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/category_dto.php';

class CategoryBLL extends Database
{
    public function create_category(CategoryDTO $cat)
    {
        $parent = is_null($cat->parent_id) ? "NULL" : $cat->parent_id;
        $sql = "INSERT INTO categories (name, parent_id, sort_order)
                VALUES ('{$cat->name}', {$parent}, {$cat->sort_order})";
        $this->execute($sql);
        $this->close();
    }

    public function delete_category(int $id)
    {
        $sql = "DELETE FROM categories WHERE id = {$id}";
        $this->execute($sql);
        $this->close();
    }

    public function update_category(CategoryDTO $cat)
    {
        $parent = is_null($cat->parent_id) ? "NULL" : $cat->parent_id;
        $sql = "UPDATE categories
                SET name = '{$cat->name}', parent_id = {$parent}, sort_order = {$cat->sort_order}
                WHERE id = {$cat->id}";
        $this->execute($sql);
        $this->close();
    }

    public function get_category(int $id): ?CategoryDTO
    {
        $sql = "SELECT * FROM categories WHERE id = {$id}";
        $result = $this->execute($sql);
        $dto = null;
        if ($row = $result->fetch_assoc()) {
            $dto = new CategoryDTO(
                (int)$row['id'],
                $row['name'],
                isset($row['parent_id']) ? (int)$row['parent_id'] : null,
                (int)$row['sort_order']
            );
        }
        $this->close();
        return $dto;
    }

    public function get_all_categories(): array
    {
        $sql = "SELECT * FROM categories ORDER BY sort_order ASC, name ASC";
        $result = $this->execute($sql);
        $list = [];
        while ($row = $result->fetch_assoc()) {
            $list[] = new CategoryDTO(
                (int)$row['id'],
                $row['name'],
                isset($row['parent_id']) ? (int)$row['parent_id'] : null,
                (int)$row['sort_order']
            );
        }
        $this->close();
        return $list;
    }
}
