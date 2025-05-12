<?php
// File: service/service_instructor.php

require_once __DIR__ . '/../model/bll/instructor_bll.php';
require_once __DIR__ . '/../model/dto/instructor_dto.php';
require_once __DIR__ . '/service_response.php';

class InstructorService
{
    private InstructorBLL $bll;

    public function __construct()
    {
        $this->bll = new InstructorBLL();
    }

    /**
     * Tạo mới giảng viên
     *
     * @param string $instructorID
     * @param string $userID
     * @param string|null $biography
     * @param string|null $profileImage
     * @return ServiceResponse
     */
    public function create_instructor(string $instructorID, string $userID, ?string $biography, ?string $profileImage): ServiceResponse
    {
        $dto = new InstructorDTO($instructorID, $userID, $biography, $profileImage);
        $ok = $this->bll->create_instructor($dto);
        if ($ok) {
            return new ServiceResponse(true, 'Tạo giảng viên thành công', $dto);
        }
        return new ServiceResponse(false, 'Tạo giảng viên thất bại');
    }

    /**
     * Lấy giảng viên theo ID
     *
     * @param string $instructorID
     * @return ServiceResponse
     */
    public function get_instructor(string $instructorID): ServiceResponse
    {
        $dto = $this->bll->get_instructor($instructorID);
        if ($dto) {
            return new ServiceResponse(true, 'Lấy giảng viên thành công', $dto);
        }
        return new ServiceResponse(false, 'Giảng viên không tồn tại');
    }

    /**
     * Lấy danh sách giảng viên
     *
     * @return ServiceResponse
     */
    public function get_all_instructors(): ServiceResponse
    {
        $list = $this->bll->get_all_instructors();
        return new ServiceResponse(true, 'Lấy danh sách giảng viên thành công', $list);
    }

    /**
     * Cập nhật giảng viên
     *
     * @param string $instructorID
     * @param string $userID
     * @param string|null $biography
     * @param string|null $profileImage
     * @return ServiceResponse
     */
    public function update_instructor(string $instructorID, string $userID, ?string $biography, ?string $profileImage): ServiceResponse
    {
        $dto = new InstructorDTO($instructorID, $userID, $biography, $profileImage);
        $ok = $this->bll->update_instructor($dto);
        if ($ok) {
            return new ServiceResponse(true, 'Cập nhật giảng viên thành công');
        }
        return new ServiceResponse(false, 'Cập nhật giảng viên thất bại');
    }

    /**
     * Xóa giảng viên
     *
     * @param string $instructorID
     * @return ServiceResponse
     */
    public function delete_instructor(string $instructorID): ServiceResponse
    {
        $ok = $this->bll->delete_instructor($instructorID);
        if ($ok) {
            return new ServiceResponse(true, 'Xóa giảng viên thành công');
        }
        return new ServiceResponse(false, 'Xóa giảng viên thất bại hoặc không tồn tại');
    }
}
