@php
    $maxSales = max(1, ...$sales);
@endphp

<div class="space-y-6">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($summaryCards as $card)
            <article class="rounded-2xl border border-slate-100 bg-white p-5 shadow-xs">
                <div class="mb-4 flex items-start justify-between">
                    <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $card['positive'] ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-500' }}">{{ $card['change'] }}</span>
                </div>
                <p class="text-xs text-slate-400">{{ $card['label'] }}</p>
                <p class="mt-1 text-xl font-bold text-slate-900">{{ $card['value'] }}</p>
            </article>
        @endforeach
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
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
                            <div class="w-full rounded-t-lg bg-[#1A7FA8]/20 transition group-hover:bg-[#1A7FA8]/45" style="height: {{ $height }}%">
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

        <section class="rounded-2xl border border-slate-100 bg-white p-6">
            <h3 class="text-base font-bold text-slate-900">Status Pesanan</h3>
            <p class="mb-5 mt-0.5 text-xs text-slate-400">Distribusi hari ini</p>

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
        </section>
    </div>

    <section class="overflow-hidden rounded-2xl border border-slate-100 bg-white">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Stok Kritis</h3>
                <p class="text-xs text-slate-400">Produk yang perlu segera diisi ulang</p>
            </div>
            <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-500">{{ count($criticalStocks) }} Produk</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                        <th class="px-6 py-3 font-semibold">Produk</th>
                        <th class="px-6 py-3 font-semibold">SKU</th>
                        <th class="px-6 py-3 font-semibold">Kategori</th>
                        <th class="px-6 py-3 font-semibold">Stok Saat Ini</th>
                        <th class="px-6 py-3 font-semibold">Min. Stok</th>
                        <th class="px-6 py-3 font-semibold">Status</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach ($criticalStocks as $item)
                        <tr class="transition hover:bg-slate-50">
                            <td class="px-6 py-4 text-sm font-medium text-slate-900">{{ $item['name'] }}</td>
                            <td class="px-6 py-4 text-xs font-mono text-slate-400">{{ $item['sku'] }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600">{{ $item['category'] }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-bold text-red-600">{{ $item['stock'] }}</span>
                                    <div class="h-1.5 w-16 overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full bg-red-400" style="width: {{ $item['percent'] }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-500">{{ $item['min'] }}</td>
                            <td class="px-6 py-4">
                                <span class="rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-500">Kritis</span>
                            </td>
                            <td class="px-6 py-4">
                                <button type="button" class="text-xs font-semibold text-[#1A7FA8] transition hover:underline">Restock</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>
