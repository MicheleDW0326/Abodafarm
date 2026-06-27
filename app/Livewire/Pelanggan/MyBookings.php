<?php

namespace App\Livewire\Pelanggan;

use App\Events\OrderStatusChanged;
use App\Models\CustomerNotification;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Services\OrderStateMachine;
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

    // Timeline data for selected booking
    public array $selectedBookingTimeline = [];

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
            ->with(['items.product.owner.farmProfile', 'statusLogs'])
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

        $selectedBooking = null;
        if ($this->selectedBookingId) {
            $selectedBooking = Order::with(['items.product.owner.farmProfile', 'statusLogs.triggeredBy'])
                ->find($this->selectedBookingId);

            if ($selectedBooking) {
                $this->selectedBookingTimeline = $selectedBooking->timeline_data;
            }
        }

        return view('livewire.pelanggan.my-bookings', [
            'bookings' => $bookings,
            'stats' => $stats,
            'selectedBooking' => $selectedBooking,
        ]);
    }

    public function selectBooking(int $bookingId): void
    {
        $this->selectedBookingId = $bookingId;

        // Load timeline data
        $booking = Order::with('statusLogs')->find($bookingId);
        if ($booking) {
            $this->selectedBookingTimeline = $booking->timeline_data;
        }
    }

    public function closeDetail(): void
    {
        $this->selectedBookingId = null;
        $this->selectedBookingTimeline = [];
    }

    public function cancelBooking(int $bookingId): void
    {
        $booking = Order::where('user_id', auth()->id())
            ->where('id', $bookingId)
            ->where('order_type', Order::TYPE_INQUIRY)
            ->whereIn('status', ['pending'])
            ->first();

        if ($booking) {
            $fromStatus = $booking->status;

            // Use state machine for cancellation
            $result = OrderStateMachine::transitionToCancelled(
                $booking,
                OrderStatusLog::TRIGGER_CUSTOMER,
                auth()->id(),
                'Dibatalkan oleh pelanggan'
            );

            if ($result['success']) {
                // Dispatch event
                event(new OrderStatusChanged(
                    $booking->fresh(['statusLogs']),
                    $fromStatus,
                    Order::STATUS_CANCELLED,
                    OrderStatusLog::TRIGGER_CUSTOMER,
                    auth()->id(),
                    'Dibatalkan oleh pelanggan'
                ));
            }

            $this->closeDetail();

            $this->dispatch('notify', [
                'type' => 'success',
                'title' => 'Berhasil',
                'message' => 'Booking berhasil dibatalkan.',
            ]);
        }
    }

    /**
     * Refresh booking data (called from Livewire polling).
     */
    public function refreshBooking(): void
    {
        if ($this->selectedBookingId) {
            $this->selectBooking($this->selectedBookingId);
        }
    }
}
