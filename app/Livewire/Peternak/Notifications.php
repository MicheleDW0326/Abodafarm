<?php

namespace App\Livewire\Peternak;

use App\Models\PeternakNotification;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.main')]
#[Title('Notifikasi')]
class Notifications extends Component
{
    public function render()
    {
        $notifications = PeternakNotification::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        $unreadCount = PeternakNotification::where('user_id', auth()->id())->unread()->count();

        return view('livewire.peternak.notifications', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }

    public function markAsRead(int $id)
    {
        $notification = PeternakNotification::where('user_id', auth()->id())->find($id);
        if ($notification) {
            $notification->markAsRead();
        }
    }

    public function markAllAsRead()
    {
        PeternakNotification::where('user_id', auth()->id())->unread()->update(['is_read' => true]);
    }
}
