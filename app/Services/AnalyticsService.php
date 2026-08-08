<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\FarmProfile;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    protected const CACHE_TTL = 300;

    protected Carbon $startDate;
    protected Carbon $endDate;
    protected string $cacheKey;

    public function __construct(
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ) {
        $this->startDate = $startDate ?? now()->startOfDay();
        $this->endDate = $endDate ?? now()->endOfDay();
        $this->cacheKey = "analytics_{$this->startDate->timestamp}_{$this->endDate->timestamp}";
    }

    public function setDateRange(Carbon $startDate, Carbon $endDate): self
    {
        $this->startDate = $startDate->startOfDay();
        $this->endDate = $endDate->endOfDay();
        $this->cacheKey = "analytics_{$this->startDate->timestamp}_{$this->endDate->timestamp}";
        return $this;
    }

    public function getDateRange(): array
    {
        return [
            'start' => $this->startDate,
            'end' => $this->endDate,
        ];
    }

    // =====================================================
    // KPI METHODS
    // =====================================================

    public function getTotalRevenue(): float
    {
        return Cache::remember("{$this->cacheKey}_total_revenue", self::CACHE_TTL, function () {
            return (float) Order::whereBetween('created_at', [$this->startDate, $this->endDate])
                ->whereIn('status', [Order::STATUS_COMPLETED, Order::STATUS_DELIVERED])
                ->sum('total_amount');
        });
    }

    public function getTodayRevenue(): float
    {
        return Cache::remember("{$this->cacheKey}_today_revenue", self::CACHE_TTL, function () {
            return (float) Order::whereDate('created_at', today())
                ->whereIn('status', [Order::STATUS_COMPLETED, Order::STATUS_DELIVERED])
                ->sum('total_amount');
        });
    }

    public function getMonthlyRevenue(): float
    {
        return Cache::remember("{$this->cacheKey}_monthly_revenue", self::CACHE_TTL, function () {
            return (float) Order::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->whereIn('status', [Order::STATUS_COMPLETED, Order::STATUS_DELIVERED])
                ->sum('total_amount');
        });
    }

    public function getTotalOrders(): int
    {
        return Cache::remember("{$this->cacheKey}_total_orders", self::CACHE_TTL, function () {
            return Order::whereBetween('created_at', [$this->startDate, $this->endDate])->count();
        });
    }

    public function getCompletedOrders(): int
    {
        return Cache::remember("{$this->cacheKey}_completed_orders", self::CACHE_TTL, function () {
            return Order::whereBetween('created_at', [$this->startDate, $this->endDate])
                ->where('status', Order::STATUS_COMPLETED)
                ->count();
        });
    }

    public function getCancelledOrders(): int
    {
        return Cache::remember("{$this->cacheKey}_cancelled_orders", self::CACHE_TTL, function () {
            return Order::whereBetween('created_at', [$this->startDate, $this->endDate])
                ->where('status', Order::STATUS_CANCELLED)
                ->count();
        });
    }

    public function getPendingOrders(): int
    {
        return Cache::remember("{$this->cacheKey}_pending_orders", self::CACHE_TTL, function () {
            return Order::whereBetween('created_at', [$this->startDate, $this->endDate])
                ->whereIn('status', [Order::STATUS_PENDING, Order::STATUS_PROCESSING])
                ->count();
        });
    }

    public function getActiveCustomers(): int
    {
        return Cache::remember("{$this->cacheKey}_active_customers", self::CACHE_TTL, function () {
            return Order::whereBetween('created_at', [$this->startDate, $this->endDate])
                ->where('user_id', '!=', null)
                ->distinct('user_id')
                ->count('user_id');
        });
    }

    public function getActiveFarmers(): int
    {
        return Cache::remember("{$this->cacheKey}_active_farmers", self::CACHE_TTL, function () {
            return FarmProfile::where('status', 'active')
                ->whereHas('user', function ($query) {
                    $query->where('is_active', true);
                })
                ->count();
        });
    }

    public function getKpiCards(): array
    {
        return Cache::remember("{$this->cacheKey}_kpi_cards", self::CACHE_TTL, function () {
            return [
                'total_revenue' => $this->getTotalRevenue(),
                'today_revenue' => $this->getTodayRevenue(),
                'monthly_revenue' => $this->getMonthlyRevenue(),
                'total_orders' => $this->getTotalOrders(),
                'completed_orders' => $this->getCompletedOrders(),
                'cancelled_orders' => $this->getCancelledOrders(),
                'pending_orders' => $this->getPendingOrders(),
                'active_customers' => $this->getActiveCustomers(),
                'active_farmers' => $this->getActiveFarmers(),
            ];
        });
    }

    // =====================================================
    // SALES CHART METHODS
    // =====================================================

    public function getDailySales(int $days = 30): array
    {
        return Cache::remember("{$this->cacheKey}_daily_sales_{$days}", self::CACHE_TTL, function () use ($days) {
            $start = now()->subDays($days)->startOfDay();
            $sales = Order::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(total_amount) as revenue')
            )
                ->whereBetween('created_at', [$start, now()])
                ->whereIn('status', [Order::STATUS_COMPLETED, Order::STATUS_DELIVERED])
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->keyBy('date');

            $result = [];
            for ($i = $days; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $result[] = [
                    'date' => $date,
                    'orders' => $sales->get($date)?->orders ?? 0,
                    'revenue' => $sales->get($date)?->revenue ?? 0,
                ];
            }

            return $result;
        });
    }

    public function getWeeklySales(int $weeks = 12): array
    {
        return Cache::remember("{$this->cacheKey}_weekly_sales_{$weeks}", self::CACHE_TTL, function () use ($weeks) {
            $start = now()->subWeeks($weeks)->startOfWeek();
            $sales = Order::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('WEEK(created_at) as week'),
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(total_amount) as revenue')
            )
                ->whereBetween('created_at', [$start, now()])
                ->whereIn('status', [Order::STATUS_COMPLETED, Order::STATUS_DELIVERED])
                ->groupBy('year', 'week')
                ->orderBy('year')
                ->orderBy('week')
                ->get();

            return $sales->map(function ($item) {
                return [
                    'year' => $item->year,
                    'week' => $item->week,
                    'label' => "Week {$item->week}, {$item->year}",
                    'orders' => $item->orders,
                    'revenue' => (float) $item->revenue,
                ];
            })->toArray();
        });
    }

    public function getMonthlySales(int $months = 12): array
    {
        return Cache::remember("{$this->cacheKey}_monthly_sales_{$months}", self::CACHE_TTL, function () use ($months) {
            $start = now()->subMonths($months)->startOfMonth();
            $sales = Order::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(total_amount) as revenue')
            )
                ->whereBetween('created_at', [$start, now()])
                ->whereIn('status', [Order::STATUS_COMPLETED, Order::STATUS_DELIVERED])
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get()
                ->keyBy(fn($item) => "{$item->year}-{$item->month}");

            $result = [];
            for ($i = $months - 1; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $key = "{$date->year}-{$date->month}";
                $result[] = [
                    'year' => $date->year,
                    'month' => $date->month,
                    'label' => $date->format('M Y'),
                    'orders' => $sales->get($key)?->orders ?? 0,
                    'revenue' => $sales->get($key)?->revenue ?? 0,
                ];
            }

            return $result;
        });
    }

    public function getYearlySales(int $years = 5): array
    {
        return Cache::remember("{$this->cacheKey}_yearly_sales_{$years}", self::CACHE_TTL, function () use ($years) {
            $start = now()->subYears($years)->startOfYear();
            $sales = Order::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(total_amount) as revenue')
            )
                ->whereBetween('created_at', [$start, now()])
                ->whereIn('status', [Order::STATUS_COMPLETED, Order::STATUS_DELIVERED])
                ->groupBy('year')
                ->orderBy('year')
                ->get()
                ->keyBy('year');

            $result = [];
            for ($i = $years - 1; $i >= 0; $i--) {
                $year = now()->subYears($i)->year;
                $result[] = [
                    'year' => $year,
                    'orders' => $sales->get($year)?->orders ?? 0,
                    'revenue' => $sales->get($year)?->revenue ?? 0,
                ];
            }

            return $result;
        });
    }

    // =====================================================
    // PRODUCT ANALYTICS
    // =====================================================

    public function getTopSellingProducts(int $limit = 10): array
    {
        return Cache::remember("{$this->cacheKey}_top_selling_products_{$limit}", self::CACHE_TTL, function () use ($limit) {
            return OrderItem::select(
                'product_id',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(price * quantity) as total_revenue')
            )
                ->whereHas('order', function ($query) {
                    $query->whereBetween('created_at', [$this->startDate, $this->endDate])
                        ->whereIn('status', [Order::STATUS_COMPLETED, Order::STATUS_DELIVERED]);
                })
                ->whereNotNull('product_id')
                ->groupBy('product_id')
                ->orderByDesc('total_quantity')
                ->limit($limit)
                ->with('product:id,name,image,category')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->product_id,
                        'name' => $item->product?->name ?? 'Unknown',
                        'image' => $item->product?->image,
                        'category' => $item->product?->category?->name ?? 'Uncategorized',
                        'quantity' => $item->total_quantity,
                        'revenue' => (float) $item->total_revenue,
                    ];
                })
                ->toArray();
        });
    }

    public function getTopSellingLivestock(int $limit = 10): array
    {
        return Cache::remember("{$this->cacheKey}_top_selling_livestock_{$limit}", self::CACHE_TTL, function () use ($limit) {
            return OrderItem::select(
                'product_id',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(price * quantity) as total_revenue')
            )
                ->whereHas('order', function ($query) {
                    $query->whereBetween('created_at', [$this->startDate, $this->endDate])
                        ->whereIn('status', [Order::STATUS_COMPLETED, Order::STATUS_DELIVERED]);
                })
                ->whereHas('product', function ($query) {
                    $query->where('category_id', 1);
                })
                ->whereNotNull('product_id')
                ->groupBy('product_id')
                ->orderByDesc('total_quantity')
                ->limit($limit)
                ->with('product:id,name,image')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->product_id,
                        'name' => $item->product?->name ?? 'Unknown',
                        'image' => $item->product?->image,
                        'quantity' => $item->total_quantity,
                        'revenue' => (float) $item->total_revenue,
                    ];
                })
                ->toArray();
        });
    }

    public function getLowStockProducts(int $limit = 10): array
    {
        return Cache::remember("{$this->cacheKey}_low_stock_products_{$limit}", self::CACHE_TTL, function () use ($limit) {
            return Product::select('id', 'name', 'image', 'stock', 'category_id')
                ->where('stock', '>', 0)
                ->where('stock', '<=', 10)
                ->with('category:id,name')
                ->orderBy('stock')
                ->limit($limit)
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'image' => $product->image,
                        'category' => $product->category?->name ?? 'Uncategorized',
                        'stock' => $product->stock,
                    ];
                })
                ->toArray();
        });
    }

    public function getOutOfStockProducts(int $limit = 10): array
    {
        return Cache::remember("{$this->cacheKey}_out_of_stock_products_{$limit}", self::CACHE_TTL, function () use ($limit) {
            return Product::select('id', 'name', 'image', 'category_id')
                ->where('stock', 0)
                ->with('category:id,name')
                ->orderBy('updated_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'image' => $product->image,
                        'category' => $product->category?->name ?? 'Uncategorized',
                    ];
                })
                ->toArray();
        });
    }

    public function getMostViewedProducts(int $limit = 10): array
    {
        return Cache::remember("{$this->cacheKey}_most_viewed_products_{$limit}", self::CACHE_TTL, function () use ($limit) {
            return Product::select('id', 'name', 'image', 'view_count', 'category_id')
                ->where('view_count', '>', 0)
                ->with('category:id,name')
                ->orderByDesc('view_count')
                ->limit($limit)
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'image' => $product->image,
                        'category' => $product->category?->name ?? 'Uncategorized',
                        'views' => $product->view_count,
                    ];
                })
                ->toArray();
        });
    }

    // =====================================================
    // FARMER ANALYTICS
    // =====================================================

    public function getTopFarmers(int $limit = 10): array
    {
        return Cache::remember("{$this->cacheKey}_top_farmers_{$limit}", self::CACHE_TTL, function () use ($limit) {
            $farmers = FarmProfile::with('user:id,name', 'user.reviews')
                ->where('status', 'active')
                ->get()
                ->map(function ($farmer) {
                    $totalRevenue = OrderItem::whereHas('order', function ($query) use ($farmer) {
                        $query->whereBetween('created_at', [$this->startDate, $this->endDate])
                            ->whereIn('status', [Order::STATUS_COMPLETED, Order::STATUS_DELIVERED]);
                    })
                        ->where('farmer_id', $farmer->user_id)
                        ->sum(DB::raw('price * quantity'));

                    $completedBookings = Booking::where('farmer_id', $farmer->user_id)
                        ->whereBetween('created_at', [$this->startDate, $this->endDate])
                        ->where('status', Booking::STATUS_COMPLETED)
                        ->count();

                    $avgRating = $farmer->user->reviews()->avg('rating') ?? 0;

                    return [
                        'id' => $farmer->id,
                        'user_id' => $farmer->user_id,
                        'name' => $farmer->user->name ?? 'Unknown',
                        'shop_name' => $farmer->shop_name,
                        'image' => $farmer->image,
                        'revenue' => (float) $totalRevenue,
                        'completed_bookings' => $completedBookings,
                        'rating' => round($avgRating, 1),
                        'trust_score' => $farmer->trust_score ?? 0,
                    ];
                })
                ->sortByDesc('revenue')
                ->take($limit)
                ->values()
                ->toArray();

            return $farmers;
        });
    }

    public function getHighestRevenueFarmers(int $limit = 10): array
    {
        $topFarmers = $this->getTopFarmers($limit);
        usort($topFarmers, fn($a, $b) => $b['revenue'] <=> $a['revenue']);
        return array_slice($topFarmers, 0, $limit);
    }

    public function getMostCompletedBookingFarmers(int $limit = 10): array
    {
        $topFarmers = $this->getTopFarmers($limit);
        usort($topFarmers, fn($a, $b) => $b['completed_bookings'] <=> $a['completed_bookings']);
        return array_slice($topFarmers, 0, $limit);
    }

    public function getBestRatedFarmers(int $limit = 10): array
    {
        $topFarmers = $this->getTopFarmers($limit);
        usort($topFarmers, fn($a, $b) => $b['rating'] <=> $a['rating']);
        return array_slice($topFarmers, 0, $limit);
    }

    // =====================================================
    // CUSTOMER ANALYTICS
    // =====================================================

    public function getNewCustomers(int $limit = 10): array
    {
        return Cache::remember("{$this->cacheKey}_new_customers_{$limit}", self::CACHE_TTL, function () use ($limit) {
            return User::select('id', 'name', 'email', 'avatar', 'created_at')
                ->whereBetween('created_at', [$this->startDate, $this->endDate])
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'pelanggan');
                })
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get()
                ->map(function ($user) {
                    $totalOrders = Order::where('user_id', $user->id)->count();
                    $totalSpent = Order::where('user_id', $user->id)
                        ->whereIn('status', [Order::STATUS_COMPLETED, Order::STATUS_DELIVERED])
                        ->sum('total_amount');

                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'avatar' => $user->avatar,
                        'joined_at' => $user->created_at,
                        'total_orders' => $totalOrders,
                        'total_spent' => (float) $totalSpent,
                    ];
                })
                ->toArray();
        });
    }

    public function getRepeatCustomers(int $limit = 10): array
    {
        return Cache::remember("{$this->cacheKey}_repeat_customers_{$limit}", self::CACHE_TTL, function () use ($limit) {
            return User::select('users.id', 'users.name', 'users.email', 'users.avatar')
                ->join('orders', 'users.id', '=', 'orders.user_id')
                ->whereBetween('orders.created_at', [$this->startDate, $this->endDate])
                ->whereIn('orders.status', [Order::STATUS_COMPLETED, Order::STATUS_DELIVERED])
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'pelanggan');
                })
                ->groupBy('users.id', 'users.name', 'users.email', 'users.avatar')
                ->selectRaw('COUNT(orders.id) as order_count, SUM(orders.total_amount) as total_spent')
                ->orderByDesc('order_count')
                ->limit($limit)
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'avatar' => $user->avatar,
                        'order_count' => $user->order_count,
                        'total_spent' => (float) $user->total_spent,
                    ];
                })
                ->toArray();
        });
    }

    public function getTopBuyers(int $limit = 10): array
    {
        return Cache::remember("{$this->cacheKey}_top_buyers_{$limit}", self::CACHE_TTL, function () use ($limit) {
            return User::select('users.id', 'users.name', 'users.email', 'users.avatar')
                ->join('orders', 'users.id', '=', 'orders.user_id')
                ->whereBetween('orders.created_at', [$this->startDate, $this->endDate])
                ->whereIn('orders.status', [Order::STATUS_COMPLETED, Order::STATUS_DELIVERED])
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'pelanggan');
                })
                ->groupBy('users.id', 'users.name', 'users.email', 'users.avatar')
                ->selectRaw('SUM(orders.total_amount) as total_spent, COUNT(orders.id) as order_count')
                ->orderByDesc('total_spent')
                ->limit($limit)
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'avatar' => $user->avatar,
                        'total_spent' => (float) $user->total_spent,
                        'order_count' => $user->order_count,
                    ];
                })
                ->toArray();
        });
    }

    public function getCustomerGrowth(): array
    {
        return Cache::remember("{$this->cacheKey}_customer_growth", self::CACHE_TTL, function () {
            $currentPeriod = User::whereBetween('created_at', [$this->startDate, $this->endDate])
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'pelanggan');
                })
                ->count();

            $daysDiff = $this->startDate->diffInDays($this->endDate);
            $previousStart = $this->startDate->copy()->subDays($daysDiff);
            $previousEnd = $this->startDate->copy()->subDay();

            $previousPeriod = User::whereBetween('created_at', [$previousStart, $previousEnd])
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'pelanggan');
                })
                ->count();

            $growth = $previousPeriod > 0
                ? round((($currentPeriod - $previousPeriod) / $previousPeriod) * 100, 1)
                : ($currentPeriod > 0 ? 100 : 0);

            return [
                'current' => $currentPeriod,
                'previous' => $previousPeriod,
                'growth' => $growth,
            ];
        });
    }

    // =====================================================
    // FINANCIAL SUMMARY
    // =====================================================

    public function getFinancialSummary(): array
    {
        return Cache::remember("{$this->cacheKey}_financial_summary", self::CACHE_TTL, function () {
            $orders = Order::whereBetween('created_at', [$this->startDate, $this->endDate])
                ->whereIn('status', [Order::STATUS_COMPLETED, Order::STATUS_DELIVERED])
                ->selectRaw('
                    SUM(total_amount) as gross_revenue,
                    SUM(discount_amount) as discount_total,
                    SUM(shipping_cost) as shipping_revenue
                ')
                ->first();

            $promotionCost = Promotion::whereBetween('start_date', [$this->startDate, $this->endDate])
                ->where('is_active', true)
                ->sum('budget');

            $grossRevenue = (float) ($orders->gross_revenue ?? 0);
            $discountTotal = (float) ($orders->discount_total ?? 0);
            $shippingRevenue = (float) ($orders->shipping_revenue ?? 0);
            $promotionCostTotal = (float) $promotionCost;
            $netRevenue = $grossRevenue - $discountTotal - $promotionCostTotal;

            return [
                'gross_revenue' => $grossRevenue,
                'discount_total' => $discountTotal,
                'promotion_cost' => $promotionCostTotal,
                'shipping_revenue' => $shippingRevenue,
                'net_revenue' => $netRevenue,
            ];
        });
    }

    // =====================================================
    // BOOKING ANALYTICS
    // =====================================================

    public function getBookingStats(): array
    {
        return Cache::remember("{$this->cacheKey}_booking_stats", self::CACHE_TTL, function () {
            $query = Booking::whereBetween('created_at', [$this->startDate, $this->endDate]);

            return [
                'total' => (clone $query)->count(),
                'completed' => (clone $query)->where('status', Booking::STATUS_COMPLETED)->count(),
                'cancelled' => (clone $query)->where('status', Booking::STATUS_CANCELLED)->count(),
                'pending' => (clone $query)->where('status', Booking::STATUS_PENDING)->count(),
                'total_amount' => (clone $query)->where('status', Booking::STATUS_COMPLETED)->sum('total_amount'),
            ];
        });
    }

    // =====================================================
    // CACHE MANAGEMENT
    // =====================================================

    public function clearCache(): void
    {
        Cache::forget("{$this->cacheKey}_kpi_cards");
        Cache::forget("{$this->cacheKey}_total_revenue");
        Cache::forget("{$this->cacheKey}_today_revenue");
        Cache::forget("{$this->cacheKey}_monthly_revenue");
        Cache::forget("{$this->cacheKey}_total_orders");
        Cache::forget("{$this->cacheKey}_completed_orders");
        Cache::forget("{$this->cacheKey}_cancelled_orders");
        Cache::forget("{$this->cacheKey}_pending_orders");
        Cache::forget("{$this->cacheKey}_active_customers");
        Cache::forget("{$this->cacheKey}_active_farmers");
        Cache::forget("{$this->cacheKey}_financial_summary");
        Cache::forget("{$this->cacheKey}_customer_growth");
        Cache::forget("{$this->cacheKey}_booking_stats");
    }

    public static function clearAllCache(): void
    {
        Cache::flush();
    }
}
