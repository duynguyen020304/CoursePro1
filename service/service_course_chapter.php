<?php
// File: service/service_course_chapter.php

require_once __DIR__ . '/../model/bll/course_chapter_bll.php';
require_once __DIR__ . '/../model/dto/course_chapter_dto.php';
require_once __DIR__ . '/service_response.php';

class CourseChapterService
{
    private CourseChapterBLL $bll;

    public function __construct()
    {
        $this->bll = new CourseChapterBLL();
    }

    // Lấy danh sách chương của một khóa học
    public function get_chapters_by_course(string $courseID): ServiceResponse
    {
        try {
            $list = $this->bll->get_chapters_by_course($courseID);
            return new ServiceResponse(true, 'Lấy chương thành công', $list);
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi lấy chương: ' . $e->getMessage());
        }
    }

    // Tạo mới chương
    public function create_chapter(string $courseID, string $title, ?string $description, int $sortOrder): ServiceResponse
    {
        $chapterID = uniqid('chap_', true);
        $dto = new ChapterDTO($chapterID, $courseID, $title, $description, $sortOrder);
        try {
            $ok = $this->bll->create_chapter($dto);
            if ($ok) {
                return new ServiceResponse(true, 'Tạo chương thành công', $dto);
            }
            return new ServiceResponse(false, 'Tạo chương thất bại');
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi tạo chương: ' . $e->getMessage());
        }
    }

    // Cập nhật chương
    public function update_chapter(string $chapterID, string $title, ?string $description, int $sortOrder): ServiceResponse
    {
        $dto = new ChapterDTO($chapterID, '', $title, $description, $sortOrder);
        try {
            $ok = $this->bll->update_chapter($dto);
            if ($ok) {
                return new ServiceResponse(true, 'Cập nhật chương thành công');
            }
            return new ServiceResponse(false, 'Cập nhật chương thất bại');
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi cập nhật chương: ' . $e->getMessage());
        }
    }

    // Xóa chương
    public function delete_chapter(string $chapterID): ServiceResponse
    {
        try {
            $exists = array_filter($this->bll->get_chapters_by_course(''), fn($c) => $c->chapterID === $chapterID);
            // Nếu cần, có thể kiểm tra tồn tại bằng cách gọi get và xử lý
            $ok = $this->bll->delete_chapter($chapterID);
            if ($ok) {
                return new ServiceResponse(true, 'Xóa chương thành công');
            }
            return new ServiceResponse(false, 'Xóa chương thất bại');
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi xóa chương: ' . $e->getMessage());
        }
    }
}
