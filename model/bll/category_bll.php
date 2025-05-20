<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/category_dto.php';

class CategoryBLL extends Database
{
    public function create_category(CategoryDTO $cat): bool
    {
        $sql = "INSERT INTO CATEGORIES (Name, Parent_ID, Sort_Order)
                VALUES (:name, :parent_id, :sort_order)";

        $bindParams = [
            ':name'       => $cat->name,
            ':parent_id'  => $cat->parent_id,
            ':sort_order' => $cat->sort_order ?? 0,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false) && ($this->getAffectedRows() === 1);
    }

    public function delete_category(int $id): bool
    {
        $sql = "DELETE FROM CATEGORIES WHERE ID = :id";
        $bindParams = [':id' => $id];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false) && ($this->getAffectedRows() === 1);
    }

    public function update_category(CategoryDTO $cat): bool
    {
        $sql = "UPDATE CATEGORIES
                SET Name = :name, Parent_ID = :parent_id, Sort_Order = :sort_order
                WHERE ID = :id_where";

        $bindParams = [
            ':name'       => $cat->name,
            ':parent_id'  => $cat->parent_id,
            ':sort_order' => $cat->sort_order ?? 0,
            ':id_where'   => $cat->id,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function get_category(int $id): ?CategoryDTO
    {
        $sql = "SELECT ID, Name, Parent_ID, Sort_Order, created_at 
                FROM CATEGORIES 
                WHERE ID = :id_param";
        $bindParams = [':id_param' => $id];

        $stid = $this->executePrepared($sql, $bindParams);
        $dto = null;

        if ($stid) {
            if (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $dto = new CategoryDTO(
                    isset($row['ID']) ? (int)$row['ID'] : 0,
                    $row['NAME'],
                    isset($row['PARENT_ID']) ? (int)$row['PARENT_ID'] : null,
                    isset($row['SORT_ORDER']) ? (int)$row['SORT_ORDER'] : 0,
                    $row['CREATED_AT'] ?? null
                );
            }
            @oci_free_statement($stid);
        }
        return $dto;
    }

    public function get_all_categories(): array
    {
        $sql = "SELECT ID, Name, Parent_ID, Sort_Order, created_at 
                FROM CATEGORIES 
                ORDER BY Sort_Order ASC, Name ASC";

        $stid = $this->executePrepared($sql);
        $list = [];

        if ($stid) {
            while (($row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $list[] = new CategoryDTO(
                    isset($row['ID']) ? (int)$row['ID'] : 0,
                    $row['NAME'],
                    isset($row['PARENT_ID']) ? (int)$row['PARENT_ID'] : null,
                    isset($row['SORT_ORDER']) ? (int)$row['SORT_ORDER'] : 0,
                    $row['CREATED_AT'] ?? null
                );
            }
            @oci_free_statement($stid);
        }
        return $list;
    }

    public function get_nested_categories(): array
    {
        $all_categories_flat = $this->get_all_categories();

        $all_by_id = [];
        foreach ($all_categories_flat as $dto) {
            $all_by_id[$dto->id] = ['data' => $dto, 'children' => []];
        }

        $tree = [];
        foreach ($all_by_id as $id => &$node) {
            $parentID = $node['data']->parent_id;
            if ($parentID === null) {
                $tree[$id] = &$node;
            } elseif (isset($all_by_id[$parentID])) {
                $all_by_id[$parentID]['children'][] = &$node;
            }
        }
        unset($node);

        return $tree;
    }
}
?>