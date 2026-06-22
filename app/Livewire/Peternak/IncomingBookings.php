<?php

namespace App\Livewire\Peternak;

use App\Models\Order;
use App\Models\PeternakNotification;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.main')]
#[Title('Booking Masuk')]
class IncomingBookings extends Component
{
    public string $statusFilter = 'all';
    public string $searchQuery = '';
    public ?int $selectedBookingId = null;

    public function render()
    {
        $userId = auth()->id();

        // Only show inquiry type orders (bookings) for products owned by this farmer
        $bookingsQuery = Order::booking()
            ->whereHas('items.product', function ($query) use ($userId) {
                $query->where('owner_id', $userId);
            })
            ->with(['items.product' => function ($q) use ($userId) {
                $q->where('owner_id', $userId);
            }, 'user'])
            ->when($this->searchQuery, function ($q) {
                $q->where(function ($query) {
                    $query->where('order_number', 'like', '%' . $this->searchQuery . '%')
                        ->orWhere('customer_name', 'like', '%' . $this->searchQuery . '%');
                });
            })
            ->when($this->statusFilter !== 'all', function ($q) {
                $q->where('status', $this->statusFilter);
            })
            ->orderBy('created_at', 'desc');

        $bookings = $bookingsQuery->get();

        // Stats - only count bookings for this farmer's products
        $baseQuery = fn($q) => $q->whereHas('items.product', fn($pq) => $pq->where('owner_id', $userId));

        $stats = [
            'total' => Order::booking()->where($baseQuery)->count(),
            'pending' => Order::booking()->where($baseQuery)->where('status', 'pending')->count(),
            'approved' => Order::booking()->where($baseQuery)->where('status', 'approved')->count(),
            'rejected' => Order::booking()->where($baseQuery)->where('status', 'rejected')->count(),
        ];

        $selectedBooking = $this->selectedBookingId
            ? Order::with(['items.product' => fn($q) => $q->where('owner_id', $userId), 'user'])->find($this->selectedBookingId)
            : null;

        return view('livewire.peternak.incoming-bookings', [
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

    public function approveBooking(int $bookingId)
    {
        $userId = auth()->id();

        $booking = Order::whereHas('items.product', fn($q) => $q->where('owner_id', $userId))
            ->find($bookingId);

        if (!$booking) {
            $this->dispatch('alert', type: 'error', message: 'Booking tidak ditemukan.');
            return;
        }

        if ($booking->status !== 'pending') {
            $this->dispatch('alert', type: 'error', message: 'Hanya booking dengan status Menunggu yang bisa disetujui.');
            return;
        }

        DB::transaction(function () use ($booking, $userId) {
            $booking->approve();

            // Get the product owner (farmer) name
            $farmer = auth()->user();

            // Create notification for customer
            PeternakNotification::create([
                'user_id' => $booking->user_id,
                'type' => 'booking_approved',
                'title' => 'Booking Disetujui',
                'message' => "Booking {$booking->order_number} telah disetujui oleh {$farmer->name}. Silakan hubungi peternak untuk langkah selanjutnya.",
                'data' => json_encode([
                    'booking_id' => $booking->id,
                    'order_number' => $booking->order_number,
                    'action' => 'approved',
                ]),
                'is_read' => false,
            ]);
        });

        $this->dispatch('alert', type: 'success', message: 'Booking berhasil disetujui.');
        $this->closeDetail();
    }

    public function rejectBooking(int $bookingId)
    {
        $userId = auth()->id();

        $booking = Order::whereHas('items.product', fn($q) => $q->where('owner_id', $userId))
            ->find($bookingId);

        if (!$booking) {
            $this->dispatch('alert', type: 'error', message: 'Booking tidak ditemukan.');
            return;
        }

        if ($booking->status !== 'pending') {
            $this->dispatch('alert', type: 'error', message: 'Hanya booking dengan status Menunggu yang bisa ditolak.');
            return;
        }

        DB::transaction(function () use ($booking, $userId) {
            $booking->reject();

            // Get the product owner (farmer) name
            $farmer = auth()->user();

            // Create notification for customer
            PeternakNotification::create([
                'user_id' => $booking->user_id,
                'type' => 'booking_rejected',
                'title' => 'Booking Ditolak',
                'message' => "Maaf, booking {$booking->order_number} telah ditolak oleh {$farmer->name}.",
                'data' => json_encode([
                    'booking_id' => $booking->id,
                    'order_number' => $booking->order_number,
                    'action' => 'rejected',
                ]),
                'is_read' => false,
            ]);
        });

        $this->dispatch('alert', type: 'info', message: 'Booking telah ditolak.');
        $this->closeDetail();
    }
}
