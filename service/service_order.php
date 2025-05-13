<?php
// File: service/service_order.php

require_once __DIR__ . '/../model/bll/order_bll.php';
require_once __DIR__ . '/../model/dto/order_dto.php';

class ServiceResponse {
    public bool $success;
    public string $message;
    public $data;

    public function __construct(bool $success, string $message = '', $data = null) {
        $this->success = $success;
        $this->message = $message;
        $this->data = $data;
    }
}

class OrderService {
    private OrderBLL $bll;

    public function __construct() {
        $this->bll = new OrderBLL();
    }

    /**
     * Tạo đơn hàng mới
     */
    public function create_order(string $orderID, string $userID, DateTime $orderDate, float $totalAmount): ServiceResponse {
        $order = new OrderDTO($orderID, $userID, $orderDate, $totalAmount);
        $success = $this->bll->create_order($order);
        if ($success) {
            return new ServiceResponse(true, 'Tạo đơn hàng thành công', $order);
        }
        return new ServiceResponse(false, 'Tạo đơn hàng thất bại');
    }

    /**
     * Lấy đơn hàng theo ID
     */
    public function get_order(string $orderID): ServiceResponse {
        $order = $this->bll->get_order($orderID);
        if ($order) {
            return new ServiceResponse(true, 'Lấy đơn hàng thành công', $order);
        }
        return new ServiceResponse(false, 'Không tìm thấy đơn hàng');
    }

    /**
     * Lấy tất cả đơn hàng của user
     */
    public function get_orders_by_user(string $userID): ServiceResponse {
        $orders = $this->bll->get_orders_by_user($userID);
        return new ServiceResponse(true, 'Lấy danh sách đơn hàng thành công', $orders);
    }

    /**
     * Cập nhật đơn hàng
     */
    public function update_order(string $orderID, string $userID, DateTime $orderDate, float $totalAmount): ServiceResponse {
        $order = new OrderDTO($orderID, $userID, $orderDate, $totalAmount);
        $success = $this->bll->update_order($order);
        if ($success) {
            return new ServiceResponse(true, 'Cập nhật đơn hàng thành công');
        }
        return new ServiceResponse(false, 'Cập nhật đơn hàng thất bại');
    }

    /**
     * Xóa đơn hàng
     */
    public function delete_order(string $orderID): ServiceResponse {
        $success = $this->bll->delete_order($orderID);
        if ($success) {
            return new ServiceResponse(true, 'Xóa đơn hàng thành công');
        }
        return new ServiceResponse(false, 'Xóa đơn hàng thất bại');
    }
}
