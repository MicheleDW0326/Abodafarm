<?php

namespace App\Livewire\Peternak;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.main')]
#[Title('Riwayat Penjualan')]
class SalesHistory extends Component
{
    public string $dateFrom = '';
    public string $dateTo = '';
    public string $statusFilter = 'all';

    public function mount()
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function render()
    {
        $userId = auth()->id();

        // Get completed orders for farmer's products
        $completedStatuses = ['completed', 'delivered', 'ready_for_delivery', 'approved'];

        $salesQuery = OrderItem::join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('products.owner_id', $userId)
            ->when($this->statusFilter === 'all', fn($q) => $q->whereIn('orders.status', $completedStatuses))
            ->when($this->statusFilter !== 'all', fn($q) => $q->where('orders.status', $this->statusFilter))
            ->whereBetween('orders.placed_at', [$this->dateFrom, $this->dateTo . ' 23:59:59'])
            ->selectRaw('order_items.*, orders.order_number, orders.placed_at, orders.customer_name, orders.status as order_status, orders.shipment_status_id');

        $sales = $salesQuery->orderBy('orders.placed_at', 'desc')->get();

        $totalRevenue = $sales->sum('subtotal');
        $totalItems = $sales->sum('quantity');
        $totalOrders = $sales->pluck('order_id')->unique()->count();

        // Stats using new FK-based relationships
        $stats = [
            'total_revenue' => OrderItem::join('products', 'order_items.product_id', '=', 'products.id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('products.owner_id', $userId)
                ->whereIn('orders.status', $completedStatuses)
                ->whereBetween('orders.placed_at', [$this->dateFrom, $this->dateTo . ' 23:59:59'])
                ->sum('order_items.subtotal'),
            'pending_bookings' => Order::booking()
                ->whereHas('items.product', fn($q) => $q->where('owner_id', $userId))
                ->where('status', 'pending')
                ->count(),
            'approved_bookings' => Order::booking()
                ->whereHas('items.product', fn($q) => $q->where('owner_id', $userId))
                ->where('status', 'approved')
                ->count(),
            'ready_for_delivery' => Order::booking()
                ->whereHas('items.product', fn($q) => $q->where('owner_id', $userId))
                ->where('status', 'ready_for_delivery')
                ->count(),
        ];

        return view('livewire.peternak.sales-history', [
            'sales' => $sales,
            'totalRevenue' => $totalRevenue,
            'totalItems' => $totalItems,
            'totalOrders' => $totalOrders,
            'stats' => $stats,
        ]);
    }
}
