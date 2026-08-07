<?php

namespace App\Livewire\Staff;

use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.main')]
#[Title('Dashboard Staff - AbodaFarm')]
class Dashboard extends Component
{
    public function render()
    {
        $now = now();
        $startOfDay = $now->copy()->startOfDay();
        $startOfWeek = $now->copy()->startOfWeek();

        // ============================================================
        // TODAY'S OVERVIEW - Single optimized query
        // ============================================================
        $todayStats = DB::table('orders')
            ->selectRaw('
                COUNT(*) as total_orders_today,
                SUM(CASE WHEN placed_at >= ? THEN 1 ELSE 0 END) as orders_today,
                SUM(CASE WHEN placed_at >= ? AND status IN (?, ?) THEN 1 ELSE 0 END) as ready_for_delivery,
                SUM(CASE WHEN placed_at >= ? AND status = ? THEN 1 ELSE 0 END) as shipped_today,
                SUM(CASE WHEN placed_at >= ? AND status IN (?, ?) THEN 1 ELSE 0 END) as completed_today,
                SUM(CASE WHEN placed_at >= ? THEN total_amount ELSE 0 END) as today_revenue
            ', [
                $startOfDay, $startOfDay, 'paid', 'approved',
                $startOfDay, 'shipped',
                $startOfDay, 'delivered', 'completed',
                $startOfDay
            ])
            ->first();

        // ============================================================
        // WEEKLY ACTIVITY - Single query
        // ============================================================
        $weeklyData = DB::table('orders')
            ->selectRaw('
                DAYOFWEEK(placed_at) as day,
                COUNT(*) as orders
            ')
            ->whereBetween('placed_at', [$startOfWeek, $now])
            ->groupBy('day')
            ->pluck('orders', 'day')
            ->toArray();

        $weekDays = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
        $weeklyOrders = [];
        for ($d = 1; $d <= 7; $d++) {
            $weeklyOrders[] = (int) ($weeklyData[$d] ?? 0);
        }

        $maxWeekly = max(1, ...$weeklyOrders);

        // ============================================================
        // RECENT ASSIGNED ORDERS - Placeholder data
        // ============================================================
        $recentOrders = collect([]);

        // ============================================================
        // ANALYTICS CARDS - Staff-focused (Today)
        // ============================================================
        $analyticsCards = [
            [
                'label' => 'Pesanan Hari Ini',
                'value' => number_format($todayStats->orders_today ?? 0, 0, ',', '.'),
                'sub_value' => 'dari total ' . number_format($todayStats->total_orders_today ?? 0, 0, ',', '.'),
                'icon' => 'fa-solid fa-shopping-bag',
                'bg' => 'bg-blue-50',
                'text' => 'text-blue-600',
                'gradient' => 'from-blue-500 to-blue-600',
            ],
            [
                'label' => 'Siap Dikirim',
                'value' => number_format($todayStats->ready_for_delivery ?? 0, 0, ',', '.'),
                'sub_value' => 'menunggu pengiriman',
                'icon' => 'fa-solid fa-boxes-packing',
                'bg' => 'bg-violet-50',
                'text' => 'text-violet-600',
                'gradient' => 'from-violet-500 to-purple-600',
            ],
            [
                'label' => 'Sedang Dikirim',
                'value' => number_format($todayStats->shipped_today ?? 0, 0, ',', '.'),
                'sub_value' => 'dalam perjalanan',
                'icon' => 'fa-solid fa-truck-fast',
                'bg' => 'bg-amber-50',
                'text' => 'text-amber-600',
                'gradient' => 'from-amber-500 to-orange-600',
            ],
            [
                'label' => 'Selesai Hari Ini',
                'value' => number_format($todayStats->completed_today ?? 0, 0, ',', '.'),
                'sub_value' => 'pesanan selesai',
                'icon' => 'fa-solid fa-circle-check',
                'bg' => 'bg-emerald-50',
                'text' => 'text-emerald-600',
                'gradient' => 'from-emerald-500 to-teal-600',
            ],
        ];

        return view('livewire.staff.dashboard', [
            'analyticsCards' => $analyticsCards,
            'weeklyOrders' => $weeklyOrders,
            'weekDays' => $weekDays,
            'maxWeekly' => $maxWeekly,
            'recentOrders' => $recentOrders,
            'todayRevenue' => $todayStats->today_revenue ?? 0,
        ]);
    }
}
