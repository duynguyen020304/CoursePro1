<?php

use DateTime;

require_once("../database.php");

class OrderDTO
{
    public string $orderID;
    public string $userID;
    public DateTime $orderDate;
    public float $totalAmount;

    public function __construct(string $orderID, string $userID, DateTime $orderDate, float $totalAmount)
    {
        $this->orderID     = $orderID;
        $this->userID      = $userID;
        $this->orderDate   = $orderDate;
        $this->totalAmount = $totalAmount;
    }
}
