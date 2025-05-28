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
require_once __DIR__ . '/../model/bll/course_image_bll.php';
require_once __DIR__ . '/../model/bll/course_requirement_bll.php';
require_once __DIR__ . '/../model/bll/course_objective_bll.php';
require_once __DIR__ . '/../model/dto/course_category_dto.php';
require_once __DIR__ . '/../model/dto/course_objective_dto.php';
require_once __DIR__ . '/../model/dto/course_requirement_dto.php';
require_once __DIR__ . '/../model/dto/chapter_dto.php';
require_once __DIR__ . '/../model/bll/chapter_bll.php';
require_once __DIR__ . '/../model/dto/lesson_dto.php';
require_once __DIR__ . '/../model/bll/lesson_bll.php';
require_once __DIR__ . '/../model/dto/video_dto.php';
require_once __DIR__ . '/../model/bll/video_bll.php';
require_once __DIR__ . '/../model/dto/resource_dto.php';
require_once __DIR__ . '/../model/bll/resource_bll.php';
require_once __DIR__ . '/service_response.php';


class CourseService
{
    private CourseBLL $courseBll;
    private CourseCategoryBLL $courseCategoryBll;
    private CategoryBLL $categoryBll;
    private InstructorBLL $instructorBll;
    private CourseInstructorBLL $courseInstructorBll;
    private CourseImageBLL $courseImageBll;
    private CourseRequirementBLL $courseRequirementBll;
    private CourseObjectiveBLL $courseObjectiveBll;
    private ChapterBLL $chapterBll;
    private LessonBLL $lessonBll;
    private VideoBLL $videoBll;
    private ResourceBLL $resourceBll;
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
        $this->courseRequirementBll = new CourseRequirementBLL();
        $this->courseObjectiveBll = new CourseObjectiveBLL();
        $this->chapterBll = new ChapterBLL();
        $this->lessonBll = new LessonBLL();
        $this->videoBll = new VideoBLL();
        $this->resourceBll = new ResourceBLL();
    }

    public function create_course(string $title, ?string $description, float $price, array $instructorID, array $categoryIDs, string $createdBy): ServiceResponse
    {
        $courseID = str_replace('.', '_', uniqid('course_', true));
        $dto = new CourseDTO($courseID, $title, $description, $price, $createdBy);
        try {
            if ($this->courseBll->create_course($dto)) {
                foreach ($categoryIDs as $catID) {
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

            $existing_categories = $this->courseCategoryBll->get_categories_by_course_id($courseID);

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

            $existing_course_instructors = $this->courseInstructorBll->get_instructors_by_course_id($courseID);
            if ($existing_course_instructors === null) {
                return new ServiceResponse(false, 'Lỗi khi lấy danh sách giảng viên hiện tại của khóa học.');
            }

            if (!empty($existing_course_instructors)) {
                foreach ($existing_course_instructors as $courseInstructor) {
                    if (!$this->courseInstructorBll->unlink_course_instructor($courseID, $courseInstructor->instructorID)) {
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
                $instructor_dtos_for_course = $this->courseInstructorBll->get_instructors_by_course_id($course->courseID);
                $course_categories = $this->courseCategoryBll->get_categories_by_course_id($course->courseID);
                $course_images = $this->courseImageBll->get_images_by_course_id($course->courseID);
                $course_requirements = $this->courseRequirementBll->get_requirements_by_course_id($course->courseID);
                $course_objectives = $this->courseObjectiveBll->get_objectives_by_course_id($course->courseID);
                $course_chapters = $this->chapterBll->get_chapters_by_course_id($course->courseID);

                $instructors_info = [];
                if (!empty($instructor_dtos_for_course)) {
                    foreach ($instructor_dtos_for_course as $instructor_dto) {
                        $instructor = $this->instructorBll->get_instructor($instructor_dto->instructorID);
                        $instructor_user = $this->userBll->get_user_by_user_id($instructor->userID);
                        $instructors_info[] = [
                            'instructorID' => $instructor_dto->instructorID,
                            'userID' => $instructor_user->userID,
                            'firstName' => $instructor_user->firstName,
                            'lastName' => $instructor_user->lastName,
                            'biography' => $instructor->biography,
                            'profileImage' => $instructor_user->profileImage,
                        ];
                    }
                }
                $tmp_course_categories = [];
                if (!empty($course_categories)) {
                    foreach ($course_categories as $course_category) {
                        $category_name = $this->categoryBll->get_category($course_category->categoryID)->name;
                        $tmp_course_categories[] = [
                            'categoryID' => $course_category->categoryID,
                            'categoryName' => $category_name,
                        ];
                    }
                }
                $tmp_course_images = [];
                if (!empty($course_images)) {
                    foreach ($course_images as $course_image) {
                        $tmp_course_images[] = [
                            'imageID' => $course_image->imageID,
                            'imagePath' => $course_image->imagePath
                        ];
                    }
                }

                $tmp_course_requirements = [];
                if (!empty($course_requirements)) {
                    foreach ($course_requirements as $course_requirement) {
                        $tmp_course_requirements[] = [
                            "requirementID" => $course_requirement->requirementID,
                            "requirement" => $course_requirement->requirement,
                        ];
                    }
                }

                $tmp_course_objectives = [];
                if (!empty($course_objectives)) {
                    foreach ($course_objectives as $course_objective) {
                        $tmp_course_objectives[] = [
                            "objectiveID" => $course_objective->objectiveID,
                            "objective" => $course_objective->objective,
                        ];
                    }
                }

                $tmp_course_chapters_lessons_resources_videos = [];
                if (!empty($course_chapters)) {
                    foreach ($course_chapters as $course_chapter) {
                        $tmp_chapter_lessons_resources_video = [];
                        $course_lessons = $this->lessonBll->get_lessons_by_chapter_id($course_chapter->chapterID);
                        foreach ($course_lessons as $course_lesson) {
                            $resources = $this->resourceBll->get_resources_by_lesson_id($course_lesson->lessonID);
                            $videos = $this->videoBll->get_videos_by_lesson($course_lesson->lessonID);
                            $tmp_lesson_resources = [];
                            $tmp_lesson_videos = [];
                            foreach ($resources as $resource) {
                                $tmp_lesson_resources[] = [
                                    "resourceID" => $resource->resourceID,
                                    "resourceTitle" => $resource->title,
                                    "resourcePath" => $resource->resourcePath,
                                    "resourceSortOrder" => $resource->sortOrder,
                                ];
                            }
                            foreach ($videos as $video) {
                                $tmp_lesson_videos[] = [
                                    "videoID" => $video->videoID,
                                    "videoTitle" => $video->title,
                                    "videoURL" => $video->url,
                                    "videoSortOrder" => $video->sortOrder,
                                ];
                            }
                            $tmp_chapter_lessons_resources_video[] = [
                                "lessonID" => $course_lesson->lessonID,
                                "lessonTitle" => $course_lesson->title,
                                "lessonContent" => $course_lesson->content,
                                "lessonResources" => $tmp_lesson_resources,
                                "lessonVideos" => $tmp_lesson_videos,
                            ];
                        }
                        $tmp_course_chapters_lessons_resources_videos[] = [
                            "chapterID" => $course_chapter->chapterID,
                            "chapterTitle" => $course_chapter->title,
                            "chapterDescription" => $course_chapter->description,
                            "chapterLessons"=> $tmp_chapter_lessons_resources_video
                        ];
                    }
                }

                $list_course_with_instructors_details[] = [
                    'courseID' => $course->courseID,
                    'title' => $course->title,
                    'description' => $course->description,
                    'price' => $course->price,
                    'createdBy' => $course->createdBy,
                    'instructors' => $instructors_info,
                    'categories' => $tmp_course_categories,
                    'images' => $tmp_course_images,
                    'requirements' => $tmp_course_requirements,
                    'objectives' => $tmp_course_objectives,
                    'chapters' => $tmp_course_chapters_lessons_resources_videos,
                ];
            }
            return new ServiceResponse(true, 'Lấy danh sách thành công', $list_course_with_instructors_details);
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi lấy danh sách: ' . $e->getMessage());
        }
    }

    public function get_k_courses_for_home_page(int $k=8): ServiceResponse
    {
        try {
            $list_course = $this->courseBll->get_all_courses();
            $list_course_with_instructors_details = [];
            $i = 0;
            foreach ($list_course as $course) {
                if ($i == $k) {
                    break;
                }
                $instructor_dtos_for_course = $this->courseInstructorBll->get_instructors_by_course_id($course->courseID);
                $course_categories = $this->courseCategoryBll->get_categories_by_course_id($course->courseID);
                $course_images = $this->courseImageBll->get_images_by_course_id($course->courseID);
                $instructors_info = [];
                if (!empty($instructor_dtos_for_course)) {
                    foreach ($instructor_dtos_for_course as $instructor_dto) {
                        $instructor = $this->instructorBll->get_instructor($instructor_dto->instructorID);
                        $instructor_user = $this->userBll->get_user_by_user_id($instructor->userID);
                        $instructors_info[] = [
                            'instructorID' => $instructor_dto->instructorID,
                            'userID' => $instructor_user->userID,
                            'firstName' => $instructor_user->firstName,
                            'lastName' => $instructor_user->lastName,
                            'biography' => $instructor->biography,
                            'profileImage' => $instructor_user->profileImage,
                        ];
                    }
                }
                $tmp_course_categories = [];
                if (!empty($course_categories)) {
                    foreach ($course_categories as $course_category) {
                        $category_name = $this->categoryBll->get_category($course_category->categoryID)->name;
                        $tmp_course_categories[] = [
                            'categoryID' => $course_category->categoryID,
                            'categoryName' => $category_name,
                        ];
                    }
                }
                $tmp_course_images = [];
                if (!empty($course_images)) {
                    foreach ($course_images as $course_image) {
                        $tmp_course_images[] = [
                            'imageID' => $course_image->imageID,
                            'imagePath' => $course_image->imagePath
                        ];
                    }
                }

                $list_course_with_instructors_details[] = [
                    'courseID' => $course->courseID,
                    'title' => $course->title,
                    'description' => $course->description,
                    'price' => $course->price,
                    'createdBy' => $course->createdBy,
                    'images' => $tmp_course_images,
                    'categories' => $tmp_course_categories,
                    'instructors' => $instructors_info,
                ];
                $i = $i + 1;
            }
            return new ServiceResponse(true, 'Lấy danh sách thành công', $list_course_with_instructors_details);
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi lấy danh sách: ' . $e->getMessage());
        }
    }

    public function get_all_courses_for_upload_video(): ServiceResponse
    {
        try {
            $list_course = $this->courseBll->get_all_courses();
            $list_course_with_instructors_details = [];
            foreach ($list_course as $course) {
//                $instructor_dtos_for_course = $this->courseInstructorBll->get_instructors_by_course_id($course->courseID);
//                $course_categories = $this->courseCategoryBll->get_categories_by_course_id($course->courseID);
                $course_images = $this->courseImageBll->get_images_by_course_id($course->courseID);
//                $instructors_info = [];
//                if (!empty($instructor_dtos_for_course)) {
//                    foreach ($instructor_dtos_for_course as $instructor_dto) {
//                        $instructor = $this->instructorBll->get_instructor($instructor_dto->instructorID);
//                        $instructor_user = $this->userBll->get_user_by_user_id($instructor->userID);
//                        $instructors_info[] = [
//                            'instructorID' => $instructor_dto->instructorID,
//                            'userID' => $instructor_user->userID,
//                            'firstName' => $instructor_user->firstName,
//                            'lastName' => $instructor_user->lastName,
//                            'biography' => $instructor->biography,
//                            'profileImage' => $instructor_user->profileImage,
//                        ];
//                    }
//                }
//                $tmp_course_categories = [];
//                if (!empty($course_categories)) {
//                    foreach ($course_categories as $course_category) {
//                        $category_name = $this->categoryBll->get_category($course_category->categoryID)->name;
//                        $tmp_course_categories[] = [
//                            'categoryID' => $course_category->categoryID,
//                            'categoryName' => $category_name,
//                        ];
//                    }
//                }
                $tmp_course_images = [];
                if (!empty($course_images)) {
                    foreach ($course_images as $course_image) {
                        $tmp_course_images[] = [
                            'imageID' => $course_image->imageID,
                            'imagePath' => $course_image->imagePath
                        ];
                    }
                }

                $list_course_with_instructors_details[] = [
                    'courseID' => $course->courseID,
                    'title' => $course->title,
                    'description' => $course->description,
//                    'price' => $course->price,
                    'createdBy' => $course->createdBy,
                    'images' => $tmp_course_images,
//                    'categories' => $tmp_course_categories,
//                    'instructors' => $instructors_info,
                ];
            }
            return new ServiceResponse(true, 'Lấy danh sách thành công', $list_course_with_instructors_details);
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi lấy danh sách: ' . $e->getMessage());
        }
    }

    public function get_all_courses_for_course_management(): ServiceResponse
    {
        try {
            $list_course = $this->courseBll->get_all_courses();
            $list_course_with_instructors_details = [];
            foreach ($list_course as $course) {
                $instructor_dtos_for_course = $this->courseInstructorBll->get_instructors_by_course_id($course->courseID);
                $course_categories = $this->courseCategoryBll->get_categories_by_course_id($course->courseID);
                $course_images = $this->courseImageBll->get_images_by_course_id($course->courseID);
                $instructors_info = [];
                if (!empty($instructor_dtos_for_course)) {
                    foreach ($instructor_dtos_for_course as $instructor_dto) {
                        $instructor = $this->instructorBll->get_instructor($instructor_dto->instructorID);
                        $instructor_user = $this->userBll->get_user_by_user_id($instructor->userID);
                        $instructors_info[] = [
                            'instructorID' => $instructor_dto->instructorID,
                            'userID' => $instructor_user->userID,
                            'firstName' => $instructor_user->firstName,
                            'lastName' => $instructor_user->lastName,
//                            'biography' => $instructor->biography,
//                            'profileImage' => $instructor_user->profileImage,
                        ];
                    }
                }
                $tmp_course_categories = [];
                if (!empty($course_categories)) {
                    foreach ($course_categories as $course_category) {
                        $category_name = $this->categoryBll->get_category($course_category->categoryID)->name;
                        $tmp_course_categories[] = [
                            'categoryID' => $course_category->categoryID,
                            'categoryName' => $category_name,
                        ];
                    }
                }
                $tmp_course_images = [];
                if (!empty($course_images)) {
                    foreach ($course_images as $course_image) {
                        $tmp_course_images[] = [
                            'imageID' => $course_image->imageID,
                            'imagePath' => $course_image->imagePath
                        ];
                    }
                }

                $list_course_with_instructors_details[] = [
                    'courseID' => $course->courseID,
                    'title' => $course->title,
                    'description' => $course->description,
                    'price' => $course->price,
                    'createdBy' => $course->createdBy,
                    'images' => $tmp_course_images,
                    'categories' => $tmp_course_categories,
                    'instructors' => $instructors_info,
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
            $course = $this->courseBll->get_course($courseID);
            if (!$course) {
                return new ServiceResponse(false, 'Khóa học không tồn tại');
            }

            $categories = $this->courseCategoryBll->get_categories_by_course_id($courseID);
            $existing_course_instructor = $this->courseInstructorBll->get_instructors_by_course_id($courseID);
            foreach ($categories as $cat) {
                if (!$this->courseCategoryBll->unlink_course_category($courseID, $cat->categoryID)) {
                    return new ServiceResponse(false, 'Gỡ liên kết danh mục thất bại');
                }
            }
            foreach ($existing_course_instructor as $courseInstructor) {
                if (!$this->courseInstructorBll->unlink_course_instructor($courseID, $courseInstructor->instructorID)) {
                    return new ServiceResponse(false, "Gỡ liên kết khóa học, giảng viên");
                }
            }
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

            if (!$course) {
                return new ServiceResponse(false, 'Không tìm thấy khóa học');
            }

            $instructor_dtos_for_course = $this->courseInstructorBll->get_instructors_by_course_id($course->courseID);
            $instructors_info = [];
            if (!empty($instructor_dtos_for_course)) {
                foreach ($instructor_dtos_for_course as $instructor_dto) {
                    $instructor = $this->instructorBll->get_instructor($instructor_dto->instructorID);
                    if ($instructor) {
                        $instructor_user = $this->userBll->get_user_by_user_id($instructor->userID);
                        if ($instructor_user) {
                            $instructors_info[] = [
                                'instructorID' => $instructor_dto->instructorID,
                                'userID' => $instructor_user->userID,
                                'firstName' => $instructor_user->firstName,
                                'lastName' => $instructor_user->lastName,
                                'biography' => $instructor->biography,
                                'profileImage' => $instructor_user->profileImage,
                            ];
                        }
                    }
                }
            }

            $course_categories = $this->courseCategoryBll->get_categories_by_course_id($course->courseID);
            $tmp_course_categories = [];
            if (!empty($course_categories)) {
                foreach ($course_categories as $course_category) {
                    $category = $this->categoryBll->get_category($course_category->categoryID);
                    if ($category) {
                        $tmp_course_categories[] = [
                            'categoryID' => $course_category->categoryID,
                            'categoryName' => $category->name,
                        ];
                    }
                }
            }

            $course_images = $this->courseImageBll->get_images_by_course_id($course->courseID);
            $tmp_course_images = [];
            if (!empty($course_images)) {
                foreach ($course_images as $course_image) {
                    $tmp_course_images[] = [
                        'imageID' => $course_image->imageID,
                        'imagePath' => $course_image->imagePath
                    ];
                }
            }

            $course_requirements = $this->courseRequirementBll->get_requirements_by_course_id($course->courseID);
            $tmp_course_requirements = [];
            if (!empty($course_requirements)) {
                foreach ($course_requirements as $course_requirement) {
                    $tmp_course_requirements[] = [
                        "requirementID" => $course_requirement->requirementID,
                        "requirement" => $course_requirement->requirement,
                    ];
                }
            }

            $course_objectives = $this->courseObjectiveBll->get_objectives_by_course_id($course->courseID);
            $tmp_course_objectives = [];
            if (!empty($course_objectives)) {
                foreach ($course_objectives as $course_objective) {
                    $tmp_course_objectives[] = [
                        "objectiveID" => $course_objective->objectiveID,
                        "objective" => $course_objective->objective,
                    ];
                }
            }

            $course_chapters = $this->chapterBll->get_chapters_by_course_id($course->courseID);
            $tmp_course_chapters_lessons_resources_videos = [];
            if (!empty($course_chapters)) {
                foreach ($course_chapters as $course_chapter) {
                    $tmp_chapter_lessons_resources_video = [];
                    $course_lessons = $this->lessonBll->get_lessons_by_chapter_id($course_chapter->chapterID);
                    foreach ($course_lessons as $course_lesson) {
                        $resources = $this->resourceBll->get_resources_by_lesson_id($course_lesson->lessonID);
                        $videos = $this->videoBll->get_videos_by_lesson($course_lesson->lessonID);
                        $tmp_lesson_resources = [];
                        $tmp_lesson_videos = [];

                        foreach ($resources as $resource) {
                            $tmp_lesson_resources[] = [
                                "resourceID" => $resource->resourceID,
                                "resourceTitle" => $resource->title,
                                "resourcePath" => $resource->resourcePath,
                                "resourceSortOrder" => $resource->sortOrder,
                            ];
                        }

                        foreach ($videos as $video) {
                            $tmp_lesson_videos[] = [
                                "videoID" => $video->videoID,
                                "videoTitle" => $video->title,
                                "videoURL" => $video->url,
                                "videoSortOrder" => $video->sortOrder,
                            ];
                        }

                        $tmp_chapter_lessons_resources_video[] = [
                            "lessonID" => $course_lesson->lessonID,
                            "lessonTitle" => $course_lesson->title,
                            "lessonContent" => $course_lesson->content,
                            "lessonResources" => $tmp_lesson_resources,
                            "lessonVideos" => $tmp_lesson_videos,
                        ];
                    }
                    $tmp_course_chapters_lessons_resources_videos[] = [
                        "chapterID" => $course_chapter->chapterID,
                        "chapterTitle" => $course_chapter->title,
                        "chapterDescription" => $course_chapter->description,
                        "chapterLessons" => $tmp_chapter_lessons_resources_video
                    ];
                }
            }

            $course_details = [
                'courseID' => $course->courseID,
                'title' => $course->title,
                'description' => $course->description,
                'price' => $course->price,
                'createdBy' => $course->createdBy,
                'instructors' => $instructors_info,
                'categories' => $tmp_course_categories,
                'images' => $tmp_course_images,
                'requirements' => $tmp_course_requirements,
                'objectives' => $tmp_course_objectives,
                'chapters' => $tmp_course_chapters_lessons_resources_videos,
            ];

            return new ServiceResponse(true, 'Lấy thông tin khóa học thành công', $course_details);
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi lấy thông tin khóa học: ' . $e->getMessage());
        }
    }
}