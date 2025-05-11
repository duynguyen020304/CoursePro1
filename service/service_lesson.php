<?php
// File: service/service_lesson.php

require_once __DIR__ . '/../model/bll/lesson_bll.php';
require_once __DIR__ . '/../model/dto/lesson_dto.php';
require_once __DIR__ . '/service_response.php';

class LessonService
{
    private LessonBLL $bll;

    public function __construct()
    {
        $this->bll = new LessonBLL();
    }

    /** Lấy một lesson theo ID */
    public function get_lesson(string $lessonID): ServiceResponse
    {
        try {
            $dto = $this->bll->get_lesson($lessonID);
            if ($dto) {
                return new ServiceResponse(true, 'Lấy lesson thành công', $dto);
            }
            return new ServiceResponse(false, 'Lesson không tồn tại');
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi lấy lesson: ' . $e->getMessage());
        }
    }

    /** Lấy tất cả lesson theo chapterID */
    public function get_lessons_by_chapter(string $chapterID): ServiceResponse
    {
        try {
            $list = $this->bll->get_lessons_by_chapter($chapterID);
            return new ServiceResponse(true, 'Lấy danh sách lesson thành công', $list);
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi lấy danh sách: ' . $e->getMessage());
        }
    }

    /** Tạo mới lesson */
    public function create_lesson(string $courseID, string $chapterID, string $title, ?string $content, int $sortOrder): ServiceResponse
    {
        $lessonID = uniqid('lesson_', true);
        $dto = new LessonDTO($lessonID, $courseID, $chapterID, $title, $content, $sortOrder);
        try {
            $ok = $this->bll->create_lesson($dto);
            if ($ok) {
                return new ServiceResponse(true, 'Tạo lesson thành công', $dto);
            }
            return new ServiceResponse(false, 'Tạo lesson thất bại');
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi tạo lesson: ' . $e->getMessage());
        }
    }

    /** Cập nhật lesson */
    public function update_lesson(string $lessonID, string $courseID, string $chapterID, string $title, ?string $content, int $sortOrder): ServiceResponse
    {
        $dto = new LessonDTO($lessonID, $courseID, $chapterID, $title, $content, $sortOrder);
        try {
            $ok = $this->bll->update_lesson($dto);
            if ($ok) {
                return new ServiceResponse(true, 'Cập nhật lesson thành công');
            }
            return new ServiceResponse(false, 'Cập nhật lesson thất bại');
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi cập nhật: ' . $e->getMessage());
        }
    }

    /** Xóa lesson */
    public function delete_lesson(string $lessonID): ServiceResponse
    {
        try {
            $exists = $this->bll->get_lesson($lessonID);
            if (!$exists) {
                return new ServiceResponse(false, 'Lesson không tồn tại');
            }
            $this->bll->delete_lesson($lessonID);
            return new ServiceResponse(true, 'Xóa lesson thành công');
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi xóa lesson: ' . $e->getMessage());
        }
    }
}
