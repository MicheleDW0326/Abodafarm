<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
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
            return 'Rp '.number_format($amount / 1_000_000_000, 1).' M';
        }

        if ($amount >= 1_000_000) {
            return 'Rp '.number_format($amount / 1_000_000, 1).' Jt';
        }

        if ($amount >= 1_000) {
            return 'Rp '.number_format($amount / 1_000, 0).'K';
        }

        return 'Rp '.number_format($amount, 0, ',', '.');
    }

    public function render()
    {
        $now = now();

        $thisMonthStart = $now->copy()->startOfMonth();
        $previousMonthStart = $thisMonthStart->copy()->subMonth();
        $previousMonthEnd = $thisMonthStart->copy()->subSecond();

        $pelangganUsersQuery = fn () => DB::table('users')
            ->join('model_has_roles', function ($join): void {
                $join->on('users.id', '=', 'model_has_roles.model_id')
                    ->where('model_has_roles.model_type', '=', 'App\\Models\\User');
            })
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('roles.name', 'pelanggan')
            ->distinct('users.id');

        $totalRevenue = (int) DB::table('orders')->sum('total_amount');
        $totalOrders = (int) DB::table('orders')->count();
        $activeCustomers = (int) $pelangganUsersQuery()->count('users.id');
        $averageOrder = $totalOrders > 0 ? (int) round($totalRevenue / $totalOrders) : 0;

        $thisMonthRevenue = (int) DB::table('orders')->whereBetween('placed_at', [$thisMonthStart, $now])->sum('total_amount');
        $previousMonthRevenue = (int) DB::table('orders')->whereBetween('placed_at', [$previousMonthStart, $previousMonthEnd])->sum('total_amount');

        $thisMonthOrders = (int) DB::table('orders')->whereBetween('placed_at', [$thisMonthStart, $now])->count();
        $previousMonthOrders = (int) DB::table('orders')->whereBetween('placed_at', [$previousMonthStart, $previousMonthEnd])->count();

        $thisMonthCustomers = (int) $pelangganUsersQuery()->whereBetween('users.created_at', [$thisMonthStart, $now])->count('users.id');
        $previousMonthCustomers = (int) $pelangganUsersQuery()->whereBetween('users.created_at', [$previousMonthStart, $previousMonthEnd])->count('users.id');

        $thisMonthAverageOrder = $thisMonthOrders > 0 ? $thisMonthRevenue / $thisMonthOrders : 0;
        $previousMonthAverageOrder = $previousMonthOrders > 0 ? $previousMonthRevenue / $previousMonthOrders : 0;

        $revenueChange = $this->buildChangeMeta($thisMonthRevenue, $previousMonthRevenue);
        $ordersChange = $this->buildChangeMeta($thisMonthOrders, $previousMonthOrders);
        $customersChange = $this->buildChangeMeta($thisMonthCustomers, $previousMonthCustomers);
        $averageChange = $this->buildChangeMeta($thisMonthAverageOrder, $previousMonthAverageOrder);

        $summaryCards = [
            [
                'label' => 'Total Pendapatan',
                'value' => $this->formatRupiahCompact($totalRevenue),
                'change' => $revenueChange['text'],
                'positive' => $revenueChange['positive'],
            ],
            [
                'label' => 'Total Pesanan',
                'value' => number_format($totalOrders, 0, ',', '.'),
                'change' => $ordersChange['text'],
                'positive' => $ordersChange['positive'],
            ],
            [
                'label' => 'Pelanggan Aktif',
                'value' => number_format($activeCustomers, 0, ',', '.'),
                'change' => $customersChange['text'],
                'positive' => $customersChange['positive'],
            ],
            [
                'label' => 'Rata-rata Pesanan',
                'value' => $this->formatRupiahCompact($averageOrder),
                'change' => $averageChange['text'],
                'positive' => $averageChange['positive'],
            ],
        ];

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

        $statusConfig = [
            'pending' => ['name' => 'Pending', 'color' => 'bg-amber-500'],
            'processing' => ['name' => 'Processing', 'color' => 'bg-blue-500'],
            'shipped' => ['name' => 'Shipped', 'color' => 'bg-violet-500'],
            'delivered' => ['name' => 'Delivered', 'color' => 'bg-emerald-500'],
            'cancelled' => ['name' => 'Cancelled', 'color' => 'bg-red-500'],
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

        $criticalStocks = [
            ['name' => 'Atlantic Salmon Fillet', 'sku' => 'SEA-SAL-001', 'category' => 'Seafood', 'stock' => '4 kg', 'min' => '20 kg', 'percent' => 20],
            ['name' => 'Tiger Prawns 500g', 'sku' => 'SEA-PRW-002', 'category' => 'Seafood', 'stock' => '7 pack', 'min' => '25 pack', 'percent' => 28],
            ['name' => 'Chicken Breast Fillet', 'sku' => 'PTR-CBF-003', 'category' => 'Poultry', 'stock' => '11 pack', 'min' => '30 pack', 'percent' => 37],
            ['name' => 'Whole Red Snapper', 'sku' => 'SEA-SNP-004', 'category' => 'Seafood', 'stock' => '6 pcs', 'min' => '15 pcs', 'percent' => 40],
            ['name' => 'Vacuum Seal Bags L', 'sku' => 'PKG-VSB-010', 'category' => 'Packaging', 'stock' => '50 pcs', 'min' => '200 pcs', 'percent' => 25],
        ];

        return view('livewire.admin.dashboard', [
            'summaryCards' => $summaryCards,
            'sales' => $sales,
            'months' => $months,
            'orderStatuses' => $orderStatuses,
            'todayOrders' => (int) DB::table('orders')->whereDate('placed_at', $now->toDateString())->count(),
            'criticalStocks' => $criticalStocks,
            'formatRupiahCompact' => fn (int|float $amount) => $this->formatRupiahCompact($amount),
            'chartYear' => $now->year,
        ]);
    }
}
