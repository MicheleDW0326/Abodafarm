<?php

namespace App\Livewire;

use App\Models\FcmToken;
use Livewire\Component;

class SaveFcmToken extends Component
{
    public string $currentToken = '';

    protected $listeners = ['fcm-token-updated' => 'saveToken'];

    public function saveToken(string $token): void
    {
        if (!auth()->check() || empty($token)) {
            return;
        }

        // Skip if same token already saved
        if ($this->currentToken === $token) {
            return;
        }

        try {
            FcmToken::updateOrCreateToken(
                auth()->id(),
                $token,
                'web',
                request()->userAgent()
            );

            $this->currentToken = $token;

            \Log::info('FCM Token saved via Livewire', [
                'user_id' => auth()->id(),
                'token_prefix' => substr($token, 0, 20) . '...',
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to save FCM token', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function removeToken(string $token): void
    {
        if (!auth()->check()) {
            return;
        }

        FcmToken::where('user_id', auth()->id())
            ->where('token', $token)
            ->delete();
    }

    public function render()
    {
        return view('livewire.save-fcm-token');
    }
}
