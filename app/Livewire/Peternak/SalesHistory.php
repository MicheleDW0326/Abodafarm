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

        // OPTIMIZED: Single query for all stats using CASE WHEN
        $statsData = DB::table('order_items as oi')
            ->join('orders as o', 'oi.order_id', '=', 'o.id')
            ->join('products as p', 'oi.product_id', '=', 'p.id')
            ->where('p.owner_id', $userId)
            ->whereBetween('o.placed_at', [$this->dateFrom, $this->dateTo . ' 23:59:59'])
            ->selectRaw("
                SUM(CASE WHEN o.status IN ('completed', 'delivered', 'approved', 'ready_for_delivery') THEN oi.subtotal ELSE 0 END) as total_revenue,
                (SELECT COUNT(DISTINCT id) FROM orders WHERE order_type = 'inquiry' AND EXISTS (SELECT 1 FROM order_items WHERE order_id = orders.id AND product_id IN (SELECT id FROM products WHERE owner_id = ?)) AND status = 'pending') as pending_bookings,
                (SELECT COUNT(DISTINCT id) FROM orders WHERE order_type = 'inquiry' AND EXISTS (SELECT 1 FROM order_items WHERE order_id = orders.id AND product_id IN (SELECT id FROM products WHERE owner_id = ?)) AND status = 'approved') as approved_bookings,
                (SELECT COUNT(DISTINCT id) FROM orders WHERE order_type = 'inquiry' AND EXISTS (SELECT 1 FROM order_items WHERE order_id = orders.id AND product_id IN (SELECT id FROM products WHERE owner_id = ?)) AND status = 'ready_for_delivery') as ready_for_delivery
            ", [$userId, $userId, $userId])
            ->first();

        $stats = [
            'total_revenue' => (float) $statsData->total_revenue,
            'pending_bookings' => (int) $statsData->pending_bookings,
            'approved_bookings' => (int) $statsData->approved_bookings,
            'ready_for_delivery' => (int) $statsData->ready_for_delivery,
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
