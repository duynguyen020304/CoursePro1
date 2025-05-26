<?php

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

    public function get_payment_by_order_id(string $orderID): ServiceResponse
    {
        $dto = $this->bll->get_payment_by_order_id($orderID);
        if ($dto) {
            return new ServiceResponse(true, 'Lấy thanh toán thành công', $dto);
        }
        return new ServiceResponse(false, 'Không tìm thấy thanh toán cho đơn hàng này');
    }

    public function update_payment(string $paymentID, string $orderID, DateTime $paymentDate, ?string $paymentMethod, ?string $paymentStatus, float $amount): ServiceResponse
    {
        $dto = new PaymentDTO($paymentID, $orderID, $paymentDate, $paymentMethod, $paymentStatus, $amount);
        $ok = $this->bll->update_payment($dto);
        if ($ok) {
            return new ServiceResponse(true, 'Cập nhật thanh toán thành công');
        }
        return new ServiceResponse(false, 'Cập nhật thanh toán thất bại');
    }

    public function delete_payment(string $paymentID): ServiceResponse
    {
        $ok = $this->bll->delete_payment($paymentID);
        if ($ok) {
            return new ServiceResponse(true, 'Xóa thanh toán thành công');
        }
        return new ServiceResponse(false, 'Xóa thanh toán thất bại hoặc không tồn tại');
    }
}