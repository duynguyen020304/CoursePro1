<?php

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

    public function create_order(string $orderID, string $userID, DateTime $orderDate, float $totalAmount): ServiceResponse
    {
        $dto = new OrderDTO($orderID, $userID, $orderDate, $totalAmount);
        $ok = $this->bll->create_order($dto);
        if ($ok) {
            return new ServiceResponse(true, 'Tạo đơn hàng thành công', $dto);
        }
        return new ServiceResponse(false, 'Tạo đơn hàng thất bại');
    }

    public function get_order_by_order_id(string $orderID): ServiceResponse
    {
        $dto = $this->bll->get_order_by_order_id($orderID);
        if ($dto) {
            return new ServiceResponse(true, 'Lấy đơn hàng thành công', $dto);
        }
        return new ServiceResponse(false, 'Không tìm thấy đơn hàng này');
    }

    public function get_orders_by_user_id(string $userID): ServiceResponse
    {
        $list = $this->bll->get_orders_by_user_id($userID);
        return new ServiceResponse(true, 'Lấy danh sách đơn hàng thành công', $list);
    }

    public function update_order(string $orderID, string $userID, DateTime $orderDate, float $totalAmount): ServiceResponse
    {
        $dto = new OrderDTO($orderID, $userID, $orderDate, $totalAmount);
        $ok = $this->bll->update_order($dto);
        if ($ok) {
            return new ServiceResponse(true, 'Cập nhật đơn hàng thành công');
        }
        return new ServiceResponse(false, 'Cập nhật đơn hàng thất bại');
    }

    public function delete_order(string $orderID): ServiceResponse
    {
        $ok = $this->bll->delete_order($orderID);
        if ($ok) {
            return new ServiceResponse(true, 'Xóa đơn hàng thành công');
        }
        return new ServiceResponse(false, 'Xóa đơn hàng thất bại hoặc không tồn tại');
    }
}