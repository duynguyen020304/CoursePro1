<?php
// File: service/service_order.php

require_once __DIR__ . '/../model/bll/order_bll.php';
require_once __DIR__ . '/../model/dto/order_dto.php';
require_once __DIR__ . '/service_response.php';

class OrderService
{
    private OrderBLL $bll;

    public function __construct()
    {
        $this->bll = new OrderBLL();
    }

    /**
     * Tạo mới đơn hàng
     */
    public function create_order(string $orderID, string $userID, DateTime $orderDate, float $totalAmount): ServiceResponse
    {
        $dto = new OrderDTO($orderID, $userID, $orderDate, $totalAmount);
        $ok = $this->bll->create_order($dto);
        if ($ok) {
            return new ServiceResponse(true, 'Tạo đơn hàng thành công', $dto);
        }
        return new ServiceResponse(false, 'Tạo đơn hàng thất bại');
    }

    /**
     * Lấy đơn hàng theo ID
     */
    public function get_order(string $orderID): ServiceResponse
    {
        $dto = $this->bll->get_order($orderID);
        if ($dto) {
            return new ServiceResponse(true, 'Lấy đơn hàng thành công', $dto);
        }
        return new ServiceResponse(false, 'Không tìm thấy đơn hàng này');
    }

    /**
     * Lấy danh sách đơn hàng của user
     */
    public function get_orders_by_user(string $userID): ServiceResponse
    {
        $list = $this->bll->get_orders_by_user($userID);
        return new ServiceResponse(true, 'Lấy danh sách đơn hàng thành công', $list);
    }

    /**
     * Cập nhật đơn hàng
     */
    public function update_order(string $orderID, string $userID, DateTime $orderDate, float $totalAmount): ServiceResponse
    {
        $dto = new OrderDTO($orderID, $userID, $orderDate, $totalAmount);
        $ok = $this->bll->update_order($dto);
        if ($ok) {
            return new ServiceResponse(true, 'Cập nhật đơn hàng thành công');
        }
        return new ServiceResponse(false, 'Cập nhật đơn hàng thất bại');
    }

    /**
     * Xóa đơn hàng
     */
    public function delete_order(string $orderID): ServiceResponse
    {
        $ok = $this->bll->delete_order($orderID);
        if ($ok) {
            return new ServiceResponse(true, 'Xóa đơn hàng thành công');
        }
        return new ServiceResponse(false, 'Xóa đơn hàng thất bại hoặc không tồn tại');
    }
}
