<?php
// service/service_course_instructor.php

require_once __DIR__ . '/../model/dto/course_instructor_dto.php';
require_once __DIR__ . '/../model/bll/course_instructor_bll.php';
require_once __DIR__ . '/service_response.php';  // giả sử bạn có class Response { public $success, $message, $data; … }

class CourseInstructorService
{
    private CourseInstructorBLL $bll;

    public function __construct()
    {
        $this->bll = new CourseInstructorBLL();
    }

    public function getByCourse($courseID) : ServiceResponse
    {
        $data = $this->bll->get_by_course($courseID);
        return new ServiceResponse(true, 'Lấy danh sách giảng viên thành công', $data);
    }

    public function add($courseID, $instructorID) :  ServiceResponse
    {
        if ($this->bll->add($courseID, $instructorID)) {
            return new ServiceResponse(true, 'Thêm mapping thành công');
        }
        return new ServiceResponse(false, 'Thêm mapping thất bại');
    }

    public function update($oldCourseID, $oldInstructorID, $newCourseID, $newInstructorID) :  ServiceResponse
    {
        if ($this->bll->update($oldCourseID, $oldInstructorID, $newCourseID, $newInstructorID)) {
            return new ServiceResponse(true, 'Cập nhật mapping thành công');
        }
        return new ServiceResponse(false, 'Cập nhật mapping thất bại');
    }

    public function delete($courseID, $instructorID) :  ServiceResponse
    {
        if ($this->bll->delete($courseID, $instructorID)) {
            return new ServiceResponse(true, 'Xóa mapping thành công');
        }
        return new ServiceResponse(false, 'Xóa mapping thất bại');
    }
}
