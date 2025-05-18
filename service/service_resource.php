<?php

require_once __DIR__ . '/../model/bll/resource_bll.php';
require_once __DIR__ . '/../model/dto/resource_dto.php';
require_once __DIR__ . '/service_response.php';

class ResourceService
{
    private ResourceBLL $resourceBll;

    public function __construct()
    {
        $this->resourceBll = new ResourceBLL();
    }

    public function get_resource_by_id(string $resourceID): ServiceResponse
    {
        try {
            $resource = $this->resourceBll->get_resource_by_id($resourceID);
            if (!$resource) {
                return new ServiceResponse(false, 'Resource không tồn tại');
            }
            return new ServiceResponse(true, 'Lấy thông tin resource thành công', $resource);
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi: ' . $e->getMessage());
        }
    }

    public function get_resources_by_lesson(string $lessonID): ServiceResponse
    {
        try {
            $resources = $this->resourceBll->get_resources_by_lesson($lessonID);
            return new ServiceResponse(true, 'Lấy danh sách resource thành công', $resources);
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi: ' . $e->getMessage());
        }
    }

    public function get_all_resources(): ServiceResponse
    {
        try {
            $resources = $this->resourceBll->get_all_resources();
            return new ServiceResponse(true, 'Lấy danh sách tất cả resource thành công', $resources);
        } catch (Exception $e) {
            return new ServiceResponse(false, 'Lỗi: ' . $e->getMessage());
        }
    }

    public function create_resource(string $lessonID, string $resourcePath, ?string $title = null, int $sortOrder = 0): ServiceResponse
    {
        $resourceID = str_replace('.', '_', uniqid('resource_', true));
        $dto = new ResourceDTO($resourceID, $lessonID, $resourcePath, $title, $sortOrder);
        if ($this->resourceBll->create_resource($dto)) {
            return new ServiceResponse(true, 'Tạo resource thành công', $dto);
        }
        return new ServiceResponse(false, 'Tạo resource thất bại');
    }

    public function update_resource(string $resourceID, string $lessonID, string $resourcePath, ?string $title = null, int $sortOrder = 0): ServiceResponse
    {
        $existing = $this->resourceBll->get_resource_by_id($resourceID);
        if (!$existing) {
            return new ServiceResponse(false, 'Resource không tồn tại');
        }
        $dto = new ResourceDTO($resourceID, $lessonID, $resourcePath, $title, $sortOrder, $existing->created_at);
        if ($this->resourceBll->update_resource($dto)) {
            return new ServiceResponse(true, 'Cập nhật resource thành công');
        }
        return new ServiceResponse(false, 'Cập nhật resource thất bại');
    }

    public function delete_resource(string $resourceID): ServiceResponse
    {
        $existing = $this->resourceBll->get_resource_by_id($resourceID);
        if (!$existing) {
            return new ServiceResponse(false, 'Resource không tồn tại');
        }
        if ($this->resourceBll->delete_resource($resourceID)) {
            return new ServiceResponse(true, 'Xóa resource thành công');
        }
        return new ServiceResponse(false, 'Xóa resource thất bại');
    }
}