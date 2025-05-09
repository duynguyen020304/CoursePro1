<?php
require_once __DIR__ . '/../model/bll/course_bll.php';
require_once __DIR__ . '/../model/bll/course_category_bll.php';
require_once __DIR__ . '/../model/dto/course_dto.php';
require_once __DIR__ . '/../model/dto/course_category_dto.php';
require_once __DIR__ . '/service_response.php';

class CourseService
{
    private CourseBLL $courseBll;
    private CourseCategoryBLL $categoryBll;

    public function __construct()
    {
        $this->courseBll = new CourseBLL();
        $this->categoryBll = new CourseCategoryBLL();
    }

    public function create_course(string $title, ?string $description, float $price, string $instructorID, array $categoryIDs): ServiceResponse
    {
        $courseID = uniqid('course_');
        $dto = new CourseDTO($courseID, $title, $description, $price, $instructorID);

        try {
            $this->courseBll->create_course($dto);

            foreach ($categoryIDs as $catID) {
                $cc = new CourseCategoryDTO($courseID, $catID);
                $this->categoryBll->link_course_category($cc);
            }

            return new ServiceResponse(true, 'Tạo khóa học thành công', $courseID);
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi tạo khóa học: ' . $e->getMessage());
        }
    }

    public function update_course(string $courseID, string $title, ?string $description, float $price, string $instructorID, array $categoryIDs): ServiceResponse
    {
        $dto = new CourseDTO($courseID, $title, $description, $price, $instructorID);

        try {
            $this->courseBll->update_course($dto);

            // Xóa các category cũ
            $existing = $this->categoryBll->get_categories_by_course($courseID);
            foreach ($existing as $cat) {
                $this->categoryBll->unlink_course_category($courseID, $cat->categoryID);
            }

            // Gán lại category mới
            foreach ($categoryIDs as $catID) {
                $cc = new CourseCategoryDTO($courseID, $catID);
                $this->categoryBll->link_course_category($cc);
            }

            return new ServiceResponse(true, 'Cập nhật khóa học thành công');
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi cập nhật: ' . $e->getMessage());
        }
    }

    public function get_all_courses(): ServiceResponse
    {
        try {
            $list = $this->courseBll->get_all_courses();
            return new ServiceResponse(true, 'Lấy danh sách thành công', $list);
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi lấsy danh sách: ' . $e->getMessage());
        }
    }
    public function delete_course(string $courseID): ServiceResponse
    {
        try {
            // Xóa toàn bộ liên kết category trước
            $categories = $this->categoryBll->get_categories_by_course($courseID);
            foreach ($categories as $cat) {
                $this->categoryBll->unlink_course_category($courseID, $cat->categoryID);
            }

            // Xóa khóa học
            $this->courseBll->delete_course($courseID);

            return new ServiceResponse(true, 'Xóa khóa học thành công');
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi xóa khóa học: ' . $e->getMessage());
        }
    }
}
