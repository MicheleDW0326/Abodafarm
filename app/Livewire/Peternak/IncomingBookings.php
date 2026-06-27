<?php

namespace App\Livewire\Peternak;

use App\Events\OrderStatusChanged;
use App\Models\CustomerNotification;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\OrderStateMachine;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.main')]
#[Title('Booking masuk')]
class IncomingBookings extends Component
{
    public string $statusFilter = 'all';
    public string $searchQuery = '';
    public ?int $selectedBookingId = null;

    // Timeline data for selected booking
    public array $selectedBookingTimeline = [];

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
            ->with(['items.product', 'user', 'statusLogs'])
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

        $selectedBooking = null;
        if ($this->selectedBookingId) {
            $selectedBooking = Order::with(['items.product', 'user', 'statusLogs.triggeredBy'])
                ->find($this->selectedBookingId);

            if ($selectedBooking) {
                $this->selectedBookingTimeline = $selectedBooking->timeline_data;
            }
        }

        return view('livewire.peternak.incoming-bookings', [
            'bookings' => $bookings,
            'stats' => $stats,
            'selectedBooking' => $selectedBooking,
        ]);
    }

    public function selectBooking(int $bookingId)
    {
        $this->selectedBookingId = $bookingId;

        // Load timeline data
        $booking = Order::with('statusLogs')->find($bookingId);
        if ($booking) {
            $this->selectedBookingTimeline = $booking->timeline_data;
        }
    }

    public function closeDetail()
    {
        $this->selectedBookingId = null;
        $this->selectedBookingTimeline = [];
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

        $customer = $booking->user;
        $productName = $booking->items->first()?->product_name ?? 'Ternak';
        $fromStatus = $booking->status;

        // Use state machine
        $result = OrderStateMachine::transitionToApproved($booking, auth()->id(), 'Booking disetujui oleh peternak');

        if ($result['success']) {
            // Dispatch event
            event(new OrderStatusChanged(
                $booking->fresh(['statusLogs']),
                $fromStatus,
                Order::STATUS_APPROVED,
                OrderStatusLog::TRIGGER_ADMIN,
                auth()->id(),
                'Booking disetujui'
            ));

            // Send notification to customer
            if ($customer) {
                try {
                    $notificationService = app(NotificationService::class);
                    $notificationService->bookingApproved(
                        $customer,
                        $productName,
                        $booking->order_number
                    );
                    $this->dispatch('refresh-notifications');
                } catch (\Exception $e) {
                    \Log::error('Notification failed for booking approval', [
                        'booking_id' => $booking->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $this->dispatch('alert', type: 'success', message: 'Booking berhasil disetujui.');
        } else {
            $this->dispatch('alert', type: 'error', message: $result['message']);
        }

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

        $customer = $booking->user;
        $productName = $booking->items->first()?->product_name ?? 'Ternak';
        $fromStatus = $booking->status;

        // Use state machine
        $result = OrderStateMachine::transitionToRejected($booking, auth()->id(), 'Booking ditolak oleh peternak');

        if ($result['success']) {
            // Dispatch event
            event(new OrderStatusChanged(
                $booking->fresh(['statusLogs']),
                $fromStatus,
                Order::STATUS_REJECTED,
                OrderStatusLog::TRIGGER_ADMIN,
                auth()->id(),
                'Booking ditolak'
            ));

            // Send notification to customer
            if ($customer) {
                try {
                    $notificationService = app(NotificationService::class);
                    $notificationService->bookingRejected(
                        $customer,
                        $productName,
                        $booking->order_number
                    );
                    $this->dispatch('refresh-notifications');
                } catch (\Exception $e) {
                    \Log::error('Notification failed for booking rejection', [
                        'booking_id' => $booking->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $this->dispatch('alert', type: 'info', message: 'Booking telah ditolak.');
        } else {
            $this->dispatch('alert', type: 'error', message: $result['message']);
        }

        $this->closeDetail();
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
