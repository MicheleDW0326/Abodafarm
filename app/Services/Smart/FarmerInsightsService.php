<?php

namespace App\Services\Smart;

use App\Models\Booking;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FarmerInsightsService
{
    protected const CACHE_TTL = 600;
    protected int $farmerId;

    public function __construct(int $farmerId)
    {
        $this->farmerId = $farmerId;
    }

    /**
     * Get all insights for a farmer dashboard.
     */
    public function getInsights(): array
    {
        $cacheKey = "farmer_insights_{$this->farmerId}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            return [
                'best_selling' => $this->getBestSellingProduct(),
                'sales_trend' => $this->getSalesTrend(),
                'low_performing' => $this->getLowPerformingProducts(),
                'low_stock_alerts' => $this->getLowStockAlerts(),
                'recommended_actions' => $this->getRecommendedActions(),
                'customer_feedback' => $this->getRecentCustomerFeedback(),
                'growth_metrics' => $this->getGrowthMetrics(),
            ];
        });
    }

    /**
     * Get best selling product insight.
     */
    public function getBestSellingProduct(): ?array
    {
        $currentMonth = OrderItem::where('farmer_id', $this->farmerId)
            ->whereHas('order', fn($q) => $q->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->whereIn('status', [Order::STATUS_DELIVERED, Order::STATUS_COMPLETED]))
            ->selectRaw('product_id, SUM(quantity) as total_sold, SUM(price * quantity) as total_revenue')
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->first();

        if (!$currentMonth) {
            return null;
        }

        $product = Product::find($currentMonth->product_id);

        // Compare with last month
        $lastMonthSold = OrderItem::where('farmer_id', $this->farmerId)
            ->where('product_id', $currentMonth->product_id)
            ->whereHas('order', fn($q) => $q->whereMonth('created_at', now()->subMonth()->month)
                ->whereYear('created_at', now()->subMonth()->year)
                ->whereIn('status', [Order::STATUS_DELIVERED, Order::STATUS_COMPLETED]))
            ->sum('quantity');

        $change = $lastMonthSold > 0
            ? round((($currentMonth->total_sold - $lastMonthSold) / $lastMonthSold) * 100, 1)
            : 100;

        return [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'image' => $product->image_url,
            ],
            'total_sold' => $currentMonth->total_sold,
            'total_revenue' => (float) $currentMonth->total_revenue,
            'change_percentage' => $change,
            'trend' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'stable'),
            'message' => $this->generateBestSellerMessage($product->name, $currentMonth->total_sold, $change),
        ];
    }

    /**
     * Get sales trend insight.
     */
    public function getSalesTrend(): array
    {
        $currentMonth = $this->getMonthlySales(now()->month, now()->year);
        $lastMonth = $this->getMonthlySales(now()->subMonth()->month, now()->subMonth()->year);

        $currentRevenue = $currentMonth['revenue'];
        $lastRevenue = $lastMonth['revenue'];
        $currentOrders = $currentMonth['orders'];
        $lastOrders = $lastMonth['orders'];

        $revenueChange = $lastRevenue > 0
            ? round((($currentRevenue - $lastRevenue) / $lastRevenue) * 100, 1)
            : ($currentRevenue > 0 ? 100 : 0);

        $ordersChange = $lastOrders > 0
            ? round((($currentOrders - $lastOrders) / $lastOrders) * 100, 1)
            : ($currentOrders > 0 ? 100 : 0);

        return [
            'revenue' => [
                'current' => $currentRevenue,
                'change' => $revenueChange,
                'trend' => $revenueChange > 0 ? 'up' : ($revenueChange < 0 ? 'down' : 'stable'),
            ],
            'orders' => [
                'current' => $currentOrders,
                'change' => $ordersChange,
                'trend' => $ordersChange > 0 ? 'up' : ($ordersChange < 0 ? 'down' : 'stable'),
            ],
            'message' => $this->generateSalesTrendMessage($revenueChange, $ordersChange),
        ];
    }

    /**
     * Get low performing products insight.
     */
    public function getLowPerformingProducts(): array
    {
        $products = Product::where('user_id', $this->farmerId)
            ->availableWithStatus()
            ->withCount([
                'orderItems as total_sold' => fn($q) => $q
                    ->whereHas('order', fn($oq) => $oq
                        ->whereIn('status', [Order::STATUS_DELIVERED, Order::STATUS_COMPLETED])
                        ->where('created_at', '>=', now()->subDays(30)))
            ])
            ->having('total_sold', '<=', 3)
            ->having('total_sold', '>', 0)
            ->orderBy('total_sold')
            ->limit(3)
            ->get(['id', 'name', 'image', 'price']);

        if ($products->isEmpty()) {
            return [];
        }

        return $products->map(fn($p) => [
            'product' => [
                'id' => $p->id,
                'name' => $p->name,
                'image' => $p->image_url,
            ],
            'sold_30_days' => $p->total_sold,
            'suggestion' => $this->getLowPerformSuggestion($p),
        ])->toArray();
    }

    /**
     * Get low stock alerts.
     */
    public function getLowStockAlerts(): array
    {
        $products = Product::where('user_id', $this->farmerId)
            ->where('stock', '>', 0)
            ->where('stock', '<=', 10)
            ->orderBy('stock')
            ->limit(5)
            ->get(['id', 'name', 'image', 'stock']);

        $outOfStock = Product::where('user_id', $this->farmerId)
            ->where('stock', 0)
            ->where('status', 'available')
            ->orderByDesc('updated_at')
            ->limit(3)
            ->get(['id', 'name', 'image']);

        $alerts = [];

        foreach ($products as $p) {
            $urgency = $p->stock <= 3 ? 'high' : 'medium';
            $alerts[] = [
                'type' => 'low_stock',
                'urgency' => $urgency,
                'product' => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'image' => $p->image_url,
                ],
                'current_stock' => $p->stock,
                'message' => $urgency === 'high'
                    ? "Urgent: Only {$p->stock} units left of {$p->name}"
                    : "{$p->name} is running low ({$p->stock} units)",
            ];
        }

        foreach ($outOfStock as $p) {
            $alerts[] = [
                'type' => 'out_of_stock',
                'urgency' => 'high',
                'product' => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'image' => $p->image_url,
                ],
                'message' => "{$p->name} is out of stock",
            ];
        }

        return $alerts;
    }

    /**
     * Get recommended actions for the farmer.
     */
    public function getRecommendedActions(): array
    {
        $actions = [];

        // Check for low performing products
        $lowPerforming = $this->getLowPerformingProducts();
        if (count($lowPerforming) > 0) {
            $actions[] = [
                'type' => 'improve_listing',
                'title' => 'Improve Product Visibility',
                'description' => count($lowPerforming) . ' product(s) have low sales. Consider updating descriptions or adjusting prices.',
                'priority' => 'medium',
                'action_url' => '/peternak/ternak',
            ];
        }

        // Check for low stock
        $lowStock = Product::where('user_id', $this->farmerId)
            ->where('stock', '>', 0)
            ->where('stock', '<=', 10)
            ->count();
        if ($lowStock > 0) {
            $actions[] = [
                'type' => 'restock',
                'title' => 'Restock Products',
                'description' => "{$lowStock} product(s) are running low on stock.",
                'priority' => 'high',
                'action_url' => '/peternak/ternak',
            ];
        }

        // Check for products without reviews
        $noReviews = Product::where('user_id', $this->farmerId)
            ->where('total_reviews', 0)
            ->count();
        if ($noReviews > 3) {
            $actions[] = [
                'type' => 'get_reviews',
                'title' => 'Build Your Reputation',
                'description' => 'Encourage customers to leave reviews by providing excellent service.',
                'priority' => 'low',
                'action_url' => null,
            ];
        }

        // Check for rating improvement opportunity
        $avgRating = Review::where('farmer_id', $this->farmerId)->avg('rating');
        if ($avgRating && $avgRating < 4.0) {
            $actions[] = [
                'type' => 'improve_quality',
                'title' => 'Improve Product Quality',
                'description' => 'Your average rating could be better. Focus on product quality and customer service.',
                'priority' => 'high',
                'action_url' => null,
            ];
        }

        // Sort by priority
        usort($actions, fn($a, $b) => $this->getPriorityScore($b['priority']) <=> $this->getPriorityScore($a['priority']));

        return array_slice($actions, 0, 5);
    }

    /**
     * Get recent customer feedback insight.
     */
    public function getRecentCustomerFeedback(): array
    {
        $recentReviews = Review::where('farmer_id', $this->farmerId)
            ->with('user:id,name,avatar', 'product:id,name')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $avgRating = Review::where('farmer_id', $this->farmerId)->avg('rating') ?? 0;
        $totalReviews = Review::where('farmer_id', $this->farmerId)->count();

        $ratingDistribution = Review::where('farmer_id', $this->farmerId)
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        return [
            'average_rating' => round($avgRating, 1),
            'total_reviews' => $totalReviews,
            'rating_distribution' => $ratingDistribution,
            'recent_reviews' => $recentReviews->map(fn($r) => [
                'id' => $r->id,
                'user' => [
                    'name' => $r->user?->name ?? 'Anonymous',
                    'avatar' => $r->user?->avatar,
                ],
                'product' => [
                    'name' => $r->product?->name,
                ],
                'rating' => $r->rating,
                'comment' => $r->comment,
                'created_at' => $r->created_at->diffForHumans(),
            ]),
        ];
    }

    /**
     * Get growth metrics.
     */
    public function getGrowthMetrics(): array
    {
        $currentMonth = $this->getMonthlyStats(now()->month, now()->year);
        $lastMonth = $this->getMonthlyStats(now()->subMonth()->month, now()->subMonth()->year);
        $lastYearMonth = $this->getMonthlyStats(now()->subYear()->month, now()->subYear()->year);

        $monthlyGrowth = $lastMonth['revenue'] > 0
            ? round((($currentMonth['revenue'] - $lastMonth['revenue']) / $lastMonth['revenue']) * 100, 1)
            : 0;

        $yearlyGrowth = $lastYearMonth['revenue'] > 0
            ? round((($currentMonth['revenue'] - $lastYearMonth['revenue']) / $lastYearMonth['revenue']) * 100, 1)
            : 0;

        return [
            'monthly_revenue' => $currentMonth['revenue'],
            'monthly_orders' => $currentMonth['orders'],
            'monthly_growth' => $monthlyGrowth,
            'yearly_growth' => $yearlyGrowth,
            'new_customers' => $currentMonth['new_customers'],
            'repeat_customers' => $currentMonth['repeat_customers'],
        ];
    }

    protected function getMonthlySales(int $month, int $year): array
    {
        $result = OrderItem::where('farmer_id', $this->farmerId)
            ->whereHas('order', fn($q) => $q
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->whereIn('status', [Order::STATUS_DELIVERED, Order::STATUS_COMPLETED]))
            ->selectRaw('SUM(price * quantity) as revenue, COUNT(DISTINCT order_id) as orders')
            ->first();

        return [
            'revenue' => (float) ($result->revenue ?? 0),
            'orders' => (int) ($result->orders ?? 0),
        ];
    }

    protected function getMonthlyStats(int $month, int $year): array
    {
        $result = OrderItem::where('farmer_id', $this->farmerId)
            ->whereHas('order', fn($q) => $q
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->whereIn('status', [Order::STATUS_DELIVERED, Order::STATUS_COMPLETED]))
            ->selectRaw('
                SUM(price * quantity) as revenue,
                COUNT(DISTINCT order_id) as orders,
                COUNT(DISTINCT CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN user_id END) as new_customers,
                COUNT(DISTINCT user_id) - COUNT(DISTINCT CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN user_id END) as repeat_customers
            ')
            ->first();

        return [
            'revenue' => (float) ($result->revenue ?? 0),
            'orders' => (int) ($result->orders ?? 0),
            'new_customers' => (int) ($result->new_customers ?? 0),
            'repeat_customers' => (int) ($result->repeat_customers ?? 0),
        ];
    }

    protected function generateBestSellerMessage(string $productName, int $sold, float $change): string
    {
        $changeText = $change > 0 ? "increased by {$change}%" : ($change < 0 ? "decreased by " . abs($change) . "%" : "stable");
        return "Your best seller this month is {$productName} with {$sold} units sold. Sales have {$changeText} compared to last month.";
    }

    protected function generateSalesTrendMessage(float $revenueChange, float $ordersChange): string
    {
        if ($revenueChange > 10 && $ordersChange > 10) {
            return "Great job! Your sales are growing significantly this month.";
        } elseif ($revenueChange > 0 && $ordersChange > 0) {
            return "Your sales are showing positive growth this month.";
        } elseif ($revenueChange < -10) {
            return "Sales have declined this month. Consider reviewing your product offerings.";
        } else {
            return "Your sales are stable this month.";
        }
    }

    protected function getLowPerformSuggestion(Product $product): string
    {
        $suggestions = [
            "Consider updating the product description for {$product->name}.",
            "Try adjusting the price of {$product->name} to increase competitiveness.",
            "Add more photos to improve visibility of {$product->name}.",
            "Consider running a promotion for {$product->name}.",
        ];

        return $suggestions[array_rand($suggestions)];
    }

    protected function getPriorityScore(string $priority): int
    {
        return match ($priority) {
            'high' => 3,
            'medium' => 2,
            'low' => 1,
            default => 0,
        };
    }

    /**
     * Clear farmer insights cache.
     */
    public function clearCache(): void
    {
        Cache::forget("farmer_insights_{$this->farmerId}");
    }
}
