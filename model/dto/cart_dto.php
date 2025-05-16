<?php
class CartDTO
{
    public string $cartID;
    public string $userID;

    public function __construct(string $cartID, string $userID)
    {
        $this->cartID = $cartID;
        $this->userID = $userID;
    }
}
