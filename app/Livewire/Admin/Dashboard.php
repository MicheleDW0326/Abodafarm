<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.main')]
#[Title('Dashboard - AbodaFarm')]
class Dashboard extends Component
{
    private const CRITICAL_STOCK_THRESHOLD = 10;

    public function render()
    {
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfWeek = $now->copy()->startOfWeek();

        // ============================================================
        // SINGLE OPTIMIZED QUERY: Core Stats
        // ============================================================
        $orderStats = DB::table('orders')
            ->selectRaw('
                COUNT(*) as total_orders,
                SUM(total_amount) as total_revenue,
                SUM(CASE WHEN placed_at >= ? THEN total_amount ELSE 0 END) as this_month_revenue,
                SUM(CASE WHEN placed_at >= ? AND placed_at < ? THEN total_amount ELSE 0 END) as last_month_revenue,
                SUM(CASE WHEN placed_at >= ? THEN 1 ELSE 0 END) as this_month_orders,
                SUM(CASE WHEN placed_at >= ? AND placed_at < ? THEN 1 ELSE 0 END) as last_month_orders,
                SUM(CASE WHEN placed_at >= ? THEN 1 ELSE 0 END) as this_week_orders
            ', [
                $startOfMonth, $startOfMonth, $now, $startOfMonth, $startOfMonth, $now, $startOfWeek
            ])
            ->first();

