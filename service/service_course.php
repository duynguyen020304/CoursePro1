<?php
require_once __DIR__ . '/../model/bll/course_bll.php';
require_once __DIR__ . '/../model/bll/course_category_bll.php';
require_once __DIR__ . '/../model/dto/course_dto.php';
require_once __DIR__ . '/../model/dto/course_category_dto.php';
require_once __DIR__ . '/../model/bll/course_instructor_bll.php';
require_once __DIR__ . '/../model/bll/course_image_bll.php';
require_once __DIR__ . '/../model/bll/user_bll.php';
require_once __DIR__ . '/../model/bll/instructor_bll.php';
require_once __DIR__ . '/../model/bll/category_bll.php';
require_once __DIR__ . '/service_response.php';


class CourseService
{
    private CourseBLL $courseBll;
    private CourseCategoryBLL $courseCategoryBll;
    private CategoryBLL $categoryBll;
    private InstructorBLL $instructorBll;
    private CourseInstructorBLL $courseInstructorBll;
    private CourseImageBLL $courseImageBll;
    private UserBLL $userBll;

    public function __construct()
    {

        $this->courseBll = new CourseBLL();
        $this->courseCategoryBll = new CourseCategoryBLL();
        $this->categoryBll = new CategoryBLL();
        $this->instructorBll = new InstructorBLL();
        $this->courseInstructorBll = new CourseInstructorBLL();
        $this->courseImageBll = new CourseImageBLL();
        $this->userBll = new UserBLL();
    }

    public function create_course(string $title, ?string $description, float $price, array $instructorID, array $categoryIDs, string $createdBy): ServiceResponse
    {
        $courseID = uniqid('course_');
        // $now = date('Y-m-d H:i:s');
//        $created_user = $this->userBll->get_user_by_id($createdBy);
        $dto = new CourseDTO($courseID, $title, $description, $price, $createdBy);
        try {
            if ($this->courseBll->create_course($dto)) {
                foreach ($categoryIDs as $catID) {
                    // Chuyển CategoryID thành chuỗi
                    $catID = (string) $catID;
                    $cc = new CourseCategoryDTO($courseID, $catID);
                    if (!$this->courseCategoryBll->link_course_category($cc)) {
                        return new ServiceResponse(false, 'Liên kết thể loại thất bại');
                    }
                }
                foreach ($instructorID as $instructor) {
                    if (!$this->courseInstructorBll->add($courseID, $instructor)) {
                        return new ServiceResponse(false, 'Liên kết giảng viên thất bại');
                    }
                }
                return new ServiceResponse(true, 'Tạo khóa học thành công', $courseID);
            }
            return new ServiceResponse(true, 'Tạo khóa học thất bại');
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi tạo khóa học: ' . $e->getMessage());
        }
    }

    public function update_course(string $courseID, string $title, ?string $description, float $price, array $instructorIDs, array $categoryIDs): ServiceResponse
    {
        try {
            $course_to_update = $this->courseBll->get_course($courseID);
            if (!$course_to_update) {
                return new ServiceResponse(false, 'Khóa học không tồn tại.');
            }

            $dto = new CourseDTO($courseID, $title, $description, $price, $course_to_update->createdBy);

            $existing_categories = $this->courseCategoryBll->get_categories_by_course($courseID);

            if (!empty($existing_categories)) {
                foreach ($existing_categories as $cat) {
                    if (!$this->courseCategoryBll->unlink_course_category($courseID, $cat->categoryID)) {
                        return new ServiceResponse(false, 'Lỗi khi xóa liên kết danh mục cũ: ' . $cat->categoryID);
                    }
                }
            }

            if (!empty($categoryIDs)) {
                foreach ($categoryIDs as $catID) {
                    $cc = new CourseCategoryDTO($courseID, $catID);
                    if (!$this->courseCategoryBll->link_course_category($cc)) {
                        return new ServiceResponse(false, 'Lỗi khi liên kết khóa học với danh mục mới: ' . $catID);
                    }
                }
            }

            $existing_course_instructors = $this->courseInstructorBll->get_by_course($courseID);
            if ($existing_course_instructors === null) {
                return new ServiceResponse(false, 'Lỗi khi lấy danh sách giảng viên hiện tại của khóa học.');
            }

            if (!empty($existing_course_instructors)) {
                foreach ($existing_course_instructors as $courseInstructor) {
                    if (!$this->courseInstructorBll->delete($courseID, $courseInstructor->instructorID)) {
                        return new ServiceResponse(false, 'Lỗi khi xóa giảng viên cũ: ' . $courseInstructor->instructorID);
                    }
                }
            }

            if (!empty($instructorIDs)) {
                foreach ($instructorIDs as $instructor_id_single) {
                    if (!$this->courseInstructorBll->add($courseID, $instructor_id_single)) {
                        return new ServiceResponse(false, 'Lỗi khi thêm giảng viên mới: ' . $instructor_id_single . ' vào khóa học.');
                    }
                }
            }

            if ($this->courseBll->update_course($dto)) {
                return new ServiceResponse(true, 'Cập nhật khóa học thành công');
            } else {
                return new ServiceResponse(false, 'Cập nhật thông tin chính của khóa học thất bại.');
            }
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi hệ thống khi cập nhật: ' . $e->getMessage());
        }
    }

    public function get_all_courses(): ServiceResponse
    {
        try {
            $list_course = $this->courseBll->get_all_courses();
            $list_course_with_instructors_details = [];
            foreach ($list_course as $course) {
                $instructor_dtos_for_course = $this->courseInstructorBll->get_by_course($course->courseID);
                $course_categories = $this->courseCategoryBll->get_categories_by_course($course->courseID);
                $instructors_info = [];
                if (!empty($instructor_dtos_for_course)) {
                    foreach ($instructor_dtos_for_course as $instructor_dto) {
                        $instructor = $this->instructorBll->get_instructor($instructor_dto->instructorID);
                        $instructor_user = $this->userBll->get_user_by_id($instructor->userID);
                        $instructors_info[] = [
                            'instructorID' => $instructor_dto->instructorID,
                            'firstName' => $instructor_user->firstName,
                            'lastName' => $instructor_user->lastName,
                        ];
                    }
                }
                $tmp_course_categories = [];
                foreach ($course_categories as $course_category) {
                    $category_name = $this->categoryBll->get_category($course_category->categoryID)->name;
                    $tmp_course_categories[] = [
                        'categoryID' => $course_category->categoryID,
                        'categoryName' => $category_name,
                    ];
                }

                $list_course_with_instructors_details[] = [
                    'courseID' => $course->courseID,
                    'title' => $course->title,
                    'description' => $course->description,
                    'price' => $course->price,
                    'createdBy' => $course->createdBy,
                    'instructors' => $instructors_info,
                    'categories' => $tmp_course_categories,
                ];
            }
            return new ServiceResponse(true, 'Lấy danh sách thành công', $list_course_with_instructors_details);
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi lấy danh sách: ' . $e->getMessage());
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
            $categories = $this->courseCategoryBll->get_categories_by_course($courseID);
            $existing_course_instructor = $this->courseInstructorBll->get_by_course($courseID);
            foreach ($categories as $cat) {
                if (!$this->courseCategoryBll->unlink_course_category($courseID, $cat->categoryID)) {
                    return new ServiceResponse(false, 'Gỡ liên kết danh mục thất bại');
                }
            }
            foreach ($existing_course_instructor as $courseInstructor) {
                if (!$this->courseInstructorBll->delete($courseID, $courseInstructor->instructorID)) {
                    return new ServiceResponse(false, "Gỡ liên kết khóa học, giảng viên");
                }
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
