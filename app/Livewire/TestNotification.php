<?php

namespace App\Livewire;

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

        try {
            $fcmService = app(FcmService::class);

            $result = $fcmService->sendToUser(
                auth()->user(),
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
                $this->error = 'Gagal mengirim notifikasi. Pastikan FCM token sudah tersimpan.';
            }
        } catch (\Exception $e) {
            $this->error = 'Error: ' . $e->getMessage();
        } finally {
            $this->isLoading = false;
        }
    }

    public function render()
    {
        return view('livewire.test-notification');
    }
}
