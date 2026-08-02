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
        <div class="rounded-lg border border-slate-200 bg-white p-5">
            <div class="flex items-center gap-3">
                <div class="rounded-lg bg-violet-100 p-3">
                    <i class="fa-solid fa-user-check text-violet-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-900">{{ $stats['activeCustomers'] }}</p>
                    <p class="text-sm text-slate-500">Aktif 30 Hari</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Search --}}
    <div class="flex items-center justify-between gap-4">
        <div class="relative flex-1 max-w-md">
            <input
                type="text"
                wire:model.live.debounce.300ms="searchQuery"
                placeholder="Cari pelanggan, email, telepon..."
                class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2 pl-10 pr-10 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
            />
            <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            @if($searchQuery)
                <button
                    wire:click="$set('searchQuery', '')"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                >
                    <i class="fa-solid fa-times-circle"></i>
                </button>
            @endif
        </div>
        <p class="text-sm text-slate-500">
            {{ $customers->total() }} pelanggan
        </p>
    </div>

    {{-- Customers Table --}}
    <div class="rounded-xl border border-slate-200 bg-white overflow-hidden shadow-sm">
        <table class="w-full text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-slate-900 cursor-pointer hover:bg-slate-100" wire:click="sortBy('name')">
                        <div class="flex items-center gap-1">
                            Pelanggan
                            @if($sortField === 'name')
                                <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-emerald-600"></i>
                            @endif
                        </div>
                    </th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-900 cursor-pointer hover:bg-slate-100" wire:click="sortBy('email')">
                        <div class="flex items-center gap-1">
                            Email
                            @if($sortField === 'email')
                                <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-emerald-600"></i>
                            @endif
                        </div>
                    </th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-900">Pesanan</th>
                    <th class="px-4 py-3 text-right font-semibold text-slate-900">Total Belanja</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-900 cursor-pointer hover:bg-slate-100" wire:click="sortBy('created_at')">
                        <div class="flex items-center gap-1">
                            Bergabung
                            @if($sortField === 'created_at')
                                <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-emerald-600"></i>
                            @endif
                        </div>
                    </th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-900">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($customers as $customer)
                    @php
                        $totalOrders = $customer->orders()->count();
                        $totalSpent = $customer->orders()->sum('total_amount');
                    @endphp
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex size-10 items-center justify-center rounded-full bg-emerald-100 text-sm font-bold text-emerald-700">
                                    {{ strtoupper(substr($customer->name, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="font-medium text-slate-900">{{ $customer->name }}</p>
                                    @if($customer->phone)
                                        <p class="text-xs text-slate-500">{{ $customer->phone }}</p>
                                    @endif
                                </div>
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
                        <td class="px-4 py-3 text-slate-500">
                            {{ $customer->created_at->format('d M Y') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button
                                wire:click="viewCustomer({{ $customer->id }})"
                                class="rounded-lg p-2 text-slate-400 hover:bg-emerald-100 hover:text-emerald-600 transition-colors"
                                title="Lihat Detail"
                            >
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center text-slate-400">
                                <i class="fa-solid fa-users text-4xl mb-3"></i>
                                <p>Belum ada pelanggan</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination --}}
        @if($customers->hasPages())
            <div class="flex items-center justify-between border-t border-slate-200 px-6 py-3">
                <p class="text-sm text-slate-500">
                    Menampilkan {{ $customers->firstItem() ?? 0 }} - {{ $customers->lastItem() ?? 0 }} dari {{ $customers->total() }}
                </p>
                {{ $customers->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Customer Detail Modal --}}
@if($selectedCustomer)
<template x-teleport="body">
<div x-show="$wire.showDetailModal" x-transition.opacity class="fixed inset-0 z-[99999]">
    <div class="absolute inset-0 bg-black/50" @click="$wire.closeDetailModal()"></div>
    <div class="absolute left-1/2 top-1/2 w-full max-w-2xl max-h-[90vh] -translate-x-1/2 -translate-y-1/2 rounded-xl bg-white shadow-xl overflow-hidden" @click.stop>
        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
            <div class="flex items-center gap-3">
                <div class="flex size-12 items-center justify-center rounded-full bg-emerald-100 text-lg font-bold text-emerald-700">
                    {{ strtoupper(substr($selectedCustomer->name, 0, 2)) }}
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">{{ $selectedCustomer->name }}</h3>
                    <p class="text-sm text-slate-500">{{ $selectedCustomer->email }}</p>
                </div>
            </div>
            <button @click="$wire.closeDetailModal()" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-times text-lg"></i>
            </button>
        </div>

        {{-- Body --}}
        <div class="px-6 py-4 max-h-[60vh] overflow-y-auto">
            {{-- Stats Grid --}}
            <div class="mb-6 grid grid-cols-3 gap-4">
                <div class="rounded-lg bg-slate-50 p-4 text-center">
                    <p class="text-2xl font-bold text-slate-900">{{ $selectedCustomer->orders()->count() }}</p>
                    <p class="text-xs text-slate-500">Total Pesanan</p>
                </div>
                <div class="rounded-lg bg-emerald-50 p-4 text-center">
                    <p class="text-xl font-bold text-emerald-700">Rp {{ number_format($selectedCustomer->orders()->sum('total_amount'), 0, ',', '.') }}</p>
                    <p class="text-xs text-slate-500">Total Belanja</p>
                </div>
                <div class="rounded-lg bg-slate-50 p-4 text-center">
                    <p class="text-lg font-bold text-slate-900">{{ $selectedCustomer->created_at->format('d M Y') }}</p>
                    <p class="text-xs text-slate-500">Member Sejak</p>
                </div>
            </div>

            {{-- Contact Info --}}
            @if($selectedCustomer->phone)
                <div class="mb-6">
                    <h4 class="mb-2 text-sm font-semibold text-slate-900">Informasi Kontak</h4>
                    <div class="flex items-center gap-2 rounded-lg border border-slate-200 p-3">
                        <i class="fa-solid fa-phone text-slate-400"></i>
                        <span class="text-slate-700">{{ $selectedCustomer->phone }}</span>
                    </div>
                </div>
            @endif

            {{-- Order History --}}
            <h4 class="mb-3 text-sm font-semibold text-slate-900">Riwayat Pesanan</h4>
            <div class="space-y-3">
                @forelse($selectedCustomer->orders->take(10) as $order)
                    <div class="flex items-center justify-between rounded-lg border border-slate-200 p-4">
                        <div>
                            <p class="font-medium text-slate-900">{{ $order->order_number }}</p>
                            <p class="text-xs text-slate-500">{{ $order->created_at->format('d M Y, H:i') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-slate-900">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $order->status_badge_class }}">
                                {{ $order->status_label }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="py-8 text-center text-sm text-slate-500">Belum ada pesanan</p>
                @endforelse
            </div>
        </div>

        {{-- Footer --}}
        <div class="flex justify-end border-t border-slate-200 px-6 py-4">
            <button @click="$wire.closeDetailModal()" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold hover:bg-slate-50">
                Tutup
            </button>
        </div>
    </div>
</div>
</template>
@endif
