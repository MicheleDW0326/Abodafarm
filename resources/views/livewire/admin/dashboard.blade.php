@php
    $maxRevenue = max(1, ...$monthlyRevenue);
    $maxOrders = max(1, ...$monthlyOrders);
    $maxWeekly = max(1, ...$weeklyOrders);
@endphp

<div class="space-y-6">
    {{-- Analytics Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($analyticsCards as $card)
            <article class="group relative overflow-hidden rounded-2xl border border-slate-100 bg-white p-5 shadow-xs transition-all hover:shadow-md">
                <div class="flex items-start justify-between">
                    <div class="rounded-xl {{ $card['bg'] }} p-3 {{ $card['text'] }}">
                        <i class="{{ $card['icon'] }} text-lg"></i>
                    </div>
                    @if(isset($card['change']))
                        <span class="rounded-full px-2 py-1 text-xs font-bold {{ $card['positive'] ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-500' }}">
                            {{ $card['change'] }}
                        </span>
                    @endif
                </div>
                <div class="mt-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ $card['label'] }}</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900">{{ $card['value'] }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $card['sub_value'] }}</p>
                </div>
            </article>
        @endforeach
    </div>

    {{-- Top Product & Top Farms Row --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Top Selling Product --}}
        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-xs">
            <div class="mb-4 flex items-center gap-3">
                <div class="rounded-xl bg-amber-50 p-2.5 text-amber-600">
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
                    <div class="flex-1">
                        <p class="font-bold text-slate-900">{{ $topProduct['name'] }}</p>
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
        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-xs lg:col-span-2">
            <div class="mb-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="rounded-xl bg-green-50 p-2.5 text-green-600">
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
                        <div class="flex items-center gap-3 rounded-xl bg-slate-50 p-3">
                            <div class="flex size-10 items-center justify-center rounded-full bg-green-100 text-green-600 font-bold">
                                #{{ $index + 1 }}
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-slate-900">{{ $farm->farm_name }}</p>
                                <p class="text-xs text-slate-500">{{ $farm->total_orders }} pesanan</p>
                            </div>
                            <div class="text-right">
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
        <section class="rounded-2xl border border-slate-100 bg-white p-6 xl:col-span-2 shadow-xs">
            <div class="mb-6 flex items-center justify-between">
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

            <div class="flex h-48 items-end gap-1">
                @foreach ($monthlyRevenue as $index => $value)
                    <div class="group relative flex flex-1 flex-col items-center gap-1">
                        <div class="flex h-40 w-full items-end justify-center">
                            {{-- Revenue Bar --}}
                            <div class="w-4/5 rounded-t-md bg-emerald-500 transition-all group-hover:bg-emerald-600"
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
        <section class="rounded-2xl border border-slate-100 bg-white p-6 shadow-xs">
            <div class="mb-6">
                <h3 class="text-base font-bold text-slate-900">Pesanan Mingguan</h3>
                <p class="mt-0.5 text-xs text-slate-400">{{ $thisWeekOrders }} pesanan minggu ini</p>
            </div>

            <div class="flex h-48 items-end gap-2">
                @foreach ($weeklyOrders as $index => $value)
                    <div class="group relative flex flex-1 flex-col items-center gap-1">
                        <div class="flex h-40 w-full items-end justify-center">
                            <div class="w-full max-w-8 rounded-t-md bg-blue-400 transition-all group-hover:bg-blue-500"
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
        <section class="rounded-2xl border border-slate-100 bg-white p-6 shadow-xs">
            <div class="mb-5">
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
                            <div class="h-full {{ $status['color'] }} rounded-full transition-all"
                                 style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Latest Transactions --}}
        <section class="rounded-2xl border border-slate-100 bg-white shadow-xs xl:col-span-2">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Transaksi Terbaru</h3>
                    <p class="mt-0.5 text-xs text-slate-400">Riwayat pesanan terbaru</p>
                </div>
                <a href="{{ route('admin.orders') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">
                    Lihat Semua <i class="fa-solid fa-arrow-right ml-1"></i>
                </a>
            </div>

            <div class="divide-y divide-slate-50">
                @forelse($latestTransactions as $tx)
                    <div class="flex items-center justify-between px-6 py-3.5 transition hover:bg-slate-50">
                        <div class="flex items-center gap-3">
                            <div class="flex size-10 items-center justify-center rounded-full
                                @if($tx->order_type === 'inquiry') bg-violet-100 text-violet-600
                                @else bg-emerald-100 text-emerald-600 @endif">
                                <i class="fa-solid {{ $tx->order_type === 'inquiry' ? 'fa-calendar-check' : 'fa-shopping-bag' }}"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-900">{{ $tx->customer_name ?: ($tx->user_name ?: 'Guest') }}</p>
                                <p class="text-xs text-slate-400">
                                    {{ $tx->order_number }}
                                    <span class="mx-1">·</span>
                                    {{ \Carbon\Carbon::parse($tx->created_at)->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold text-slate-900">Rp {{ number_format($tx->total_amount, 0, ',', '.') }}</p>
                            @php
                                $statusClass = match($tx->status) {
                                    'delivered', 'completed' => 'bg-emerald-50 text-emerald-700',
                                    'shipped' => 'bg-blue-50 text-blue-700',
                                    'processing' => 'bg-violet-50 text-violet-700',
                                    'pending' => 'bg-amber-50 text-amber-700',
                                    'cancelled', 'rejected' => 'bg-red-50 text-red-700',
                                    default => 'bg-slate-50 text-slate-600'
                                };
                            @endphp
                            <span class="mt-1 inline-block rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $statusClass }}">
                                {{ ucfirst($tx->status) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-12 text-center">
                        <i class="fa-solid fa-receipt text-4xl text-slate-200 mb-3"></i>
                        <p class="text-sm text-slate-400">Belum ada transaksi</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    {{-- Critical Stocks --}}
    @if($criticalStocks->count() > 0)
        <section class="rounded-2xl border border-red-100 bg-white shadow-xs">
            <div class="flex items-center justify-between border-b border-red-100 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="rounded-xl bg-red-50 p-2 text-red-600">
                        <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Stok Kritis</h3>
                        <p class="text-xs text-slate-400">Produk daging segar yang perlu diisi ulang</p>
                    </div>
                </div>
                <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-600">
                    {{ $criticalStocks->count() }} Produk
                </span>
            </div>

            <div class="grid grid-cols-1 gap-4 p-6 sm:grid-cols-2 lg:grid-cols-5">
                @foreach($criticalStocks as $item)
                    <div class="rounded-xl border border-red-100 bg-red-50/50 p-4">
                        <p class="font-semibold text-slate-900 line-clamp-1">{{ $item['name'] }}</p>
                        <p class="mt-0.5 text-xs text-slate-500">{{ $item['category'] }}</p>
                        <div class="mt-2 flex items-center justify-between">
                            <span class="text-lg font-bold text-red-600">{{ $item['stock'] }}</span>
                            <span class="text-xs text-slate-400">unit</span>
                        </div>
                        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-red-100">
                            <div class="h-full rounded-full bg-red-400" style="width: {{ ($item['stock'] / 10) * 100 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</div>
