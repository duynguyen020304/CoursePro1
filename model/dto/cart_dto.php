<?php
class CartDTO
{
    public string $cartID;
    public string $userID;
    public ?string $created_at;

    public function __construct(string $cartID, string $userID, ?string $created_at=null)
    {
        $this->cartID = $cartID;
        $this->userID = $userID;
        $this->created_at = $created_at;
    }
}
