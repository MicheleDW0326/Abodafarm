<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
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
     *
     * @param Order $order
     * @return array{snap_token: string, redirect_url: string}
     * @throws Exception
     */
    public static function createSnapToken(Order $order): array
    {
        self::init();

        // Get order items
        $items = $order->items()->with('product')->get();

        // Build item details for Midtrans
        $itemDetails = [];
        foreach ($items as $item) {
            $itemDetails[] = [
                'id' => $item->product_id ?? 'item_' . $item->id,
                'price' => (int) $item->unit_price,
                'quantity' => (int) $item->quantity,
                'name' => $item->product_name ?? 'Product',
            ];
        }

        // Note: Shipping cost is included in total_amount if applicable
        // If you have a separate shipping_cost field, uncomment and modify the code below:
        // if ($order->shipping_cost > 0) {
        //     $itemDetails[] = [
        //         'id' => 'SHIPPING',
        //         'price' => (int) $order->shipping_cost,
        //         'quantity' => 1,
        //         'name' => 'Shipping Cost',
        //     ];
        // }

        // Build transaction details
        $transactionDetails = [
            'order_id' => $order->order_number,
            'gross_amount' => (int) $order->total_amount,
        ];

        // Build customer details
        $customerDetails = [
            'first_name' => $order->customer_name,
            'phone' => $order->customer_phone,
            'shipping_address' => [
                'address' => $order->customer_address,
            ],
        ];

        // Build Snap parameters
        $params = [
            'transaction_details' => $transactionDetails,
            'item_details' => $itemDetails,
            'customer_details' => $customerDetails,
            'enabled_payments' => [
                'gopay',
                'bank_transfer',
                'mandiri_clickpay',
                'cimb_clicks',
                'bca_klikbca',
                'bca_klikpay',
                'bri_epay',
                'echannel',
                'permata_va',
                'bca_va',
                'bni_va',
                'bri_va',
                'other_va',
                'akulaku',
                'indomaret',
                'alfamart',
            ],
            'callbacks' => [
                'finish' => route('checkout.success', ['order_id' => $order->id]),
            ],
        ];

        try {
            // Get Snap Token
            $snapToken = Snap::getSnapToken($params);

            // Create or update payment record
            $payment = Payment::updateOrCreate(
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
                'snap_token' => $snapToken,
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
     *
     * @param array $notificationPayload
     * @return bool
     */
    public static function handleNotification(array $notificationPayload): bool
    {
        self::init();

        try {
            // Get transaction status from Midtrans
            $transactionStatusData = Transaction::status($notificationPayload['order_id']);

            // Convert to object if it's an array
            $transactionStatus = is_array($transactionStatusData)
                ? (object) $transactionStatusData
                : $transactionStatusData;

            Log::info('Midtrans Notification Received', [
                'order_id' => $notificationPayload['order_id'],
                'transaction_status' => $transactionStatus->transaction_status ?? 'unknown',
            ]);

            // Find the order
            $order = Order::where('order_number', $notificationPayload['order_id'])->first();

            if (!$order) {
                Log::warning('Order not found for Midtrans notification', [
                    'order_number' => $notificationPayload['order_id'],
                ]);
                return false;
            }

            // Find or create payment record
            $payment = Payment::firstOrCreate(
                ['order_id' => $order->id],
                [
                    'gross_amount' => $transactionStatus->gross_amount ?? 0,
                    'transaction_status' => Payment::STATUS_PENDING,
                ]
            );

            // Update payment with transaction details
            $payment->update([
                'transaction_id' => $transactionStatus->transaction_id ?? null,
                'payment_type' => $transactionStatus->payment_type ?? null,
                'transaction_status' => $transactionStatus->transaction_status ?? 'pending',
                'fraud_status' => $transactionStatus->fraud_status ?? null,
            ]);

            // Handle based on transaction status
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

        // Update paid_at if transaction is successful
        if (in_array($status, ['capture', 'settlement'])) {
            if ($transactionStatus->fraud_status === 'accept' || $transactionStatus->fraud_status === null) {
                $payment->paid_at = now();
                $payment->transaction_status = Payment::STATUS_SETTLEMENT;
                $payment->save();

                // Update order status
                $order->update([
                    'status' => 'paid',
                    'admin_notes' => ($order->admin_notes ?? '') . "\n" .
                        "Payment confirmed via Midtrans. Transaction ID: {$transactionStatus->transaction_id}",
                ]);

                // Send FCM notification to customer
                self::sendPaymentSuccessNotification($order, $transactionStatus->transaction_id);

                Log::info('Order marked as paid', [
                    'order_id' => $order->id,
                    'transaction_id' => $transactionStatus->transaction_id,
                ]);

                return true;
            }
        }

        // Handle other statuses
        $payment->transaction_status = $status;
        $payment->save();

        // Update order status based on payment status
        $orderStatusMap = [
            'deny' => 'payment_failed',
            'cancel' => 'cancelled',
            'expire' => 'expired',
            'refund' => 'refunded',
        ];

        if (isset($orderStatusMap[$status])) {
            $order->update(['status' => $orderStatusMap[$status]]);
        }

        return true;
    }

    /**
     * Check transaction status directly from Midtrans.
     *
     * @param string $orderId
     * @return object|null
     */
    public static function checkTransactionStatus(string $orderId): ?object
    {
        self::init();

        try {
            $response = Transaction::status($orderId);

            // Convert array to object if needed
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
     * Send payment success notification via FCM and in-app notification.
     */
    protected static function sendPaymentSuccessNotification(Order $order, ?string $transactionId = null): void
    {
        // Get the user
        $user = $order->user;

        if (!$user) {
            Log::warning('Cannot send payment notification: User not found', [
                'order_id' => $order->id,
            ]);
            return;
        }

        $orderNumber = $order->order_number;
        $totalAmount = 'Rp ' . number_format($order->total_amount, 0, ',', '.');
        $itemCount = $order->total_items;

        // Use NotificationService for unified notification
        try {
            $notificationService = app(NotificationService::class);
            $notificationService->paymentSuccess(
                $user,
                $orderNumber,
                $totalAmount,
                $itemCount
            );

            Log::info('Payment success notification sent via NotificationService', [
                'order_id' => $order->id,
                'user_id' => $user->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send payment success notification', [
                'order_id' => $order->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}