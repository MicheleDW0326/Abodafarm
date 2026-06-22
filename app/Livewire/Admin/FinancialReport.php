<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.main')]
#[Title('Laporan Keuangan')]
class FinancialReport extends Component
{
    public string $period = 'month'; // day, week, month, year
    public string $dateFrom = '';
    public string $dateTo = '';

    public function mount()
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function getSummaryStats()
    {
        $query = Order::query()
            ->whereBetween('placed_at', [$this->dateFrom, $this->dateTo . ' 23:59:59']);

        $totalRevenue = $query->sum('total_amount');
        $totalOrders = $query->count();
        $averageOrder = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        return [
            'total_revenue' => $totalRevenue,
            'total_orders' => $totalOrders,
            'average_order' => $averageOrder,
        ];
    }

    public function getRevenueByPeriod()
    {
        $format = match($this->period) {
            'day' => '%Y-%m-%d',
            'week' => '%Y-W%v',
            'month' => '%Y-%m',
            'year' => '%Y',
        };

        $data = Order::selectRaw("DATE_FORMAT(placed_at, '{$format}') as period, SUM(total_amount) as revenue, COUNT(*) as orders")
            ->whereBetween('placed_at', [$this->dateFrom, $this->dateTo . ' 23:59:59'])
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return $data;
    }

    public function getRevenueByStatus()
    {
        return Order::selectRaw('status, SUM(total_amount) as revenue, COUNT(*) as orders')
            ->whereBetween('placed_at', [$this->dateFrom, $this->dateTo . ' 23:59:59'])
            ->groupBy('status')
            ->get();
    }

    public function getTopProducts()
    {
        return OrderItem::selectRaw('product_name, SUM(quantity) as total_qty, SUM(subtotal) as total_sales')
            ->whereHas('order', function ($query) {
                $query->whereBetween('placed_at', [$this->dateFrom, $this->dateTo . ' 23:59:59']);
            })
            ->groupBy('product_name')
            ->orderByDesc('total_sales')
            ->limit(10)
            ->get();
    }

    public function getDailyComparison()
    {
        $today = now()->format('Y-m-d');
        $yesterday = now()->subDay()->format('Y-m-d');

        $todayRevenue = Order::whereDate('placed_at', $today)->sum('total_amount');
        $yesterdayRevenue = Order::whereDate('placed_at', $yesterday)->sum('total_amount');

        $todayOrders = Order::whereDate('placed_at', $today)->count();
        $yesterdayOrders = Order::whereDate('placed_at', $yesterday)->count();

        return [
            'today_revenue' => $todayRevenue,
            'yesterday_revenue' => $yesterdayRevenue,
            'today_orders' => $todayOrders,
            'yesterday_orders' => $yesterdayOrders,
            'revenue_change' => $yesterdayRevenue > 0 ? (($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100 : 0,
            'orders_change' => $yesterdayOrders > 0 ? (($todayOrders - $yesterdayOrders) / $yesterdayOrders) * 100 : 0,
        ];
    }

    public function getInventoryStats()
    {
        $liveProducts = Product::live()->count();
        $freshProducts = Product::freshType()->count();
        $availableProducts = Product::available()->count();
        $lowStockProducts = Product::freshType()->where('stock', '<', 50)->count();

        return [
            'live' => $liveProducts,
            'fresh' => $freshProducts,
            'available' => $availableProducts,
            'low_stock' => $lowStockProducts,
        ];
    }

    public function render()
    {
        return view('livewire.admin.financial-report', [
            'summary' => $this->getSummaryStats(),
            'revenueByPeriod' => $this->getRevenueByPeriod(),
            'revenueByStatus' => $this->getRevenueByStatus(),
            'topProducts' => $this->getTopProducts(),
            'dailyComparison' => $this->getDailyComparison(),
            'inventoryStats' => $this->getInventoryStats(),
        ]);
    }
}
