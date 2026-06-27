<?php

namespace App\Services;

use App\Models\FcmToken;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    private string $serverKey;
    private string $apiUrl = 'https://fcm.googleapis.com/fcm/send';

    public function __construct()
    {
        // Prioritize env variable directly, fallback to config
        $this->serverKey = env('FCM_SERVER_KEY') ?: config('services.fcm.server_key', '');
    }

    /**
     * Send notification to a single user
     */
    public function sendToUser(User $user, string $title, string $body, array $data = []): bool
    {
        $tokens = FcmToken::where('user_id', $user->id)
            ->active()
            ->pluck('token')
            ->toArray();

        if (empty($tokens)) {
            Log::info("No FCM tokens found for user {$user->id}");
            return false;
        }

        return $this->sendToTokens($tokens, $title, $body, $data);
    }

    /**
     * Send notification to multiple tokens
     */
    public function sendToTokens(array $tokens, string $title, string $body, array $data = []): bool
    {
        if (empty($this->serverKey)) {
            Log::warning('FCM Server Key not configured');
            return false;
        }

        if (empty($tokens)) {
            return false;
        }

        $payload = $this->buildPayload($tokens, $title, $body, $data);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->serverKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl, $payload);

            if ($response->successful()) {
                $result = $response->json();

                // Log success/failure
                Log::info('FCM notification sent', [
                    'success' => $result['success'] ?? 0,
                    'failure' => $result['failure'] ?? 0,
                    'tokens_count' => count($tokens),
                ]);

                // Clean up invalid tokens
                if (isset($result['results']) && is_array($result['results'])) {
                    $this->handleResults($tokens, $result['results']);
                }

                return ($result['success'] ?? 0) > 0;
            }

            Log::error('FCM notification failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('FCM notification error', [
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send booking approved notification to user
     */
    public function sendBookingApprovedNotification(User $user, string $orderNumber, string $productName): bool
    {
        $title = 'Booking Disetujui! 🎉';
        $body = "Booking {$orderNumber} untuk {$productName} telah disetujui oleh peternak.";

        $data = [
            'type' => 'booking_approved',
            'order_number' => $orderNumber,
            'action' => 'view_booking',
        ];

        return $this->sendToUser($user, $title, $body, $data);
    }

    /**
     * Send booking rejected notification to user
     */
    public function sendBookingRejectedNotification(User $user, string $orderNumber, string $productName): bool
    {
        $title = 'Booking Ditolak';
        $body = "Maaf, booking {$orderNumber} untuk {$productName} telah ditolak.";

        $data = [
            'type' => 'booking_rejected',
            'order_number' => $orderNumber,
            'action' => 'view_booking',
        ];

        return $this->sendToUser($user, $title, $body, $data);
    }

    /**
     * Send new booking notification to farmer
     */
    public function sendNewBookingNotification(User $farmer, string $customerName, string $productName, string $orderNumber): bool
    {
        $title = 'Booking Baru! 📋';
        $body = "{$customerName} mengajukan booking untuk {$productName}";

        $data = [
            'type' => 'new_booking',
            'order_number' => $orderNumber,
            'action' => 'view_booking',
        ];

        return $this->sendToUser($farmer, $title, $body, $data);
    }

    /**
     * Send payment success notification to user
     */
    public function sendPaymentSuccessNotification(User $user, string $orderNumber, string $amount, int $itemCount): bool
    {
        $title = 'Pembayaran Berhasil! 🎉';
        $body = "Pembayaran untuk pesanan {$orderNumber} ({$itemCount} item) sebesar {$amount} telah berhasil. Pesanan Anda sedang diproses.";

        $data = [
            'type' => 'payment_success',
            'order_number' => $orderNumber,
            'action' => 'view_order',
        ];

        return $this->sendToUser($user, $title, $body, $data);
    }

    /**
     * Build FCM payload
     */
    private function buildPayload(array $tokens, string $title, string $body, array $data): array
    {
        // If only one token, send to single device
        if (count($tokens) === 1) {
            return [
                'to' => $tokens[0],
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'sound' => 'default',
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ],
                'data' => array_merge($data, [
                    'title' => $title,
                    'body' => $body,
                ]),
                'android' => [
                    'priority' => 'high',
                    'notification' => [
                        'channel_id' => 'abodafarm_booking',
                        'sound' => 'default',
                    ],
                ],
                'webpush' => [
                    'headers' => [
                        'Urgency' => 'high',
                    ],
                    'fcm_options' => [
                        'link' => config('app.url'),
                    ],
                ],
            ];
        }

        // Multiple tokens - use registration_ids
        return [
            'registration_ids' => $tokens,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'sound' => 'default',
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ],
            'data' => array_merge($data, [
                'title' => $title,
                'body' => $body,
            ]),
            'android' => [
                'priority' => 'high',
                'notification' => [
                    'channel_id' => 'abodafarm_booking',
                    'sound' => 'default',
                ],
            ],
            'webpush' => [
                'headers' => [
                    'Urgency' => 'high',
                ],
                'fcm_options' => [
                    'link' => config('app.url'),
                ],
            ],
        ];
    }

    /**
     * Handle FCM results - clean up invalid tokens
     */
    private function handleResults(array $tokens, array $results): void
    {
        foreach ($results as $index => $result) {
            if (!isset($tokens[$index])) {
                continue;
            }

            $token = $tokens[$index];

            // Check for errors that indicate invalid token
            if (isset($result['error'])) {
                $error = $result['error'];

                // Unregistered, invalid, or not registered tokens should be deleted
                if (in_array($error, ['NotRegistered', 'InvalidRegistration', 'MismatchSenderId'])) {
                    FcmToken::where('token', $token)->delete();
                    Log::info("Deleted invalid FCM token", ['token' => substr($token, 0, 20) . '...', 'error' => $error]);
                }
            }

            // Mark token as used if successful
            if (isset($result['message_id'])) {
                FcmToken::where('token', $token)->update(['last_used_at' => now()]);
            }
        }
    }

    /**
     * Check if FCM is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->serverKey);
    }
}
