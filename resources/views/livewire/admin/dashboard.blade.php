@php
    $maxRevenue = max(1, ...$monthlyRevenue);
    $maxOrders = max(1, ...$monthlyOrders);
    $maxWeekly = max(1, ...$weeklyOrders);

    function getCardColor($text) {
        if (str_contains($text, 'emerald')) return 'emerald';
        if (str_contains($text, 'blue')) return 'blue';
        if (str_contains($text, 'violet')) return 'violet';
        if (str_contains($text, 'rose')) return 'rose';
        if (str_contains($text, 'amber')) return 'amber';
        return 'purple';
    }
@endphp

<div class="space-y-6">
    {{-- Analytics Cards - Using Stat Card Component --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($analyticsCards as $card)
            <x-ui.card.stat
                icon="{{ $card['icon'] }}"
                label="{{ $card['label'] }}"
                :value="$card['value']"
                :change="$card['change'] ?? null"
                :trend="($card['positive'] ?? true) ? 'up' : 'down'"
                color="{{ getCardColor($card['text']) }}"
                sub="{{ $card['sub_value'] ?? '' }}"
            />
        @endforeach
    </div>

    {{-- Top Product & Top Farms Row --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Top Selling Product --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center gap-3">
                <div class="rounded-xl bg-amber-100 p-2.5 text-amber-600">
                    <i class="fa-solid fa-trophy text-lg"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Produk Terlaris</h3>
                    <p class="text-xs text-slate-400">Berdasarkan total penjualan</p>
                </div>
            </div>
            @if($topProduct)
                <div class="flex items-center gap-4 rounded-xl bg-gradient-to-r from-amber-50 to-orange-50 p-4">
                    <div class="flex size-14 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                        <i class="fa-solid fa-crown text-2xl"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-slate-900 truncate">{{ $topProduct['name'] }}</p>
                        <div class="mt-1 flex items-center gap-3 text-xs text-slate-500">
                            <span><i class="fa-solid fa-box-open mr-1"></i>{{ $topProduct['qty'] }} terjual</span>
                            <span><i class="fa-solid fa-money-bill mr-1"></i>{{ $topProduct['sales'] }}</span>
                        </div>
                    </div>
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-8 text-center">
                    <i class="fa-solid fa-box-open text-4xl text-slate-200 mb-3"></i>
                    <p class="text-sm text-slate-400">Belum ada data penjualan</p>
                </div>
            @endif
        </div>

        {{-- Top Farms --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-2">
            <div class="mb-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="rounded-xl bg-green-100 p-2.5 text-green-600">
                        <i class="fa-solid fa-tractor text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Peternak Unggulan</h3>
                        <p class="text-xs text-slate-400">Berdasarkan total omset</p>
                    </div>
                </div>
            </div>
            @if($topFarms->count() > 0)
                <div class="space-y-3">
                    @foreach($topFarms as $index => $farm)
                        <div class="flex items-center gap-3 rounded-xl bg-slate-50 p-3 hover:bg-slate-100 transition-colors">
                            <div class="flex size-10 items-center justify-center rounded-full bg-green-100 text-green-600 font-bold text-sm">
                                #{{ $index + 1 }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-slate-900 truncate">{{ $farm->farm_name }}</p>
                                <p class="text-xs text-slate-500">{{ $farm->total_orders }} pesanan</p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="font-bold text-emerald-600">{{ $formatCompact($farm->total_sales) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-8 text-center">
                    <i class="fa-solid fa-tractor text-4xl text-slate-200 mb-3"></i>
                    <p class="text-sm text-slate-400">Belum ada data peternak</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        {{-- Monthly Revenue Chart --}}
        <section class="rounded-2xl border border-slate-200 bg-white p-5 xl:col-span-2 shadow-sm">
            <div class="mb-5 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Pendapatan Bulanan {{ $chartYear }}</h3>
                    <p class="mt-0.5 text-xs text-slate-400">Grafik pendapatan per bulan</p>
                </div>
                <div class="flex items-center gap-4 text-xs">
                    <span class="flex items-center gap-1.5">
                        <span class="size-2 rounded-full bg-emerald-500"></span> Pendapatan
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="size-2 rounded-full bg-blue-400"></span> Pesanan
                    </span>
                </div>
            </div>

            <div class="flex h-52 items-end gap-1">
                @foreach ($monthlyRevenue as $index => $value)
                    <div class="group relative flex flex-1 flex-col items-center gap-1">
                        <div class="flex h-44 w-full items-end justify-center">
                            {{-- Revenue Bar --}}
                            <div class="w-4/5 rounded-t-md bg-emerald-500 transition-all duration-200 group-hover:bg-emerald-600"
                                 style="height: {{ max(4, ($value / $maxRevenue) * 100) }}%">
                            </div>
                        </div>
                        <span class="text-[10px] text-slate-400">{{ $months[$index] }}</span>

                        {{-- Tooltip --}}
                        <div class="absolute -top-12 left-1/2 -translate-x-1/2 rounded-md bg-slate-900 px-2 py-1 text-[10px] text-white opacity-0 transition group-hover:opacity-100 pointer-events-none z-10 whitespace-nowrap">
                            <div class="font-semibold">{{ $formatCompact($value) }}</div>
                            <div class="text-slate-300">{{ $monthlyOrders[$index] }} pesanan</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Weekly Orders Chart --}}
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-5">
                <h3 class="text-base font-bold text-slate-900">Pesanan Mingguan</h3>
                <p class="mt-0.5 text-xs text-slate-400">{{ $thisWeekOrders }} pesanan minggu ini</p>
            </div>

            <div class="flex h-52 items-end gap-2">
                @foreach ($weeklyOrders as $index => $value)
                    <div class="group relative flex flex-1 flex-col items-center gap-1">
                        <div class="flex h-44 w-full items-end justify-center">
                            <div class="w-full max-w-8 rounded-t-md bg-blue-400 transition-all duration-200 group-hover:bg-blue-500"
                                 style="height: {{ max(4, ($value / $maxWeekly) * 100) }}%">
                            </div>
                        </div>
                        <span class="text-[10px] text-slate-400">{{ $weekDays[$index] }}</span>

                        {{-- Tooltip --}}
                        <div class="absolute -top-8 left-1/2 -translate-x-1/2 rounded-md bg-slate-900 px-2 py-1 text-[10px] text-white opacity-0 transition group-hover:opacity-100 pointer-events-none z-10">
                            {{ $value }} pesanan
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    {{-- Order Status & Latest Transactions Row --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        {{-- Order Status Distribution --}}
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4">
                <h3 class="text-sm font-bold text-slate-900">Distribusi Status</h3>
                <p class="mt-0.5 text-xs text-slate-400">{{ number_format($totalStatusOrders, 0, ',', '.') }} total pesanan</p>
            </div>

            <div class="space-y-3">
                @foreach($orderStatusDistribution as $status)
                    @php $percent = $totalStatusOrders > 0 ? round(($status['count'] / $totalStatusOrders) * 100) : 0; @endphp
                    <div>
                        <div class="mb-1 flex items-center justify-between text-xs">
                            <span class="text-slate-600">{{ $status['label'] }}</span>
                            <span class="font-semibold text-slate-900">{{ $status['count'] }}</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full {{ $status['color'] }} rounded-full transition-all duration-500"
                                 style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Latest Transactions --}}
        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm xl:col-span-2 overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Transaksi Terbaru</h3>
                    <p class="mt-0.5 text-xs text-slate-400">Riwayat pesanan terbaru</p>
                </div>
                <a href="{{ route('admin.orders') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 transition-colors">
                    Lihat Semua <i class="fa-solid fa-arrow-right ml-1"></i>
                </a>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($latestTransactions as $tx)
                    <div class="flex items-center justify-between px-5 py-3.5 transition hover:bg-slate-50">
                        <div class="flex items-center gap-3">
                            <div class="flex size-10 items-center justify-center rounded-full shrink-0
                                @if($tx->order_type === 'inquiry') bg-violet-100 text-violet-600
                                @else bg-emerald-100 text-emerald-600 @endif">
                                <i class="fa-solid {{ $tx->order_type === 'inquiry' ? 'fa-calendar-check' : 'fa-shopping-bag' }}"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-slate-900 truncate">{{ $tx->customer_name ?: ($tx->user_name ?: 'Guest') }}</p>
                                <p class="text-xs text-slate-400">
                                    {{ $tx->order_number }}
                                    <span class="mx-1">·</span>
                                    {{ \Carbon\Carbon::parse($tx->created_at)->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                        <div class="text-right shrink-0 ml-4">
                            <p class="text-sm font-bold text-slate-900">Rp {{ number_format($tx->total_amount, 0, ',', '.') }}</p>
                            <x-ui.badge.status :status="$tx->status" size="sm" :showIcon="false" />
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-8">
                        <x-ui.empty-state
                            icon="fa-receipt"
                            title="Belum ada transaksi"
                            description="Transaksi terbaru akan muncul di sini"
                        />
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    {{-- Critical Stocks --}}
    @if($criticalStocks->count() > 0)
        <section class="rounded-2xl border border-red-200 bg-white shadow-sm overflow-hidden">
            <div class="flex items-center justify-between border-b border-red-100 px-5 py-4 bg-red-50/50">
                <div class="flex items-center gap-3">
                    <div class="rounded-xl bg-red-100 p-2 text-red-600">
                        <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Stok Kritis</h3>
                        <p class="text-xs text-slate-500">Produk daging segar yang perlu diisi ulang</p>
                    </div>
                </div>
                <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-600">
                    {{ $criticalStocks->count() }} Produk
                </span>
            </div>

            <div class="grid grid-cols-2 gap-4 p-5 sm:grid-cols-3 lg:grid-cols-5">
                @foreach($criticalStocks as $item)
                    <div class="rounded-xl border border-red-200 bg-red-50/30 p-4 hover:bg-red-50 transition-colors">
                        <p class="font-semibold text-slate-900 line-clamp-1 text-sm">{{ $item['name'] }}</p>
                        <p class="mt-0.5 text-xs text-slate-500">{{ $item['category'] }}</p>
                        <div class="mt-3 flex items-center justify-between">
                            <span class="text-xl font-bold text-red-600">{{ $item['stock'] }}</span>
                            <span class="text-xs text-slate-400">unit</span>
                        </div>
                        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-red-100">
                            <div class="h-full rounded-full bg-red-400 transition-all" style="width: {{ ($item['stock'] / 10) * 100 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</div>
