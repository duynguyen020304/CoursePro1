<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Cart;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Display a listing of orders for the authenticated user
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Order::with(['details.course.instructor.user', 'payment'])
            ->where('user_id', $user->user_id)
            ->orderBy('order_date', 'desc');

        $orders = $query->paginate($request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    /**
     * Create an order from the user's cart
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // Get user's cart
        $cart = Cart::with('items.course')->where('user_id', $user->user_id)->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty',
            ], 400);
        }

        DB::beginTransaction();

        try {
            // Calculate total amount
            $totalAmount = $cart->items->sum(function ($item) {
                return $item->course->price * $item->quantity;
            });

            // Create order
            $order = Order::create([
                'order_id' => 'order_' . Str::uuid(),
                'user_id' => $user->user_id,
                'order_date' => now(),
                'total_amount' => $totalAmount,
            ]);

            // Create order details
            foreach ($cart->items as $item) {
                OrderDetail::create([
                    'order_id' => $order->order_id,
                    'course_id' => $item->course_id,
                    'price' => $item->course->price,
                ]);
            }

            // Create payment record
            $payment = Payment::create([
                'payment_id' => 'payment_' . Str::uuid(),
                'order_id' => $order->order_id,
                'payment_date' => now(),
                'payment_method' => $request->payment_method ?? 'pending',
                'payment_status' => 'pending',
                'amount' => $totalAmount,
            ]);

            // Clear cart
            $cart->items()->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => $order->load(['details.course.instructor.user', 'payment']),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified order
     */
    public function show($orderId)
    {
        $user = request()->user();

        $order = Order::with(['details.course.instructor.user', 'payment', 'user'])
            ->where('order_id', $orderId)
            ->where('user_id', $user->user_id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    /**
     * Update payment status for an order (admin only)
     */
    public function updatePayment(Request $request, $orderId)
    {
        $request->validate([
            'payment_status' => 'required|in:pending,completed,failed,refunded',
        ]);

        $order = Order::with('payment')->findOrFail($orderId);

        if ($order->payment) {
            $order->payment->update([
                'payment_status' => $request->payment_status,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment status updated successfully',
            'data' => $order->fresh(['payment']),
        ]);
    }
}
