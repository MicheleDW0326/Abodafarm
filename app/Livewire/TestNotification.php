<?php

namespace App\Livewire;

use App\Models\FcmToken;
use App\Services\FcmService;
use Livewire\Component;

class TestNotification extends Component
{
    public bool $isLoading = false;
    public ?string $status = null;
    public ?string $error = null;

    public function sendTestNotification(): void
    {
        if (!auth()->check()) {
            $this->error = 'Anda harus login terlebih dahulu.';
            return;
        }

        $this->isLoading = true;
        $this->status = null;
        $this->error = null;

        $user = auth()->user();
        $fcmService = app(FcmService::class);

        // Debug: Check FCM configuration
        $serverKey = env('FCM_SERVER_KEY');
        \Log::info('FCM Debug', [
            'user_id' => $user->id,
            'server_key_set' => !empty($serverKey),
            'server_key_prefix' => $serverKey ? substr($serverKey, 0, 10) . '...' : null,
        ]);

        // Check if user has FCM tokens
        $tokens = FcmToken::where('user_id', $user->id)->active()->get();

        \Log::info('FCM Tokens Debug', [
            'user_id' => $user->id,
            'tokens_count' => $tokens->count(),
            'tokens' => $tokens->pluck('token')->map(fn($t) => substr($t, 0, 30) . '...')->toArray(),
        ]);

        if ($tokens->isEmpty()) {
            $this->error = 'FCM Token belum tersimpan. Refresh halaman ini dan izinkan notifikasi saat muncul pop-up.';
            $this->isLoading = false;
            return;
        }

        try {
            $result = $fcmService->sendToUser(
                $user,
                'Test Notification! 🎉',
                'Notifikasi FCM berhasil terkirim ke browser Anda.',
                [
                    'type' => 'test',
                    'action' => 'test_clicked',
                    'test_id' => uniqid(),
                ]
            );

            if ($result) {
                $this->status = 'Notifikasi test berhasil dikirim! Cek browser Anda.';
            } else {
                $this->error = 'Gagal mengirim notifikasi. Cek console untuk detail.';
            }
        } catch (\Exception $e) {
            $this->error = 'Error: ' . $e->getMessage();
            \Log::error('FCM Send Error', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        } finally {
            $this->isLoading = false;
        }
    }

    public function render()
    {
        return view('livewire.test-notification');
    }
}
