<?php
require_once __DIR__ . '/../Model/BLL/cart_bll.php';
require_once __DIR__ . '/../Model/DTO/cart_dto.php';

class CartService
{
    private $cartBLL;

    public function __construct()
    {
        $this->cartBLL = new CartBLL();
    }

    public function get_cart_by_user(string $userID): ?CartDTO
    {
        return $this->cartBLL->get_cart_by_user($userID);
    }

    public function create_cart(string $userID): array
    {
        $cartID = uniqid('cart_', true);
        $dto = new CartDTO($cartID, $userID);
        $success = $this->cartBLL->create_cart($dto);

        return [
            'success' => $success,
            'cartID' => $cartID
        ];
    }

    public function update_cart(string $cartID, string $userID): bool
    {
        $this->cartBLL->delete_cart($cartID);
        $dto = new CartDTO($cartID, $userID);
        return $this->cartBLL->create_cart($dto);
    }

    public function delete_cart(string $cartID): bool
    {
        return $this->cartBLL->delete_cart($cartID);
    }
}
