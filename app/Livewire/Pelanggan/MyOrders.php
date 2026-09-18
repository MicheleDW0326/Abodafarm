<?php

namespace App\Livewire\Pelanggan;

use App\Enums\ShipmentStatusEnum;
use App\Models\Order;
use App\Models\CustomerNotification;
use App\Models\OrderStatusLog;
use App\Services\OrderStateMachine;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.main')]
#[Title('Pesanan Daging - Akun Saya')]
class MyOrders extends Component
{
    public string $statusFilter = 'all';
    public string $searchQuery = '';
    public ?int $selectedOrderId = null;

    public array $statusOptions = [
        'all' => 'Semua',
        'pending' => 'Menunggu Pembayaran',
        'paid' => 'Lunas',
        'processing' => 'Diproses',
        'shipped' => 'Dikirim',
        'delivered' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];

    // Payment & Delivery progress definitions
    public array $paymentSteps = [
        'pending' => ['label' => 'Menunggu', 'description' => 'Selesaikan pembayaran untuk melanjutkan'],
        'paid' => ['label' => 'Lunas', 'description' => 'Pembayaran berhasil, pesanan sedang diproses'],
    ];

    public array $deliverySteps = [
        'processing' => ['label' => 'Diproses', 'description' => 'Pesanan sedang disiapkan'],
        'shipped' => ['label' => 'Dikirim', 'description' => 'Pesanan sedang dalam perjalanan'],
        'delivered' => ['label' => 'Diterima', 'description' => 'Pesanan telah diterima'],
        'completed' => ['label' => 'Selesai', 'description' => 'Pesanan selesai'],
    ];

    public function render()
    {
        $userId = auth()->id();

        // Get only cart type orders (fresh meat purchases)
        $ordersQuery = Order::cart()
            ->where('user_id', $userId)
            ->with(['items.product'])
            ->when($this->searchQuery, function ($q) {
                $q->where(function ($query) {
                    $query->where('order_number', 'like', '%' . $this->searchQuery . '%');
                });
            })
            ->when($this->statusFilter !== 'all', function ($q) {
                // Use effective_status for backward compatibility
                $q->where('status', $this->statusFilter);
            })
            ->orderBy('created_at', 'desc');

        $orders = $ordersQuery->get();

        // OPTIMIZED: Single query for all stats using CASE WHEN
        $statsData = DB::table('orders')
            ->where('user_id', $userId)
            ->where('order_type', 'cart')
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status IN ('paid', 'processing') THEN 1 ELSE 0 END) as processing,
                SUM(CASE WHEN status = 'shipped' THEN 1 ELSE 0 END) as shipped,
                SUM(CASE WHEN status IN ('delivered', 'completed') THEN 1 ELSE 0 END) as completed
            ")
            ->first();

        $stats = [
            'total' => (int) $statsData->total,
            'pending' => (int) $statsData->pending,
            'processing' => (int) $statsData->processing,
            'shipped' => (int) $statsData->shipped,
            'completed' => (int) $statsData->completed,
        ];

        $selectedOrder = $this->selectedOrderId
            ? Order::with(['items.product', 'statusLogs.triggeredBy'])->find($this->selectedOrderId)
            : null;

