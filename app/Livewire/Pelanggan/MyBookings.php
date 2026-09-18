<?php

namespace App\Livewire\Pelanggan;

use App\Events\OrderStatusChanged;
use App\Models\CustomerNotification;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Services\OrderStateMachine;
use Illuminate\Support\Facades\DB;
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
        'survey_scheduled' => 'Survei Dijadwalkan',
        'approved' => 'Disetujui',
        'ready_for_delivery' => 'Siap Dikirim',
        'completed' => 'Selesai',
        'rejected' => 'Ditolak',
        'cancelled' => 'Dibatalkan',
    ];

    // Booking progress steps
    public array $bookingSteps = [
        'pending' => ['label' => 'Menunggu', 'description' => 'Menunggu konfirmasi dari peternak'],
        'survey_scheduled' => ['label' => 'Survei', 'description' => 'Survei lapangan telah dijadwalkan'],
        'approved' => ['label' => 'Disetujui', 'description' => 'Booking disetujui, menunggu pengiriman'],
        'ready_for_delivery' => ['label' => 'Siap Kirim', 'description' => 'Ternak siap diambil/dikirim'],
        'completed' => ['label' => 'Selesai', 'description' => 'Booking telah selesai'],
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

        // OPTIMIZED: Single query for all stats using CASE WHEN
        $statsData = DB::table('orders')
            ->where('user_id', $userId)
            ->where('order_type', 'inquiry')
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'survey_scheduled' THEN 1 ELSE 0 END) as survey_scheduled,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'ready_for_delivery' THEN 1 ELSE 0 END) as ready_for_delivery,
                SUM(CASE WHEN status IN ('rejected', 'cancelled') THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN status IN ('completed', 'delivered') THEN 1 ELSE 0 END) as completed
            ")
            ->first();

        $stats = [
            'total' => (int) $statsData->total,
            'pending' => (int) $statsData->pending,
            'survey_scheduled' => (int) $statsData->survey_scheduled,
            'approved' => (int) $statsData->approved,
            'ready_for_delivery' => (int) $statsData->ready_for_delivery,
            'rejected' => (int) $statsData->rejected,
            'completed' => (int) $statsData->completed,
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
                    $booking->fresh(['statusLogs.triggeredBy']),
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

    /**
     * Get booking progress for a booking.
     */
    public function getBookingProgress(Order $booking): array
    {
        $status = $booking->status;
        $stepKeys = array_keys($this->bookingSteps);
        $currentIndex = 0;

        foreach ($stepKeys as $index => $key) {
            if ($status === $key) {
                $currentIndex = $index;
                break;
            }
        }

        // Convert string keys to integer keys for proper iteration in blade
        $stepsWithIntKeys = array_values($this->bookingSteps);

        // Handle terminal states
        if (in_array($status, ['rejected', 'cancelled'])) {
            return [
                'steps' => $stepsWithIntKeys,
                'currentStep' => -1,
                'isRejected' => true,
                'isCancelled' => $status === 'cancelled',
                'isComplete' => false,
            ];
        }

        return [
            'steps' => $stepsWithIntKeys,
            'currentStep' => $currentIndex,
            'isRejected' => false,
            'isCancelled' => false,
            'isComplete' => $currentIndex >= 4,
        ];
    }

    /**
     * Get status explanation for customers.
     */
    public function getStatusExplanation(string $status): array
    {
        return match($status) {
            'pending' => [
                'title' => 'Menunggu Konfirmasi',
                'description' => 'Pesanan Anda sedang ditinjau oleh peternak. Anda akan mendapat notifikasi setelah booking disetujui atau ditolak.',
                'action' => 'Hubungi Peternak',
                'icon' => 'clock',
                'color' => 'amber',
            ],
            'survey_scheduled' => [
                'title' => 'Survei Dijadwalkan',
                'description' => 'Peternak telah menjadwalkan survei lapangan. Hubungi peternak untuk konfirmasi tanggal dan detail.',
                'action' => 'Hubungi Peternak',
                'icon' => 'calendar-check',
                'color' => 'blue',
            ],
            'approved' => [
                'title' => 'Booking Disetujui',
                'description' => 'Selamat! Booking Anda disetujui. Hubungi peternak untuk koordinasi pembayaran dan pengiriman.',
                'action' => 'Hubungi Peternak',
                'icon' => 'check-circle',
                'color' => 'green',
            ],
            'ready_for_delivery' => [
                'title' => 'Siap Dikirim/Diambil',
                'description' => 'Ternak siap untuk dikirim atau diambil. Hubungi peternak untuk info pengambilan.',
                'action' => 'Hubungi Peternak',
                'icon' => 'truck-fast',
                'color' => 'violet',
            ],
            'completed' => [
                'title' => 'Booking Selesai',
                'description' => 'Booking telah selesai. Jangan lupa untuk memberikan review!',
                'action' => 'Beri Review',
                'icon' => 'check-double',
                'color' => 'emerald',
            ],
            'rejected' => [
                'title' => 'Booking Ditolak',
                'description' => 'Mohon maaf, peternak tidak dapat memenuhi booking ini. Anda dapat mencoba booking ternak lain.',
                'action' => null,
                'icon' => 'xmark-circle',
                'color' => 'red',
            ],
            'cancelled' => [
                'title' => 'Dibatalkan',
                'description' => 'Booking ini telah dibatalkan.',
                'action' => null,
                'icon' => 'xmark',
                'color' => 'slate',
            ],
            default => [
                'title' => 'Status Tidak Diketahui',
                'description' => 'Hubungi kami untuk informasi lebih lanjut.',
                'action' => 'Hubungi Kami',
                'icon' => 'question',
                'color' => 'slate',
            ],
        };
    }
}
