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
        $now = date('Y-m-d H:i:s');
        $dto = new CourseDTO($courseID, $title, $description, $price, $instructorID, $now);

        try {
            // Bước 1: Tạo khóa học
            if (!$this->courseBll->create_course($dto)) {
                return new ServiceResponse(false, 'Tạo khóa học thất bại');
            }

            // Bước 2: Gắn category
            foreach ($categoryIDs as $catID) {
                // Chuyển CategoryID thành chuỗi
                $catID = (string) $catID;
                $cc = new CourseCategoryDTO($courseID, $catID);
                if (!$this->categoryBll->link_course_category($cc)) {
                    return new ServiceResponse(false, 'Liên kết thể loại thất bại');
                }
            }

            // Bước 3: Thành công
            return new ServiceResponse(true, 'Tạo khóa học thành công', $courseID);
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi tạo khóa học: ' . $e->getMessage());
        }
    }

    public function update_course(string $courseID, string $title, ?string $description, float $price, string $instructorID, array $categoryIDs, string $createdBy): ServiceResponse
    {
        $dto = new CourseDTO($courseID, $title, $description, $price, $instructorID, $createdBy);

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
            // Kiểm tra khóa học có tồn tại không
            $course = $this->courseBll->get_course($courseID);
            if (!$course) {
                return new ServiceResponse(false, 'Khóa học không tồn tại');
            }

            // Xóa toàn bộ liên kết với danh mục
            $categories = $this->categoryBll->get_categories_by_course($courseID);
            foreach ($categories as $cat) {
                $this->categoryBll->unlink_course_category($courseID, $cat->categoryID);
            }

            // Xóa khóa học
            if ($this->courseBll->delete_course($courseID)) {
                return new ServiceResponse(true, 'Xóa khóa học thành công');
            }
            return new ServiceResponse(true, 'Xóa khóa học thất bại');
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi xóa khóa học: ' . $e->getMessage());
        }
    }
    public function get_course_by_id(string $courseID): ServiceResponse
    {
        try {
            $course = $this->courseBll->get_course($courseID);

            if ($course) {
                return new ServiceResponse(true, 'Tìm thấy khóa học', $course);
            } else {
                return new ServiceResponse(false, 'Không tìm thấy khóa học với ID đã cung cấp');
            }
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi lấy thông tin khóa học: ' . $e->getMessage());
        }
    }
    public function save_course_image(string $courseID, string $imagePath)
    {
        require_once __DIR__ . '/../model/bll/course_image_bll.php';
        require_once __DIR__ . '/../model/dto/course_image_dto.php';

        $imageBLL = new CourseImageBLL();
        $imageID = uniqid('img_');
        $dto = new CourseImageDTO($imageID, $courseID, $imagePath, null, 0);
        $result = $imageBLL->create_image($dto);
        if (!$result) {
            return new ServiceResponse(false, 'Lỗi khi lưu ảnh khóa học');
        }
        return new ServiceResponse(true, 'Lưu ảnh thành công');
    }
}
