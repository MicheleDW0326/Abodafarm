<?php

namespace App\Livewire\Peternak;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.main')]
#[Title('Dashboard Peternak')]
class Dashboard extends Component
{
    public array $stats = [];

    public array $monthlyRevenue = [];

    public array $monthlyOrders = [];

    public array $topProducts = [];

    public array $latestReviews = [];

    public string $chartYear;

    public array $orderStatusDistribution = [];

    public int $totalStatusOrders = 0;

    public int $thisMonthRevenue = 0;

    public int $thisMonthOrders = 0;

    public int $revenueChange = 0;

    public int $ordersChange = 0;

    public function mount(): void
    {
        $userId = auth()->id();
        $this->chartYear = now()->format('Y');

        $this->loadStats($userId);
        $this->loadMonthlyData($userId);
        $this->loadTopProducts($userId);
        $this->loadLatestReviews($userId);
        $this->loadOrderStatusDistribution($userId);
    }

    private function loadStats(int $userId): void
    {
        // Optimized single query for all order stats using CASE WHEN
        $orderStats = DB::table('order_items as oi')
            ->join('orders as o', 'oi.order_id', '=', 'o.id')
            ->join('products as p', 'oi.product_id', '=', 'p.id')
            ->where('p.owner_id', $userId)
            ->selectRaw("
                COUNT(DISTINCT o.id) as total_orders,
                SUM(CASE WHEN o.status NOT IN ('cancelled', 'rejected') THEN oi.subtotal ELSE 0 END) as total_revenue,
                SUM(CASE WHEN o.status IN ('delivered', 'completed') THEN oi.subtotal ELSE 0 END) as completed_revenue,
                SUM(CASE WHEN o.status IN ('delivered', 'completed') THEN 1 ELSE 0 END) as completed_orders,
                SUM(CASE WHEN o.order_type = 'inquiry' AND o.status NOT IN ('cancelled', 'rejected') THEN 1 ELSE 0 END) as total_bookings,
                SUM(CASE WHEN o.order_type = 'inquiry' AND o.status IN ('pending', 'approved') THEN 1 ELSE 0 END) as active_bookings,
                SUM(CASE WHEN o.order_type = 'cart' AND o.status NOT IN ('cancelled', 'rejected') THEN 1 ELSE 0 END) as total_orders_count,
                SUM(CASE WHEN o.order_type = 'cart' AND o.status NOT IN ('cancelled', 'rejected') THEN oi.subtotal ELSE 0 END) as cart_revenue
            ")
            ->first();

        // Review stats - single query
        $reviewStats = DB::table('reviews as r')
            ->join('products as p', 'r.product_id', '=', 'p.id')
            ->where('p.owner_id', $userId)
            ->where('r.status', Review::STATUS_APPROVED)
            ->where('r.is_visible', true)
            ->selectRaw("
                COUNT(*) as total_reviews,
                COALESCE(AVG(r.rating), 0) as avg_rating,
                SUM(CASE WHEN r.rating = 5 THEN 1 ELSE 0 END) as five_star,
                SUM(CASE WHEN r.rating = 4 THEN 1 ELSE 0 END) as four_star,
                SUM(CASE WHEN r.rating = 3 THEN 1 ELSE 0 END) as three_star,
                SUM(CASE WHEN r.rating = 2 THEN 1 ELSE 0 END) as two_star,
                SUM(CASE WHEN r.rating = 1 THEN 1 ELSE 0 END) as one_star
            ")
            ->first();

        // Monthly comparison for change percentage
        $thisMonth = now();
        $lastMonth = now()->subMonth();

        $thisMonthData = DB::table('order_items as oi')
            ->join('orders as o', 'oi.order_id', '=', 'o.id')
            ->join('products as p', 'oi.product_id', '=', 'p.id')
            ->where('p.owner_id', $userId)
            ->where('o.status', 'not in', ['cancelled', 'rejected'])
            ->whereMonth('o.placed_at', $thisMonth->month)
            ->whereYear('o.placed_at', $thisMonth->year)
            ->selectRaw("
                COALESCE(SUM(oi.subtotal), 0) as revenue,
                COUNT(DISTINCT o.id) as orders
            ")
            ->first();

        $lastMonthData = DB::table('order_items as oi')
            ->join('orders as o', 'oi.order_id', '=', 'o.id')
            ->join('products as p', 'oi.product_id', '=', 'p.id')
            ->where('p.owner_id', $userId)
            ->where('o.status', 'not in', ['cancelled', 'rejected'])
            ->whereMonth('o.placed_at', $lastMonth->month)
            ->whereYear('o.placed_at', $lastMonth->year)
            ->selectRaw("
                COALESCE(SUM(oi.subtotal), 0) as revenue,
                COUNT(DISTINCT o.id) as orders
            ")
            ->first();

        $this->thisMonthRevenue = (int) $thisMonthData->revenue;
        $this->thisMonthOrders = (int) $thisMonthData->orders;

        // Calculate percentage change
        $this->revenueChange = $lastMonthData->revenue > 0
            ? round((($thisMonthData->revenue - $lastMonthData->revenue) / $lastMonthData->revenue) * 100)
            : ($thisMonthData->revenue > 0 ? 100 : 0);

        $this->ordersChange = $lastMonthData->orders > 0
            ? round((($thisMonthData->orders - $lastMonthData->orders) / $lastMonthData->orders) * 100)
            : ($thisMonthData->orders > 0 ? 100 : 0);

        // Product count
        $productCount = Product::ownedBy($userId)->count();
        $activeProducts = Product::ownedBy($userId)->available()->inStock()->count();

        $this->stats = [
            'totalRevenue' => (int) ($orderStats->total_revenue ?? 0),
            'thisMonthRevenue' => $this->thisMonthRevenue,
            'revenueChange' => $this->revenueChange,
            'totalBookings' => (int) ($orderStats->total_bookings ?? 0),
            'activeBookings' => (int) ($orderStats->active_bookings ?? 0),
            'completedOrders' => (int) ($orderStats->completed_orders ?? 0),
            'totalReviews' => (int) ($reviewStats->total_reviews ?? 0),
            'avgRating' => round((float) $reviewStats->avg_rating, 1),
            'totalProducts' => $productCount,
            'activeProducts' => $activeProducts,
            'ratingDistribution' => [
                5 => (int) ($reviewStats->five_star ?? 0),
                4 => (int) ($reviewStats->four_star ?? 0),
                3 => (int) ($reviewStats->three_star ?? 0),
                2 => (int) ($reviewStats->two_star ?? 0),
                1 => (int) ($reviewStats->one_star ?? 0),
            ],
        ];
    }

    private function loadMonthlyData(int $userId): void
    {
        $months = [];
        $revenues = [];
        $orders = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $months[] = $month->format('M');

            $data = DB::table('order_items as oi')
                ->join('orders as o', 'oi.order_id', '=', 'o.id')
                ->join('products as p', 'oi.product_id', '=', 'p.id')
                ->where('p.owner_id', $userId)
                ->where('o.status', 'not in', ['cancelled', 'rejected'])
                ->whereMonth('o.placed_at', $month->month)
                ->whereYear('o.placed_at', $month->year)
                ->selectRaw("
                    COALESCE(SUM(oi.subtotal), 0) as revenue,
                    COUNT(DISTINCT o.id) as orders
                ")
                ->first();

            $revenues[] = (int) $data->revenue;
            $orders[] = (int) $data->orders;
        }

        $this->monthlyRevenue = $revenues;
        $this->monthlyOrders = $orders;
    }

    private function loadTopProducts(int $userId): void
    {
        $this->topProducts = DB::table('order_items as oi')
            ->join('products as p', 'oi.product_id', '=', 'p.id')
            ->join('orders as o', 'oi.order_id', '=', 'o.id')
            ->where('p.owner_id', $userId)
            ->where('o.status', 'not in', ['cancelled', 'rejected'])
            ->groupBy('p.id', 'p.name', 'p.image')
            ->selectRaw("
                p.id,
                p.name,
                p.image,
                p.slug,
                COUNT(oi.id) as order_count,
                SUM(oi.quantity) as total_qty,
                SUM(oi.subtotal) as total_sales
            ")
            ->orderByDesc('total_sales')
            ->limit(5)
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'image' => $item->image,
                'slug' => $item->slug,
                'order_count' => (int) $item->order_count,
                'total_qty' => (int) $item->total_qty,
                'total_sales' => (int) $item->total_sales,
                'formatted_sales' => 'Rp ' . number_format($item->total_sales, 0, ',', '.'),
            ])
            ->toArray();
    }

    private function loadLatestReviews(int $userId): void
    {
        $this->latestReviews = Review::whereHas('product', fn($q) => $q->where('owner_id', $userId))
            ->approved()
            ->visible()
            ->with(['user', 'product'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn($review) => [
                'id' => $review->id,
                'rating' => $review->rating,
                'content' => $review->content,
                'reviewer_name' => $review->reviewer_name,
                'reviewer_avatar' => $review->reviewer_avatar,
                'product_name' => $review->product?->name,
                'product_slug' => $review->product?->slug,
                'created_at' => $review->created_at->diffForHumans(),
                'rating_stars' => $review->rating,
            ])
            ->toArray();
    }

    private function loadOrderStatusDistribution(int $userId): void
    {
        $statuses = DB::table('orders as o')
            ->join('order_items as oi', 'o.id', '=', 'oi.order_id')
            ->join('products as p', 'oi.product_id', '=', 'p.id')
            ->where('p.owner_id', auth()->id())
            ->groupBy('o.status')
            ->selectRaw('o.status, COUNT(DISTINCT o.id) as count')
            ->get();

        $this->totalStatusOrders = $statuses->sum('count');

        $statusConfig = [
            'pending' => ['label' => 'Menunggu', 'color' => 'bg-amber-400'],
            'approved' => ['label' => 'Diterima', 'color' => 'bg-blue-400'],
            'processing' => ['label' => 'Diproses', 'color' => 'bg-violet-400'],
            'shipped' => ['label' => 'Dikirim', 'color' => 'bg-purple-400'],
            'delivered' => ['label' => 'Selesai', 'color' => 'bg-emerald-400'],
            'completed' => ['label' => 'Completed', 'color' => 'bg-teal-400'],
            'cancelled' => ['label' => 'Batal', 'color' => 'bg-red-400'],
            'rejected' => ['label' => 'Ditolak', 'color' => 'bg-rose-400'],
        ];

        $this->orderStatusDistribution = $statuses
            ->map(fn($item) => [
                'status' => $item->status,
                'label' => $statusConfig[$item->status]['label'] ?? ucfirst($item->status),
                'count' => (int) $item->count,
                'color' => $statusConfig[$item->status]['color'] ?? 'bg-slate-400',
            ])
            ->toArray();
    }

    public function formatRupiah(int $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    public function formatCompact(int $amount): string
    {
        if ($amount >= 1000000) {
            return 'Rp ' . number_format($amount / 1000000, 1, ',', '.') . 'jt';
        }
        if ($amount >= 1000) {
            return 'Rp ' . number_format($amount / 1000, 1, ',', '.') . 'rb';
        }
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    public function render()
    {
        return view('livewire.peternak.dashboard', [
            'formatRupiah' => fn ($amount) => 'Rp ' . number_format($amount, 0, ',', '.'),
        ]);
    }
}