        return view('livewire.pelanggan.my-orders', [
            'orders' => $orders,
            'stats' => $stats,
            'selectedOrder' => $selectedOrder,
            'formatRupiah' => fn ($amount) => 'Rp ' . number_format($amount, 0, ',', '.'),
        ]);
    }

    public function selectOrder(int $orderId): void
    {
        $this->selectedOrderId = $orderId;
    }

    public function closeDetail(): void
    {
        $this->selectedOrderId = null;
    }

    public function cancelOrder(int $orderId): void
    {
        $order = Order::where('user_id', auth()->id())
            ->where('id', $orderId)
            ->where('status', 'pending')
            ->first();

        if ($order) {
            $fromStatus = $order->status;

            // Use state machine for cancellation
            $result = OrderStateMachine::transitionToCancelled(
                $order,
                OrderStatusLog::TRIGGER_CUSTOMER,
                auth()->id(),
                'Dibatalkan oleh pelanggan'
            );

            if ($result['success']) {
                // Create notification
                CustomerNotification::forOrderStatus(
                    auth()->id(),
                    $order->order_number,
                    'cancelled',
                    'Pesanan Anda telah dibatalkan.'
                );

                $this->dispatch('notify', [
                    'type' => 'success',
                    'title' => 'Berhasil',
                    'message' => 'Pesanan berhasil dibatalkan.',
                ]);
            } else {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'title' => 'Gagal',
                    'message' => $result['message'],
                ]);
            }

            $this->closeDetail();
        }
    }

    public function markAsRead(int $orderId): void
    {
        // For future: mark related notifications as read
    }

    /**
     * Get payment progress for an order.
     */
    public function getPaymentProgress(Order $order): array
    {
        $status = $order->status;
        $steps = ['pending', 'paid'];
        $currentIndex = array_search($status, $steps);

        // If status is paid or beyond, payment is complete
        if (in_array($status, ['paid', 'processing', 'shipped', 'delivered', 'completed'])) {
            $currentIndex = 1;
        } elseif ($currentIndex === false) {
            $currentIndex = 0;
        }

        return [
            'steps' => $this->paymentSteps,
            'currentStep' => $currentIndex,
            'isComplete' => $currentIndex >= 1,
        ];
    }

    /**
     * Get delivery progress for an order.
     */
    public function getDeliveryProgress(Order $order): array
    {
        $status = $order->status;
        $stepKeys = array_keys($this->deliverySteps);
        $currentIndex = 0;

        foreach ($stepKeys as $index => $key) {
            if ($status === $key || in_array($status, ['shipped', 'delivered', 'completed'])) {
                if (in_array($status, ['delivered', 'completed'])) {
                    $currentIndex = 3; // Delivered = index 3
                } elseif ($status === 'shipped') {
                    $currentIndex = 2; // Shipped = index 2
                } elseif ($status === 'processing') {
                    $currentIndex = 1; // Processing = index 1
                }
                break;
            }
        }

        return [
            'steps' => $this->deliverySteps,
            'currentStep' => $currentIndex,
            'isComplete' => $currentIndex >= 3,
        ];
    }

    /**
     * Get status explanation for customers.
     */
    public function getStatusExplanation(string $status): array
    {
        return match($status) {
            'pending' => [
                'title' => 'Menunggu Pembayaran',
                'description' => 'Selesaikan pembayaran Anda menggunakan Midtrans. Pesanan akan diproses setelah pembayaran berhasil.',
                'action' => 'Bayar Sekarang',
                'actionUrl' => 'checkout',
                'icon' => 'clock',
                'color' => 'amber',
            ],
            'paid' => [
                'title' => 'Pembayaran Berhasil',
                'description' => 'Pembayaran Anda telah kami terima. Tim kami sedang mempersiapkan pesanan Anda.',
                'action' => null,
                'actionUrl' => null,
                'icon' => 'check-circle',
                'color' => 'green',
            ],
            'processing' => [
                'title' => 'Sedang Diproses',
                'description' => 'Pesanan Anda sedang disiapkan dan dikemas. Biasanya memakan waktu 1-2 hari kerja.',
                'action' => null,
                'actionUrl' => null,
                'icon' => 'cog',
                'color' => 'blue',
            ],
            'shipped' => [
                'title' => 'Sedang Dikirim',
                'description' => 'Pesanan telah dikirim! Gunakan nomor resi untuk melacak pengiriman.',
                'action' => 'Lacak Pengiriman',
                'actionUrl' => null,
                'icon' => 'truck',
                'color' => 'purple',
            ],
            'delivered' => [
                'title' => 'Telah Diterima',
                'description' => 'Pesanan telah diterima. Jangan lupa untuk memberikan review!',
                'action' => 'Beri Review',
                'actionUrl' => null,
                'icon' => 'package',
                'color' => 'emerald',
            ],
            'completed' => [
                'title' => 'Selesai',
                'description' => 'Transaksi telah selesai. Terima kasih telah berbelanja di AbodaFarm!',
                'action' => 'Beri Review',
                'actionUrl' => null,
                'icon' => 'check-double',
                'color' => 'emerald',
            ],
            'cancelled' => [
                'title' => 'Dibatalkan',
                'description' => 'Pesanan ini telah dibatalkan.',
                'action' => null,
                'actionUrl' => null,
                'icon' => 'xmark',
                'color' => 'slate',
            ],
            default => [
                'title' => 'Status Tidak Diketahui',
                'description' => 'Hubungi kami untuk informasi lebih lanjut.',
                'action' => null,
                'actionUrl' => null,
                'icon' => 'question',
                'color' => 'slate',
            ],
        };
    }
}
