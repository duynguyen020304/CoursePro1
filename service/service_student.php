<?php
// File: service/service_student.php

require_once __DIR__ . '/../model/bll/student_bll.php';
require_once __DIR__ . '/../model/dto/student_dto.php';
require_once __DIR__ . '/service_response.php';

class StudentService
{
    private StudentBLL $bll;

    public function __construct()
    {
        $this->bll = new StudentBLL();
    }

    /**
     * Tạo mới sinh viên
     *
     * @param string $studentID
     * @param string $userID
     * @param DateTime $enrollmentDate
     * @param string|null $completedCourses
     * @return ServiceResponse
     */
    public function create_student(string $studentID, string $userID): ServiceResponse
    {
        $dto = new StudentDTO($studentID, $userID);
        $ok = $this->bll->create_student($dto);
        if ($ok) {
            return new ServiceResponse(true, 'Tạo sinh viên thành công', $dto);
        }
        return new ServiceResponse(false, 'Tạo sinh viên thất bại');
    }

    /**
     * Lấy sinh viên theo ID
     *
     * @param string $studentID
     * @return ServiceResponse
     */
    public function get_student(string $studentID): ServiceResponse
    {
        $dto = $this->bll->get_student($studentID);
        if ($dto) {
            return new ServiceResponse(true, 'Lấy sinh viên thành công', $dto);
        }
        return new ServiceResponse(false, 'Sinh viên không tồn tại');
    }

    /**
     * Lấy tất cả sinh viên
     *
     * @return ServiceResponse
     */
    public function get_all_students(): ServiceResponse
    {
        $list = $this->bll->get_all_students();
        return new ServiceResponse(true, 'Lấy danh sách sinh viên thành công', $list);
    }

    /**
     * Cập nhật sinh viên
     *
     * @param string $studentID
     * @param string $userID
     * @param DateTime $enrollmentDate
     * @param string|null $completedCourses
     * @return ServiceResponse
     */
    public function update_student(string $studentID, string $userID): ServiceResponse
    {
        $dto = new StudentDTO($studentID, $userID);
        $ok = $this->bll->update_student($dto);
        if ($ok) {
            return new ServiceResponse(true, 'Cập nhật sinh viên thành công');
        }
        return new ServiceResponse(false, 'Cập nhật sinh viên thất bại');
    }

    /**
     * Xóa sinh viên
     *
     * @param string $studentID
     * @return ServiceResponse
     */
    public function delete_student(string $studentID): ServiceResponse
    {
        $ok = $this->bll->delete_student($studentID);
        if ($ok) {
            return new ServiceResponse(true, 'Xóa sinh viên thành công');
        }
        return new ServiceResponse(false, 'Xóa sinh viên thất bại hoặc không tồn tại');
    }
}
