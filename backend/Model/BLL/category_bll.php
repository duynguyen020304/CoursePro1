<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../DTO/category_dto.php';

class CategoryBLL extends Database
{
    /**
     * Tạo một danh mục mới.
     *
     * @param CategoryDTO $cat Đối tượng danh mục cần tạo.
     * @return bool True nếu thành công, ngược lại false.
     */
    public function create_category(CategoryDTO $cat): bool
    {
        $sql = "INSERT INTO categories (name, parent_id, sort_order) VALUES (?, ?, ?)";
        
        $bindParams = [
            $cat->name,
            $cat->parent_id,
            $cat->sort_order ?? 0,
        ];

        $result = $this->executePrepared($sql, $bindParams);
        return $result !== false;
    }

    /**
     * Xóa một danh mục.
     *
     * @param int $id ID của danh mục cần xóa.
     * @return bool True nếu thành công, ngược lại false.
     */
    public function delete_category(int $id): bool
    {
        $sql = "DELETE FROM categories WHERE id = ?";
        $bindParams = [$id];
        $result = $this->executePrepared($sql, $bindParams);
        return $result !== false;
    }

    /**
     * Cập nhật thông tin một danh mục.
     *
     * @param CategoryDTO $cat Đối tượng danh mục với thông tin đã cập nhật.
     * @return bool True nếu thành công, ngược lại false.
     */
    public function update_category(CategoryDTO $cat): bool
    {
        $sql = "UPDATE categories SET name = ?, parent_id = ?, sort_order = ? WHERE id = ?";
        
        $bindParams = [
            $cat->name,
            $cat->parent_id,
            $cat->sort_order ?? 0,
            $cat->id,
        ];

        $result = $this->executePrepared($sql, $bindParams);
        return $result !== false;
    }

    /**
     * Lấy thông tin một danh mục bằng ID.
     *
     * @param int $id ID của danh mục.
     * @return ?CategoryDTO Đối tượng CategoryDTO hoặc null nếu không tìm thấy.
     */
    public function get_category(int $id): ?CategoryDTO
    {
        $sql = "SELECT id, name, parent_id, sort_order, created_at FROM categories WHERE id = ?";
        $bindParams = [$id];
        $result = $this->executePrepared($sql, $bindParams);
        $dto = null;

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $dto = new CategoryDTO(
                (int)$row['id'],
                $row['name'],
                isset($row['parent_id']) ? (int)$row['parent_id'] : null,
                (int)$row['sort_order'],
                $row['created_at']
            );
            $result->free();
        }
        return $dto;
    }

    /**
     * Lấy tất cả các danh mục dưới dạng danh sách phẳng.
     *
     * @return array Mảng các đối tượng CategoryDTO.
     */
    public function get_all_categories(): array
    {
        $sql = "SELECT id, name, parent_id, sort_order, created_at FROM categories ORDER BY sort_order ASC, name ASC";
        $list = [];
        $rows = $this->fetchAll($sql); // Sử dụng phương thức fetchAll tiện lợi

        foreach ($rows as $row) {
            $list[] = new CategoryDTO(
                (int)$row['id'],
                $row['name'],
                isset($row['parent_id']) ? (int)$row['parent_id'] : null,
                (int)$row['sort_order'],
                $row['created_at']
            );
        }
        return $list;
    }

    /**
     * Lấy các danh mục dưới dạng cây phân cấp.
     *
     * @return array Cây danh mục.
     */
    public function get_nested_categories(): array
    {
        $all_categories_flat = $this->get_all_categories();

        $all_by_id = [];
        foreach ($all_categories_flat as $dto) {
            // Thêm một thuộc tính 'children' vào đối tượng DTO để lưu các con
            $dto->children = []; 
            $all_by_id[$dto->id] = $dto;
        }

        $tree = [];
        foreach ($all_by_id as $id => &$node) {
            $parentID = $node->parent_id;
            if ($parentID === null) {
                // Đây là node gốc
                $tree[$id] = &$node;
            } elseif (isset($all_by_id[$parentID])) {
                // Thêm node này vào mảng children của cha nó
                $all_by_id[$parentID]->children[] = &$node;
            }
        }
        unset($node); // Hủy tham chiếu cuối cùng

        return array_values($tree); // Trả về mảng không có key là ID
    }
}
?>
