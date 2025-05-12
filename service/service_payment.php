<?php
// File: service/service_payment.php

require_once __DIR__ . '/../model/bll/payment_bll.php';
require_once __DIR__ . '/../model/dto/payment_dto.php';
require_once __DIR__ . '/service_response.php';

class PaymentService
{
    private PaymentBLL $bll;

    public function __construct()
    {
        $this->bll = new PaymentBLL();
    }

    /**
     * Tạo mới thanh toán
     *
     * @param string $orderID
     * @param DateTime $paymentDate
     * @param string|null $paymentMethod
     * @param string|null $paymentStatus
     * @param float $amount
     * @return ServiceResponse
     */
    public function create_payment(string $orderID, DateTime $paymentDate, ?string $paymentMethod, ?string $paymentStatus, float $amount): ServiceResponse
    {
        $paymentID = uniqid('payment_', true);
        $dto = new PaymentDTO($paymentID, $orderID, $paymentDate, $paymentMethod, $paymentStatus, $amount);

        $ok = $this->bll->create_payment($dto);
        if ($ok) {
            return new ServiceResponse(true, 'Tạo thanh toán thành công', $dto);
        }
        return new ServiceResponse(false, 'Tạo thanh toán thất bại');
    }

    /**
     * Lấy thanh toán theo OrderID
     *
     * @param string $orderID
     * @return ServiceResponse
     */
    public function get_payment_by_order(string $orderID): ServiceResponse
    {
        $dto = $this->bll->get_payment_by_order($orderID);
        if ($dto) {
            return new ServiceResponse(true, 'Lấy thanh toán thành công', $dto);
        }
        return new ServiceResponse(false, 'Không tìm thấy thanh toán cho đơn hàng này');
    }

    /**
     * Cập nhật thanh toán
     *
     * @param string $paymentID
     * @param string $orderID
     * @param DateTime $paymentDate
     * @param string|null $paymentMethod
     * @param string|null $paymentStatus
     * @param float $amount
     * @return ServiceResponse
     */
    public function update_payment(string $paymentID, string $orderID, DateTime $paymentDate, ?string $paymentMethod, ?string $paymentStatus, float $amount): ServiceResponse
    {
        $dto = new PaymentDTO($paymentID, $orderID, $paymentDate, $paymentMethod, $paymentStatus, $amount);
        $ok = $this->bll->update_payment($dto);
        if ($ok) {
            return new ServiceResponse(true, 'Cập nhật thanh toán thành công');
        }
        return new ServiceResponse(false, 'Cập nhật thanh toán thất bại');
    }

    /**
     * Xóa thanh toán theo PaymentID
     *
     * @param string $paymentID
     * @return ServiceResponse
     */
    public function delete_payment(string $paymentID): ServiceResponse
    {
        $ok = $this->bll->delete_payment($paymentID);
        if ($ok) {
            return new ServiceResponse(true, 'Xóa thanh toán thành công');
        }
        return new ServiceResponse(false, 'Xóa thanh toán thất bại hoặc không tồn tại');
    }
}
