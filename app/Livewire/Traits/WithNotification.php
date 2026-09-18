<?php

namespace App\Livewire\Traits;

trait WithNotification
{
    public function notify(string $message, string $type = 'success'): void
    {
        $this->dispatch('notify', [
            'message' => $message,
            'type' => $type,
        ]);
    }

    public function success(string $message): void
    {
        $this->notify($message, 'success');
    }

    public function error(string $message): void
    {
        $this->notify($message, 'error');
    }

    public function warning(string $message): void
    {
        $this->notify($message, 'warning');
    }

    public function info(string $message): void
    {
        $this->notify($message, 'info');
    }
}
