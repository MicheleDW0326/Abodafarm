<?php

namespace App\Services\Smart;

use App\Models\Category;
use App\Models\FarmProfile;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdminInsightsService
{
    protected const CACHE_TTL = 600;

    /**
     * Get all admin insights.
     */
    public function getInsights(): array
    {
        return Cache::remember('admin_insights', self::CACHE_TTL, function () {
            return [
                'sales_overview' => $this->getSalesOverview(),
                'top_category' => $this->getTopCategory(),
                'most_active_farmer' => $this->getMostActiveFarmer(),
                'most_active_customer' => $this->getMostActiveCustomer(),
                'declining_products' => $this->getDecliningProducts(),
                'high_demand_products' => $this->getHighDemandProducts(),
                'alerts' => $this->getAlerts(),
                'opportunities' => $this->getOpportunities(),
            ];
        });
    }

    /**
     * Get sales overview insight.
     */
    public function getSalesOverview(): array
    {
        $currentMonth = $this->getMonthlySales(now()->month, now()->year);
        $lastMonth = $this->getMonthlySales(now()->subMonth()->month, now()->subMonth()->year);

        $revenueChange = $lastMonth['revenue'] > 0
            ? round((($currentMonth['revenue'] - $lastMonth['revenue']) / $lastMonth['revenue']) * 100, 1)
            : ($currentMonth['revenue'] > 0 ? 100 : 0);

        $ordersChange = $lastMonth['orders'] > 0
            ? round((($currentMonth['orders'] - $lastMonth['orders']) / $lastMonth['orders']) * 100, 1)
            : ($currentMonth['orders'] > 0 ? 100 : 0);

        return [
            'revenue' => [
                'current' => $currentMonth['revenue'],
                'last_month' => $lastMonth['revenue'],
                'change' => $revenueChange,
                'trend' => $revenueChange > 0 ? 'up' : ($revenueChange < 0 ? 'down' : 'stable'),
            ],
            'orders' => [
                'current' => $currentMonth['orders'],
                'last_month' => $lastMonth['orders'],
                'change' => $ordersChange,
                'trend' => $ordersChange > 0 ? 'up' : ($ordersChange < 0 ? 'down' : 'stable'),
            ],
            'message' => $this->generateSalesOverviewMessage($revenueChange, $ordersChange),
        ];
    }

    /**
     * Get top performing category.
     */
    public function getTopCategory(): ?array
    {
        $category = Category::select('categories.*')
            ->selectRaw('SUM(order_items.quantity) as total_sold')
            ->selectRaw('SUM(order_items.price * order_items.quantity) as total_revenue')
            ->join('products', 'categories.id', '=', 'products.category_id')
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereMonth('orders.created_at', now()->month)
            ->whereYear('orders.created_at', now()->year)
            ->whereIn('orders.status', [Order::STATUS_DELIVERED, Order::STATUS_COMPLETED])
            ->groupBy('categories.id')
            ->orderByDesc('total_revenue')
            ->first();

        if (!$category) {
            return null;
        }

        // Compare with last month
        $lastMonthRevenue = Category::selectRaw('SUM(order_items.price * order_items.quantity) as total_revenue')
            ->join('products', 'categories.id', '=', 'products.category_id')
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('categories.id', $category->id)
            ->whereMonth('orders.created_at', now()->subMonth()->month)
            ->whereYear('orders.created_at', now()->subMonth()->year)
            ->whereIn('orders.status', [Order::STATUS_DELIVERED, Order::STATUS_COMPLETED])
            ->value('total_revenue') ?? 0;

        $change = $lastMonthRevenue > 0
            ? round((($category->total_revenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : 100;

        return [
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'image' => $category->image_url,
            ],
            'total_sold' => (int) $category->total_sold,
            'total_revenue' => (float) $category->total_revenue,
            'change_percentage' => $change,
            'trend' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'stable'),
            'message' => "{$category->name} is your top-selling category with Rp " . number_format($category->total_revenue, 0, ',', '.') . " in revenue this month.",
        ];
    }

    /**
     * Get most active farmer.
     */
    public function getMostActiveFarmer(): ?array
    {
        $farmer = FarmProfile::select('farm_profiles.*')
            ->selectRaw('COUNT(DISTINCT orders.id) as total_orders')
            ->selectRaw('SUM(orders.total_amount) as total_revenue')
            ->join('users', 'farm_profiles.user_id', '=', 'users.id')
            ->join('orders', 'users.id', '=', 'orders.user_id')
            ->whereMonth('orders.created_at', now()->month)
            ->whereYear('orders.created_at', now()->year)
            ->whereIn('orders.status', [Order::STATUS_DELIVERED, Order::STATUS_COMPLETED])
            ->groupBy('farm_profiles.id')
            ->orderByDesc('total_revenue')
            ->first();

        if (!$farmer) {
            // Try based on products sold instead
            $farmer = FarmProfile::select('farm_profiles.*')
                ->selectRaw('SUM(order_items.quantity) as total_sold')
                ->selectRaw('SUM(order_items.price * order_items.quantity) as total_revenue')
                ->join('users', 'farm_profiles.user_id', '=', 'users.id')
                ->join('products', 'users.id', '=', 'products.owner_id')
                ->join('order_items', 'products.id', '=', 'order_items.product_id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereMonth('orders.created_at', now()->month)
                ->whereYear('orders.created_at', now()->year)
                ->whereIn('orders.status', [Order::STATUS_DELIVERED, Order::STATUS_COMPLETED])
                ->groupBy('farm_profiles.id')
                ->orderByDesc('total_revenue')
                ->first();
        }

        if (!$farmer) {
            return null;
        }

        $user = User::find($farmer->user_id);
        $avgRating = $user?->reviews()->avg('rating') ?? 0;

        return [
            'farmer' => [
                'id' => $farmer->user_id,
                'name' => $user?->name ?? 'Unknown',
                'shop_name' => $farmer->shop_name,
                'image' => $farmer->image ? asset('storage/' . $farmer->image) : null,
                'rating' => round($avgRating, 1),
            ],
            'total_revenue' => (float) ($farmer->total_revenue ?? 0),
            'total_orders' => (int) ($farmer->total_orders ?? $farmer->total_sold ?? 0),
            'message' => "{$user?->name} is your top-performing farmer with Rp " . number_format($farmer->total_revenue ?? 0, 0, ',', '.') . " in revenue.",
        ];
    }

    /**
     * Get most active customer.
     */
    public function getMostActiveCustomer(): ?array
    {
        $customer = User::select('users.*')
            ->selectRaw('COUNT(orders.id) as total_orders')
            ->selectRaw('SUM(orders.total_amount) as total_spent')
            ->join('orders', 'users.id', '=', 'orders.user_id')
            ->whereMonth('orders.created_at', now()->month)
            ->whereYear('orders.created_at', now()->year)
            ->whereIn('orders.status', [Order::STATUS_DELIVERED, Order::STATUS_COMPLETED])
            ->whereHas('roles', fn($q) => $q->where('name', 'pelanggan'))
            ->groupBy('users.id')
            ->orderByDesc('total_spent')
            ->first();

        if (!$customer) {
            return null;
        }

        return [
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'avatar' => $customer->avatar,
            ],
            'total_orders' => (int) $customer->total_orders,
            'total_spent' => (float) $customer->total_spent,
            'message' => "{$customer->name} is your top customer with Rp " . number_format($customer->total_spent, 0, ',', '.') . " spent this month.",
        ];
    }

    /**
     * Get products with declining sales.
     */
    public function getDecliningProducts(int $limit = 5): array
    {
        $products = Product::select('products.*')
            ->selectRaw('COALESCE(current_sales.total_sold, 0) as current_sold')
            ->selectRaw('COALESCE(previous_sales.total_sold, 0) as previous_sold')
            ->leftJoinSub(
                OrderItem::select('product_id', DB::raw('SUM(quantity) as total_sold'))
                    ->whereHas('order', fn($q) => $q
                        ->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year)
                        ->whereIn('status', [Order::STATUS_DELIVERED, Order::STATUS_COMPLETED]))
                    ->groupBy('product_id'),
                'current_sales',
                'products.id',
                '=',
                'current_sales.product_id'
            )
            ->leftJoinSub(
                OrderItem::select('product_id', DB::raw('SUM(quantity) as total_sold'))
                    ->whereHas('order', fn($q) => $q
                        ->whereMonth('created_at', now()->subMonth()->month)
                        ->whereYear('created_at', now()->subMonth()->year)
                        ->whereIn('status', [Order::STATUS_DELIVERED, Order::STATUS_COMPLETED]))
                    ->groupBy('product_id'),
                'previous_sales',
                'products.id',
                '=',
                'previous_sales.product_id'
            )
            ->whereNotNull('current_sales.product_id')
            ->whereNotNull('previous_sales.product_id')
            ->where('previous_sales.total_sold', '>', 0)
            ->get()
            ->map(fn($p) => [
                'product' => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'image' => $p->image_url,
                ],
                'current_sold' => (int) $p->current_sold,
                'previous_sold' => (int) $p->previous_sold,
                'change' => $p->previous_sold > 0
                    ? round((($p->current_sold - $p->previous_sold) / $p->previous_sold) * 100, 1)
                    : 0,
            ])
            ->filter(fn($p) => $p['change'] < -10)
            ->sortBy('change')
            ->take($limit)
            ->values();

        return $products->toArray();
    }

    /**
     * Get products with high demand.
     */
    public function getHighDemandProducts(int $limit = 5): array
    {
        $products = Product::select('products.*')
            ->selectRaw('SUM(order_items.quantity) as total_sold')
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereMonth('orders.created_at', now()->month)
            ->whereYear('orders.created_at', now()->year)
            ->whereIn('orders.status', [Order::STATUS_DELIVERED, Order::STATUS_COMPLETED])
            ->groupBy('products.id')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get();

        return $products->map(fn($p) => [
            'product' => [
                'id' => $p->id,
                'name' => $p->name,
                'image' => $p->image_url,
                'stock' => $p->stock,
            ],
            'total_sold' => (int) $p->total_sold,
            'stock_status' => $p->stock <= 5 ? 'low' : ($p->stock == 0 ? 'out' : 'ok'),
        ])->toArray();
    }

    /**
     * Get system alerts.
     */
    public function getAlerts(): array
    {
        $alerts = [];

        // Out of stock products
        $outOfStock = Product::where('stock', 0)
            ->where('status', 'available')
            ->count();
        if ($outOfStock > 0) {
            $alerts[] = [
                'type' => 'out_of_stock',
                'severity' => 'high',
                'message' => "{$outOfStock} product(s) are out of stock",
                'action_url' => '/admin/produk',
            ];
        }

        // Pending farmer verifications
        $pendingFarmers = FarmProfile::where('status', 'pending')->count();
        if ($pendingFarmers > 0) {
            $alerts[] = [
                'type' => 'pending_verification',
                'severity' => 'medium',
                'message' => "{$pendingFarmers} farmer(s) awaiting verification",
                'action_url' => '/admin/verifikasi-peternak',
            ];
        }

        // Low stock products
        $lowStock = Product::where('stock', '>', 0)
            ->where('stock', '<=', 5)
            ->count();
        if ($lowStock > 0) {
            $alerts[] = [
                'type' => 'low_stock',
                'severity' => 'medium',
                'message' => "{$lowStock} product(s) are running low on stock",
                'action_url' => '/admin/produk',
            ];
        }

        // Failed deliveries
        $failedDeliveries = DB::table('deliveries')
            ->whereDate('last_attempt_at', '>=', now()->subDays(7))
            ->where('status', 'delivery_failed')
            ->count();
        if ($failedDeliveries > 0) {
            $alerts[] = [
                'type' => 'failed_deliveries',
                'severity' => 'medium',
                'message' => "{$failedDeliveries} delivery attempt(s) failed in the last 7 days",
                'action_url' => '/admin/pemesanan',
            ];
        }

        return $alerts;
    }

    /**
     * Get business opportunities.
     */
    public function getOpportunities(): array
    {
        $opportunities = [];

        // High demand but low stock
        $highDemandLowStock = Product::select('products.*')
            ->selectRaw('SUM(order_items.quantity) as total_demand')
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereMonth('orders.created_at', now()->month)
            ->whereYear('orders.created_at', now()->year)
            ->whereIn('orders.status', [Order::STATUS_DELIVERED, Order::STATUS_COMPLETED])
            ->where('products.stock', '<=', 10)
            ->groupBy('products.id')
            ->orderByDesc('total_demand')
            ->limit(3)
            ->get();

        if ($highDemandLowStock->isNotEmpty()) {
            $opportunities[] = [
                'type' => 'stock_upportunity',
                'title' => 'Stock High Demand Products',
                'description' => 'Some products have high demand but low stock. Consider restocking: ' . $highDemandLowStock->pluck('name')->implode(', '),
                'priority' => 'high',
            ];
        }

        // New customer growth
        $newCustomers = User::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->whereHas('roles', fn($q) => $q->where('name', 'pelanggan'))
            ->count();
        $lastMonthCustomers = User::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->whereHas('roles', fn($q) => $q->where('name', 'pelanggan'))
            ->count();

        if ($newCustomers > $lastMonthCustomers && $newCustomers > 5) {
            $opportunities[] = [
                'type' => 'customer_growth',
                'title' => 'Customer Growth Opportunity',
                'description' => "New customer signups are up. Consider launching a referral program to capitalize on growth.",
                'priority' => 'medium',
            ];
        }

        // Category expansion
        $topCategory = $this->getTopCategory();
        if ($topCategory) {
            $opportunities[] = [
                'type' => 'category_expansion',
                'title' => 'Expand ' . $topCategory['category']['name'],
                'description' => 'This category is performing well. Consider adding more variety or suppliers.',
                'priority' => 'low',
            ];
        }

        return $opportunities;
    }

    protected function getMonthlySales(int $month, int $year): array
    {
        $result = Order::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->whereIn('status', [Order::STATUS_DELIVERED, Order::STATUS_COMPLETED])
            ->selectRaw('SUM(total_amount) as revenue, COUNT(*) as orders')
            ->first();

        return [
            'revenue' => (float) ($result->revenue ?? 0),
            'orders' => (int) ($result->orders ?? 0),
        ];
    }

    protected function generateSalesOverviewMessage(float $revenueChange, float $ordersChange): string
    {
        if ($revenueChange > 20 && $ordersChange > 10) {
            return "Excellent performance! Revenue is up {$revenueChange}% and orders are up {$ordersChange}% compared to last month.";
        } elseif ($revenueChange > 0 && $ordersChange > 0) {
            return "Positive growth this month. Revenue is up {$revenueChange}% and orders increased by {$ordersChange}%.";
        } elseif ($revenueChange < -10) {
            return "Revenue has declined by " . abs($revenueChange) . "% compared to last month. Review your marketing and product strategies.";
        } else {
            return "Sales are relatively stable compared to last month.";
        }
    }

    /**
     * Clear admin insights cache.
     */
    public function clearCache(): void
    {
        Cache::forget('admin_insights');
    }
}