        $bookingStats = DB::table('orders')
            ->selectRaw('
                COUNT(*) as total_bookings,
                SUM(CASE WHEN placed_at >= ? THEN 1 ELSE 0 END) as this_month_bookings,
                SUM(CASE WHEN placed_at >= ? AND status IN (?, ?) THEN 1 ELSE 0 END) as approved_bookings
            ', [$startOfMonth, $startOfMonth, 'approved', 'completed'])
            ->where('order_type', 'inquiry')
            ->first();

        $userStats = DB::table('users')
            ->selectRaw('
                COUNT(*) as total_customers,
                SUM(CASE WHEN users.created_at >= ? THEN 1 ELSE 0 END) as new_customers
            ', [$startOfMonth])
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('roles.name', 'pelanggan')
            ->first();

        // ============================================================
        // CALCULATE CHANGES
        // ============================================================
        $thisMonthRevenue = (int) ($orderStats->this_month_revenue ?? 0);
        $lastMonthRevenue = (int) ($orderStats->last_month_revenue ?? 0);
        $thisMonthOrders = (int) ($orderStats->this_month_orders ?? 0);
        $lastMonthOrders = (int) ($orderStats->last_month_orders ?? 0);
        $thisWeekOrders = (int) ($orderStats->this_week_orders ?? 0);
        $totalOrders = (int) ($orderStats->total_orders ?? 0);
        $totalRevenue = (int) ($orderStats->total_revenue ?? 0);

        $revenueChange = $this->calcChange($thisMonthRevenue, $lastMonthRevenue);
        $ordersChange = $this->calcChange($thisMonthOrders, $lastMonthOrders);

        // ============================================================
        // ANALYTICS CARDS
        // ============================================================
        $analyticsCards = [
            [
                'label' => 'Total Pendapatan',
                'value' => $this->formatCompact($totalRevenue),
                'sub_value' => 'Rp ' . number_format($thisMonthRevenue, 0, ',', '.') . ' bulan ini',
                'change' => $revenueChange['text'],
                'positive' => $revenueChange['positive'],
                'icon' => 'fa-solid fa-money-bill-wave',
                'bg' => 'bg-emerald-50',
                'text' => 'text-emerald-600',
            ],
            [
                'label' => 'Total Pesanan',
                'value' => number_format($totalOrders, 0, ',', '.'),
                'sub_value' => $thisMonthOrders . ' pesanan bulan ini',
                'change' => $ordersChange['text'],
                'positive' => $ordersChange['positive'],
                'icon' => 'fa-solid fa-shopping-cart',
                'bg' => 'bg-blue-50',
                'text' => 'text-blue-600',
            ],
            [
                'label' => 'Total Booking',
                'value' => number_format($bookingStats->total_bookings ?? 0, 0, ',', '.'),
                'sub_value' => ($bookingStats->this_month_bookings ?? 0) . ' booking bulan ini',
                'icon' => 'fa-solid fa-calendar-check',
                'bg' => 'bg-violet-50',
                'text' => 'text-violet-600',
            ],
            [
                'label' => 'Pelanggan',
                'value' => number_format($userStats->total_customers ?? 0, 0, ',', '.'),
                'sub_value' => '+' . ($userStats->new_customers ?? 0) . ' bulan ini',
                'icon' => 'fa-solid fa-users',
                'bg' => 'bg-purple-50',
                'text' => 'text-purple-600',
            ],
        ];

        // ============================================================
        // TOP SELLING PRODUCT (Single Query)
        // ============================================================
        $topProduct = OrderItem::selectRaw('product_id, product_name, SUM(quantity) as total_qty, SUM(subtotal) as total_sales')
            ->whereHas('order', fn($q) => $q->whereIn('status', ['delivered', 'completed', 'shipped']))
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('total_sales')
            ->first();

        $topProductData = $topProduct ? [
            'name' => $topProduct->product_name,
            'qty' => number_format($topProduct->total_qty, 0, ',', '.'),
            'sales' => $this->formatCompact($topProduct->total_sales),
        ] : null;

        // ============================================================
        // TOP FARM (Single Query)
        // ============================================================
        $topFarms = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('users', 'products.owner_id', '=', 'users.id')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->selectRaw('
                users.id,
                users.name as farm_name,
                COUNT(DISTINCT orders.id) as total_orders,
                SUM(order_items.subtotal) as total_sales
            ')
            ->where('roles.name', 'peternak')
            ->whereIn('orders.status', ['delivered', 'completed', 'shipped'])
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_sales')
            ->limit(3)
            ->get();

        // ============================================================
        // MONTHLY REVENUE CHART (Single Query)
        // ============================================================
        $monthlyData = DB::table('orders')
            ->selectRaw('MONTH(placed_at) as month, SUM(total_amount) as revenue, COUNT(*) as orders')
            ->whereYear('placed_at', $now->year)
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        $monthlyRevenue = [];
        $monthlyOrders = [];
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        for ($m = 1; $m <= 12; $m++) {
            $monthlyRevenue[] = (int) ($monthlyData[$m]->revenue ?? 0);
            $monthlyOrders[] = (int) ($monthlyData[$m]->orders ?? 0);
        }

        // ============================================================
        // WEEKLY ORDERS CHART (Single Query)
        // ============================================================
        $weeklyData = DB::table('orders')
            ->selectRaw('
                DAYOFWEEK(placed_at) as day,
                COUNT(*) as orders
            ')
            ->whereBetween('placed_at', [$now->copy()->startOfWeek(), $now])
            ->groupBy('day')
            ->pluck('orders', 'day')
            ->toArray();

        $weekDays = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
        $weeklyOrders = [];
        for ($d = 1; $d <= 7; $d++) {
            $weeklyOrders[] = (int) ($weeklyData[$d] ?? 0);
        }

        // ============================================================
        // LATEST TRANSACTIONS (Single Query)
        // ============================================================
        $latestTransactions = DB::table('orders')
            ->selectRaw('
                orders.id,
                orders.order_number,
                orders.customer_name,
                orders.total_amount,
                orders.status,
                orders.order_type,
                orders.created_at,
                users.name as user_name
            ')
            ->leftJoin('users', 'orders.user_id', '=', 'users.id')
            ->orderByDesc('orders.created_at')
            ->limit(8)
            ->get();

        // ============================================================
        // ORDER STATUS COUNTS (Single Query)
        // ============================================================
        $statusCounts = DB::table('orders')
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $totalStatusOrders = array_sum($statusCounts) ?: 1;

        $orderStatusDistribution = [
            ['label' => 'Pending', 'count' => $statusCounts['pending'] ?? 0, 'color' => 'bg-amber-500'],
            ['label' => 'Diproses', 'count' => $statusCounts['processing'] ?? 0, 'color' => 'bg-blue-500'],
            ['label' => 'Dikirim', 'count' => $statusCounts['shipped'] ?? 0, 'color' => 'bg-violet-500'],
            ['label' => 'Selesai', 'count' => ($statusCounts['delivered'] ?? 0) + ($statusCounts['completed'] ?? 0), 'color' => 'bg-emerald-500'],
            ['label' => 'Batal', 'count' => ($statusCounts['cancelled'] ?? 0) + ($statusCounts['rejected'] ?? 0), 'color' => 'bg-red-500'],
        ];

        // ============================================================
        // CRITICAL STOCKS (Single Query)
        // ============================================================
        $criticalStocks = Product::with('category:id,name')
            ->select('id', 'name', 'category_id', 'stock')
            ->where('stock', '<', self::CRITICAL_STOCK_THRESHOLD)
            ->where('type', 'fresh')
            ->orderBy('stock', 'asc')
            ->limit(5)
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'category' => $p->category?->name,
                'stock' => $p->stock,
            ]);

        return view('livewire.admin.dashboard', [
            'analyticsCards' => $analyticsCards,
            'topProduct' => $topProductData,
            'topFarms' => $topFarms,
            'monthlyRevenue' => $monthlyRevenue,
            'monthlyOrders' => $monthlyOrders,
            'weeklyOrders' => $weeklyOrders,
            'weekDays' => $weekDays,
            'months' => $months,
            'latestTransactions' => $latestTransactions,
            'orderStatusDistribution' => $orderStatusDistribution,
            'totalStatusOrders' => $totalStatusOrders,
            'criticalStocks' => $criticalStocks,
            'chartYear' => $now->year,
            'thisWeekOrders' => $thisWeekOrders,
            'formatCompact' => fn($n) => $this->formatCompact($n),
        ]);
    }

    private function calcChange(float $current, float $previous): array
    {
        if ($previous <= 0) {
            return ['text' => $current > 0 ? '+100%' : '0%', 'positive' => true];
        }
        $delta = (($current - $previous) / $previous) * 100;
        return [
            'text' => sprintf('%+.0f%%', $delta),
            'positive' => $delta >= 0,
        ];
    }

    private function formatCompact(int $amount): string
    {
        if ($amount >= 1_000_000_000) {
            return 'Rp ' . number_format($amount / 1_000_000_000, 1) . ' M';
        }
        if ($amount >= 1_000_000) {
            return 'Rp ' . number_format($amount / 1_000_000, 1) . ' Jt';
        }
        if ($amount >= 1_000) {
            return 'Rp ' . number_format($amount / 1_000, 0) . 'K';
        }
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}
