<?php

namespace App\Livewire;

use App\Models\CustomerNotification;
use App\Models\PeternakNotification;
use Illuminate\Support\Collection;
use Livewire\Component;

class NotificationDropdown extends Component
{
    protected $listeners = ['refresh-notifications' => '$refresh'];

    public function getNotifications(): Collection
    {
        $userId = auth()->id();

        $peternak = PeternakNotification::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(fn ($n) => (object) [
                'id' => $n->id,
                'type' => $n->type,
                'title' => $n->title,
                'message' => $n->message,
                'data' => $n->data,
                'is_read' => $n->is_read,
                'created_at' => $n->created_at,
                'source' => 'peternak',
                'icon' => $n->icon,
            ]);

        $customer = CustomerNotification::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(fn ($n) => (object) [
                'id' => $n->id,
                'type' => $n->type,
                'title' => $n->title,
                'message' => $n->message,
                'data' => $n->data,
                'is_read' => $n->is_read,
                'created_at' => $n->created_at,
                'source' => 'customer',
                'icon' => $n->icon,
            ]);

        return $peternak->merge($customer)
            ->sortByDesc('created_at')
            ->take(5)
            ->values();
    }

    public function getUnreadCount(): int
    {
        $userId = auth()->id();

        return PeternakNotification::where('user_id', $userId)->where('is_read', false)->count()
            + CustomerNotification::where('user_id', $userId)->where('is_read', false)->count();
    }

    public function markAsRead(int $id, string $source = 'peternak'): void
    {
        if ($source === 'customer') {
            $notification = CustomerNotification::where('user_id', auth()->id())->find($id);
        } else {
            $notification = PeternakNotification::where('user_id', auth()->id())->find($id);
        }

        if ($notification) {
            $notification->markAsRead();
        }
    }

    public function markAllAsRead(): void
    {
        $userId = auth()->id();

        PeternakNotification::where('user_id', $userId)->where('is_read', false)->update(['is_read' => true]);
        CustomerNotification::where('user_id', $userId)->where('is_read', false)->update(['is_read' => true]);
    }

    public function render()
    {
        return view('livewire.notification-dropdown', [
            'notifications' => $this->getNotifications(),
            'unreadCount' => $this->getUnreadCount(),
        ]);
    }
}
