<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CartItemController extends Controller
{
    /**
     * Add item to cart
     */
    public function store(Request $request)
    {
        $request->validate([
            'course_id' => 'required|string|exists:courses,course_id',
            'quantity' => 'nullable|integer|min:1',
        ]);

        $user = $request->user();

        // Get or create cart
        $cart = Cart::firstOrCreate(
            ['user_id' => $user->user_id],
            ['cart_id' => 'cart_' . Str::uuid()]
        );

        // Check if item already exists
        $existingItem = CartItem::where('cart_id', $cart->cart_id)
            ->where('course_id', $request->course_id)
            ->first();

        if ($existingItem) {
            return response()->json([
                'success' => false,
                'message' => 'Course already in cart',
            ], 400);
        }

        $cartItem = CartItem::create([
            'cart_item_id' => 'cart_item_' . Str::uuid(),
            'cart_id' => $cart->cart_id,
            'course_id' => $request->course_id,
            'quantity' => $request->quantity ?? 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Added to cart successfully',
            'data' => $cartItem->load('course.instructor.user', 'course.images'),
        ], 201);
    }

    /**
     * Remove item from cart
     */
    public function destroy($cartItemId)
    {
        $cartItem = CartItem::findOrFail($cartItemId);
        $cartItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart',
        ]);
    }
}
