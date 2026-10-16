<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.main')]
#[Title('Laporan')]
class FinancialReport extends Component
{
    // Report tab
    public string $activeTab = 'overview';

    // Date filters
    public string $period = 'month';
    public string $dateFrom = '';
    public string $dateTo = '';

    // Status filters
    public string $orderType = 'all'; // all, cart, inquiry
    public string $statusFilter = 'all';

    public function mount()
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    // ============================================================
    // TAB NAVIGATION
    // ============================================================
    public function setTab(string $tab)
    {
        $this->activeTab = $tab;
    }

    // ============================================================
    // OVERVIEW REPORT
    // ============================================================
    public function getOverviewStats(): array
    {
        $query = $this->getBaseOrderQuery();

        $totalRevenue = $query->sum('total_amount');
        $totalOrders = $query->count();
        $averageOrder = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        // Revenue by order type
        $cartRevenue = (clone $this->getBaseOrderQuery())->where('order_type', 'cart')->sum('total_amount');
        $inquiryRevenue = (clone $this->getBaseOrderQuery())->where('order_type', 'inquiry')->sum('total_amount');

        return [
            'total_revenue' => $totalRevenue,
            'total_orders' => $totalOrders,
            'average_order' => $averageOrder,
            'cart_revenue' => $cartRevenue,
            'inquiry_revenue' => $inquiryRevenue,
        ];
    }

    // ============================================================
    // ORDERS REPORT
    // ============================================================
    public function getOrdersReport(): array
    {
        // OPTIMIZED: SQL GROUP BY instead of PHP groupBy for payment status
        $ordersByPaymentStatus = DB::table('orders as o')
            ->join('order_statuses as ps', 'o.payment_status_id', '=', 'ps.id')
            ->whereBetween('o.placed_at', [$this->dateFrom, $this->dateTo . ' 23:59:59'])
            ->whereNotNull('o.payment_status_id')
            ->where('ps.type', 'payment')
            ->selectRaw("COALESCE(ps.code, 'unknown') as status, ps.label, ps.color, COUNT(*) as count, SUM(o.total_amount) as revenue")
            ->groupBy('ps.code', 'ps.label', 'ps.color')
            ->get()
            ->toArray();

        // OPTIMIZED: SQL GROUP BY instead of PHP groupBy for shipment status
        $ordersByShipmentStatus = DB::table('orders as o')
            ->join('order_statuses as ss', 'o.shipment_status_id', '=', 'ss.id')
            ->whereBetween('o.placed_at', [$this->dateFrom, $this->dateTo . ' 23:59:59'])
            ->whereNotNull('o.shipment_status_id')
            ->selectRaw("COALESCE(ss.code, 'unknown') as status, ss.label, ss.color, COUNT(*) as count, SUM(o.total_amount) as revenue")
            ->groupBy('ss.code', 'ss.label', 'ss.color')
            ->get()
            ->toArray();

        return [
            'by_payment_status' => $ordersByPaymentStatus,
            'by_shipment_status' => $ordersByShipmentStatus,
        ];
    }

