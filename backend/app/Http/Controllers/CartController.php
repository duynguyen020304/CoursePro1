<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CartController extends Controller
{
    /**
     * Get user's cart with items
     */
    public function getUserCart(Request $request)
    {
        $user = $request->user();

        $cart = Cart::with(['items.course.instructor.user', 'items.course.images'])
            ->where('user_id', $user->user_id)
            ->first();

        if (!$cart) {
            return response()->json([
                'success' => true,
                'data' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $cart,
        ]);
    }

    /**
     * Create or get user's cart
     */
    private function getOrCreateCart($userId)
    {
        return Cart::firstOrCreate(
            ['user_id' => $userId],
            ['cart_id' => 'cart_' . Str::uuid()]
        );
    }

    /**
     * Clear user's cart
     */
    public function clearCart(Request $request)
    {
        $user = $request->user();
        $cart = Cart::where('user_id', $user->user_id)->first();

        if ($cart) {
            CartItem::where('cart_id', $cart->cart_id)->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully',
        ]);
    }
}
