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
     * @param string   $orderID
     * @param DateTime $paymentDate
     * @param string|null $paymentMethod
     * @param string|null $paymentStatus
     * @param float    $amount
     * @return ServiceResponse
     */
    public function create_payment(string $orderID, DateTime $paymentDate, ?string $paymentMethod, ?string $paymentStatus, float $amount): ServiceResponse
    {
        // Sinh PaymentID
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
}