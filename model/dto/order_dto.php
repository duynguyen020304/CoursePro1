<?php
class OrderDTO
{
    public string $orderID;
    public string $userID;
    public DateTime $orderDate;
    public float $totalAmount;
    public ?string $created_at;

    public function __construct(string $orderID, string $userID, DateTime $orderDate, float $totalAmount, ?string $created_at=null)
    {
        $this->orderID     = $orderID;
        $this->userID      = $userID;
        $this->orderDate   = $orderDate;
        $this->totalAmount = $totalAmount;
        $this->created_at  = $created_at;
    }
}
