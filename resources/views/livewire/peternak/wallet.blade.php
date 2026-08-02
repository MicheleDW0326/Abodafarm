<div class="space-y-6">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Dompet & Pendapatan</h2>
        <p class="mt-1 text-sm text-slate-500">Kelola keuangan peternakan Anda</p>
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
    </div>

    {{-- Earnings Summary --}}
    <div class="rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-emerald-100/50 p-6">
        <div class="grid gap-6 md:grid-cols-3">
            <div class="text-center md:border-r md:border-emerald-200">
                <p class="text-sm font-medium text-emerald-700">Pendapatan Bersih</p>
                <p class="mt-2 text-3xl font-bold text-emerald-700">Rp {{ number_format($netEarnings, 0, ',', '.') }}</p>
                <p class="mt-1 text-xs text-emerald-600">Setelah komisi {{ $commissionRate * 100 }}%</p>
            </div>
            <div class="text-center md:border-r md:border-emerald-200">
                <p class="text-sm text-emerald-700">Penjualan Kotor</p>
                <p class="mt-2 text-2xl font-semibold text-emerald-600">Rp {{ number_format($grossSales, 0, ',', '.') }}</p>
                <p class="mt-1 text-xs text-red-500">Komisi: Rp {{ number_format($commission, 0, ',', '.') }}</p>
            </div>
            <div class="text-center">
                <p class="text-sm text-violet-700">Pending</p>
                <p class="mt-2 text-2xl font-semibold text-violet-600">Rp {{ number_format($pendingEarnings, 0, ',', '.') }}</p>
                <p class="mt-1 text-xs text-violet-500">Menunggu konfirmasi</p>
            </div>
        </div>
    </div>

    {{-- Transactions --}}
    <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white">
        <div class="border-b border-slate-100 px-6 py-4">
            <h3 class="font-semibold text-slate-900">Riwayat Transaksi</h3>
            <p class="mt-1 text-sm text-slate-500">Transaksi yang sudah selesai (approved ke atas)</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                        <th class="px-4 py-3 font-semibold">Tanggal</th>
                        <th class="px-4 py-3 font-semibold">Kode</th>
                        <th class="px-4 py-3 font-semibold">Ternak</th>
                        <th class="px-4 py-3 font-semibold">Jumlah</th>
                        <th class="px-4 py-3 font-semibold">Status</th>
                        <th class="px-4 py-3 font-semibold text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($transactions as $trx)
                        @php
                            $statusClass = match($trx->order_status) {
                                'approved' => 'bg-green-100 text-green-700',
                                'ready_for_delivery' => 'bg-violet-100 text-violet-700',
                                'processing' => 'bg-blue-100 text-blue-700',
                                'shipped' => 'bg-purple-100 text-purple-700',
                                'delivered' => 'bg-emerald-100 text-emerald-700',
                                'completed' => 'bg-emerald-100 text-emerald-700',
                                default => 'bg-slate-100 text-slate-700',
                            };
                            $statusLabel = match($trx->order_status) {
                                'approved' => 'Disetujui',
                                'ready_for_delivery' => 'Siap Kirim',
                                'processing' => 'Diproses',
                                'shipped' => 'Dikirim',
                                'delivered' => 'Diterima',
                                'completed' => 'Selesai',
                                default => ucfirst($trx->order_status),
                            };
                        @endphp
                        <tr class="transition hover:bg-slate-50">
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $trx->placed_at?->format('d M Y') ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm font-semibold text-slate-900">{{ $trx->order_number }}</td>
                            <td class="px-4 py-3 text-sm text-slate-900">{{ $trx->product_name }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $trx->quantity }} ekor</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-sm font-semibold text-emerald-600">Rp {{ number_format($trx->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center">
                                <i class="fa-solid fa-wallet text-5xl text-slate-200"></i>
                                <p class="mt-4 text-slate-500">Belum ada transaksi</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Info --}}
    <div class="rounded-xl border border-blue-100 bg-blue-50 p-4">
        <div class="flex items-start gap-3">
            <i class="fa-solid fa-circle-info text-blue-400 mt-0.5"></i>
            <div class="text-sm text-blue-700">
                <p class="font-medium">Informasi Pendapatan</p>
                <ul class="mt-2 space-y-1 text-blue-600">
                    <li>Hanya booking/pesanan dengan status <strong>Disetujui</strong> ke atas yang dihitung sebagai pendapatan.</li>
                    <li>Pendapatan <strong>Pending</strong> adalah booking yang sudah disetujui tapi belum selesai.</li>
                    <li>Komisi {{ $commissionRate * 100 }}% akan dipotong saat pencairan.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
