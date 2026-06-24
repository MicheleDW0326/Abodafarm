<?php

namespace App\Livewire\Pelanggan;

use App\Models\Order;
use App\Models\CustomerNotification;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.main')]
#[Title('Booking Ternak - Akun Saya')]
class MyBookings extends Component
{
    public string $statusFilter = 'all';
    public string $searchQuery = '';
    public ?int $selectedBookingId = null;

    public array $statusOptions = [
        'all' => 'Semua',
        'pending' => 'Menunggu',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];

    public function render()
    {
        $userId = auth()->id();

        // Get only inquiry type orders (bookings) for this customer
        $bookingsQuery = Order::booking()
            ->where('user_id', $userId)
            ->with(['items.product.owner.farmProfile'])
            ->when($this->searchQuery, function ($q) {
                $q->where(function ($query) {
                    $query->where('order_number', 'like', '%' . $this->searchQuery . '%');
                });
            })
            ->when($this->statusFilter !== 'all', function ($q) {
                $q->where('status', $this->statusFilter);
            })
            ->orderBy('created_at', 'desc');

        $bookings = $bookingsQuery->get();

        // Stats
        $stats = [
            'total' => Order::booking()->where('user_id', $userId)->count(),
            'pending' => Order::booking()->where('user_id', $userId)->where('status', 'pending')->count(),
            'approved' => Order::booking()->where('user_id', $userId)->where('status', 'approved')->count(),
            'rejected' => Order::booking()->where('user_id', $userId)->where('status', 'rejected')->count(),
            'completed' => Order::booking()->where('user_id', $userId)->where('status', 'completed')->count(),
        ];

        $selectedBooking = $this->selectedBookingId
            ? Order::with(['items.product.owner.farmProfile'])->find($this->selectedBookingId)
            : null;

        return view('livewire.pelanggan.my-bookings', [
            'bookings' => $bookings,
            'stats' => $stats,
            'selectedBooking' => $selectedBooking,
        ]);
    }

    public function selectBooking(int $bookingId): void
    {
        $this->selectedBookingId = $bookingId;
    }

    public function closeDetail(): void
    {
        $this->selectedBookingId = null;
    }

    public function cancelBooking(int $bookingId): void
    {
        $booking = Order::where('user_id', auth()->id())
            ->where('id', $bookingId)
            ->where('order_type', Order::TYPE_INQUIRY)
            ->whereIn('status', ['pending'])
            ->first();

        if ($booking) {
            $booking->update(['status' => 'cancelled']);

            // Create notification
            CustomerNotification::forOrderStatus(
                auth()->id(),
                $booking->order_number,
                'cancelled',
                'Booking Anda telah dibatalkan.'
            );

            $this->closeDetail();

            $this->dispatch('notify', [
                'type' => 'success',
                'title' => 'Berhasil',
                'message' => 'Booking berhasil dibatalkan.',
            ]);
        }
    }
}
