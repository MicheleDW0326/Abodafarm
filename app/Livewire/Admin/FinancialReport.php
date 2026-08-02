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
        // Order counts by status using new FK architecture
        $ordersByPaymentStatus = Order::query()
            ->whereBetween('placed_at', [$this->dateFrom, $this->dateTo . ' 23:59:59'])
            ->whereNotNull('payment_status_id')
            ->whereHas('paymentStatusRelation', function ($q) {
                $q->where('type', 'payment');
            })
            ->with('paymentStatusRelation')
            ->get()
            ->groupBy(fn($o) => $o->paymentStatusRelation?->code ?? 'unknown')
            ->map(fn($group) => [
                'status' => $group->first()->paymentStatusRelation?->code ?? 'unknown',
                'label' => $group->first()->paymentStatusRelation?->label ?? 'Unknown',
                'color' => $group->first()->paymentStatusRelation?->color ?? 'slate',
                'count' => $group->count(),
                'revenue' => $group->sum('total_amount'),
            ])
            ->values()
            ->toArray();

        $ordersByShipmentStatus = Order::query()
            ->whereBetween('placed_at', [$this->dateFrom, $this->dateTo . ' 23:59:59'])
            ->whereNotNull('shipment_status_id')
            ->whereHas('shipmentStatusRelation')
            ->with('shipmentStatusRelation')
            ->get()
            ->groupBy(fn($o) => $o->shipmentStatusRelation?->code ?? 'unknown')
            ->map(fn($group) => [
                'status' => $group->first()->shipmentStatusRelation?->code ?? 'unknown',
                'label' => $group->first()->shipmentStatusRelation?->label ?? 'Unknown',
                'color' => $group->first()->shipmentStatusRelation?->color ?? 'slate',
                'count' => $group->count(),
                'revenue' => $group->sum('total_amount'),
            ])
            ->values()
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
        $bookingStats = Order::booking()
            ->whereBetween('placed_at', [$this->dateFrom, $this->dateTo . ' 23:59:59'])
            ->with('bookingStatusRelation')
            ->get()
            ->groupBy(fn($o) => $o->bookingStatusRelation?->code ?? 'unknown')
            ->map(fn($group) => [
                'status' => $group->first()->bookingStatusRelation?->code ?? 'unknown',
                'label' => $group->first()->bookingStatusRelation?->label ?? 'Unknown',
                'color' => $group->first()->bookingStatusRelation?->color ?? 'slate',
                'count' => $group->count(),
                'revenue' => $group->sum('total_amount'),
            ])
            ->values()
            ->toArray();

        // Survey stats
        $surveyScheduled = Order::booking()
            ->whereBetween('placed_at', [$this->dateFrom, $this->dateTo . ' 23:59:59'])
            ->where('status', 'survey_scheduled')
            ->count();

        $totalBookings = Order::booking()
            ->whereBetween('placed_at', [$this->dateFrom, $this->dateTo . ' 23:59:59'])
            ->count();

        return [
            'by_status' => $bookingStats,
            'survey_scheduled' => $surveyScheduled,
            'total_bookings' => $totalBookings,
        ];
    }

    // ============================================================
    // PAYMENTS REPORT
    // ============================================================
    public function getPaymentsReport(): array
    {
        $payments = Payment::whereBetween('created_at', [$this->dateFrom, $this->dateTo . ' 23:59:59'])
            ->with('order')
            ->get();

        $paidPayments = $payments->where('status', 'paid');
        $failedPayments = $payments->where('status', 'failed');

        return [
            'total_transactions' => $payments->count(),
            'successful_payments' => $paidPayments->count(),
            'failed_payments' => $failedPayments->count(),
            'total_collected' => $paidPayments->sum('amount'),
            'payment_methods' => $payments
                ->groupBy('payment_type')
                ->map(fn($group) => [
                    'type' => $group->first()->payment_type ?? 'unknown',
                    'count' => $group->count(),
                    'amount' => $group->sum('amount'),
                ])
                ->values()
                ->toArray(),
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

        // Revenue by status
        $revenueByStatus = Order::query()
            ->whereBetween('placed_at', [$this->dateFrom, $this->dateTo . ' 23:59:59'])
            ->with('shipmentStatusRelation')
            ->get()
            ->groupBy(fn($o) => $o->shipmentStatusRelation?->code ?? 'completed')
            ->map(fn($group) => [
                'status' => $group->first()->shipmentStatusRelation?->code ?? 'completed',
                'label' => $group->first()->shipmentStatusRelation?->label ?? 'Selesai',
                'color' => $group->first()->shipmentStatusRelation?->color ?? 'emerald',
                'revenue' => $group->sum('total_amount'),
                'count' => $group->count(),
            ])
            ->values()
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
        // Order fulfillment metrics
        $orders = Order::whereBetween('placed_at', [$this->dateFrom, $this->dateTo . ' 23:59:59'])
            ->with(['statusLogs'])
            ->get();

        // Calculate average processing time
        $processingLogs = $orders
            ->flatMap(fn($o) => $o->statusLogs->toArray())
            ->filter(fn($log) => in_array($log['to_status'], ['processing', 'shipped']));

        // Order status distribution
        $statusDistribution = Order::query()
            ->whereBetween('placed_at', [$this->dateFrom, $this->dateTo . ' 23:59:59'])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Staff activity (based on status logs)
        $staffActivity = DB::table('order_status_logs')
            ->join('users', 'order_status_logs.triggered_by_id', '=', 'users.id')
            ->whereBetween('order_status_logs.created_at', [$this->dateFrom, $this->dateTo . ' 23:59:59'])
            ->where('triggered_by_type', 'staff')
            ->selectRaw('users.name, COUNT(*) as action_count')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('action_count')
            ->limit(10)
            ->get();

        // Farmer activity
        $farmerActivity = DB::table('order_status_logs')
            ->join('users', 'order_status_logs.triggered_by_id', '=', 'users.id')
            ->whereBetween('order_status_logs.created_at', [$this->dateFrom, $this->dateTo . ' 23:59:59'])
            ->where('triggered_by_type', 'farmer')
            ->selectRaw('users.name, COUNT(*) as action_count')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('action_count')
            ->limit(10)
            ->get();

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
