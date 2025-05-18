<?php
require_once __DIR__ . '/../model/bll/student_bll.php';
require_once __DIR__ . '/../model/dto/student_dto.php';
require_once __DIR__ . '/../model/bll/user_bll.php';
require_once __DIR__ . '/../model/dto/user_dto.php';
require_once __DIR__ . '/service_response.php';

class StudentService
{
    private StudentBLL $studentBLL;
    private UserBLL $userBLL;

    public function __construct()
    {
        $this->studentBLL = new StudentBLL();
        $this->userBLL = new UserBLL();
    }

    public function create_student(string $studentID, string $userID): ServiceResponse
    {
        $dto = new StudentDTO($studentID, $userID);
        $ok = $this->studentBLL->create_student($dto);
        if ($ok) {
            return new ServiceResponse(true, 'Tạo sinh viên thành công', $dto);
        }
        return new ServiceResponse(false, 'Tạo sinh viên thất bại');
    }

    public function get_student(string $studentID): ServiceResponse
    {
        $dto = $this->studentBLL->get_student($studentID);
        if ($dto) {
            return new ServiceResponse(true, 'Lấy sinh viên thành công', $dto);
        }
        return new ServiceResponse(false, 'Sinh viên không tồn tại');
    }

    public function get_student_by_user_id(string $userID): ServiceResponse {
        $dto = $this->studentBLL->get_student_by_user_id($userID);
        if ($dto) {
            return new ServiceResponse(true, 'Lấy sinh viên thành công', $dto);
        }
        $studentDto = $this->userBLL->get_user_by_id($userID);
        if (empty($studentDto->userID)) {
            return new ServiceResponse(false, 'Dữ liệu giảng viên không hợp lệ (Thiếu UserID)');
        }
        return new ServiceResponse(false, 'Sinh viên không tồn tại');
    }

    public function get_all_students(): ServiceResponse
    {
        $list = $this->studentBLL->get_all_students();
        return new ServiceResponse(true, 'Lấy danh sách sinh viên thành công', $list);
    }

    public function update_student(string $studentID, string $userID): ServiceResponse
    {
        $dto = new StudentDTO($studentID, $userID);
        $ok = $this->studentBLL->update_student($dto);
        if ($ok) {
            return new ServiceResponse(true, 'Cập nhật sinh viên thành công');
        }
        return new ServiceResponse(false, 'Cập nhật sinh viên thất bại');
    }

    public function delete_student(string $studentID): ServiceResponse
    {
        $ok = $this->studentBLL->delete_student($studentID);
        if ($ok) {
            return new ServiceResponse(true, 'Xóa sinh viên thành công');
        }
        return new ServiceResponse(false, 'Xóa sinh viên thất bại hoặc không tồn tại');
    }
}
