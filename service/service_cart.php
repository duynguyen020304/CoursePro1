<?php
require_once __DIR__ . '/../model/bll/cart_bll.php';
require_once __DIR__ . '/../model/dto/cart_dto.php';

class CartService
{
    private $cartBLL;

    public function __construct()
    {
        $this->cartBLL = new CartBLL();
    }

    public function getCartByUser(string $userID): ?CartDTO
    {
        return $this->cartBLL->get_cart_by_user($userID);
    }

    public function createCart(string $userID): array
    {
        $cartID = uniqid('cart_', true);
        $dto = new CartDTO($cartID, $userID);
        $success = $this->cartBLL->create_cart($dto);

        return [
            'success' => $success,
            'cartID' => $cartID
        ];
    }

    public function updateCart(string $cartID, string $userID): bool
    {
        $this->cartBLL->delete_cart($cartID);
        $dto = new CartDTO($cartID, $userID);
        return $this->cartBLL->create_cart($dto);
    }

    public function deleteCart(string $cartID): bool
    {
        return $this->cartBLL->delete_cart($cartID);
    }
}
