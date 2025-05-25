<?php

class OrderDetailDTO
{
    public string $orderID;
    public string $courseID;
    public float $price;
    public ?string $created_at;

    public function __construct(string $orderID, string $courseID, float $price, ?string $created_at=null)
    {
        $this->orderID  = $orderID;
        $this->courseID = $courseID;
        $this->price    = $price;
        $this->created_at = $created_at;
    }
}
