<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    /**
     * Generate Snap Token for the order.
     */
    public function snapToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer|exists:orders,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $order = Order::with('items.product')->findOrFail($request->order_id);

            if ($order->payment && $order->payment->isCompleted()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order already has a completed payment',
                ], 400);
            }

            // Check if order is in valid state for payment
            // Use effective_status for the new architecture
            $currentStatus = $order->effective_status ?? $order->status;
            if (!in_array($currentStatus, [Order::STATUS_PENDING, Order::STATUS_PROCESSING])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order is not in a valid state for payment',
                ], 400);
            }

            $result = MidtransService::createSnapToken($order);

            return response()->json([
                'success' => true,
                'message' => 'Snap token created successfully',
                'data' => [
                    'snap_token' => $result['snap_token'],
                    'redirect_url' => $result['redirect_url'],
                    'payment_id' => $order->payment->id ?? null,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create Snap Token', [
                'order_id' => $request->order_id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create Snap token: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle Midtrans notification webhook.
     */
    public function notification(Request $request): JsonResponse
    {
        Log::info('Midtrans Notification Received', [
            'payload' => $request->all(),
            'ip' => $request->ip(),
        ]);

        $validator = Validator::make($request->all(), [
            'order_id' => 'required|string',
            'transaction_status' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid notification payload',
            ], 400);
        }

        $success = MidtransService::handleNotification($request->all());

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Notification processed successfully',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to process notification',
        ], 500);
    }

    /**
     * Get payment status for an order.
     */
    public function status(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer|exists:orders,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $order = Order::with('payment')->findOrFail($request->order_id);

            if (!$order->payment) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'has_payment' => false,
                        'status' => null,
                        'message' => 'No payment record found',
                    ],
                ]);
            }

            $payment = $order->payment;

            if ($payment->isPending()) {
                $midtransStatus = MidtransService::checkTransactionStatus($order->order_number);

                if ($midtransStatus) {
                    $payment->update([
                        'transaction_id' => $midtransStatus->transaction_id ?? $payment->transaction_id,
                        'payment_type' => $midtransStatus->payment_type ?? $payment->payment_type,
                        'transaction_status' => $midtransStatus->transaction_status ?? $payment->transaction_status,
                        'fraud_status' => $midtransStatus->fraud_status ?? $payment->fraud_status,
                    ]);

                    MidtransService::handleNotification([
                        'order_id' => $order->order_number,
                    ]);

                    $payment->refresh();
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'has_payment' => true,
                    'payment_id' => $payment->id,
                    'transaction_id' => $payment->transaction_id,
                    'payment_type' => $payment->payment_type,
                    'gross_amount' => $payment->gross_amount,
                    'formatted_amount' => $payment->formatted_amount,
                    'status' => $payment->transaction_status,
                    'status_label' => $payment->status_label,
                    'badge_class' => $payment->status_badge_class,
                    'is_completed' => $payment->isCompleted(),
                    'is_pending' => $payment->isPending(),
                    'is_failed' => $payment->isFailed(),
                    'is_refunded' => $payment->isRefunded(),
                    'has_chargeback' => $payment->hasChargeback(),
                    'paid_at' => $payment->paid_at?->toIso8601String(),
                    'snap_token' => $payment->snap_token,
                    'order_status' => $order->effective_status ?? $order->status,
                    'order_status_label' => $order->status_label,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get payment status', [
                'order_id' => $request->order_id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get payment status: ' . $e->getMessage(),
            ], 500);
        }
    }
}
