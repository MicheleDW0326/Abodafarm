<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\FarmProfile;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FarmerAnalyticsService
{
    protected const CACHE_TTL = 300;

    protected Carbon $startDate;
    protected Carbon $endDate;
    protected int $farmerId;

    public function __construct(
        int $farmerId,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ) {
        $this->farmerId = $farmerId;
        $this->startDate = $startDate ?? now()->startOfDay();
        $this->endDate = $endDate ?? now()->endOfDay();
    }

    public function setDateRange(Carbon $startDate, Carbon $endDate): self
    {
        $this->startDate = $startDate->startOfDay();
        $this->endDate = $endDate->endOfDay();
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
    // FARMER KPI METHODS
    // =====================================================

    public function getTotalRevenue(): float
    {
        return Cache::remember("farmer_{$this->farmerId}_revenue_{$this->startDate->timestamp}", self::CACHE_TTL, function () {
            return (float) OrderItem::where('farmer_id', $this->farmerId)
                ->whereHas('order', function ($query) {
                    $query->whereBetween('created_at', [$this->startDate, $this->endDate])
                        ->whereIn('status', [Order::STATUS_COMPLETED, Order::STATUS_DELIVERED]);
                })
                ->sum(DB::raw('price * quantity'));
        });
    }

    public function getTotalOrders(): int
    {
        return Cache::remember("farmer_{$this->farmerId}_orders_{$this->startDate->timestamp}", self::CACHE_TTL, function () {
            return OrderItem::where('farmer_id', $this->farmerId)
                ->whereHas('order', function ($query) {
                    $query->whereBetween('created_at', [$this->startDate, $this->endDate]);
                })
                ->distinct('order_id')
                ->count('order_id');
        });
    }

    public function getCompletedOrders(): int
    {
        return Cache::remember("farmer_{$this->farmerId}_completed_{$this->startDate->timestamp}", self::CACHE_TTL, function () {
            return OrderItem::where('farmer_id', $this->farmerId)
                ->whereHas('order', function ($query) {
                    $query->whereBetween('created_at', [$this->startDate, $this->endDate])
                        ->where('status', Order::STATUS_COMPLETED);
                })
                ->distinct('order_id')
                ->count('order_id');
        });
    }

    public function getPendingOrders(): int
    {
        return Cache::remember("farmer_{$this->farmerId}_pending_{$this->startDate->timestamp}", self::CACHE_TTL, function () {
            return OrderItem::where('farmer_id', $this->farmerId)
                ->whereHas('order', function ($query) {
                    $query->whereBetween('created_at', [$this->startDate, $this->endDate])
                        ->whereIn('status', [Order::STATUS_PENDING, Order::STATUS_PROCESSING]);
                })
                ->distinct('order_id')
                ->count('order_id');
        });
    }

    public function getTotalBookings(): int
    {
        return Cache::remember("farmer_{$this->farmerId}_bookings_{$this->startDate->timestamp}", self::CACHE_TTL, function () {
            return Booking::where('farmer_id', $this->farmerId)
                ->whereBetween('created_at', [$this->startDate, $this->endDate])
                ->count();
        });
    }

    public function getCompletedBookings(): int
    {
        return Cache::remember("farmer_{$this->farmerId}_bookings_completed_{$this->startDate->timestamp}", self::CACHE_TTL, function () {
            return Booking::where('farmer_id', $this->farmerId)
                ->whereBetween('created_at', [$this->startDate, $this->endDate])
                ->where('status', Booking::STATUS_COMPLETED)
                ->count();
        });
    }

    public function getAverageRating(): float
    {
        return Cache::remember("farmer_{$this->farmerId}_rating_{$this->startDate->timestamp}", self::CACHE_TTL, function () {
            $avg = Review::where('farmer_id', $this->farmerId)
                ->whereBetween('created_at', [$this->startDate, $this->endDate])
                ->avg('rating');
            return round($avg ?? 0, 1);
        });
    }

    public function getTotalReviews(): int
    {
        return Cache::remember("farmer_{$this->farmerId}_reviews_{$this->startDate->timestamp}", self::CACHE_TTL, function () {
            return Review::where('farmer_id', $this->farmerId)
                ->whereBetween('created_at', [$this->startDate, $this->endDate])
                ->count();
        });
    }

    public function getKpiCards(): array
    {
        return Cache::remember("farmer_{$this->farmerId}_kpi_{$this->startDate->timestamp}", self::CACHE_TTL, function () {
            return [
                'total_revenue' => $this->getTotalRevenue(),
                'total_orders' => $this->getTotalOrders(),
                'completed_orders' => $this->getCompletedOrders(),
                'pending_orders' => $this->getPendingOrders(),
                'total_bookings' => $this->getTotalBookings(),
                'completed_bookings' => $this->getCompletedBookings(),
                'average_rating' => $this->getAverageRating(),
                'total_reviews' => $this->getTotalReviews(),
            ];
        });
    }

    // =====================================================
    // PRODUCT ANALYTICS
    // =====================================================

    public function getTopSellingProducts(int $limit = 10): array
    {
        return Cache::remember("farmer_{$this->farmerId}_top_products_{$limit}_{$this->startDate->timestamp}", self::CACHE_TTL, function () use ($limit) {
            return OrderItem::select(
                'product_id',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(price * quantity) as total_revenue')
            )
                ->where('farmer_id', $this->farmerId)
                ->whereHas('order', function ($query) {
                    $query->whereBetween('created_at', [$this->startDate, $this->endDate])
                        ->whereIn('status', [Order::STATUS_COMPLETED, Order::STATUS_DELIVERED]);
                })
                ->whereNotNull('product_id')
                ->groupBy('product_id')
                ->orderByDesc('total_quantity')
                ->limit($limit)
                ->with('product:id,name,image,stock')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->product_id,
                        'name' => $item->product?->name ?? 'Unknown',
                        'image' => $item->product?->image,
                        'stock' => $item->product?->stock ?? 0,
                        'quantity' => $item->total_quantity,
                        'revenue' => (float) $item->total_revenue,
                    ];
                })
                ->toArray();
        });
    }

    public function getLowStockProducts(int $limit = 10): array
    {
        return Cache::remember("farmer_{$this->farmerId}_low_stock_{$limit}", self::CACHE_TTL, function () use ($limit) {
            return Product::where('user_id', $this->farmerId)
                ->where('stock', '>', 0)
                ->where('stock', '<=', 10)
                ->orderBy('stock')
                ->limit($limit)
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'image' => $product->image,
                        'stock' => $product->stock,
                    ];
                })
                ->toArray();
        });
    }

    public function getOutOfStockProducts(int $limit = 10): array
    {
        return Cache::remember("farmer_{$this->farmerId}_out_of_stock_{$limit}", self::CACHE_TTL, function () use ($limit) {
            return Product::where('user_id', $this->farmerId)
                ->where('stock', 0)
                ->orderBy('updated_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'image' => $product->image,
                    ];
                })
                ->toArray();
        });
    }

    public function getMostViewedProducts(int $limit = 10): array
    {
        return Cache::remember("farmer_{$this->farmerId}_most_viewed_{$limit}", self::CACHE_TTL, function () use ($limit) {
            return Product::where('user_id', $this->farmerId)
                ->where('view_count', '>', 0)
                ->orderByDesc('view_count')
                ->limit($limit)
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'image' => $product->image,
                        'views' => $product->view_count,
                    ];
                })
                ->toArray();
        });
    }

    // =====================================================
    // SALES CHART METHODS
    // =====================================================

    public function getDailySales(int $days = 30): array
    {
        return Cache::remember("farmer_{$this->farmerId}_daily_sales_{$days}_{$this->startDate->timestamp}", self::CACHE_TTL, function () use ($days) {
            $start = now()->subDays($days)->startOfDay();
            $sales = OrderItem::select(
                DB::raw('DATE(order_items.created_at) as date'),
                DB::raw('SUM(order_items.quantity) as quantity'),
                DB::raw('SUM(order_items.price * order_items.quantity) as revenue')
            )
                ->where('farmer_id', $this->farmerId)
                ->whereHas('order', function ($query) use ($start) {
                    $query->whereBetween('created_at', [$start, now()])
                        ->whereIn('status', [Order::STATUS_COMPLETED, Order::STATUS_DELIVERED]);
                })
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->keyBy('date');

            $result = [];
            for ($i = $days; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $result[] = [
                    'date' => $date,
                    'quantity' => $sales->get($date)?->quantity ?? 0,
                    'revenue' => $sales->get($date)?->revenue ?? 0,
                ];
            }

            return $result;
        });
    }

    public function getMonthlySales(int $months = 12): array
    {
        return Cache::remember("farmer_{$this->farmerId}_monthly_sales_{$months}_{$this->startDate->timestamp}", self::CACHE_TTL, function () use ($months) {
            $start = now()->subMonths($months)->startOfMonth();
            $sales = OrderItem::select(
                DB::raw('YEAR(order_items.created_at) as year'),
                DB::raw('MONTH(order_items.created_at) as month'),
                DB::raw('SUM(order_items.quantity) as quantity'),
                DB::raw('SUM(order_items.price * order_items.quantity) as revenue')
            )
                ->where('farmer_id', $this->farmerId)
                ->whereHas('order', function ($query) use ($start) {
                    $query->whereBetween('created_at', [$start, now()])
                        ->whereIn('status', [Order::STATUS_COMPLETED, Order::STATUS_DELIVERED]);
                })
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
                    'quantity' => $sales->get($key)?->quantity ?? 0,
                    'revenue' => $sales->get($key)?->revenue ?? 0,
                ];
            }

            return $result;
        });
    }

    // =====================================================
    // REVIEW ANALYTICS
    // =====================================================

    public function getRecentReviews(int $limit = 10): array
    {
        return Cache::remember("farmer_{$this->farmerId}_recent_reviews_{$limit}", self::CACHE_TTL, function () use ($limit) {
            return Review::where('farmer_id', $this->farmerId)
                ->whereBetween('created_at', [$this->startDate, $this->endDate])
                ->with('user:id,name,avatar', 'order:id,order_number')
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get()
                ->map(function ($review) {
                    return [
                        'id' => $review->id,
                        'user_name' => $review->user?->name ?? 'Anonymous',
                        'user_avatar' => $review->user?->avatar,
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                        'product_name' => $review->product?->name,
                        'order_number' => $review->order?->order_number,
                        'created_at' => $review->created_at,
                    ];
                })
                ->toArray();
        });
    }

    public function getRatingDistribution(): array
    {
        return Cache::remember("farmer_{$this->farmerId}_rating_dist_{$this->startDate->timestamp}", self::CACHE_TTL, function () {
            $distribution = Review::where('farmer_id', $this->farmerId)
                ->whereBetween('created_at', [$this->startDate, $this->endDate])
                ->select('rating', DB::raw('COUNT(*) as count'))
                ->groupBy('rating')
                ->pluck('count', 'rating')
                ->toArray();

            $result = [];
            for ($i = 5; $i >= 1; $i--) {
                $result[$i] = $distribution[$i] ?? 0;
            }

            return $result;
        });
    }

    // =====================================================
    // FINANCIAL SUMMARY
    // =====================================================

    public function getFinancialSummary(): array
    {
        return Cache::remember("farmer_{$this->farmerId}_financial_{$this->startDate->timestamp}", self::CACHE_TTL, function () {
            $orders = OrderItem::where('farmer_id', $this->farmerId)
                ->whereHas('order', function ($query) {
                    $query->whereBetween('created_at', [$this->startDate, $this->endDate])
                        ->whereIn('status', [Order::STATUS_COMPLETED, Order::STATUS_DELIVERED]);
                })
                ->selectRaw('
                    SUM(price * quantity) as gross_revenue,
                    SUM(quantity) as total_items
                ')
                ->first();

            $bookingsRevenue = Booking::where('farmer_id', $this->farmerId)
                ->whereBetween('created_at', [$this->startDate, $this->endDate])
                ->where('status', Booking::STATUS_COMPLETED)
                ->sum('total_amount');

            return [
                'gross_revenue' => (float) ($orders->gross_revenue ?? 0) + (float) $bookingsRevenue,
                'product_revenue' => (float) ($orders->gross_revenue ?? 0),
                'booking_revenue' => (float) $bookingsRevenue,
                'total_items_sold' => (int) ($orders->total_items ?? 0),
            ];
        });
    }

    // =====================================================
    // CACHE MANAGEMENT
    // =====================================================

    public function clearCache(): void
    {
        Cache::forget("farmer_{$this->farmerId}_kpi_{$this->startDate->timestamp}");
        Cache::forget("farmer_{$this->farmerId}_revenue_{$this->startDate->timestamp}");
        Cache::forget("farmer_{$this->farmerId}_orders_{$this->startDate->timestamp}");
        Cache::forget("farmer_{$this->farmerId}_financial_{$this->startDate->timestamp}");
    }
}
