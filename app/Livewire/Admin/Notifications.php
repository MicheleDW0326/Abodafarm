<?php

namespace App\Livewire\Admin;

use App\Models\CustomerNotification;
use App\Models\PeternakNotification;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.main')]
#[Title('Notifikasi - Admin')]
class Notifications extends Component
{
    public string $filterSource = 'all'; // all, customer, peternak
    public string $filterType = 'all';
    public bool $showUnreadOnly = false;
    public string $searchQuery = '';

    public array $filterOptions = [
        'all' => 'Semua',
        'payment_success' => 'Pembayaran Berhasil',
        'payment_failed' => 'Pembayaran Gagal',
        'booking_approved' => 'Booking Disetujui',
        'booking_rejected' => 'Booking Ditolak',
        'new_booking' => 'Booking Baru',
        'product_published' => 'Produk Dipublikasikan',
        'product_disabled' => 'Produk Dinonaktifkan',
        'new_farmer_registration' => 'Peternak Baru',
        'order_shipped' => 'Pesanan Dikirim',
        'order_delivered' => 'Pesanan Selesai',
    ];

    public function render()
    {
        $notifications = $this->getNotifications();

        $stats = [
            'total' => $this->getTotalCount(),
            'unread' => $this->getUnreadCount(),
        ];

        return view('livewire.admin.notifications', [
            'notifications' => $notifications,
            'stats' => $stats,
        ]);
    }

    public function getNotifications(): array
    {
        $customerNotifications = [];
        $peternakNotifications = [];

        // Get Customer Notifications
        if ($this->filterSource === 'all' || $this->filterSource === 'customer') {
            $query = CustomerNotification::query()
                ->when($this->filterType !== 'all', fn($q) => $q->where('type', $this->filterType))
                ->when($this->showUnreadOnly, fn($q) => $q->unread())
                ->when($this->searchQuery, function ($q) {
                    $q->where(function ($query) {
                        $query->where('title', 'like', '%' . $this->searchQuery . '%')
                            ->orWhere('message', 'like', '%' . $this->searchQuery . '%');
                    });
                })
                ->orderBy('created_at', 'desc')
                ->limit(50);

            $customerNotifications = $query->get()
                ->map(fn($n) => [
                    'id' => $n->id,
                    'type' => $n->type,
                    'title' => $n->title,
                    'message' => $n->message,
                    'data' => $n->data,
                    'is_read' => $n->is_read,
                    'created_at' => $n->created_at,
                    'source' => 'customer',
                    'color_class' => $n->color_class,
                    'icon' => $n->icon,
                ])
                ->toArray();
        }

        // Get Peternak Notifications (Admin also gets peternak notifications)
        if ($this->filterSource === 'all' || $this->filterSource === 'peternak') {
            $query = PeternakNotification::query()
                ->when($this->filterType !== 'all', fn($q) => $q->where('type', $this->filterType))
                ->when($this->showUnreadOnly, fn($q) => $q->unread())
                ->when($this->searchQuery, function ($q) {
                    $q->where(function ($query) {
                        $query->where('title', 'like', '%' . $this->searchQuery . '%')
                            ->orWhere('message', 'like', '%' . $this->searchQuery . '%');
                    });
                })
                ->orderBy('created_at', 'desc')
                ->limit(50);

            $peternakNotifications = $query->get()
                ->map(fn($n) => [
                    'id' => $n->id,
                    'type' => $n->type,
                    'title' => $n->title,
                    'message' => $n->message,
                    'data' => $n->data,
                    'is_read' => $n->is_read,
                    'created_at' => $n->created_at,
                    'source' => 'peternak',
                    'color_class' => $n->color_class ?? 'text-sky-600 bg-sky-50',
                    'icon' => $n->icon,
                ])
                ->toArray();
        }

        // Merge and sort by created_at
        $allNotifications = array_merge($customerNotifications, $peternakNotifications);
        usort($allNotifications, fn($a, $b) => strtotime($b['created_at']) - strtotime($a['created_at']));

        return array_slice($allNotifications, 0, 50);
    }

    public function getTotalCount(): int
    {
        return CustomerNotification::count() + PeternakNotification::count();
    }

    public function getUnreadCount(): int
    {
        return CustomerNotification::unread()->count() + PeternakNotification::unread()->count();
    }

    public function markAsRead(int $id, string $source): void
    {
        if ($source === 'customer') {
            $notification = CustomerNotification::find($id);
        } else {
            $notification = PeternakNotification::find($id);
        }

        if ($notification) {
            $notification->markAsRead();
        }
    }

    public function markAllAsRead(): void
    {
        CustomerNotification::where('is_read', false)->update(['is_read' => true]);
        PeternakNotification::where('is_read', false)->update(['is_read' => true]);

        $this->dispatch('alert', [
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => 'Semua notifikasi ditandai sudah dibaca.',
        ]);
    }

    public function deleteNotification(int $id, string $source): void
    {
        if ($source === 'customer') {
            CustomerNotification::find($id)?->delete();
        } else {
            PeternakNotification::find($id)?->delete();
        }
    }

    public function clearAll(): void
    {
        CustomerNotification::truncate();
        PeternakNotification::truncate();

        $this->dispatch('alert', [
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => 'Semua notifikasi dihapus.',
        ]);
    }

    public function updatedFilterSource(): void
    {
        // No need to reload, Livewire will auto-update
    }

    public function updatedFilterType(): void
    {
        // No need to reload, Livewire will auto-update
    }

    public function updatedShowUnreadOnly(): void
    {
        // No need to reload, Livewire will auto-update
    }

    public function updatedSearchQuery(): void
    {
        // No need to reload, Livewire will auto-update
    }
}
