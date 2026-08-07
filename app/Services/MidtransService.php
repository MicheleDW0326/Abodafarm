<?php

namespace App\Services;

use App\Enums\PaymentStatusEnum;
use App\Events\OrderStatusChanged;
use App\Events\PaymentReceived;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\OrderStatusLog;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;
use Exception;

class MidtransService
{
    public static function init(): void
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$clientKey = config('services.midtrans.client_key');
        Config::$isProduction = config('services.midtrans.is_production', false);
        Config::$isSanitized = config('services.midtrans.is_sanitized', true);
        Config::$is3ds = config('services.midtrans.is_3ds', true);
    }

    /**
     * Create Snap token for the given order.
     */
    public static function createSnapToken(Order $order): array
    {
        self::init();

        $existingPayment = Payment::where('order_id', $order->id)
            ->whereIn('transaction_status', [Payment::STATUS_PENDING, 'pending'])
            ->first();

        if ($existingPayment && $existingPayment->snap_token) {
            try {
                $status = Transaction::status($order->order_number);
                if ($status && !in_array($status->transaction_status, ['expire', 'cancel', 'deny'])) {
                    Log::info('Midtrans: Reusing existing Snap Token', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                    ]);

                    return [
                        'snap_token' => $existingPayment->snap_token,
                        'redirect_url' => config('services.midtrans.is_production')
                            ? "https://app.midtrans.com/snap/v2/vtweb/{$existingPayment->snap_token}"
                            : "https://app.sandbox.midtrans.com/snap/v2/vtweb/{$existingPayment->snap_token}",
                    ];
                }
            } catch (Exception $e) {
                Log::info('Midtrans: Existing token invalid, creating new one', [
                    'order_id' => $order->id,
                    'reason' => $e->getMessage(),
                ]);
            }
        }

        $items = $order->items()->with('product')->get();
        $itemDetails = [];
        foreach ($items as $item) {
            $itemDetails[] = [
                'id' => $item->product_id ?? 'item_' . $item->id,
                'price' => (int) $item->unit_price,
                'quantity' => (int) $item->quantity,
                'name' => $item->product_name ?? 'Product',
            ];
        }

        $transactionDetails = [
            'order_id' => $order->order_number,
            'gross_amount' => (int) $order->total_amount,
        ];

        $customerDetails = [
            'first_name' => $order->customer_name,
            'phone' => $order->customer_phone,
            'shipping_address' => [
                'address' => $order->customer_address,
            ],
        ];

        $params = [
            'transaction_details' => $transactionDetails,
            'item_details' => $itemDetails,
            'customer_details' => $customerDetails,
            'enabled_payments' => [
                'gopay', 'bank_transfer', 'mandiri_clickpay', 'cimb_clicks',
                'bca_klikbca', 'bca_klikpay', 'bri_epay', 'echannel',
                'permata_va', 'bca_va', 'bni_va', 'bri_va', 'other_va',
                'akulaku', 'indomaret', 'alfamart',
            ],
            'callbacks' => [
                'finish' => route('checkout.success', ['order_id' => $order->id]),
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);

            Payment::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'transaction_id' => null,
                    'payment_type' => null,
                    'gross_amount' => $order->total_amount,
                    'transaction_status' => Payment::STATUS_PENDING,
                    'fraud_status' => null,
                    'snap_token' => $snapToken,
                ]
            );

            Log::info('Midtrans Snap Token created', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ]);

            return [
                'snap_token' => $snapToken,
                'redirect_url' => config('services.midtrans.is_production')
                    ? "https://app.midtrans.com/snap/v2/vtweb/{$snapToken}"
                    : "https://app.sandbox.midtrans.com/snap/v2/vtweb/{$snapToken}",
            ];
        } catch (Exception $e) {
            Log::error('Midtrans Snap Token creation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle Midtrans notification/webhook.
     */
    public static function handleNotification(array $notificationPayload): bool
    {
        self::init();

        try {
            $transactionStatusData = Transaction::status($notificationPayload['order_id']);
            $transactionStatus = is_array($transactionStatusData)
                ? (object) $transactionStatusData
                : $transactionStatusData;

            Log::info('Midtrans Notification Received', [
                'order_id' => $notificationPayload['order_id'],
                'transaction_status' => $transactionStatus->transaction_status ?? 'unknown',
            ]);

            $order = Order::where('order_number', $notificationPayload['order_id'])->first();

            if (!$order) {
                Log::warning('Order not found for Midtrans notification', [
                    'order_number' => $notificationPayload['order_id'],
                ]);
                return false;
            }

            $payment = Payment::firstOrCreate(
                ['order_id' => $order->id],
                [
                    'gross_amount' => $transactionStatus->gross_amount ?? 0,
                    'transaction_status' => Payment::STATUS_PENDING,
                ]
            );

            $payment->update([
                'transaction_id' => $transactionStatus->transaction_id ?? null,
                'payment_type' => $transactionStatus->payment_type ?? null,
                'transaction_status' => $transactionStatus->transaction_status ?? 'pending',
                'fraud_status' => $transactionStatus->fraud_status ?? null,
            ]);

            $statusUpdated = self::handleTransactionStatus($order, $payment, $transactionStatus);

            Log::info('Midtrans Notification Processed', [
                'order_id' => $order->id,
                'status_updated' => $statusUpdated,
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Midtrans Notification Handling Failed', [
                'order_id' => $notificationPayload['order_id'] ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Handle transaction status and update order accordingly.
     */
    protected static function handleTransactionStatus(
        Order $order,
        Payment $payment,
        $transactionStatus
    ): bool {
        $status = $transactionStatus->transaction_status ?? 'pending';

        // Successful payment statuses
        $successStatuses = ['capture', 'settlement'];

        if (in_array($status, $successStatuses)) {
            if ($transactionStatus->fraud_status === 'accept' || $transactionStatus->fraud_status === null) {
                return self::handleSuccessfulPayment($order, $payment, $transactionStatus);
            }
        }

        // Failed/cancelled/expired payment
        return self::handleFailedPayment($order, $payment, $transactionStatus);
    }

    /**
     * Handle successful payment.
     */
    protected static function handleSuccessfulPayment(
        Order $order,
        Payment $payment,
        $transactionStatus
    ): bool {
        $fromStatus = $order->status;

        // Check if stock has already been decremented (idempotency)
        $stockAlreadyDecremented = $order->order_type === Order::TYPE_CART && $payment->paid_at !== null;

        // Update payment
        $payment->paid_at = now();
        $payment->transaction_status = Payment::STATUS_SETTLEMENT;
        $payment->save();

        // Use NEW FK-based state machine transition
        $notes = "Pembayaran berhasil via Midtrans. Transaction ID: {$transactionStatus->transaction_id}";

        // First, set payment status to PAID using FK
        $paymentResult = OrderStateMachine::setPaymentStatus(
            $order,
            PaymentStatusEnum::PAID,
            OrderStatusLog::TRIGGER_WEBHOOK,
            null,
            $notes
        );

        if ($paymentResult['success']) {
            // Decrement stock only for cart orders (fresh meat) and only once
            if (!$stockAlreadyDecremented) {
                self::decrementOrderStock($order);
            }

            // Dispatch events
            event(new PaymentReceived($order, $payment, $transactionStatus->transaction_id));
            event(new OrderStatusChanged(
                $order->fresh(),
                $fromStatus,
                Order::STATUS_PAID,
                OrderStatusLog::TRIGGER_WEBHOOK,
                null,
                $notes
            ));
        }

        Log::info('Order marked as paid via FK transition', [
            'order_id' => $order->id,
            'transaction_id' => $transactionStatus->transaction_id,
            'stock_decremented' => !$stockAlreadyDecremented && $order->order_type === Order::TYPE_CART,
        ]);

        return true;
    }

    /**
     * Decrement stock for all items in an order.
     */
    protected static function decrementOrderStock(Order $order): void
    {
        $order->load('items.product');

        foreach ($order->items as $item) {
            if ($item->product_id && $item->product) {
                $newStock = max(0, $item->product->stock - $item->quantity);
                $item->product->update(['stock' => $newStock]);

                Log::info('Stock decremented for product', [
                    'product_id' => $item->product_id,
                    'quantity_ordered' => $item->quantity,
                    'previous_stock' => $item->product->stock + $item->quantity,
                    'new_stock' => $newStock,
                ]);
            }
        }
    }

    /**
     * Handle failed/cancelled/expired payment.
     */
    protected static function handleFailedPayment(
        Order $order,
        Payment $payment,
        $transactionStatus
    ): bool {
        $status = $transactionStatus->transaction_status ?? 'pending';
        $fromStatus = $order->status;

        // Determine which payment status to use
        $paymentEnum = match($status) {
            'deny' => PaymentStatusEnum::PAYMENT_FAILED,
            'cancel' => PaymentStatusEnum::PAYMENT_FAILED,
            'expire' => PaymentStatusEnum::PAYMENT_FAILED,
            default => PaymentStatusEnum::PENDING,
        };

        // Update payment record
        $payment->transaction_status = $status;
        $payment->save();

        // If payment is truly failed (not just pending), cancel the order
        $failedStatuses = ['deny', 'cancel', 'expire'];
        if (in_array($status, $failedStatuses)) {
            // Set payment status to failed using FK
            $notes = "Pembayaran {$status}: " . ($transactionStatus->status_message ?? '');

            $paymentResult = OrderStateMachine::setPaymentStatus(
                $order,
                PaymentStatusEnum::PAYMENT_FAILED,
                OrderStatusLog::TRIGGER_WEBHOOK,
                null,
                $notes
            );

            if ($paymentResult['success']) {
                // Also set shipment status to cancelled
                OrderStateMachine::setShipmentStatus(
                    $order,
                    \App\Enums\ShipmentStatusEnum::CANCELLED,
                    OrderStatusLog::TRIGGER_WEBHOOK,
                    null,
                    $notes
                );

                event(new OrderStatusChanged(
                    $order->fresh(),
                    $fromStatus,
                    Order::STATUS_CANCELLED,
                    OrderStatusLog::TRIGGER_WEBHOOK,
                    null,
                    $notes
                ));
            }
        }

        return true;
    }

    /**
     * Check transaction status directly from Midtrans.
     */
    public static function checkTransactionStatus(string $orderId): ?object
    {
        self::init();

        try {
            $response = Transaction::status($orderId);
            if (is_array($response)) {
                return (object) $response;
            }
            return $response;
        } catch (Exception $e) {
            Log::error('Midtrans Status Check Failed', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get the OrderStatus ID for a given code.
     * Helper method for backward compatibility.
     */
    public static function getOrderStatusId(string $code): ?int
    {
        return OrderStatus::findIdByCode($code);
    }
}
