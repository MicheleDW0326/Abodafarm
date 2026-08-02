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
use Carbon\Carbon;

#[Layout('layouts.main')]
#[Title('Booking masuk')]
class IncomingBookings extends Component
{
    public string $statusFilter = 'all';
    public string $searchQuery = '';
    public ?int $selectedBookingId = null;

    // Survey scheduling modal
    public bool $showSurveyModal = false;
    public ?string $surveyDate = null;
    public ?string $surveyNotes = null;

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
            'survey_scheduled' => (clone $statsQuery)->where('status', 'survey_scheduled')->count(),
            'approved' => (clone $statsQuery)->where('status', 'approved')->count(),
            'ready_for_delivery' => (clone $statsQuery)->where('status', 'ready_for_delivery')->count(),
            'rejected' => (clone $statsQuery)->where('status', 'rejected')->count(),
        ];

        $selectedBooking = null;
        if ($this->selectedBookingId) {
            $selectedBooking = Order::with(['items.product', 'user', 'statusLogs' => function ($q) {
                $q->with('triggeredBy');
            }])->find($this->selectedBookingId);

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
        $this->closeSurveyModal();
    }

    // ============================================================
    // APPROVE BOOKING
    // ============================================================
    public function approveBooking(int $bookingId)
    {
        $userId = auth()->id();
        $isAdminOrStaff = auth()->user()->hasRole(['admin', 'staf']);

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
            event(new OrderStatusChanged(
                $booking->fresh(['statusLogs.triggeredBy']),
                $fromStatus,
                Order::STATUS_APPROVED,
                OrderStatusLog::TRIGGER_ADMIN,
                auth()->id(),
                'Booking disetujui'
            ));

            $this->sendNotification($customer, 'approved', $productName, $booking->order_number);
            $this->dispatch('alert', type: 'success', message: 'Booking berhasil disetujui.');
        } else {
            $this->dispatch('alert', type: 'error', message: $result['message']);
        }

        $this->closeDetail();
    }

    // ============================================================
    // REJECT BOOKING
    // ============================================================
    public function rejectBooking(int $bookingId)
    {
        $userId = auth()->id();
        $isAdminOrStaff = auth()->user()->hasRole(['admin', 'staf']);

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

        $result = OrderStateMachine::transitionToRejected($booking, auth()->id(), 'Booking ditolak oleh peternak');

        if ($result['success']) {
            event(new OrderStatusChanged(
                $booking->fresh(['statusLogs.triggeredBy']),
                $fromStatus,
                Order::STATUS_REJECTED,
                OrderStatusLog::TRIGGER_ADMIN,
                auth()->id(),
                'Booking ditolak'
            ));

            $this->sendNotification($customer, 'rejected', $productName, $booking->order_number);
            $this->dispatch('alert', type: 'info', message: 'Booking telah ditolak.');
        } else {
            $this->dispatch('alert', type: 'error', message: $result['message']);
        }

        $this->closeDetail();
    }

    // ============================================================
    // SCHEDULE SURVEY
    // ============================================================
    public function openSurveyModal(int $bookingId)
    {
        $this->selectedBookingId = $bookingId;
        $this->surveyDate = Carbon::tomorrow()->format('Y-m-d');
        $this->surveyNotes = null;
        $this->showSurveyModal = true;
    }

    public function closeSurveyModal()
    {
        $this->showSurveyModal = false;
        $this->surveyDate = null;
        $this->surveyNotes = null;
    }

    public function scheduleSurvey()
    {
        if (!$this->surveyDate) {
            $this->dispatch('alert', type: 'error', message: 'Silakan pilih tanggal survei.');
            return;
        }

        $userId = auth()->id();
        $isAdminOrStaff = auth()->user()->hasRole(['admin', 'staf']);

        $bookingQuery = Order::booking();
        if (!$isAdminOrStaff) {
            $bookingQuery->whereHas('items.product', fn($q) => $q->where('owner_id', $userId));
        }

        $booking = $bookingQuery->find($this->selectedBookingId);

        if (!$booking) {
            $this->dispatch('alert', type: 'error', message: 'Booking tidak ditemukan.');
            return;
        }

        if ($booking->status !== 'pending') {
            $this->dispatch('alert', type: 'error', message: 'Hanya booking dengan status Menunggu yang bisa dijadwalkan surveinya.');
            return;
        }

        $customer = $booking->user;
        $productName = $booking->items->first()?->product_name ?? 'Ternak';
        $fromStatus = $booking->status;
        $surveyDateFormatted = Carbon::parse($this->surveyDate)->format('d M Y');
        $notes = "Survei dijadwalkan untuk {$surveyDateFormatted}" . ($this->surveyNotes ? ". Catatan: {$this->surveyNotes}" : "");

        // First approve the booking
        $result = OrderStateMachine::transitionToApproved($booking, auth()->id(), $notes);

        if (!$result['success']) {
            $this->dispatch('alert', type: 'error', message: $result['message']);
            return;
        }

        // Then update to survey_scheduled status
        $booking->update(['status' => 'survey_scheduled']);
        OrderStatusLog::log(
            $booking,
            'approved',
            'survey_scheduled',
            OrderStatusLog::TRIGGER_FARMER,
            auth()->id(),
            $notes
        );

        // Update admin_notes with survey info
        $booking->update([
            'admin_notes' => ($booking->admin_notes ? $booking->admin_notes . "\n" : '') . "[{$surveyDateFormatted}] {$this->surveyNotes}"
        ]);

        event(new OrderStatusChanged(
            $booking->fresh(['statusLogs.triggeredBy']),
            $fromStatus,
            'survey_scheduled',
            OrderStatusLog::TRIGGER_FARMER,
            auth()->id(),
            $notes
        ));

        $this->sendNotification($customer, 'survey_scheduled', $productName, $booking->order_number, $surveyDateFormatted);
        $this->dispatch('alert', type: 'success', message: "Survei berhasil dijadwalkan untuk {$surveyDateFormatted}.");
        $this->closeSurveyModal();
        $this->closeDetail();
    }

    // ============================================================
    // READY FOR DELIVERY
    // ============================================================
    public function markReadyForDelivery(int $bookingId)
    {
        $userId = auth()->id();
        $isAdminOrStaff = auth()->user()->hasRole(['admin', 'staf']);

        $bookingQuery = Order::booking();
        if (!$isAdminOrStaff) {
            $bookingQuery->whereHas('items.product', fn($q) => $q->where('owner_id', $userId));
        }

        $booking = $bookingQuery->find($bookingId);

        if (!$booking) {
            $this->dispatch('alert', type: 'error', message: 'Booking tidak ditemukan.');
            return;
        }

        // Allow transition from approved or survey_scheduled
        if (!in_array($booking->status, ['approved', 'survey_scheduled'])) {
            $this->dispatch('alert', type: 'error', message: 'Booking harus sudah disetujui untuk bisa ditandai siap kirim.');
            return;
        }

        $customer = $booking->user;
        $productName = $booking->items->first()?->product_name ?? 'Ternak';
        $fromStatus = $booking->status;

        $booking->update(['status' => 'ready_for_delivery']);
        OrderStatusLog::log(
            $booking,
            $fromStatus,
            'ready_for_delivery',
            OrderStatusLog::TRIGGER_FARMER,
            auth()->id(),
            'Ternak siap untuk pengiriman/pengambilan'
        );

        event(new OrderStatusChanged(
            $booking->fresh(['statusLogs.triggeredBy']),
            $fromStatus,
            'ready_for_delivery',
            OrderStatusLog::TRIGGER_FARMER,
            auth()->id(),
            'Ternak siap untuk pengiriman/pengambilan'
        ));

        $this->sendNotification($customer, 'ready_for_delivery', $productName, $booking->order_number);
        $this->dispatch('alert', type: 'success', message: 'Booking ditandai siap untuk pengiriman/pengambilan.');
        $this->closeDetail();
    }

    // ============================================================
    // HELPER METHODS
    // ============================================================
    protected function sendNotification($customer, string $type, string $productName, string $orderNumber, ?string $extra = null)
    {
        if (!$customer) return;

        try {
            $notificationService = app(NotificationService::class);

            match($type) {
                'approved' => $notificationService->bookingApproved($customer, $productName, $orderNumber),
                'rejected' => $notificationService->bookingRejected($customer, $productName, $orderNumber),
                'survey_scheduled' => $notificationService->toCustomer(
                    $customer,
                    'survey_scheduled',
                    'Survei Dijadwalkan',
                    "Survei untuk {$productName} (Order: {$orderNumber}) dijadwalkan pada {$extra}",
                    ['order_number' => $orderNumber, 'product_name' => $productName]
                ),
                'ready_for_delivery' => $notificationService->toCustomer(
                    $customer,
                    'ready_for_delivery',
                    'Siap Diambil/Dikirim',
                    "{$productName} (Order: {$orderNumber}) siap untuk diambil/dikirim!",
                    ['order_number' => $orderNumber, 'product_name' => $productName]
                ),
                default => null,
            };

            $this->dispatch('refresh-notifications');
        } catch (\Exception $e) {
            \Log::error("Notification failed for booking {$type}", [
                'booking_number' => $orderNumber,
                'error' => $e->getMessage(),
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
