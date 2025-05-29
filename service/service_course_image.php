<?php

require_once __DIR__ . '/../model/bll/course_image_bll.php';
require_once __DIR__ . '/../model/dto/course_image_dto.php';
require_once __DIR__ . '/service_response.php';

class CourseImageService
{
    private CourseImageBLL $bll;

    public function __construct()
    {
        $this->bll = new CourseImageBLL();
    }

    public function get_images(string $courseID): ServiceResponse
    {
        try {
            $images = $this->bll->get_images_by_course_id($courseID);
            return new ServiceResponse(true, 'Lấy ảnh thành công', $images);
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi lấy ảnh: ' . $e->getMessage());
        }
    }

    public function add_image(string $imageID, string $courseID, string $imagePath, ?string $caption = null, int $sortOrder = 0): ServiceResponse
    {
        try {
            $dto = new CourseImageDTO($imageID, $courseID, $imagePath, $caption, $sortOrder);
            $ok = $this->bll->create_image($dto);
            if ($ok) {
                return new ServiceResponse(true, 'Thêm ảnh thành công', $dto);
            }
            return new ServiceResponse(false, 'Thêm ảnh thất bại');
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi thêm ảnh: ' . $e->getMessage());
        }
    }

    public function unlink_image_course(string $imageID, string $courseID): ServiceResponse
    {
        try {
            $ok = $this->bll->unlink_image_course($imageID, $courseID);
            if ($ok) {
                return new ServiceResponse(true, 'Xóa ảnh thành công');
            }
            return new ServiceResponse(false, 'Ảnh không tồn tại');
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi xóa ảnh: ' . $e->getMessage());
        }
    }
}