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

    public function mount()
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function render()
    {
        $userId = auth()->id();

        $salesQuery = OrderItem::join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('products.owner_id', $userId)
            ->where('orders.status', '!=', 'cancelled')
            ->whereBetween('orders.placed_at', [$this->dateFrom, $this->dateTo . ' 23:59:59'])
            ->selectRaw('order_items.*, orders.order_number, orders.placed_at, orders.customer_name, orders.status as order_status');

        $sales = $salesQuery->orderBy('orders.placed_at', 'desc')->get();

        $totalRevenue = $sales->sum('subtotal');
        $totalItems = $sales->sum('quantity');
        $totalOrders = $sales->pluck('order_id')->unique()->count();

        return view('livewire.peternak.sales-history', [
            'sales' => $sales,
            'totalRevenue' => $totalRevenue,
            'totalItems' => $totalItems,
            'totalOrders' => $totalOrders,
        ]);
    }
}
