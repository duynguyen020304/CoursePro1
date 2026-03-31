<?php

namespace App\Http\Controllers;

use App\Models\OrderDetail;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderDetailController extends Controller
{
    /**
     * Get order details for an order
     */
    public function index($orderId)
    {
        $order = Order::where('order_id', $orderId)->firstOrFail();

        $details = OrderDetail::where('order_id', $orderId)
            ->with(['course.instructor.user'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $details,
        ]);
    }

    /**
     * Get a specific order detail
     */
    public function show($orderId, $courseId)
    {
        $detail = OrderDetail::where('order_id', $orderId)
            ->where('course_id', $courseId)
            ->with(['course.instructor.user', 'order'])
            ->first();

        if (!$detail) {
            return response()->json([
                'success' => false,
                'message' => 'Order detail not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $detail,
        ]);
    }
}
