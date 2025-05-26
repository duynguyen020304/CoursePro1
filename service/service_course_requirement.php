<?php
require_once __DIR__ . '/../model/bll/course_requirement_bll.php';
require_once __DIR__ . '/../model/dto/course_requirement_dto.php';
require_once __DIR__ . '/service_response.php';

class CourseRequirementService
{
    private CourseRequirementBLL $bll;

    public function __construct()
    {
        $this->bll = new CourseRequirementBLL();
    }

    public function create(string $courseID, string $requirement): ServiceResponse
    {
        $newCourseRequirementID = str_replace('.', '_', uniqid('req_', true));
        $dto = new CourseRequirementDTO($newCourseRequirementID, $courseID, $requirement);
        try {
            $ok = $this->bll->create($dto);
            if ($ok) {
                return new ServiceResponse(true, 'Tạo yêu cầu khóa học thành công', $dto);
            }
            return new ServiceResponse(false, 'Tạo yêu cầu khóa học thất bại');
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi tạo yêu cầu: ' . $e->getMessage());
        }
    }

    public function update(string $requirementID, string $courseID, string $requirement): ServiceResponse
    {
        $dto = new CourseRequirementDTO($requirementID, $courseID, $requirement);
        try {
            $ok = $this->bll->update($dto);
            if ($ok) {
                return new ServiceResponse(true, 'Cập nhật yêu cầu khóa học thành công');
            }
            return new ServiceResponse(false, 'Cập nhật yêu cầu khóa học thất bại');
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi cập nhật yêu cầu: ' . $e->getMessage());
        }
    }

    public function delete(string $requirementID): ServiceResponse
    {
        try {
            $obj = $this->bll->get_requirement_by_requirement_id($requirementID);
            if (!$obj) {
                return new ServiceResponse(false, 'Yêu cầu không tồn tại');
            }
            $ok = $this->bll->delete($requirementID);
            if ($ok) {
                return new ServiceResponse(true, 'Xóa yêu cầu thành công');
            }
            return new ServiceResponse(false, 'Xóa yêu cầu thất bại');
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi xóa yêu cầu: ' . $e->getMessage());
        }
    }

    public function get_requirement_by_requirement_id(string $requirementID): ServiceResponse
    {
        try {
            $obj = $this->bll->get_requirement_by_requirement_id($requirementID);
            if ($obj) {
                return new ServiceResponse(true, 'Lấy yêu cầu thành công', $obj);
            }
            return new ServiceResponse(false, 'Yêu cầu không tồn tại');
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi lấy yêu cầu: ' . $e->getMessage());
        }
    }

    public function get_requirements_by_course_id(string $courseID): ServiceResponse
    {
        try {
            $objs = $this->bll->get_requirements_by_course_id($courseID);
            return new ServiceResponse(true, 'Lấy danh sách yêu cầu thành công', $objs);
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi lấy danh sách yêu cầu: ' . $e->getMessage());
        }
    }
}