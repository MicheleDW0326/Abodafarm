<?php

namespace App\Livewire\Pelanggan;

use App\Models\CustomerNotification;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.main')]
#[Title('Notifikasi - Akun Saya')]
class MyNotifications extends Component
{
    public array $notifications = [];
    public string $filterType = 'all';
    public bool $showUnreadOnly = false;

    public array $filterOptions = [
        'all' => 'Semua',
        'order_status' => 'Status Pesanan',
        'booking_approved' => 'Booking Disetujui',
        'booking_rejected' => 'Booking Ditolak',
        'promo' => 'Promo',
        'system' => 'Sistem',
    ];

    public function mount(): void
    {
        $this->loadNotifications();
    }

    public function render()
    {
        return view('livewire.pelanggan.my-notifications');
    }

    public function loadNotifications(): void
    {
        $userId = auth()->id();

        $query = CustomerNotification::forUser($userId)
            ->when($this->filterType !== 'all', function ($q) {
                $q->where('type', $this->filterType);
            })
            ->when($this->showUnreadOnly, function ($q) {
                $q->unread();
            })
            ->orderBy('created_at', 'desc');

        $this->notifications = $query->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'is_read' => $notification->is_read,
                    'data' => $notification->data,
                    'created_at' => $notification->created_at,
                    'color_class' => $notification->color_class,
                    'icon' => $notification->icon,
                ];
            })
            ->toArray();
    }

    public function markAsRead(int $id): void
    {
        $notification = CustomerNotification::where('user_id', auth()->id())->find($id);
        if ($notification) {
            $notification->markAsRead();
            $this->loadNotifications();
        }
    }

    public function markAllAsRead(): void
    {
        CustomerNotification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        $this->loadNotifications();

        $this->dispatch('notify', [
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => 'Semua notifikasi ditandai sudah dibaca.',
        ]);
    }

    public function deleteNotification(int $id): void
    {
        $notification = CustomerNotification::where('user_id', auth()->id())->find($id);
        if ($notification) {
            $notification->delete();
            $this->loadNotifications();
        }
    }

    public function clearAll(): void
    {
        CustomerNotification::where('user_id', auth()->id())->delete();
        $this->loadNotifications();

        $this->dispatch('notify', [
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => 'Semua notifikasi dihapus.',
        ]);
    }

    public function updatedFilterType(): void
    {
        $this->loadNotifications();
    }

    public function updatedShowUnreadOnly(): void
    {
        $this->loadNotifications();
    }

    public function getUnreadCount(): int
    {
        return CustomerNotification::where('user_id', auth()->id())->unread()->count();
    }
}