    // ============================================================
    // BOOKINGS REPORT
    // ============================================================
    public function getBookingsReport(): array
    {
        // OPTIMIZED: SQL GROUP BY instead of PHP groupBy
        $bookingStats = DB::table('orders as o')
            ->join('order_statuses as bs', 'o.booking_status_id', '=', 'bs.id')
            ->whereBetween('o.placed_at', [$this->dateFrom, $this->dateTo . ' 23:59:59'])
            ->where('o.order_type', 'inquiry')
            ->whereNotNull('o.booking_status_id')
            ->selectRaw("COALESCE(bs.code, 'unknown') as status, bs.label, bs.color, COUNT(*) as count, SUM(o.total_amount) as revenue")
            ->groupBy('bs.code', 'bs.label', 'bs.color')
            ->get()
            ->toArray();

        // Single query for all booking counts
        $bookingCounts = DB::table('orders')
            ->whereBetween('placed_at', [$this->dateFrom, $this->dateTo . ' 23:59:59'])
            ->where('order_type', 'inquiry')
            ->selectRaw("
                COUNT(*) as total_bookings,
                SUM(CASE WHEN booking_status_id IS NOT NULL AND EXISTS (SELECT 1 FROM order_statuses WHERE id = booking_status_id AND code = 'survey_scheduled') THEN 1 ELSE 0 END) as survey_scheduled
            ")
            ->first();

        return [
            'by_status' => $bookingStats,
            'survey_scheduled' => (int) $bookingCounts->survey_scheduled,
            'total_bookings' => (int) $bookingCounts->total_bookings,
        ];
    }

    // ============================================================
    // PAYMENTS REPORT
    // ============================================================
    public function getPaymentsReport(): array
    {
        // OPTIMIZED: Single query with GROUP BY instead of loading all to memory
        // Note: Payment model uses 'transaction_status' column, not 'status'
        // and 'gross_amount' column, not 'amount'
        $paymentStats = Payment::whereBetween('created_at', [$this->dateFrom, $this->dateTo . ' 23:59:59'])
            ->selectRaw("
                COUNT(*) as total_transactions,
                SUM(CASE WHEN transaction_status IN ('settlement', 'capture') THEN 1 ELSE 0 END) as successful_payments,
                SUM(CASE WHEN transaction_status IN ('deny', 'cancel', 'expire') THEN 1 ELSE 0 END) as failed_payments,
                SUM(CASE WHEN transaction_status IN ('settlement', 'capture') THEN gross_amount ELSE 0 END) as total_collected
            ")
            ->first();

        $paymentMethods = Payment::whereBetween('created_at', [$this->dateFrom, $this->dateTo . ' 23:59:59'])
            ->selectRaw('payment_type as type, COUNT(*) as count, SUM(gross_amount) as amount')
            ->groupBy('payment_type')
            ->get()
            ->toArray();

        return [
            'total_transactions' => (int) $paymentStats->total_transactions,
            'successful_payments' => (int) $paymentStats->successful_payments,
            'failed_payments' => (int) $paymentStats->failed_payments,
            'total_collected' => (int) $paymentStats->total_collected,
            'payment_methods' => $paymentMethods,
        ];
    }

    // ============================================================
    // REVENUE REPORT
    // ============================================================
    public function getRevenueReport(): array
    {
        $format = match($this->period) {
            'day' => '%Y-%m-%d',
            'week' => '%Y-W%v',
            'month' => '%Y-%m',
            'year' => '%Y',
        };

        // Revenue by period
        $revenueByPeriod = Order::query()
            ->whereBetween('placed_at', [$this->dateFrom, $this->dateTo . ' 23:59:59'])
            ->selectRaw("DATE_FORMAT(placed_at, '{$format}') as period, SUM(total_amount) as revenue, COUNT(*) as orders")
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        // Revenue by status - OPTIMIZED: SQL GROUP BY instead of PHP groupBy
        $revenueByStatus = DB::table('orders as o')
            ->leftJoin('order_statuses as ss', 'o.shipment_status_id', '=', 'ss.id')
            ->whereBetween('o.placed_at', [$this->dateFrom, $this->dateTo . ' 23:59:59'])
            ->selectRaw("COALESCE(ss.code, 'completed') as status, COALESCE(ss.label, 'Selesai') as label, COALESCE(ss.color, 'emerald') as color, SUM(o.total_amount) as revenue, COUNT(*) as count")
            ->groupBy(DB::raw("COALESCE(ss.code, 'completed')"), DB::raw("COALESCE(ss.label, 'Selesai')"), DB::raw("COALESCE(ss.color, 'emerald')"))
            ->get()
            ->toArray();

        // Top products
        $topProducts = OrderItem::selectRaw('product_name, SUM(quantity) as total_qty, SUM(subtotal) as total_sales')
            ->whereHas('order', function ($query) {
                $query->whereBetween('placed_at', [$this->dateFrom, $this->dateTo . ' 23:59:59']);
            })
            ->groupBy('product_name')
            ->orderByDesc('total_sales')
            ->limit(10)
            ->get();

        return [
            'by_period' => $revenueByPeriod,
            'by_status' => $revenueByStatus,
            'top_products' => $topProducts,
        ];
    }

    // ============================================================
    // OPERATIONAL REPORT
    // ============================================================
    public function getOperationalReport(): array
    {
        // OPTIMIZED: Order status distribution - single query
        $statusDistribution = Order::query()
            ->whereBetween('placed_at', [$this->dateFrom, $this->dateTo . ' 23:59:59'])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // OPTIMIZED: Combined staff and farmer activity in one query
        $activityStats = DB::table('order_status_logs')
            ->join('users', 'order_status_logs.triggered_by_id', '=', 'users.id')
            ->whereBetween('order_status_logs.created_at', [$this->dateFrom, $this->dateTo . ' 23:59:59'])
            ->whereIn('triggered_by_type', ['staff', 'farmer'])
            ->selectRaw("
                triggered_by_type,
                users.id,
                users.name,
                COUNT(*) as action_count
            ")
            ->groupBy('triggered_by_type', 'users.id', 'users.name')
            ->orderByDesc('action_count')
            ->limit(10)
            ->get();

        $staffActivity = $activityStats->where('triggered_by_type', 'staff')->values();
        $farmerActivity = $activityStats->where('triggered_by_type', 'farmer')->values();

        return [
            'status_distribution' => $statusDistribution,
            'staff_activity' => $staffActivity,
            'farmer_activity' => $farmerActivity,
            'total_logs' => DB::table('order_status_logs')
                ->whereBetween('created_at', [$this->dateFrom, $this->dateTo . ' 23:59:59'])
                ->count(),
        ];
    }

    // ============================================================
    // HELPER METHODS
    // ============================================================
    protected function getBaseOrderQuery()
    {
        return Order::query()
            ->whereBetween('placed_at', [$this->dateFrom, $this->dateTo . ' 23:59:59'])
            ->when($this->orderType !== 'all', fn($q) => $q->where('order_type', $this->orderType));
    }

    public function getDailyComparison(): array
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

    public function getInventoryStats(): array
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

    // ============================================================
    // RENDER
    // ============================================================
    public function render()
    {
        return view('livewire.admin.financial-report', [
            'overviewStats' => $this->getOverviewStats(),
            'ordersReport' => $this->getOrdersReport(),
            'bookingsReport' => $this->getBookingsReport(),
            'paymentsReport' => $this->getPaymentsReport(),
            'revenueReport' => $this->getRevenueReport(),
            'operationalReport' => $this->getOperationalReport(),
            'dailyComparison' => $this->getDailyComparison(),
            'inventoryStats' => $this->getInventoryStats(),
        ]);
    }
}
