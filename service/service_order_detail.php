<?php
// File: service/service_order_detail.php

require_once __DIR__ . '/../model/bll/order_detail_bll.php';
require_once __DIR__ . '/../model/dto/order_detail_dto.php';
require_once __DIR__ . '/service_response.php';

class OrderDetailService
{
    private OrderDetailBLL $bll;

    public function __construct()
    {
        $this->bll = new OrderDetailBLL();
    }

    /**
     * Thêm chi tiết đơn hàng
     */
    public function add_detail(string $orderID, string $courseID, float $price): ServiceResponse
    {
        $dto = new OrderDetailDTO($orderID, $courseID, $price);
        $ok = $this->bll->add_detail($dto);
        if ($ok) {
            return new ServiceResponse(true, 'Thêm chi tiết đơn hàng thành công', $dto);
        }
        return new ServiceResponse(false, 'Thêm chi tiết đơn hàng thất bại');
    }

    /**
     * Lấy chi tiết theo đơn hàng
     */
    public function get_details_by_order(string $orderID): ServiceResponse
    {
        $list = $this->bll->get_details_by_order($orderID);
        return new ServiceResponse(true, 'Lấy chi tiết đơn hàng thành công', $list);
    }

    /**
     * Cập nhật chi tiết đơn hàng
     */
    public function update_detail(string $orderID, string $courseID, float $price): ServiceResponse
    {
        $dto = new OrderDetailDTO($orderID, $courseID, $price);
        $ok = $this->bll->update_detail($dto);
        if ($ok) {
            return new ServiceResponse(true, 'Cập nhật chi tiết đơn hàng thành công');
        }
        return new ServiceResponse(false, 'Cập nhật chi tiết đơn hàng thất bại');
    }

    /**
     * Xóa chi tiết đơn hàng
     */
    public function delete_detail(string $orderID, string $courseID): ServiceResponse
    {
        $ok = $this->bll->delete_detail($orderID, $courseID);
        if ($ok) {
            return new ServiceResponse(true, 'Xóa chi tiết đơn hàng thành công');
        }
        return new ServiceResponse(false, 'Xóa chi tiết đơn hàng thất bại');
    }
}
