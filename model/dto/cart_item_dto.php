<?php
class CartItemDTO
{
    public string $cartItemID;
    public string $cartID;
    public string $courseID;
    public int $quantity;
    public ?string $created_at;

    public function __construct(string $cartItemID, string $cartID, string $courseID, int $quantity, ?string $created_at=null)
    {
        $this->cartItemID = $cartItemID;
        $this->cartID     = $cartID;
        $this->courseID   = $courseID;
        $this->quantity   = $quantity;
        $this->created_at = $created_at;
    }
}
