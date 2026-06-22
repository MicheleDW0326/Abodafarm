<?php

namespace App\Livewire\Peternak;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PeternakNotification;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.main')]
#[Title('Dashboard Peternak')]
class Dashboard extends Component
{
    public function render()
    {
        $userId = auth()->id();
        $now = now();

        // Stats
        $totalLivestock = Product::ownedBy($userId)->live()->count();
        $activeLivestock = Product::ownedBy($userId)->live()->whereIn('status', ['available', 'pre_order'])->count();
        $soldLivestock = Product::ownedBy($userId)->live()->where('status', 'sold')->count();
        $totalRevenue = $this->getTotalRevenue($userId);
        $activeOrders = $this->getActiveOrders($userId);
        $unreadNotifications = PeternakNotification::where('user_id', $userId)->unread()->count();

        // Recent orders for owned products
        $recentOrders = $this->getRecentOrders($userId);

        // Low stock warning (for fresh meat products)
        $lowStockProducts = Product::ownedBy($userId)
            ->freshType()
            ->where('stock', '<', 5)
            ->count();

        // Monthly revenue
        $monthlyRevenue = $this->getMonthlyRevenue($userId);

        return view('livewire.peternak.dashboard', [
            'stats' => [
                'totalLivestock' => $totalLivestock,
                'activeLivestock' => $activeLivestock,
                'soldLivestock' => $soldLivestock,
                'totalRevenue' => $totalRevenue,
                'activeOrders' => $activeOrders,
                'unreadNotifications' => $unreadNotifications,
                'lowStockProducts' => $lowStockProducts,
            ],
            'recentOrders' => $recentOrders,
            'monthlyRevenue' => $monthlyRevenue,
            'formatRupiah' => fn ($amount) => 'Rp ' . number_format($amount, 0, ',', '.'),
        ]);
    }

    private function getTotalRevenue(int $userId): int
    {
        return (int) DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('products.owner_id', $userId)
            ->where('orders.status', '!=', 'cancelled')
            ->sum('order_items.subtotal');
    }

    private function getActiveOrders(int $userId): int
    {
        return Order::whereHas('items.product', function ($query) use ($userId) {
            $query->where('owner_id', $userId);
        })
            ->whereIn('status', ['pending', 'processing', 'shipped'])
            ->count();
    }

    private function getRecentOrders(int $userId)
    {
        return Order::whereHas('items.product', function ($query) use ($userId) {
            $query->where('owner_id', $userId);
        })
            ->with(['items.product' => function ($q) use ($userId) {
                $q->where('owner_id', $userId);
            }])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    private function getMonthlyRevenue(int $userId): array
    {
        $months = [];
        $revenues = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $months[] = $month->format('M');

            $revenue = DB::table('order_items')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('products.owner_id', $userId)
                ->whereMonth('orders.placed_at', $month->month)
                ->whereYear('orders.placed_at', $month->year)
                ->where('orders.status', '!=', 'cancelled')
                ->sum('order_items.subtotal');

            $revenues[] = (int) $revenue;
        }

        return [
            'labels' => $months,
            'data' => $revenues,
        ];
    }
}
