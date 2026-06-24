<?php

namespace App\Livewire\Pelanggan;

use App\Models\Order;
use App\Models\CustomerNotification;
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
                $q->where('status', $this->statusFilter);
            })
            ->orderBy('created_at', 'desc');

        $orders = $ordersQuery->get();

        // Stats
        $stats = [
            'total' => Order::cart()->where('user_id', $userId)->count(),
            'pending' => Order::cart()->where('user_id', $userId)->where('status', 'pending')->count(),
            'processing' => Order::cart()->where('user_id', $userId)->whereIn('status', ['paid', 'processing'])->count(),
            'shipped' => Order::cart()->where('user_id', $userId)->where('status', 'shipped')->count(),
            'completed' => Order::cart()->where('user_id', $userId)->whereIn('status', ['delivered', 'completed'])->count(),
        ];

        $selectedOrder = $this->selectedOrderId
            ? Order::with(['items.product'])->find($this->selectedOrderId)
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
            ->whereIn('status', ['pending'])
            ->first();

        if ($order) {
            $order->update(['status' => 'cancelled']);

            // Create notification
            CustomerNotification::forOrderStatus(
                auth()->id(),
                $order->order_number,
                'cancelled',
                'Pesanan Anda telah dibatalkan.'
            );

            $this->closeDetail();

            $this->dispatch('notify', [
                'type' => 'success',
                'title' => 'Berhasil',
                'message' => 'Pesanan berhasil dibatalkan.',
            ]);
        }
    }

    public function markAsRead(int $orderId): void
    {
        // For future: mark related notifications as read
    }
}
