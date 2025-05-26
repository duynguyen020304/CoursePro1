<?php
require_once __DIR__ . '/../model/bll/course_objective_bll.php';
require_once __DIR__ . '/../model/dto/course_objective_dto.php';
require_once __DIR__ . '/service_response.php';

class CourseObjectiveService
{
    private CourseObjectiveBLL $bll;

    public function __construct()
    {
        $this->bll = new CourseObjectiveBLL();
    }

    public function create(string $courseID, string $objective): ServiceResponse
    {
        $newCourseObjectiveID = str_replace('.', '_', uniqid('obj_', true));
        $dto = new CourseObjectiveDTO($newCourseObjectiveID, $courseID, $objective);
        try {
            $ok = $this->bll->create($dto);
            if ($ok) {
                return new ServiceResponse(true, 'Tạo mục tiêu khóa học thành công', $dto);
            }
            return new ServiceResponse(false, 'Tạo mục tiêu khóa học thất bại');
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi tạo mục tiêu: ' . $e->getMessage());
        }
    }

    public function update(string $objectiveID, string $courseID, string $objective): ServiceResponse
    {
        $dto = new CourseObjectiveDTO($objectiveID, $courseID, $objective);
        try {
            $ok = $this->bll->update($dto);
            if ($ok) {
                return new ServiceResponse(true, 'Cập nhật mục tiêu khóa học thành công');
            }
            return new ServiceResponse(false, 'Cập nhật mục tiêu khóa học thất bại');
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi cập nhật mục tiêu: ' . $e->getMessage());
        }
    }

    public function delete(string $objectiveID): ServiceResponse
    {
        try {
            $obj = $this->bll->get_objective_by_objective_id($objectiveID);
            if (!$obj) {
                return new ServiceResponse(false, 'Mục tiêu không tồn tại');
            }
            $ok = $this->bll->delete($objectiveID);
            if ($ok) {
                return new ServiceResponse(true, 'Xóa mục tiêu thành công');
            }
            return new ServiceResponse(false, 'Xóa mục tiêu thất bại');
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi xóa mục tiêu: ' . $e->getMessage());
        }
    }

    public function get_objective_by_objective_id(string $objectiveID): ServiceResponse
    {
        try {
            $obj = $this->bll->get_objective_by_objective_id($objectiveID);
            if ($obj) {
                return new ServiceResponse(true, 'Lấy mục tiêu thành công', $obj);
            }
            return new ServiceResponse(false, 'Mục tiêu không tồn tại');
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi lấy mục tiêu: ' . $e->getMessage());
        }
    }

    public function get_objectives_by_course_id(string $courseID): ServiceResponse
    {
        try {
            $objs = $this->bll->get_objectives_by_course_id($courseID);
            return new ServiceResponse(true, 'Lấy danh sách mục tiêu thành công', $objs);
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi khi lấy danh sách mục tiêu: ' . $e->getMessage());
        }
    }
}