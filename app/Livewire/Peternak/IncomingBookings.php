<?php

namespace App\Livewire\Peternak;

use App\Models\Order;
use App\Models\PeternakNotification;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
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

        // Show ALL inquiry type orders (bookings)
        // Admin/super admin sees all, farmer sees their own products
        $isAdminOrStaff = auth()->user()->hasRole(['admin', 'staf']);

        $bookingsQuery = Order::booking()
            ->when(!$isAdminOrStaff, function ($query) use ($userId) {
                // Farmer: only show bookings for their own products
                $query->whereHas('items.product', function ($q) use ($userId) {
                    $q->where('owner_id', $userId);
                });
            })
            ->with(['items.product', 'user'])
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

        // Stats
        $statsQuery = Order::booking();
        if (!$isAdminOrStaff) {
            $statsQuery->whereHas('items.product', fn($q) => $q->where('owner_id', $userId));
        }

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'pending' => (clone $statsQuery)->where('status', 'pending')->count(),
            'approved' => (clone $statsQuery)->where('status', 'approved')->count(),
            'rejected' => (clone $statsQuery)->where('status', 'rejected')->count(),
        ];

        $selectedBooking = $this->selectedBookingId
            ? Order::with(['items.product', 'user'])->find($this->selectedBookingId)
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
        $isAdminOrStaff = auth()->user()->hasRole(['admin', 'staf']);

        // Admin/staff can approve any booking, farmers only their own
        $bookingQuery = Order::booking();
        if (!$isAdminOrStaff) {
            $bookingQuery->whereHas('items.product', fn($q) => $q->where('owner_id', $userId));
        }

        $booking = $bookingQuery->find($bookingId);

        if (!$booking) {
            $this->dispatch('alert', type: 'error', message: 'Booking tidak ditemukan.');
            return;
        }

        if ($booking->status !== 'pending') {
            $this->dispatch('alert', type: 'error', message: 'Hanya booking dengan status Menunggu yang bisa disetujui.');
            return;
        }

        DB::transaction(function () use ($booking) {
            $booking->approve();

            // Get the approver name
            $approver = auth()->user();

            // Create notification for customer
            PeternakNotification::create([
                'user_id' => $booking->user_id,
                'type' => 'booking_approved',
                'title' => 'Booking Disetujui',
                'message' => "Booking {$booking->order_number} telah disetujui oleh {$approver->name}. Silakan hubungi peternak untuk langkah selanjutnya.",
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
        $isAdminOrStaff = auth()->user()->hasRole(['admin', 'staf']);

        // Admin/staff can reject any booking, farmers only their own
        $bookingQuery = Order::booking();
        if (!$isAdminOrStaff) {
            $bookingQuery->whereHas('items.product', fn($q) => $q->where('owner_id', $userId));
        }

        $booking = $bookingQuery->find($bookingId);

        if (!$booking) {
            $this->dispatch('alert', type: 'error', message: 'Booking tidak ditemukan.');
            return;
        }

        if ($booking->status !== 'pending') {
            $this->dispatch('alert', type: 'error', message: 'Hanya booking dengan status Menunggu yang bisa ditolak.');
            return;
        }

        DB::transaction(function () use ($booking) {
            $booking->reject();

            // Get the rejector name
            $rejector = auth()->user();

            // Create notification for customer
            PeternakNotification::create([
                'user_id' => $booking->user_id,
                'type' => 'booking_rejected',
                'title' => 'Booking Ditolak',
                'message' => "Maaf, booking {$booking->order_number} telah ditolak oleh {$rejector->name}.",
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
