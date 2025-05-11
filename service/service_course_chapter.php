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

    /**
     * Lấy danh sách chương của một khóa học
     *
     * @param string $courseID
     * @return ServiceResponse
     */
    public function get_chapters_by_course(string $courseID): ServiceResponse
    {
        try {
            $chapters = $this->bll->get_chapters_by_course($courseID);
            return new ServiceResponse(true, 'Lấy danh sách chương thành công', $chapters);
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi lấy chương: ' . $e->getMessage());
        }
    }
}
