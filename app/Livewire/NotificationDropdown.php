<?php

namespace App\Livewire;

use App\Models\PeternakNotification;
use Livewire\Component;

class NotificationDropdown extends Component
{
    public function getNotifications()
    {
        return PeternakNotification::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    public function getUnreadCount()
    {
        return PeternakNotification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->count();
    }

    public function markAsRead(int $id)
    {
        $notification = PeternakNotification::where('user_id', auth()->id())->find($id);
        if ($notification) {
            $notification->markAsRead();
        }
    }

    public function render()
    {
        return view('livewire.notification-dropdown', [
            'notifications' => $this->getNotifications(),
            'unreadCount' => $this->getUnreadCount(),
        ]);
    }
}
