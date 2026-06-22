<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-sm text-slate-500">Analisis keuangan dan performa penjualan</p>
        </div>

        {{-- Period Filter --}}
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex rounded-lg bg-slate-100 p-1">
                <button wire:click="$set('period', 'day')" class="rounded-md px-3 py-1.5 text-xs font-medium transition-all {{ $period === 'day' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-600' }}">
                    Harian
                </button>
                <button wire:click="$set('period', 'week')" class="rounded-md px-3 py-1.5 text-xs font-medium transition-all {{ $period === 'week' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-600' }}">
                    Mingguan
                </button>
                <button wire:click="$set('period', 'month')" class="rounded-md px-3 py-1.5 text-xs font-medium transition-all {{ $period === 'month' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-600' }}">
                    Bulanan
                </button>
                <button wire:click="$set('period', 'year')" class="rounded-md px-3 py-1.5 text-xs font-medium transition-all {{ $period === 'year' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-600' }}">
                    Tahunan
                </button>
            </div>

            <div class="flex items-center gap-2">
                <input type="date" wire:model.live="dateFrom" class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm" />
                <span class="text-slate-400">-</span>
                <input type="date" wire:model.live="dateTo" class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm" />
            </div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <div class="flex items-center justify-between">
                <div class="rounded-lg bg-emerald-100 p-3">
                    <i class="fa-solid fa-money-bill-wave text-xl text-emerald-600"></i>
                </div>
                <span class="text-xs font-medium {{ $dailyComparison['revenue_change'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ $dailyComparison['revenue_change'] >= 0 ? '+' : '' }}{{ number_format($dailyComparison['revenue_change'], 1) }}%
                </span>
            </div>
            <p class="mt-4 text-2xl font-bold text-slate-900">Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}</p>
            <p class="text-sm text-slate-500">Total Pendapatan</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <div class="flex items-center justify-between">
                <div class="rounded-lg bg-blue-100 p-3">
                    <i class="fa-solid fa-receipt text-xl text-blue-600"></i>
                </div>
                <span class="text-xs font-medium {{ $dailyComparison['orders_change'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ $dailyComparison['orders_change'] >= 0 ? '+' : '' }}{{ number_format($dailyComparison['orders_change'], 1) }}%
                </span>
            </div>
            <p class="mt-4 text-2xl font-bold text-slate-900">{{ number_format($summary['total_orders'], 0, ',', '.') }}</p>
            <p class="text-sm text-slate-500">Total Pesanan</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <div class="flex items-center justify-between">
                <div class="rounded-lg bg-purple-100 p-3">
                    <i class="fa-solid fa-chart-line text-xl text-purple-600"></i>
                </div>
            </div>
            <p class="mt-4 text-2xl font-bold text-slate-900">Rp {{ number_format($summary['average_order'], 0, ',', '.') }}</p>
            <p class="text-sm text-slate-500">Rata-rata Pesanan</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <div class="flex items-center justify-between">
                <div class="rounded-lg bg-amber-100 p-3">
                    <i class="fa-solid fa-box text-xl text-amber-600"></i>
                </div>
            </div>
            <p class="mt-4 text-2xl font-bold text-slate-900">{{ $inventoryStats['available'] }}</p>
            <p class="text-sm text-slate-500">Produk Tersedia</p>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Revenue by Status --}}
        <div class="rounded-xl border border-slate-200 bg-white p-6">
            <h3 class="text-base font-semibold text-slate-900">Pendapatan per Status</h3>
            <p class="mt-1 text-sm text-slate-500">Distribusi pendapatan berdasarkan status pesanan</p>

            <div class="mt-6 space-y-4">
                @php
                    $totalRevenue = $summary['total_revenue'];
                @endphp
                @forelse($revenueByStatus as $item)
                    @php
                        $percent = $totalRevenue > 0 ? ($item->revenue / $totalRevenue) * 100 : 0;
                        $color = match($item->status) {
                            'pending' => 'bg-amber-500',
                            'processing' => 'bg-blue-500',
                            'shipped' => 'bg-violet-500',
                            'delivered' => 'bg-emerald-500',
                            'cancelled' => 'bg-red-500',
                            default => 'bg-slate-500',
                        };
                        $label = match($item->status) {
                            'pending' => 'Pending',
                            'processing' => 'Diproses',
                            'shipped' => 'Dikirim',
                            'delivered' => 'Selesai',
                            'cancelled' => 'Batal',
                            default => ucfirst($item->status),
                        };
                    @endphp
                    <div>
                        <div class="mb-1 flex items-center justify-between text-sm">
                            <div class="flex items-center gap-2">
                                <span class="size-2.5 rounded-full {{ $color }}"></span>
                                <span class="text-slate-700">{{ $label }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-slate-900">Rp {{ number_format($item->revenue, 0, ',', '.') }}</span>
                                <span class="w-10 text-right text-xs text-slate-400">{{ number_format($percent, 0) }}%</span>
                            </div>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full {{ $color }}" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="py-4 text-center text-sm text-slate-400">Tidak ada data</p>
                @endforelse
            </div>
        </div>

        {{-- Daily Comparison --}}
        <div class="rounded-xl border border-slate-200 bg-white p-6">
            <h3 class="text-base font-semibold text-slate-900">Perbandingan Harian</h3>
            <p class="mt-1 text-sm text-slate-500">Hari ini vs kemarin</p>

            <div class="mt-6 grid grid-cols-2 gap-4">
                <div class="rounded-lg bg-slate-50 p-4">
                    <p class="text-xs text-slate-500">Hari Ini</p>
                    <p class="mt-1 text-xl font-bold text-slate-900">Rp {{ number_format($dailyComparison['today_revenue'], 0, ',', '.') }}</p>
                    <p class="text-xs text-slate-500">{{ $dailyComparison['today_orders'] }} pesanan</p>
                </div>
                <div class="rounded-lg bg-slate-50 p-4">
                    <p class="text-xs text-slate-500">Kemarin</p>
                    <p class="mt-1 text-xl font-bold text-slate-900">Rp {{ number_format($dailyComparison['yesterday_revenue'], 0, ',', '.') }}</p>
                    <p class="text-xs text-slate-500">{{ $dailyComparison['yesterday_orders'] }} pesanan</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Top Products --}}
    <div class="rounded-xl border border-slate-200 bg-white overflow-hidden">
        <div class="border-b border-slate-200 px-6 py-4">
            <h3 class="text-base font-semibold text-slate-900">Produk Terlaris</h3>
            <p class="mt-1 text-sm text-slate-500">Berdasarkan total penjualan dalam periode</p>
        </div>

        <table class="w-full text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left font-semibold text-slate-900">#</th>
                    <th class="px-6 py-3 text-left font-semibold text-slate-900">Produk</th>
                    <th class="px-6 py-3 text-right font-semibold text-slate-900">Terjual</th>
                    <th class="px-6 py-3 text-right font-semibold text-slate-900">Total Penjualan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($topProducts as $index => $product)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-3 text-slate-400">{{ $index + 1 }}</td>
                        <td class="px-6 py-3 font-medium text-slate-900">{{ $product->product_name }}</td>
                        <td class="px-6 py-3 text-right text-slate-600">{{ number_format($product->total_qty, 0, ',', '.') }} unit</td>
                        <td class="px-6 py-3 text-right font-semibold text-emerald-600">Rp {{ number_format($product->total_sales, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center">
                            <i class="fa-solid fa-chart-bar text-4xl text-slate-300 mb-3"></i>
                            <p class="text-slate-500">Belum ada data penjualan</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Inventory Overview --}}
    <div class="rounded-xl border border-slate-200 bg-white p-6">
        <h3 class="text-base font-semibold text-slate-900">Overview Inventaris</h3>
        <p class="mt-1 text-sm text-slate-500">Statistik produk saat ini</p>

        <div class="mt-6 grid gap-4 md:grid-cols-4">
            <div class="rounded-lg border border-slate-200 bg-green-50 p-4 text-center">
                <i class="fa-solid fa-cow text-2xl text-green-600"></i>
                <p class="mt-2 text-2xl font-bold text-green-700">{{ $inventoryStats['live'] }}</p>
                <p class="text-sm text-green-600">Ternak Hidup</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-rose-50 p-4 text-center">
                <i class="fa-solid fa-drumstick-bite text-2xl text-rose-600"></i>
                <p class="mt-2 text-2xl font-bold text-rose-700">{{ $inventoryStats['fresh'] }}</p>
                <p class="text-sm text-rose-600">Daging Segar</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-emerald-50 p-4 text-center">
                <i class="fa-solid fa-check-circle text-2xl text-emerald-600"></i>
                <p class="mt-2 text-2xl font-bold text-emerald-700">{{ $inventoryStats['available'] }}</p>
                <p class="text-sm text-emerald-600">Tersedia</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-amber-50 p-4 text-center">
                <i class="fa-solid fa-exclamation-triangle text-2xl text-amber-600"></i>
                <p class="mt-2 text-2xl font-bold text-amber-700">{{ $inventoryStats['low_stock'] }}</p>
                <p class="text-sm text-amber-600">Stok Rendah</p>
            </div>
        </div>
    </div>
</div>
