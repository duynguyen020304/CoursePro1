<?php
require_once __DIR__ . '/../model/bll/category_bll.php';
require_once __DIR__ . '/../model/dto/category_dto.php';
require_once __DIR__ . '/service_response.php';

class CategoryService
{
    private CategoryBLL $bll;

    public function __construct()
    {
        $this->bll = new CategoryBLL();
    }

    public function get_all(): ServiceResponse
    {
        try {
            $categories = $this->bll->get_all_categories();
            return new ServiceResponse(true, 'Lấy danh sách danh mục thành công', $categories);
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi: ' . $e->getMessage());
        }
    }

    public function get_tree(): ServiceResponse
    {
        try {
            $tree = $this->bll->get_nested_categories();
            return new ServiceResponse(true, 'Lấy danh mục phân cấp thành công', $tree);
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi: ' . $e->getMessage());
        }
    }

    public function create(CategoryDTO $cat): ServiceResponse
    {
        try {
            $this->bll->create_category($cat);
            return new ServiceResponse(true, 'Thêm danh mục thành công');
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi thêm: ' . $e->getMessage());
        }
    }

    public function update(CategoryDTO $cat): ServiceResponse
    {
        try {
            $this->bll->update_category($cat);
            return new ServiceResponse(true, 'Cập nhật danh mục thành công');
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi cập nhật: ' . $e->getMessage());
        }
    }

    public function delete(int $id): ServiceResponse
    {
        try {
            $exists = $this->bll->get_category($id);
            if (!$exists) {
                return new ServiceResponse(false, 'Danh mục không tồn tại');
            }

            if ($this->bll->delete_category($id)) {
                return new ServiceResponse(true, 'Xóa danh mục thành công');
            }
            return new ServiceResponse(false, 'Xóa danh mục thất bại');
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi xóa: ' . $e->getMessage());
        }
    }
}
