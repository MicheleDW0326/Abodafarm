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
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-emerald-700">Pendapatan Bersih</p>
                <p class="mt-2 text-3xl font-bold text-emerald-700">Rp {{ number_format($netEarnings, 0, ',', '.') }}</p>
                <p class="mt-1 text-xs text-emerald-600">Setelah komisi {{ $commissionRate * 100 }}%</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-emerald-700">Penjualan Kotor</p>
                <p class="mt-1 text-lg font-semibold text-emerald-600">Rp {{ number_format($grossSales, 0, ',', '.') }}</p>
                <p class="mt-1 text-xs text-red-500">Komisi: Rp {{ number_format($commission, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    {{-- Transactions --}}
    <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white">
        <div class="border-b border-slate-100 px-6 py-4">
            <h3 class="font-semibold text-slate-900">Riwayat Transaksi</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                        <th class="px-4 py-3 font-semibold">Tanggal</th>
                        <th class="px-4 py-3 font-semibold">Kode</th>
                        <th class="px-4 py-3 font-semibold">Ternak</th>
                        <th class="px-4 py-3 font-semibold">Jumlah</th>
                        <th class="px-4 py-3 font-semibold">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($transactions as $trx)
                        <tr class="transition hover:bg-slate-50">
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $trx->placed_at?->format('d M Y') ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm font-semibold text-slate-900">{{ $trx->order_number }}</td>
                            <td class="px-4 py-3 text-sm text-slate-900">{{ $trx->product_name }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $trx->quantity }} ekor</td>
                            <td class="px-4 py-3 text-sm font-semibold text-emerald-600">Rp {{ number_format($trx->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center">
                                <i class="fa-solid fa-wallet text-5xl text-slate-200"></i>
                                <p class="mt-4 text-slate-500">Belum ada transaksi</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
