<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">
                Pesanan
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Kelola pesanan yang ditugaskan kepada Anda
            </p>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-500">Total Pesanan</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900">{{ $stats['total'] }}</p>
                </div>
                <div class="rounded-lg bg-slate-100 p-2.5">
                    <i class="fa-solid fa-inbox text-slate-600"></i>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-500">Menunggu</p>
                    <p class="mt-1 text-2xl font-bold text-amber-600">{{ $stats['pending'] }}</p>
                </div>
                <div class="rounded-lg bg-amber-100 p-2.5">
                    <i class="fa-solid fa-clock text-amber-600"></i>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-500">Diproses</p>
                    <p class="mt-1 text-2xl font-bold text-blue-600">{{ $stats['processing'] }}</p>
                </div>
                <div class="rounded-lg bg-blue-100 p-2.5">
                    <i class="fa-solid fa-gear text-blue-600"></i>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-500">Dikirim</p>
                    <p class="mt-1 text-2xl font-bold text-purple-600">{{ $stats['shipped'] }}</p>
                </div>
                <div class="rounded-lg bg-purple-100 p-2.5">
                    <i class="fa-solid fa-truck text-purple-600"></i>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-500">Selesai</p>
                    <p class="mt-1 text-2xl font-bold text-emerald-600">{{ $stats['completed'] }}</p>
                </div>
                <div class="rounded-lg bg-emerald-100 p-2.5">
                    <i class="fa-solid fa-check-circle text-emerald-600"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Search & Filter Bar --}}
    <div class="rounded-xl border border-slate-200 bg-white p-4">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end">
            {{-- Search --}}
            <div class="flex-1">
                <label class="mb-1.5 block text-xs font-medium text-slate-600">Cari</label>
                <div class="relative">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="searchQuery"
                        placeholder="Invoice, nama, telepon, produk..."
                        class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2 pl-10 pr-4 text-sm placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
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
            </div>

            {{-- Status Filter --}}
            <div class="w-full lg:w-48">
                <label class="mb-1.5 block text-xs font-medium text-slate-600">Status</label>
                <select
                    wire:model.live="statusFilter"
                    class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2 px-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                >
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Date From --}}
            <div class="w-full lg:w-40">
                <label class="mb-1.5 block text-xs font-medium text-slate-600">Dari Tanggal</label>
                <input
                    type="date"
                    wire:model.live="dateFrom"
                    class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2 px-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                />
            </div>

            {{-- Date To --}}
            <div class="w-full lg:w-40">
                <label class="mb-1.5 block text-xs font-medium text-slate-600">Sampai Tanggal</label>
                <input
                    type="date"
                    wire:model.live="dateTo"
                    class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2 px-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                />
            </div>

            {{-- Clear Filters --}}
            <button
                wire:click="clearFilters"
                class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-50"
            >
                <i class="fa-solid fa-rotate-left mr-1.5"></i>
                Reset
            </button>
        </div>
    </div>

    {{-- Orders Table --}}
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-slate-900 whitespace-nowrap">
                            Invoice
                        </th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-900 whitespace-nowrap">
                            Pelanggan
                        </th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-900 whitespace-nowrap">
                            Produk
                        </th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-900 whitespace-nowrap">
                            Pembayaran
                        </th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-900 whitespace-nowrap">
                            Pesanan
                        </th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-900 whitespace-nowrap">
                            Pengiriman
                        </th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-900 whitespace-nowrap">
                            Tanggal
                        </th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-900 whitespace-nowrap">
                            Ditugaskan Oleh
                        </th>
                        <th class="px-4 py-3 text-right font-semibold text-slate-900 whitespace-nowrap">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $order)
                        <tr class="transition-colors hover:bg-slate-50">
                            {{-- Invoice --}}
                            <td class="px-4 py-3">
                                <div>
                                    <a href="{{ route('staff.orders.detail', $order->id) }}"
                                       class="font-semibold text-emerald-600 hover:text-emerald-700 hover:underline">
                                        {{ $order->order_number }}
                                    </a>
                                    <p class="text-xs text-slate-400 mt-0.5">
                                        @if($order->order_type === 'cart')
                                            <span class="inline-flex items-center">
                                                <i class="fa-solid fa-shopping-cart mr-1"></i>Pembelian
                                            </span>
                                        @else
                                            <span class="inline-flex items-center">
                                                <i class="fa-solid fa-calendar-check mr-1"></i>Booking
                                            </span>
                                        @endif
                                    </p>
                                </div>
                            </td>

                            {{-- Customer --}}
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="flex size-8 items-center justify-center rounded-full bg-slate-100 text-xs font-semibold text-slate-600">
                                        {{ strtoupper(substr($order->customer_name ?? 'G', 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-slate-900">{{ $order->customer_name ?? 'Guest' }}</p>
                                        <p class="text-xs text-slate-400">{{ $order->customer_phone ?? '-' }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Product Summary --}}
                            <td class="px-4 py-3">
                                <p class="text-slate-700 max-w-[150px] truncate" title="{{ $this->getProductSummary($order) }}">
                                    {{ $this->getProductSummary($order) }}
                                </p>
                                <p class="text-xs text-slate-400">{{ $order->total_items }} item</p>
                            </td>

                            {{-- Payment Status --}}
                            <td class="px-4 py-3 text-center">
                                @php
                                    $paymentStatus = $order->payment_status_label ?? $order->payment_status_enum?->label() ?? '-';
                                    $paymentBadgeClass = match($order->payment_status_enum?->value) {
                                        'paid', 'success' => 'bg-green-50 text-green-700',
                                        'pending' => 'bg-amber-50 text-amber-700',
                                        'failed', 'expired' => 'bg-red-50 text-red-700',
                                        default => 'bg-slate-50 text-slate-600',
                                    };
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $paymentBadgeClass }}">
                                    {{ $paymentStatus }}
                                </span>
                            </td>

                            {{-- Order Status --}}
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $this->getStatusBadgeClass($order->status) }}">
                                    {{ $this->getStatusLabel($order->status) }}
                                </span>
                            </td>

                            {{-- Shipping Status --}}
                            <td class="px-4 py-3 text-center">
                                @php
                                    $shippingStatus = $order->shipment_status_label ?? '-';
                                    $shippingBadgeClass = match($order->shipment_status_enum?->value) {
                                        'pending' => 'bg-slate-50 text-slate-600',
                                        'processing' => 'bg-blue-50 text-blue-700',
                                        'shipped' => 'bg-purple-50 text-purple-700',
                                        'delivered' => 'bg-emerald-50 text-emerald-700',
                                        default => 'bg-slate-50 text-slate-600',
                                    };
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $shippingBadgeClass }}">
                                    {{ $shippingStatus ?: '-' }}
                                </span>
                            </td>

                            {{-- Order Date --}}
                            <td class="px-4 py-3 whitespace-nowrap">
                                <p class="text-slate-700">{{ $order->created_at->format('d M Y') }}</p>
                                <p class="text-xs text-slate-400">{{ $order->created_at->format('H:i') }}</p>
                            </td>

                            {{-- Assigned By --}}
                            <td class="px-4 py-3">
                                @if($order->assignedByUser)
                                    <div class="flex items-center gap-2">
                                        <div class="flex size-6 items-center justify-center rounded-full bg-emerald-100 text-[10px] font-bold text-emerald-700">
                                            {{ strtoupper(substr($order->assignedByUser->name, 0, 1)) }}
                                        </div>
                                        <span class="text-sm text-slate-600">{{ $order->assignedByUser->name }}</span>
                                    </div>
                                    @if($order->assigned_at)
                                        <p class="text-xs text-slate-400 mt-0.5">
                                            {{ $order->assigned_at->format('d M Y') }}
                                        </p>
                                    @endif
                                @else
                                    <span class="text-sm text-slate-400">-</span>
                                @endif
                            </td>

                            {{-- Action --}}
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    {{-- Complete Delivery Button (only for shipped orders) --}}
                                    @if($order->order_type === 'cart' && $order->status === 'shipped' && $order->isAssignedTo(auth()->id()))
                                        <button
                                            wire:click="$dispatch('open-complete-delivery', { order: {{ $order->id }} })"
                                            class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white transition-colors hover:bg-emerald-700"
                                            title="Selesaikan Pengiriman"
                                        >
                                            <i class="fa-solid fa-check-circle text-[10px]"></i>
                                            Selesai
                                        </button>
                                    @endif

                                    {{-- Start Delivery Button (only for cart orders ready for delivery) --}}
                                    @if($order->order_type === 'cart' && in_array($order->status, ['paid', 'approved', 'processing', 'ready_to_ship']))
                                        <button
                                            wire:click="$dispatch('open-start-delivery', { order: {{ $order->id }} })"
                                            class="inline-flex items-center gap-1.5 rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-medium text-white transition-colors hover:bg-violet-700"
                                            title="Mulai Kirim"
                                        >
                                            <i class="fa-solid fa-truck text-[10px]"></i>
                                            Kirim
                                        </button>
                                    @endif

                                    <a href="{{ route('staff.orders.detail', $order->id) }}"
                                       class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-700 transition-colors hover:bg-emerald-100">
                                        <i class="fa-solid fa-eye"></i>
                                        Detail
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12">
                                <div class="flex flex-col items-center justify-center text-center">
                                    <div class="rounded-full bg-slate-100 p-4 text-slate-300 mb-4">
                                        <i class="fa-solid fa-inbox text-4xl"></i>
                                    </div>
                                    <h3 class="text-lg font-semibold text-slate-700 mb-1">Tidak Ada Pesanan</h3>
                                    <p class="text-sm text-slate-500 mb-4">
                                        @if($searchQuery || $statusFilter !== 'all' || $dateFrom || $dateTo)
                                            Pesanan tidak ditemukan dengan filter yang diberikan.
                                        @else
                                            Belum ada pesanan yang ditugaskan kepada Anda.
                                        @endif
                                    </p>
                                    @if($searchQuery || $statusFilter !== 'all' || $dateFrom || $dateTo)
                                        <button
                                            wire:click="clearFilters"
                                            class="inline-flex items-center gap-2 rounded-lg bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700 transition-colors hover:bg-emerald-100"
                                        >
                                            <i class="fa-solid fa-rotate-left"></i>
                                            Reset Filter
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($orders->hasPages())
            <div class="flex items-center justify-between border-t border-slate-200 px-6 py-4">
                <p class="text-sm text-slate-500">
                    Menampilkan {{ $orders->firstItem() ?? 0 }} - {{ $orders->lastItem() ?? 0 }} dari {{ $orders->total() }} pesanan
                </p>
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
