<?php

namespace App\Livewire\Peternak;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
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

        // Get only cart type orders (not bookings/inquiries)
        $ordersQuery = Order::cart()
            ->whereHas('items.product', function ($query) use ($userId) {
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
                // Use status column for backward compatibility
                $q->where('status', $this->statusFilter);
            })
            ->orderBy('created_at', 'desc');

        $orders = $ordersQuery->get();

        // OPTIMIZED: Single query for all stats using CASE WHEN
        $statsQuery = DB::table('orders as o')
            ->join('order_items as oi', 'o.id', '=', 'oi.order_id')
            ->join('products as p', 'oi.product_id', '=', 'p.id')
            ->where('p.owner_id', $userId)
            ->where('o.order_type', 'cart')
            ->selectRaw("
                COUNT(DISTINCT o.id) as total,
                SUM(CASE WHEN o.status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN o.status = 'processing' THEN 1 ELSE 0 END) as processing,
                SUM(CASE WHEN o.status = 'shipped' THEN 1 ELSE 0 END) as shipped
            ")
            ->first();

        $stats = [
            'total' => (int) $statsQuery->total,
            'pending' => (int) $statsQuery->pending,
            'processing' => (int) $statsQuery->processing,
            'shipped' => (int) $statsQuery->shipped,
        ];

        $selectedOrder = $this->selectedOrderId
            ? Order::with(['items.product' => fn($q) => $q->where('owner_id', $userId), 'statusLogs.triggeredBy'])->find($this->selectedOrderId)
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
