<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\Faq;
use App\Models\HeroSlide;
use App\Models\Product;
use App\Models\Testimonial;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.main')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    /**
     * Build signed percentage and trend direction.
     */
    private function buildChangeMeta(float $current, float $previous): array
    {
        if ($previous <= 0) {
            if ($current <= 0) {
                return ['text' => '0.0%', 'positive' => true];
            }

            return ['text' => '+100.0%', 'positive' => true];
        }

        $delta = (($current - $previous) / $previous) * 100;

        return [
            'text' => sprintf('%+.1f%%', $delta),
            'positive' => $delta >= 0,
        ];
    }

    /**
     * Format currency to compact rupiah text.
     */
    private function formatRupiahCompact(int|float $amount): string
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

    public function render()
    {
        $now = now();

        $thisMonthStart = $now->copy()->startOfMonth();
        $previousMonthStart = $thisMonthStart->copy()->subMonth();
        $previousMonthEnd = $thisMonthStart->copy()->subSecond();

        // Base queries
        $pelangganUsersQuery = fn () => DB::table('users')
            ->join('model_has_roles', function ($join): void {
                $join->on('users.id', '=', 'model_has_roles.model_id')
                    ->where('model_has_roles.model_type', '=', 'App\\Models\\User');
            })
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('roles.name', 'pelanggan')
            ->distinct('users.id');

        // Basic stats
        $totalRevenue = (int) DB::table('orders')->sum('total_amount');
        $totalOrders = (int) DB::table('orders')->count();
        $activeCustomers = (int) $pelangganUsersQuery()->count('users.id');
        $averageOrder = $totalOrders > 0 ? (int) round($totalRevenue / $totalOrders) : 0;

        // Farm-specific stats
        $totalLiveStock = Product::live()->count();
        $totalFreshMeat = Product::freshType()->count();
        $totalCategories = Category::active()->count();
        $totalTestimonials = Testimonial::active()->count();
        $totalFaqs = Faq::active()->count();
        $totalHeroSlides = HeroSlide::active()->count();

        // Monthly comparisons
        $thisMonthRevenue = (int) DB::table('orders')->whereBetween('placed_at', [$thisMonthStart, $now])->sum('total_amount');
        $previousMonthRevenue = (int) DB::table('orders')->whereBetween('placed_at', [$previousMonthStart, $previousMonthEnd])->sum('total_amount');

        $thisMonthOrders = (int) DB::table('orders')->whereBetween('placed_at', [$thisMonthStart, $now])->count();
        $previousMonthOrders = (int) DB::table('orders')->whereBetween('placed_at', [$previousMonthStart, $previousMonthEnd])->count();

        $thisMonthCustomers = (int) $pelangganUsersQuery()->whereBetween('users.created_at', [$thisMonthStart, $now])->count('users.id');
        $previousMonthCustomers = (int) $pelangganUsersQuery()->whereBetween('users.created_at', [$previousMonthStart, $previousMonthEnd])->count('users.id');

        $thisMonthAverageOrder = $thisMonthOrders > 0 ? $thisMonthRevenue / $thisMonthOrders : 0;
        $previousMonthAverageOrder = $previousMonthOrders > 0 ? $previousMonthRevenue / $previousMonthOrders : 0;

        // Calculate changes
        $revenueChange = $this->buildChangeMeta($thisMonthRevenue, $previousMonthRevenue);
        $ordersChange = $this->buildChangeMeta($thisMonthOrders, $previousMonthOrders);
        $customersChange = $this->buildChangeMeta($thisMonthCustomers, $previousMonthCustomers);
        $averageChange = $this->buildChangeMeta($thisMonthAverageOrder, $previousMonthAverageOrder);

        // Summary Cards
        $summaryCards = [
            [
                'label' => 'Total Pendapatan',
                'value' => $this->formatRupiahCompact($totalRevenue),
                'change' => $revenueChange['text'],
                'positive' => $revenueChange['positive'],
                'icon' => 'fa-solid fa-money-bill-wave',
                'color' => 'emerald',
            ],
            [
                'label' => 'Total Pesanan',
                'value' => number_format($totalOrders, 0, ',', '.'),
                'change' => $ordersChange['text'],
                'positive' => $ordersChange['positive'],
                'icon' => 'fa-solid fa-shopping-cart',
                'color' => 'blue',
            ],
            [
                'label' => 'Pelanggan Aktif',
                'value' => number_format($activeCustomers, 0, ',', '.'),
                'change' => $customersChange['text'],
                'positive' => $customersChange['positive'],
                'icon' => 'fa-solid fa-users',
                'color' => 'purple',
            ],
            [
                'label' => 'Rata-rata Pesanan',
                'value' => $this->formatRupiahCompact($averageOrder),
                'change' => $averageChange['text'],
                'positive' => $averageChange['positive'],
                'icon' => 'fa-solid fa-chart-line',
                'color' => 'amber',
            ],
        ];

        // Farm Overview Cards
        $farmCards = [
            [
                'label' => 'Ternak Hidup',
                'value' => number_format($totalLiveStock, 0, ',', '.'),
                'icon' => 'fa-solid fa-cow',
                'color' => 'green',
            ],
            [
                'label' => 'Daging Segar',
                'value' => number_format($totalFreshMeat, 0, ',', '.'),
                'icon' => 'fa-solid fa-drumstick-bite',
                'color' => 'rose',
            ],
            [
                'label' => 'Kategori',
                'value' => number_format($totalCategories, 0, ',', '.'),
                'icon' => 'fa-solid fa-tags',
                'color' => 'sky',
            ],
            [
                'label' => 'Testimonial',
                'value' => number_format($totalTestimonials, 0, ',', '.'),
                'icon' => 'fa-solid fa-quote-left',
                'color' => 'yellow',
            ],
            [
                'label' => 'FAQ',
                'value' => number_format($totalFaqs, 0, ',', '.'),
                'icon' => 'fa-solid fa-circle-question',
                'color' => 'indigo',
            ],
            [
                'label' => 'Hero Slides',
                'value' => number_format($totalHeroSlides, 0, ',', '.'),
                'icon' => 'fa-solid fa-images',
                'color' => 'pink',
            ],
        ];

        // Monthly Sales Chart
        $monthlyRows = DB::table('orders')
            ->selectRaw('MONTH(placed_at) as month_number, SUM(total_amount) as total_amount')
            ->whereYear('placed_at', $now->year)
            ->groupBy('month_number')
            ->pluck('total_amount', 'month_number');

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $sales = [];

        for ($month = 1; $month <= 12; $month++) {
            $sales[] = (int) ($monthlyRows[$month] ?? 0);
        }

        // Order Status Distribution
        $statusConfig = [
            'pending' => ['name' => 'Pending', 'color' => 'bg-amber-500'],
            'processing' => ['name' => 'Diproses', 'color' => 'bg-blue-500'],
            'shipped' => ['name' => 'Dikirim', 'color' => 'bg-violet-500'],
            'delivered' => ['name' => 'Selesai', 'color' => 'bg-emerald-500'],
            'cancelled' => ['name' => 'Batal', 'color' => 'bg-red-500'],
        ];

        $statusCounts = DB::table('orders')
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $orderStatuses = [];
        $totalStatusOrders = max(1, (int) DB::table('orders')->count());

        foreach ($statusConfig as $key => $config) {
            $count = (int) ($statusCounts[$key] ?? 0);
            $percent = (int) round(($count / $totalStatusOrders) * 100);

            $orderStatuses[] = [
                'name' => $config['name'],
                'count' => $count,
                'percent' => $percent,
                'color' => $config['color'],
            ];
        }

        // Order Type Distribution (cart vs inquiry)
        $orderTypeCounts = DB::table('orders')
            ->selectRaw('order_type, COUNT(*) as aggregate')
            ->groupBy('order_type')
            ->pluck('aggregate', 'order_type');

        $orderTypes = [
            [
                'name' => 'Pembelian',
                'count' => (int) ($orderTypeCounts['cart'] ?? 0),
                'color' => 'bg-emerald-500',
            ],
            [
                'name' => 'Inquiry',
                'count' => (int) ($orderTypeCounts['inquiry'] ?? 0),
                'color' => 'bg-amber-500',
            ],
        ];

        // Critical Stocks - Updated with type distinction
        $criticalStocks = [];
        $minStockThreshold = 100;

        $lowStockProducts = Product::with('category')
            ->where('stock', '<', $minStockThreshold)
            ->where('type', 'fresh') // Only fresh meat has stock tracking
            ->orderBy('stock', 'asc')
            ->limit(10)
            ->get();

        foreach ($lowStockProducts as $product) {
            $percent = max(0, min(100, (int) round(($product->stock / $minStockThreshold) * 100)));

            $criticalStocks[] = [
                'name' => $product->name,
                'sku' => 'PRD-' . str_pad($product->id, 4, '0', STR_PAD_LEFT),
                'category' => $product->category->name,
                'stock' => $product->stock . ' unit',
                'min' => $minStockThreshold . ' unit',
                'percent' => $percent,
                'product_id' => $product->id,
            ];
        }

        // Recent Orders
        $recentOrders = DB::table('orders')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Top Products
        $topProducts = DB::table('order_items')
            ->selectRaw('product_name, SUM(quantity) as total_qty, SUM(subtotal) as total_sales')
            ->groupBy('product_name')
            ->orderByDesc('total_sales')
            ->limit(5)
            ->get();

        return view('livewire.admin.dashboard', [
            'summaryCards' => $summaryCards,
            'farmCards' => $farmCards,
            'sales' => $sales,
            'months' => $months,
            'orderStatuses' => $orderStatuses,
            'orderTypes' => $orderTypes,
            'todayOrders' => (int) DB::table('orders')->whereDate('placed_at', $now->toDateString())->count(),
            'criticalStocks' => $criticalStocks,
            'recentOrders' => $recentOrders,
            'topProducts' => $topProducts,
            'formatRupiahCompact' => fn (int|float $amount) => $this->formatRupiahCompact($amount),
            'chartYear' => $now->year,
        ]);
    }
}
