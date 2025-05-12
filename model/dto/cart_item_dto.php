<?php
class CartItemDTO
{
    public string $cartItemID;
    public string $cartID;
    public string $courseID;
    public int $quantity;

    public function __construct(string $cartItemID, string $cartID, string $courseID, int $quantity)
    {
        $this->cartItemID = $cartItemID;
        $this->cartID     = $cartID;
        $this->courseID   = $courseID;
        $this->quantity   = $quantity;
    }
}
