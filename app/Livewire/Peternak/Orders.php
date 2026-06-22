<?php

namespace App\Livewire\Peternak;

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.main')]
#[Title('Pesanan Masuk')]
class Orders extends Component
{
    public string $statusFilter = 'all';
    public string $searchQuery = '';
    public ?int $selectedOrderId = null;

    public function render()
    {
        $userId = auth()->id();

        $ordersQuery = Order::whereHas('items.product', function ($query) use ($userId) {
            $query->where('owner_id', $userId);
        })
            ->with(['items.product' => function ($q) use ($userId) {
                $q->where('owner_id', $userId);
            }])
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

        $orders = $ordersQuery->get();

        // Stats
        $stats = [
            'total' => Order::whereHas('items.product', fn($q) => $q->where('owner_id', $userId))->count(),
            'pending' => Order::whereHas('items.product', fn($q) => $q->where('owner_id', $userId))->where('status', 'pending')->count(),
            'processing' => Order::whereHas('items.product', fn($q) => $q->where('owner_id', $userId))->where('status', 'processing')->count(),
            'shipped' => Order::whereHas('items.product', fn($q) => $q->where('owner_id', $userId))->where('status', 'shipped')->count(),
        ];

        $selectedOrder = $this->selectedOrderId
            ? Order::with(['items.product' => fn($q) => $q->where('owner_id', $userId)])->find($this->selectedOrderId)
            : null;

        return view('livewire.peternak.orders', [
            'orders' => $orders,
            'stats' => $stats,
            'selectedOrder' => $selectedOrder,
        ]);
    }

    public function selectOrder(int $orderId)
    {
        $this->selectedOrderId = $orderId;
    }

    public function closeDetail()
    {
        $this->selectedOrderId = null;
    }
}
