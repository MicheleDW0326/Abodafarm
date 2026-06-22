@php
    $maxSales = max(1, ...$sales);
@endphp

<div class="space-y-6">
    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($summaryCards as $card)
            <article class="rounded-2xl border border-slate-100 bg-white p-5 shadow-xs">
                <div class="mb-4 flex items-start justify-between">
                    <div class="rounded-lg p-2 @if($card['color'] === 'emerald') bg-emerald-50 text-emerald-600 @elseif($card['color'] === 'blue') bg-blue-50 text-blue-600 @elseif($card['color'] === 'purple') bg-purple-50 text-purple-600 @else bg-amber-50 text-amber-600 @endif">
                        <i class="{{ $card['icon'] ?? 'fa-solid fa-chart-line' }} text-sm"></i>
                    </div>
                    <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $card['positive'] ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-500' }}">{{ $card['change'] }}</span>
                </div>
                <p class="text-xs text-slate-400">{{ $card['label'] }}</p>
                <p class="mt-1 text-xl font-bold text-slate-900">{{ $card['value'] }}</p>
            </article>
        @endforeach
    </div>

    {{-- Farm Overview Cards --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-6">
        @foreach ($farmCards as $card)
            <article class="rounded-2xl border border-slate-100 bg-white p-4 shadow-xs">
                <div class="mb-3 flex items-center gap-2 @if($card['color'] === 'green') text-green-600 @elseif($card['color'] === 'rose') text-rose-600 @elseif($card['color'] === 'sky') text-sky-600 @elseif($card['color'] === 'yellow') text-yellow-600 @elseif($card['color'] === 'indigo') text-indigo-600 @else text-pink-600 @endif">
                    <i class="{{ $card['icon'] ?? 'fa-solid fa-info-circle' }}"></i>
                    <span class="text-xs font-medium">{{ $card['label'] }}</span>
                </div>
                <p class="text-2xl font-bold text-slate-900">{{ $card['value'] }}</p>
            </article>
        @endforeach
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        {{-- Sales Chart --}}
        <section class="rounded-2xl border border-slate-100 bg-white p-6 xl:col-span-2">
            <div class="mb-6 flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Grafik Penjualan {{ $chartYear }}</h3>
                    <p class="mt-0.5 text-xs text-slate-400">Performa bulanan sepanjang tahun</p>
                </div>
                <div class="rounded-lg bg-slate-100 p-1 text-xs font-semibold">
                    <button type="button" class="rounded-md bg-white px-3 py-1.5 text-slate-900">Pendapatan</button>
                    <button type="button" class="px-3 py-1.5 text-slate-500">Pesanan</button>
                </div>
            </div>

            <div class="flex h-52 items-end gap-2">
                @foreach ($sales as $index => $value)
                    @php
                        $height = max(16, (int) round(($value / $maxSales) * 100));
                    @endphp
                    <div class="group flex flex-1 flex-col items-center gap-2">
                        <div class="relative flex h-40 w-full items-end justify-center">
                            <div class="w-full rounded-t-lg bg-emerald-500/20 transition group-hover:bg-emerald-500/40" style="height: {{ $height }}%">
                                <div class="absolute -top-8 left-1/2 -translate-x-1/2 rounded-md bg-slate-900 px-2 py-1 text-[10px] text-white opacity-0 transition group-hover:opacity-100">
                                        {{ $formatRupiahCompact($value) }}
                                </div>
                            </div>
                        </div>
                        <span class="text-[10px] text-slate-400">{{ $months[$index] }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Order Status & Type --}}
        <section class="space-y-6">
            <div class="rounded-2xl border border-slate-100 bg-white p-6">
                <h3 class="text-base font-bold text-slate-900">Status Pesanan</h3>
                <p class="mb-5 mt-0.5 text-xs text-slate-400">Distribusi semua pesanan</p>

                <div class="space-y-3">
                    @foreach ($orderStatuses as $status)
                        <div>
                            <div class="mb-1 flex items-center justify-between text-sm">
                                <div class="flex items-center gap-2">
                                    <span class="size-2.5 rounded-full {{ $status['color'] }}"></span>
                                    <span class="text-slate-700">{{ $status['name'] }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-slate-900">{{ $status['count'] }}</span>
                                    <span class="w-8 text-right text-xs text-slate-400">{{ $status['percent'] }}%</span>
                                </div>
                            </div>
                            <div class="h-1.5 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full {{ $status['color'] }}" style="width: {{ $status['percent'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4">
                    <span class="text-sm text-slate-500">Total Hari Ini</span>
                    <span class="text-lg font-bold text-slate-900">{{ number_format($todayOrders, 0, ',', '.') }}</span>
                </div>
            </div>

            {{-- Order Type Distribution --}}
            <div class="rounded-2xl border border-slate-100 bg-white p-6">
                <h3 class="text-base font-bold text-slate-900">Tipe Pesanan</h3>
                <p class="mb-5 mt-0.5 text-xs text-slate-400">Pembelian vs Inquiry</p>

                <div class="space-y-3">
                    @foreach ($orderTypes as $type)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="size-2.5 rounded-full {{ $type['color'] }}"></span>
                                <span class="text-sm text-slate-700">{{ $type['name'] }}</span>
                            </div>
                            <span class="font-semibold text-slate-900">{{ $type['count'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        {{-- Critical Stocks --}}
        <section class="overflow-hidden rounded-2xl border border-slate-100 bg-white">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Stok Kritis</h3>
                    <p class="text-xs text-slate-400">Produk daging segar yang perlu diisi ulang</p>
                </div>
                <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-500">{{ count($criticalStocks) }} Produk</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                            <th class="px-4 py-3 font-semibold">Produk</th>
                            <th class="px-4 py-3 font-semibold">Stok</th>
                            <th class="px-4 py-3 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($criticalStocks as $item)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <span class="text-sm font-medium text-slate-900">{{ $item['name'] }}</span>
                                    <span class="block text-xs text-slate-400">{{ $item['category'] }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-bold text-red-600">{{ $item['stock'] }}</span>
                                        <div class="h-1.5 w-12 overflow-hidden rounded-full bg-slate-100">
                                            <div class="h-full rounded-full bg-red-400" style="width: {{ $item['percent'] }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-500">Kritis</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-sm text-slate-400">
                                    <i class="fa-solid fa-check-circle text-green-500 mr-2"></i>
                                    Semua stok aman!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Recent Orders --}}
        <section class="overflow-hidden rounded-2xl border border-slate-100 bg-white">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Pesanan Terbaru</h3>
                    <p class="text-xs text-slate-400">Riwayat pesanan terbaru</p>
                </div>
                <a href="{{ route('admin.orders') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">Lihat Semua</a>
            </div>

            <div class="divide-y divide-slate-50">
                @forelse($recentOrders as $order)
                    <div class="flex items-center justify-between px-6 py-4">
                        <div>
                            <p class="text-sm font-medium text-slate-900">{{ $order->customer_name }}</p>
                            <p class="text-xs text-slate-400">{{ $order->order_number }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-slate-900">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold
                                @if($order->status === 'delivered') bg-green-50 text-green-700
                                @elseif($order->status === 'pending') bg-amber-50 text-amber-700
                                @elseif($order->status === 'cancelled') bg-red-50 text-red-700
                                @else bg-blue-50 text-blue-700 @endif">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-sm text-slate-400">
                        <i class="fa-solid fa-inbox mr-2"></i>
                        Belum ada pesanan
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</div>
