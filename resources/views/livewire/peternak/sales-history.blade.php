<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Riwayat Penjualan</h2>
            <p class="mt-1 text-sm text-slate-500">Lacak penjualan ternak Anda</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col gap-4 rounded-xl border border-slate-100 bg-white p-4 sm:flex-row sm:items-center">
        <div class="flex items-center gap-2">
            <label class="text-sm text-slate-600">Dari:</label>
            <input type="date" wire:model.live="dateFrom" class="rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
        </div>
        <div class="flex items-center gap-2">
            <label class="text-sm text-slate-600">Sampai:</label>
            <input type="date" wire:model.live="dateTo" class="rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
        </div>
        <div class="flex-1"></div>
        <select
            wire:model.live="statusFilter"
            class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
        >
            <option value="all">Semua Status</option>
            <option value="pending">Menunggu</option>
            <option value="approved">Disetujui</option>
            <option value="ready_for_delivery">Siap Dikirim</option>
            <option value="delivered">Diterima</option>
            <option value="completed">Selesai</option>
        </select>
    </div>

    {{-- Summary --}}
    <div class="grid gap-4 sm:grid-cols-4">
        <article class="rounded-xl border border-slate-100 bg-white p-5">
            <p class="text-sm text-slate-500">Total Penjualan</p>
            <p class="mt-1 text-2xl font-bold text-emerald-600">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
        </article>
        <article class="rounded-xl border border-slate-100 bg-white p-5">
            <p class="text-sm text-slate-500">Total Ternak Terjual</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $totalItems }} ekor</p>
        </article>
        <article class="rounded-xl border border-slate-100 bg-white p-5">
            <p class="text-sm text-slate-500">Jumlah Pesanan</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $totalOrders }}</p>
        </article>
        <article class="rounded-xl border border-amber-100 bg-amber-50 p-5">
            <p class="text-sm text-amber-700">Menunggu Persetujuan</p>
            <p class="mt-1 text-2xl font-bold text-amber-600">{{ $stats['pending_bookings'] }}</p>
        </article>
    </div>

    {{-- Sales Table --}}
    <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                        <th class="px-4 py-3 font-semibold">Tanggal</th>
                        <th class="px-4 py-3 font-semibold">Kode Pesanan</th>
                        <th class="px-4 py-3 font-semibold">Ternak</th>
                        <th class="px-4 py-3 font-semibold">Pembeli</th>
                        <th class="px-4 py-3 font-semibold">Qty</th>
                        <th class="px-4 py-3 font-semibold">Status</th>
                        <th class="px-4 py-3 font-semibold text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($sales as $sale)
                        @php
                            $statusClass = match($sale->order_status) {
                                'pending' => 'bg-amber-100 text-amber-700',
                                'approved' => 'bg-green-100 text-green-700',
                                'ready_for_delivery' => 'bg-violet-100 text-violet-700',
                                'processing' => 'bg-blue-100 text-blue-700',
                                'shipped' => 'bg-purple-100 text-purple-700',
                                'delivered' => 'bg-emerald-100 text-emerald-700',
                                'completed' => 'bg-emerald-100 text-emerald-700',
                                'cancelled' => 'bg-slate-100 text-slate-600',
                                default => 'bg-slate-100 text-slate-700',
                            };
                            $statusLabel = match($sale->order_status) {
                                'pending' => 'Menunggu',
                                'approved' => 'Disetujui',
                                'ready_for_delivery' => 'Siap Kirim',
                                'processing' => 'Diproses',
                                'shipped' => 'Dikirim',
                                'delivered' => 'Diterima',
                                'completed' => 'Selesai',
                                'cancelled' => 'Dibatalkan',
                                default => ucfirst($sale->order_status),
                            };
                        @endphp
                        <tr class="transition hover:bg-slate-50">
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $sale->placed_at?->format('d M Y') ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm font-semibold text-slate-900">{{ $sale->order_number }}</td>
                            <td class="px-4 py-3 text-sm text-slate-900">{{ $sale->product_name }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $sale->customer_name }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $sale->quantity }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-sm font-semibold text-emerald-600">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center">
                                <i class="fa-solid fa-chart-line text-5xl text-slate-200"></i>
                                <p class="mt-4 text-slate-500">Belum ada data penjualan</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
