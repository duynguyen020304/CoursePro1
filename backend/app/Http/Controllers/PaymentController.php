<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Order;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Display payment details for an order
     */
    public function show($orderId)
    {
        $order = Order::where('order_id', $orderId)->firstOrFail();

        $payment = Payment::with('order')
            ->where('order_id', $orderId)
            ->first();

        if (!$payment) {
            return $this->error('Payment not found for this order', 404);
        }

        return $this->success($payment, 'Payment retrieved successfully');
    }

    /**
     * Update payment status
     */
    public function updateStatus(Request $request, $paymentId)
    {
        $request->validate([
            'payment_status' => 'required|in:pending,processing,completed,failed,refunded',
        ]);

        $payment = Payment::findOrFail($paymentId);
        $payment->update(['payment_status' => $request->payment_status]);

        return $this->success($payment->fresh(['order']), 'Payment status updated successfully');
    }

    /**
     * Record a completed payment
     */
    public function complete(Request $request, $orderId)
    {
        $request->validate([
            'payment_method' => 'required|string',
        ]);

        $resolvedOrderId = $orderId ?: $request->input('order_id');
        if (!$resolvedOrderId) {
            return $this->error('Order not found', 404);
        }

        $order = Order::where('order_id', $resolvedOrderId)->first();

        if (!$order) {
            return $this->error('Order not found', 404);
        }

        // Authorization: only the order owner or a user with payment-management capability can complete payment
        $user = $request->user();
        if (!$user->canManagePayments() && $order->user_id !== $user->user_id) {
            return $this->error('Unauthorized to complete this order payment', 403);
        }

        $payment = Payment::where('order_id', $resolvedOrderId)->first();

        if ($payment) {
            $payment->update([
                'payment_status' => 'completed',
                'payment_method' => $request->payment_method,
            ]);
        } else {
            $payment = Payment::create([
                'payment_id' => 'payment_' . uniqid(),
                'order_id' => $resolvedOrderId,
                'payment_date' => now(),
                'payment_method' => $request->payment_method,
                'payment_status' => 'completed',
                'amount' => $order->total_amount,
            ]);
        }

        return $this->success($payment->fresh(['order']), 'Payment completed successfully');
    }
}
