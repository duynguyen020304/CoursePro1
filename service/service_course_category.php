<?php
// File: service/service_course_category.php

require_once __DIR__ . '/../model/bll/course_category_bll.php';
require_once __DIR__ . '/../model/dto/course_category_dto.php';
require_once __DIR__ . '/service_response.php';

class CourseCategoryService
{
    private CourseCategoryBLL $bll;

    public function __construct()
    {
        $this->bll = new CourseCategoryBLL();
    }

    /**
     * Lấy danh sách Category gắn với Course
     */
    public function get_categories_by_course(string $courseID): ServiceResponse
    {
        try {
            $list = $this->bll->get_categories_by_course($courseID);
            return new ServiceResponse(true, "Lấy danh sách danh mục cho khóa học {$courseID} thành công", $list);
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi truy vấn: ' . $e->getMessage());
        }
    }

    /**
     * Thêm Category vào Course
     */
    public function add_category_to_course(string $courseID, string $categoryID): ServiceResponse
    {
        try {
            $dto = new CourseCategoryDTO($courseID, $categoryID);
            $ok = $this->bll->link_course_category($dto);
            if ($ok) {
                return new ServiceResponse(true, 'Gán danh mục thành công');
            } else {
                return new ServiceResponse(false, 'Gán danh mục thất bại');
            }
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi gán: ' . $e->getMessage());
        }
    }

    /**
     * Xóa Category khỏi Course
     */
    public function remove_category_from_course(string $courseID, string $categoryID): ServiceResponse
    {
        try {
            $this->bll->unlink_course_category($courseID, $categoryID);
            return new ServiceResponse(true, 'Xóa danh mục thành công');
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi xóa: ' . $e->getMessage());
        }
    }
}