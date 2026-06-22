<div wire:ignore class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-slate-500">Kelola data pelanggan</p>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-3">
        <div class="rounded-lg border border-slate-200 bg-white p-5">
            <div class="flex items-center gap-3">
                <div class="rounded-lg bg-emerald-100 p-3">
                    <i class="fa-solid fa-users text-emerald-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-900">{{ $stats['total'] }}</p>
                    <p class="text-sm text-slate-500">Total Pelanggan</p>
                </div>
            </div>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-5">
            <div class="flex items-center gap-3">
                <div class="rounded-lg bg-blue-100 p-3">
                    <i class="fa-solid fa-user-plus text-blue-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-900">{{ $stats['newThisMonth'] }}</p>
                    <p class="text-sm text-slate-500">Baru Bulan Ini</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Search --}}
    <div class="relative">
        <input
            type="text"
            wire:model.live.debounce.300ms="searchQuery"
            placeholder="Cari pelanggan..."
            class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2 pl-10 pr-4 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 lg:w-80"
        />
        <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="7" /><path d="m20 20-3.5-3.5" stroke-linecap="round" />
        </svg>
    </div>

    {{-- Customers Table --}}
    <div class="rounded-xl border border-slate-200 bg-white overflow-hidden shadow-sm">
        <table class="w-full text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-slate-900">Pelanggan</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-900">Email</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-900">Total Pesanan</th>
                    <th class="px-4 py-3 text-right font-semibold text-slate-900">Total Belanja</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-900">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($customers as $customer)
                    @php
                        $totalOrders = $customer->orders()->count();
                        $totalSpent = $customer->orders()->sum('total_amount');
                    @endphp
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex size-10 items-center justify-center rounded-full bg-emerald-100 text-sm font-bold text-emerald-700">
                                    {{ substr($customer->name, 0, 2) }}
                                </div>
                                <span class="font-medium text-slate-900">{{ $customer->name }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $customer->email }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">
                                {{ $totalOrders }} pesanan
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-slate-900">
                            Rp {{ number_format($totalSpent, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button wire:click="viewCustomer({{ $customer->id }})" class="rounded-lg p-2 text-slate-400 hover:bg-emerald-100 hover:text-emerald-600">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <i class="fa-solid fa-users text-4xl text-slate-300 mb-3"></i>
                            <p class="text-slate-500">Belum ada pelanggan</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Customer Detail Modal --}}
@if($selectedCustomer)
    <template x-teleport="body">
    <div x-show="$wire.showDetailModal" x-transition.opacity class="fixed inset-0 z-[99999]">
        <div class="absolute inset-0 bg-black/50" @click="$wire.closeDetailModal()"></div>
        <div class="absolute left-1/2 top-1/2 w-full max-w-2xl max-h-[90vh] -translate-x-1/2 -translate-y-1/2 rounded-xl bg-white shadow-xl overflow-hidden" @click.stop>
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">{{ $selectedCustomer->name }}</h3>
                    <p class="text-sm text-slate-500">{{ $selectedCustomer->email }}</p>
                </div>
                <button @click="$wire.closeDetailModal()" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
            <div class="px-6 py-4 max-h-[60vh] overflow-y-auto">
                <div class="mb-6 grid grid-cols-3 gap-4">
                    <div class="rounded-lg bg-slate-50 p-4 text-center">
                        <p class="text-2xl font-bold text-slate-900">{{ $selectedCustomer->orders()->count() }}</p>
                        <p class="text-xs text-slate-500">Total Pesanan</p>
                    </div>
                    <div class="rounded-lg bg-emerald-50 p-4 text-center">
                        <p class="text-2xl font-bold text-emerald-700">Rp {{ number_format($selectedCustomer->orders()->sum('total_amount'), 0, ',', '.') }}</p>
                        <p class="text-xs text-slate-500">Total Belanja</p>
                    </div>
                    <div class="rounded-lg bg-slate-50 p-4 text-center">
                        <p class="text-2xl font-bold text-slate-900">{{ $selectedCustomer->created_at->format('d M Y') }}</p>
                        <p class="text-xs text-slate-500">Member Sejak</p>
                    </div>
                </div>

                <h4 class="mb-3 font-semibold text-slate-900">Riwayat Pesanan</h4>
                <div class="space-y-3">
                    @forelse($selectedCustomer->orders->take(10) as $order)
                        <div class="flex items-center justify-between rounded-lg border border-slate-200 p-3">
                            <div>
                                <p class="font-medium text-slate-900">{{ $order->order_number }}</p>
                                <p class="text-xs text-slate-500">{{ $order->created_at->format('d M Y, H:i') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-slate-900">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                                @switch($order->status)
                                    @case('pending')
                                        <span class="text-xs text-amber-600">Pending</span>
                                        @break
                                    @case('processing')
                                        <span class="text-xs text-blue-600">Diproses</span>
                                        @break
                                    @case('shipped')
                                        <span class="text-xs text-violet-600">Dikirim</span>
                                        @break
                                    @case('delivered')
                                        <span class="text-xs text-green-600">Selesai</span>
                                        @break
                                    @case('cancelled')
                                        <span class="text-xs text-red-600">Batal</span>
                                        @break
                                @endswitch
                            </div>
                        </div>
                    @empty
                        <p class="py-4 text-center text-sm text-slate-500">Belum ada pesanan</p>
                    @endforelse
                </div>
            </div>
            <div class="flex justify-end border-t border-slate-200 px-6 py-4">
                <button @click="$wire.closeDetailModal()" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold">Tutup</button>
            </div>
        </div>
    </div>
@endif
</template>
