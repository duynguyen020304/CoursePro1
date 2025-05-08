<?php

use DateTime;

require_once("../database.php");

class OrderDetailDTO
{
    public string $orderID;
    public string $courseID;
    public float $price;

    public function __construct(string $orderID, string $courseID, float $price)
    {
        $this->orderID  = $orderID;
        $this->courseID = $courseID;
        $this->price    = $price;
    }
}
