<?php

class PaymentDTO
{
    public string $paymentID;
    public string $orderID;
    public DateTime $paymentDate;
    public ?string $paymentMethod;
    public ?string $paymentStatus;
    public float $amount;
    public ?string $created_at;

    public function __construct(string $paymentID, string $orderID, DateTime $paymentDate, ?string $paymentMethod, ?string $paymentStatus, float $amount, ?string $created_at=null)
    {
        $this->paymentID      = $paymentID;
        $this->orderID        = $orderID;
        $this->paymentDate    = $paymentDate;
        $this->paymentMethod  = $paymentMethod;
        $this->paymentStatus  = $paymentStatus;
        $this->amount         = $amount;
        $this->created_at     = $created_at;
    }
}
