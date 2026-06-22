<?php

namespace App\Livewire\Pelanggan;

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.main')]
#[Title('Booking Saya')]
class MyBookings extends Component
{
    public string $statusFilter = 'all';
    public string $searchQuery = '';
    public ?int $selectedBookingId = null;

    public function render()
    {
        $userId = auth()->id();

        // Get only inquiry type orders (bookings) for this customer
        $bookingsQuery = Order::booking()
            ->where('user_id', $userId)
            ->with(['items.product.owner'])
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
        ];

        $selectedBooking = $this->selectedBookingId
            ? Order::with(['items.product.owner'])->find($this->selectedBookingId)
            : null;

        return view('livewire.pelanggan.my-bookings', [
            'bookings' => $bookings,
            'stats' => $stats,
            'selectedBooking' => $selectedBooking,
        ]);
    }

    public function selectBooking(int $bookingId)
    {
        $this->selectedBookingId = $bookingId;
    }

    public function closeDetail()
    {
        $this->selectedBookingId = null;
    }
}
