<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/category_dto.php';

class CategoryBLL extends Database
{
    public function create_category(CategoryDTO $cat): bool
    {
        $sql = "BEGIN CATEGORY_PKG.CREATE_CATEGORY_PROC(:name, :parent_id, :sort_order); END;";

        $bindParams = [
            ':name'       => $cat->name,
            ':parent_id'  => $cat->parent_id,
            ':sort_order' => $cat->sort_order ?? 0,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function delete_category(int $id): bool
    {
        $sql = "BEGIN CATEGORY_PKG.DELETE_CATEGORY_PROC(:id); END;";
        $bindParams = [':id' => $id];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function update_category(CategoryDTO $cat): bool
    {
        $sql = "BEGIN CATEGORY_PKG.UPDATE_CATEGORY_PROC(:id_where, :name, :parent_id, :sort_order); END;";

        $bindParams = [
            ':id_where'   => $cat->id,
            ':name'       => $cat->name,
            ':parent_id'  => $cat->parent_id,
            ':sort_order' => $cat->sort_order ?? 0,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function get_category(int $id): ?CategoryDTO
    {
        $sql = "BEGIN :result_cursor := CATEGORY_PKG.GET_CATEGORY_BY_ID_FUNC(:id_param); END;";
        $bindParams = [
            ':id_param' => $id
        ];

        $dto = null;
        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[CategoryBLL] Failed to create new cursor for GET_CATEGORY_BY_ID_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return null;
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[CategoryBLL] OCI Parse failed for GET_CATEGORY_BY_ID_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return null;
        }

        @oci_bind_by_name($parsed_stid, ':id_param', $bindParams[':id_param']);
        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[CategoryBLL] OCI Execute failed for GET_CATEGORY_BY_ID_FUNC. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }
        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[CategoryBLL] OCI Execute failed for result cursor. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }
        $stid_cursor = $out_cursor;

        if ($stid_cursor) {
            if (($row = @oci_fetch_array($stid_cursor, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $dto = new CategoryDTO(
                    isset($row['ID']) ? (int)$row['ID'] : 0,
                    $row['NAME'],
                    isset($row['PARENT_ID']) ? (int)$row['PARENT_ID'] : null,
                    isset($row['SORT_ORDER']) ? (int)$row['SORT_ORDER'] : 0,
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid_cursor);
        }
        @oci_free_statement($parsed_stid);
        return $dto;
    }

    public function get_all_categories(): array
    {
        $sql = "BEGIN :result_cursor := CATEGORY_PKG.GET_ALL_CATEGORIES_FUNC(); END;";
        $list = [];

        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[CategoryBLL] Failed to create new cursor for GET_ALL_CATEGORIES_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return [];
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[CategoryBLL] OCI Parse failed for GET_ALL_CATEGORIES_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return [];
        }

        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[CategoryBLL] OCI Execute failed for GET_ALL_CATEGORIES_FUNC. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[CategoryBLL] OCI Execute failed for result cursor. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }

        $stid_cursor = $out_cursor;
        if ($stid_cursor) {
            while (($row = @oci_fetch_array($stid_cursor, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $list[] = new CategoryDTO(
                    isset($row['ID']) ? (int)$row['ID'] : 0,
                    $row['NAME'],
                    isset($row['PARENT_ID']) ? (int)$row['PARENT_ID'] : null,
                    isset($row['SORT_ORDER']) ? (int)$row['SORT_ORDER'] : 0,
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid_cursor);
        }
        @oci_free_statement($parsed_stid);
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