<div class="space-y-6">
    {{-- Header with Bulk Actions --}}
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-slate-500">Kelola semua pesanan dan inquiry dari pelanggan</p>
        </div>
        @if(count($selectedOrders) > 0)
            <div class="flex items-center gap-3 rounded-lg bg-emerald-50 px-4 py-2">
                <span class="text-sm font-medium text-emerald-700">{{ count($selectedOrders) }} dipilih</span>
                <div class="relative" x-data="{ open: false }">
                    <button
                        @click="open = !open"
                        class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-emerald-700"
                    >
                        Update Status <i class="fa-solid fa-chevron-down ml-1"></i>
                    </button>
                    <div
                        x-show="open"
                        @click.away="open = false"
                        @click="open = false"
                        class="absolute right-0 top-full z-20 mt-1 w-44 rounded-lg border border-slate-200 bg-white py-1 shadow-lg"
                    >
                        {{-- Admin can only bulk update to these statuses --}}
                        <button
                            wire:click="bulkUpdateStatus('processing')"
                            class="w-full px-4 py-2 text-left text-xs hover:bg-slate-50 text-slate-700"
                        >
                            <i class="fa-solid fa-gear mr-2 text-blue-500"></i>Proses
                        </button>
                        <button
                            wire:click="bulkUpdateStatus('approved')"
                            class="w-full px-4 py-2 text-left text-xs hover:bg-slate-50 text-slate-700"
                        >
                            <i class="fa-solid fa-check mr-2 text-green-500"></i>Setujui
                        </button>
                        <button
                            wire:click="bulkUpdateStatus('rejected')"
                            class="w-full px-4 py-2 text-left text-xs hover:bg-red-50 text-red-600"
                        >
                            <i class="fa-solid fa-times mr-2"></i>Tolak
                        </button>
                        <hr class="my-1 border-slate-100">
                        <button
                            wire:click="bulkUpdateStatus('cancelled')"
                            class="w-full px-4 py-2 text-left text-xs hover:bg-red-50 text-red-600"
                        >
                            <i class="fa-solid fa-ban mr-2"></i>Batalkan
                        </button>
                    </div>
                </div>
                <button
                    wire:click="clearSelection"
                    class="ml-2 text-sm text-slate-500 hover:text-slate-700"
                >
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
        @endif
    </div>

    {{-- Stats Cards (Clickable) --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-7">
        <button
            wire:click="setStatusFilter('all'); setTab('all')"
            class="rounded-lg border p-4 text-left transition @if($activeTab === 'all' && $statusFilter === 'all') border-emerald-500 bg-emerald-50 @else border-slate-200 bg-white hover:border-slate-300 @endif"
        >
            <div class="flex items-center gap-3">
                <div class="rounded-lg bg-slate-100 p-2">
                    <i class="fa-solid fa-receipt text-slate-600"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Total</p>
                    <p class="text-xl font-bold text-slate-900">{{ $stats['total'] }}</p>
                </div>
            </div>
        </button>

        <button
            wire:click="setTab('cart'); setStatusFilter('all')"
            class="rounded-lg border p-4 text-left transition @if($activeTab === 'cart') border-emerald-500 bg-emerald-50 @else border-slate-200 bg-white hover:border-slate-300 @endif"
        >
            <div class="flex items-center gap-3">
                <div class="rounded-lg bg-emerald-100 p-2">
                    <i class="fa-solid fa-shopping-cart text-emerald-600"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Pembelian</p>
                    <p class="text-xl font-bold text-slate-900">{{ $stats['cart'] }}</p>
                </div>
            </div>
        </button>

        <button
            wire:click="setTab('inquiry'); setStatusFilter('all')"
            class="rounded-lg border p-4 text-left transition @if($activeTab === 'inquiry') border-amber-500 bg-amber-50 @else border-slate-200 bg-white hover:border-slate-300 @endif"
        >
            <div class="flex items-center gap-3">
                <div class="rounded-lg bg-amber-100 p-2">
                    <i class="fa-solid fa-circle-question text-amber-600"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Inquiry</p>
                    <p class="text-xl font-bold text-slate-900">{{ $stats['inquiry'] }}</p>
                </div>
            </div>
        </button>

        <button
            wire:click="setStatusFilter('pending')"
            class="rounded-lg border p-4 text-left transition @if($statusFilter === 'pending') border-amber-500 bg-amber-50 @else border-slate-200 bg-white hover:border-slate-300 @endif"
        >
            <div class="flex items-center gap-3">
                <div class="rounded-lg bg-amber-100 p-2">
                    <i class="fa-solid fa-clock text-amber-600"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Menunggu</p>
                    <p class="text-xl font-bold text-amber-600">{{ $stats['pending'] + ($stats['approved'] ?? 0) }}</p>
                </div>
            </div>
        </button>

        <button
            wire:click="setStatusFilter('processing')"
            class="rounded-lg border p-4 text-left transition @if($statusFilter === 'processing') border-blue-500 bg-blue-50 @else border-slate-200 bg-white hover:border-slate-300 @endif"
        >
            <div class="flex items-center gap-3">
                <div class="rounded-lg bg-blue-100 p-2">
                    <i class="fa-solid fa-gear text-blue-600"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Diproses</p>
                    <p class="text-xl font-bold text-blue-600">{{ $stats['processing'] }}</p>
                </div>
            </div>
        </button>

        <button
            wire:click="setStatusFilter('shipped')"
            class="rounded-lg border p-4 text-left transition @if($statusFilter === 'shipped') border-violet-500 bg-violet-50 @else border-slate-200 bg-white hover:border-slate-300 @endif"
        >
            <div class="flex items-center gap-3">
                <div class="rounded-lg bg-violet-100 p-2">
                    <i class="fa-solid fa-truck text-violet-600"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Dikirim</p>
                    <p class="text-xl font-bold text-violet-600">{{ $stats['shipped'] }}</p>
                </div>
            </div>
        </button>

        <button
            wire:click="setStatusFilter('delivered')"
            class="rounded-lg border p-4 text-left transition @if($statusFilter === 'delivered') border-emerald-500 bg-emerald-50 @else border-slate-200 bg-white hover:border-slate-300 @endif"
        >
            <div class="flex items-center gap-3">
                <div class="rounded-lg bg-emerald-100 p-2">
                    <i class="fa-solid fa-check-circle text-emerald-600"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Selesai</p>
                    <p class="text-xl font-bold text-emerald-600">{{ $stats['delivered'] + $stats['completed'] }}</p>
                </div>
            </div>
        </button>
    </div>

    {{-- Filters & Search --}}
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        {{-- Type Tabs --}}
        <div class="flex gap-1 rounded-lg bg-slate-100 p-1">
            <button
                wire:click="setTab('all')"
                class="rounded-md px-4 py-2 text-sm font-medium transition-all @if($activeTab === 'all') bg-white text-emerald-700 shadow-sm @else text-slate-600 hover:text-slate-900 @endif"
            >
                Semua
            </button>
            <button
                wire:click="setTab('cart')"
                class="rounded-md px-4 py-2 text-sm font-medium transition-all @if($activeTab === 'cart') bg-white text-emerald-700 shadow-sm @else text-slate-600 hover:text-slate-900 @endif"
            >
                <i class="fa-solid fa-shopping-cart mr-1"></i>Pembelian
            </button>
            <button
                wire:click="setTab('inquiry')"
                class="rounded-md px-4 py-2 text-sm font-medium transition-all @if($activeTab === 'inquiry') bg-white text-amber-700 shadow-sm @else text-slate-600 hover:text-slate-900 @endif"
            >
                <i class="fa-solid fa-circle-question mr-1"></i>Inquiry
            </button>
        </div>

        {{-- Search with clear button --}}
        <div class="relative w-full lg:w-80">
            <input
                type="text"
                wire:model.live.debounce.300ms="searchQuery"
                placeholder="Cari pesanan, pelanggan, telepon..."
                class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2 pl-10 pr-10 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
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

    {{-- Status Filter Pills --}}
    <div class="flex flex-wrap gap-2">
        @foreach($statusOptions as $key => $label)
            <button
                wire:click="setStatusFilter('{{ $key }}')"
                class="rounded-full px-3 py-1.5 text-xs font-medium transition @if($statusFilter === $key) bg-emerald-600 text-white shadow-sm @else bg-slate-100 text-slate-600 hover:bg-slate-200 @endif"
            >
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Orders Table --}}
    <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50">
                        <th class="w-12 px-4 py-3">
                            <input
                                type="checkbox"
                                wire:model.live="selectAll"
                                class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                            />
                        </th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-900 cursor-pointer hover:bg-slate-100" wire:click="sortBy('order_number')">
                            <div class="flex items-center gap-1">
                                Pesanan
                                @if($sortField === 'order_number')
                                    <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-emerald-600"></i>
                                @endif
                            </div>
                        </th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-900 cursor-pointer hover:bg-slate-100" wire:click="sortBy('customer_name')">
                            <div class="flex items-center gap-1">
                                Pelanggan
                                @if($sortField === 'customer_name')
                                    <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-emerald-600"></i>
                                @endif
                            </div>
                        </th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-900">Tipe</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-900">Jumlah</th>
                        <th class="px-4 py-3 text-right font-semibold text-slate-900 cursor-pointer hover:bg-slate-100" wire:click="sortBy('total_amount')">
                            <div class="flex items-center justify-end gap-1">
                                Total
                                @if($sortField === 'total_amount')
                                    <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-emerald-600"></i>
                                @endif
                            </div>
                        </th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-900 cursor-pointer hover:bg-slate-100" wire:click="sortBy('status')">
                            <div class="flex items-center justify-center gap-1">
                                Status
                                @if($sortField === 'status')
                                    <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-emerald-600"></i>
                                @endif
                            </div>
                        </th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-900">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $order)
                        @php
                            $isSelected = in_array((string) $order->id, $selectedOrders);
                        @endphp
                        <tr class="transition-colors hover:bg-slate-50 {{ $isSelected ? 'bg-emerald-50' : '' }}">
                            <td class="px-4 py-3">
                                <input
                                    type="checkbox"
                                    value="{{ $order->id }}"
                                    wire:model.live="selectedOrders"
                                    class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                                />
                            </td>
                            <td class="px-4 py-3">
                                <div>
                                    <p class="font-medium text-slate-900">{{ $order->order_number }}</p>
                                    <p class="text-xs text-slate-500">{{ $order->created_at->format('d M Y, H:i') }}</p>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div>
                                    <p class="font-medium text-slate-900">{{ $order->customer_name }}</p>
                                    <p class="text-xs text-slate-500">{{ $order->customer_phone }}</p>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($order->order_type === 'cart')
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-700">
                                        <i class="fa-solid fa-shopping-cart mr-1"></i>Pembelian
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-700">
                                        <i class="fa-solid fa-circle-question mr-1"></i>Inquiry
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                {{ $order->total_items }} item
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-slate-900">
                                Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $order->status_badge_class }}">
                                    {{ $order->status_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button
                                        wire:click="viewOrder({{ $order->id }})"
                                        class="rounded-lg p-2 text-slate-600 transition-all hover:bg-slate-100"
                                        title="Detail"
                                    >
                                        <i class="fa-solid fa-eye"></i>
                                    </button>

                                    @php
                                        $validNextStatuses = \App\Services\OrderStateMachine::getValidNextStatuses($order);
                                        $canProcess = in_array('processing', $validNextStatuses);
                                        $canApprove = in_array('approved', $validNextStatuses);
                                        $canReject = in_array('rejected', $validNextStatuses);
                                        $canCancel = in_array('cancelled', $validNextStatuses);
                                    @endphp

                                    {{-- Only show dropdown if there are available actions for Admin --}}
                                    @if($canProcess || $canApprove || $canReject || $canCancel)
                                        <div class="relative" x-data="{ open: false }">
                                            <button
                                                @click="open = !open"
                                                class="rounded-lg p-2 text-slate-600 transition-all hover:bg-slate-100"
                                            >
                                                <i class="fa-solid fa-ellipsis-vertical"></i>
                                            </button>
                                            <div
                                                x-show="open"
                                                @click.away="open = false"
                                                @click="open = false"
                                                class="absolute right-0 top-full z-10 mt-1 w-48 rounded-lg border border-slate-200 bg-white py-1 shadow-lg"
                                            >
                                                {{-- Assign Staff (only for cart orders) --}}
                                                @if($order->order_type === 'cart' && !$order->assigned_staff_id)
                                                    <button
                                                        wire:click="openStaffSelection({{ $order->id }})"
                                                        class="w-full px-4 py-2 text-left text-xs hover:bg-emerald-50 text-emerald-700"
                                                    >
                                                        <i class="fa-solid fa-user-plus mr-2 text-emerald-500"></i>Tugaskan Staff
                                                    </button>
                                                @endif

                                                {{-- Reassign Staff (if already assigned) --}}
                                                @if($order->order_type === 'cart' && $order->assigned_staff_id)
                                                    <button
                                                        wire:click="openStaffSelection({{ $order->id }})"
                                                        class="w-full px-4 py-2 text-left text-xs hover:bg-emerald-50 text-emerald-700"
                                                    >
                                                        <i class="fa-solid fa-user-pen mr-2 text-emerald-500"></i>Ganti Staff
                                                    </button>
                                                @endif

                                                @if($canProcess || $canApprove || $canReject || $canCancel)
                                                    <hr class="my-1 border-slate-100">
                                                @endif

                                                {{-- Process Order (Admin can do this) --}}
                                                @if($canProcess)
                                                    <button
                                                        wire:click="updateStatus({{ $order->id }}, 'processing')"
                                                        class="w-full px-4 py-2 text-left text-xs hover:bg-slate-50 text-slate-700"
                                                    >
                                                        <i class="fa-solid fa-gear mr-2 text-blue-500"></i>Proses
                                                    </button>
                                                @endif

                                                {{-- Approve Booking (Admin can do this for inquiries) --}}
                                                @if($canApprove)
                                                    <button
                                                        wire:click="updateStatus({{ $order->id }}, 'approved')"
                                                        class="w-full px-4 py-2 text-left text-xs hover:bg-slate-50 text-slate-700"
                                                    >
                                                        <i class="fa-solid fa-check mr-2 text-green-500"></i>Setujui
                                                    </button>
                                                @endif

                                                {{-- Reject Booking (Admin can do this for inquiries) --}}
                                                @if($canReject)
                                                    <button
                                                        wire:click="updateStatus({{ $order->id }}, 'rejected')"
                                                        class="w-full px-4 py-2 text-left text-xs hover:bg-red-50 text-red-600"
                                                    >
                                                        <i class="fa-solid fa-times mr-2"></i>Tolak
                                                    </button>
                                                @endif

                                                {{-- Cancel Order (Admin can do this for valid statuses) --}}
                                                @if($canCancel)
                                                    <hr class="my-1 border-slate-100">
                                                    <button
                                                        wire:click="updateStatus({{ $order->id }}, 'cancelled')"
                                                        class="w-full px-4 py-2 text-left text-xs hover:bg-red-50 text-red-600"
                                                    >
                                                        <i class="fa-solid fa-ban mr-2"></i>Batalkan
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center text-slate-400">
                                    <i class="fa-solid fa-inbox text-4xl mb-3"></i>
                                    <p>Belum ada pesanan</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($orders->hasPages())
            <div class="flex items-center justify-between border-t border-slate-200 px-6 py-3">
                <p class="text-sm text-slate-500">
                    Menampilkan {{ $orders->firstItem() ?? 0 }} - {{ $orders->lastItem() ?? 0 }} dari {{ $orders->total() }} pesanan
                </p>
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Order Detail Modal --}}
@if($selectedOrder)
<template x-teleport="body">
<div x-show="$wire.showDetailModal" x-transition.opacity.duration.300ms class="fixed inset-0 z-[99999]">
    <div class="absolute inset-0 bg-black/50" @click="$wire.closeDetailModal()" @keydown.escape.window="$wire.closeDetailModal()"></div>
    <div class="absolute left-1/2 top-1/2 w-full max-w-2xl max-h-[90vh] -translate-x-1/2 -translate-y-1/2 rounded-xl bg-white shadow-xl overflow-hidden" @click.stop>
        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">{{ $selectedOrder->order_number }}</h3>
                <p class="text-sm text-slate-500">{{ $selectedOrder->created_at->format('d M Y, H:i') }}</p>
            </div>
            <button @click="$wire.closeDetailModal()" class="text-slate-400 transition hover:text-slate-600">
                <i class="fa-solid fa-times text-lg"></i>
            </button>
        </div>

        {{-- Body --}}
        <div class="px-6 py-4 max-h-[60vh] overflow-y-auto space-y-6">
            {{-- Status & Type Badges --}}
            <div class="flex items-center gap-3">
                @if($selectedOrder->order_type === 'cart')
                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-sm font-medium text-emerald-700">
                        <i class="fa-solid fa-shopping-cart mr-1"></i>Pembelian
                    </span>
                @else
                    <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-sm font-medium text-amber-700">
                        <i class="fa-solid fa-circle-question mr-1"></i>Inquiry
                    </span>
                @endif
                <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium {{ $selectedOrder->status_badge_class }}">
                    {{ $selectedOrder->status_label }}
                </span>
            </div>

            {{-- Customer Info --}}
            <div class="rounded-lg bg-slate-50 p-4">
                <h4 class="mb-3 font-semibold text-slate-900">Informasi Pelanggan</h4>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-slate-500">Nama</p>
                        <p class="font-medium text-slate-900">{{ $selectedOrder->customer_name }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500">Telepon</p>
                        <p class="font-medium text-slate-900">{{ $selectedOrder->customer_phone }}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-slate-500">Alamat</p>
                        <p class="font-medium text-slate-900">{{ $selectedOrder->customer_address }}</p>
                    </div>
                </div>
            </div>

            {{-- Order Items --}}
            <div>
                <h4 class="mb-3 font-semibold text-slate-900">Item Pesanan</h4>
                <div class="divide-y divide-slate-100">
                    @forelse($selectedOrder->items as $item)
                        <div class="flex items-center justify-between py-3">
                            <div class="flex items-center gap-3">
                                @if($item->product_image)
                                    <img src="{{ asset('storage/' . $item->product_image) }}" alt="" class="size-12 rounded-lg object-cover" />
                                @else
                                    <div class="size-12 rounded-lg bg-slate-200"></div>
                                @endif
                                <div>
                                    <p class="font-medium text-slate-900">{{ $item->product_name }}</p>
                                    <p class="text-xs text-slate-500">{{ $item->quantity }} x Rp {{ number_format($item->unit_price, 0, ',', '.') }}</p>
                                </div>
                            </div>
                            <p class="font-semibold text-slate-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                        </div>
                    @empty
                        <p class="py-4 text-center text-sm text-slate-500">Tidak ada item</p>
                    @endforelse
                </div>
            </div>

            {{-- Summary --}}
            <div class="rounded-lg bg-emerald-50 p-4">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-600">Total ({{ $selectedOrder->total_items }} item)</span>
                    <span class="text-xl font-bold text-emerald-700">Rp {{ number_format($selectedOrder->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>

            {{-- Notes & Extra Info --}}
            @if($selectedOrder->notes)
                <div>
                    <h4 class="mb-2 font-semibold text-slate-900">Catatan</h4>
                    <p class="text-sm text-slate-600">{{ $selectedOrder->notes }}</p>
                </div>
            @endif

            @if($selectedOrder->order_type === 'inquiry')
                @if($selectedOrder->preferred_delivery_date)
                    <div>
                        <h4 class="mb-2 font-semibold text-slate-900">Tanggal Pengiriman Yang Diinginkan</h4>
                        <p class="text-sm text-slate-600">{{ \Carbon\Carbon::parse($selectedOrder->preferred_delivery_date)->format('d M Y') }}</p>
                    </div>
                @endif

                @if($selectedOrder->pickup_location)
                    <div>
                        <h4 class="mb-2 font-semibold text-slate-900">Lokasi Pengambilan</h4>
                        <p class="text-sm text-slate-600">{{ $selectedOrder->pickup_location }}</p>
                    </div>
                @endif
            @endif

            {{-- Timeline --}}
            <div>
                <h4 class="mb-3 font-semibold text-slate-900">Riwayat Pesanan</h4>
                <x-order-timeline :order="$selectedOrder" />
            </div>

            {{-- Delivery Proof Section (for delivered orders) --}}
            @if($selectedOrder->status === 'delivered' && ($selectedOrder->receiver_name || $selectedOrder->delivery_photo))
                <div class="rounded-lg bg-emerald-50 p-4">
                    <h4 class="mb-3 font-semibold text-emerald-900 flex items-center gap-2">
                        <i class="fa-solid fa-clipboard-check text-emerald-600"></i>
                        Bukti Pengiriman
                    </h4>
                    <div class="space-y-3">
                        @if($selectedOrder->delivery_photo)
                            <div>
                                <p class="text-xs text-emerald-600 mb-1">Foto Bukti</p>
                                <a href="{{ asset('storage/' . $selectedOrder->delivery_photo) }}"
                                   target="_blank"
                                   class="block overflow-hidden rounded-lg border border-emerald-200 hover:border-emerald-300 transition-colors">
                                    <img src="{{ asset('storage/' . $selectedOrder->delivery_photo) }}"
                                         alt="Bukti Pengiriman"
                                         class="max-h-32 w-full object-cover" />
                                </a>
                            </div>
                        @endif

                        @if($selectedOrder->receiver_name)
                            <div>
                                <p class="text-xs text-emerald-600">Nama Penerima</p>
                                <p class="font-medium text-emerald-900">{{ $selectedOrder->receiver_name }}</p>
                            </div>
                        @endif

                        @if($selectedOrder->delivery_completed_at)
                            <div>
                                <p class="text-xs text-emerald-600">Waktu Penyelesaian</p>
                                <p class="font-medium text-emerald-900">
                                    {{ \Carbon\Carbon::parse($selectedOrder->delivery_completed_at)->format('d M Y, H:i') }}
                                </p>
                            </div>
                        @endif

                        @if($selectedOrder->delivery_note)
                            <div>
                                <p class="text-xs text-emerald-600">Catatan</p>
                                <p class="text-sm text-emerald-800 whitespace-pre-line">{{ $selectedOrder->delivery_note }}</p>
                            </div>
                        @endif

                        @if($selectedOrder->delivery_latitude && $selectedOrder->delivery_longitude)
                            <div>
                                <p class="text-xs text-emerald-600 mb-1">Lokasi GPS</p>
                                <a href="https://www.google.com/maps?q={{ $selectedOrder->delivery_latitude }},{{ $selectedOrder->delivery_longitude }}"
                                   target="_blank"
                                   class="inline-flex items-center gap-1 text-xs font-medium text-emerald-700 hover:text-emerald-800">
                                    <i class="fa-solid fa-map-marker-alt"></i>
                                    Lihat di Google Maps
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Completion Section (for completed orders) --}}
            @if($selectedOrder->status === 'completed')
                <div class="rounded-lg bg-indigo-50 p-4 border border-indigo-100">
                    <h4 class="mb-3 font-semibold text-indigo-900 flex items-center gap-2">
                        <i class="fa-solid fa-flag-checkered text-indigo-600"></i>
                        Informasi Penyelesaian
                    </h4>
                    <div class="space-y-3">
                        @if($selectedOrder->completed_at)
                            <div>
                                <p class="text-xs text-indigo-600">Waktu Penyelesaian</p>
                                <p class="font-medium text-indigo-900">
                                    {{ \Carbon\Carbon::parse($selectedOrder->completed_at)->format('d M Y, H:i') }}
                                </p>
                            </div>
                        @endif

                        <div>
                            <p class="text-xs text-indigo-600">Metode Penyelesaian</p>
                            <p class="font-medium text-indigo-900 flex items-center gap-2">
                                @if($selectedOrder->completed_automatically)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700">
                                        <i class="fa-solid fa-robot"></i>
                                        Otomatis
                                    </span>
                                @elseif($selectedOrder->completed_by_customer)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700">
                                        <i class="fa-solid fa-user"></i>
                                        Pelanggan
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700">
                                        <i class="fa-solid fa-hand"></i>
                                        Manual
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-end gap-3 border-t border-slate-200 px-6 py-4">
            <button @click="$wire.closeDetailModal()" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition-all hover:bg-slate-50">
                Tutup
            </button>
        </div>
    </div>
</div>
</template>
@endif

{{-- Staff Selection Modal --}}
@if($showStaffSelection && $orderForAssignment)
<template x-teleport="body">
<div x-show="$wire.showStaffSelection" x-transition.opacity.duration.300ms class="fixed inset-0 z-[99999]">
    <div class="absolute inset-0 bg-black/50" @click="$wire.closeStaffSelection()" @keydown.escape.window="$wire.closeStaffSelection()"></div>

    <div class="absolute left-1/2 top-1/2 w-full max-w-lg -translate-x-1/2 -translate-y-1/2 rounded-xl bg-white shadow-xl"
         @click.stop>
        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
            <div>
                <h3 class="text-lg font-bold text-slate-900">
                    <i class="fa-solid fa-user-plus mr-2 text-emerald-600"></i>
                    Tugaskan Staff
                </h3>
                <p class="mt-1 text-sm text-slate-500">
                    Pesanan: <span class="font-medium text-slate-700">{{ $orderForAssignment->order_number }}</span>
                </p>
            </div>
            <button
                @click="$wire.closeStaffSelection()"
                class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors"
            >
                <i class="fa-solid fa-times text-lg"></i>
            </button>
        </div>

        {{-- Current Assignment Info --}}
        @if($orderForAssignment->assigned_staff_id)
            <div class="border-b border-slate-200 bg-amber-50 px-6 py-3">
                <div class="flex items-center gap-2 text-sm text-amber-800">
                    <i class="fa-solid fa-info-circle"></i>
                    <span>Pesanan ini saat ini ditugaskan ke <strong>{{ $orderForAssignment->assignedStaff?->name ?? 'Staff' }}</strong>. Memilih staff baru akan mengganti penugasan.</span>
                </div>
            </div>
        @endif

        {{-- Search --}}
        <div class="border-b border-slate-200 px-6 py-4">
            <div class="relative">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="staffSearchQuery"
                    placeholder="Cari staff berdasarkan nama..."
                    class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                />
                <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            </div>
        </div>

        {{-- Staff List --}}
        <div class="max-h-80 overflow-y-auto p-4">
            @php
                $availableStaff = \App\Models\User::role('staf')
                    ->when($staffSearchQuery ?? '', function ($q, $search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    })
                    ->get()
                    ->map(function ($staff) {
                        $activeOrders = \App\Models\Order::where('assigned_staff_id', $staff->id)
                            ->whereNotIn('status', ['completed', 'cancelled', 'rejected'])
                            ->count();
                        return [
                            'id' => $staff->id,
                            'name' => $staff->name,
                            'phone' => $staff->phone ?? '-',
                            'active_orders' => $activeOrders,
                            'status' => $activeOrders >= 5 ? 'busy' : 'available',
                            'initials' => $staff->initials(),
                        ];
                    });
            @endphp

            @if($availableStaff->isEmpty())
                <div class="py-8 text-center">
                    <div class="mb-3 inline-block rounded-full bg-slate-100 p-4 text-slate-300">
                        <i class="fa-solid fa-users-slash text-3xl"></i>
                    </div>
                    <p class="text-sm text-slate-500">
                        @if(($staffSearchQuery ?? ''))
                            Tidak ada staff yang cocok dengan pencarian.
                        @else
                            Tidak ada staff yang tersedia.
                        @endif
                    </p>
                </div>
            @else
                <div class="space-y-2">
                    @foreach($availableStaff as $staff)
                        <button
                            type="button"
                            wire:click="assignStaffToOrder({{ $orderForAssignment->id }}, {{ $staff['id'] }})"
                            class="w-full flex items-center gap-4 rounded-xl border-2 border-slate-200 bg-white p-4 text-left transition-all hover:border-emerald-200 hover:bg-emerald-50/50"
                        >
                            {{-- Avatar --}}
                            <div class="flex-shrink-0">
                                <div class="size-12 rounded-full bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center text-white font-bold text-lg">
                                    {{ $staff['initials'] }}
                                </div>
                            </div>

                            {{-- Info --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <p class="font-semibold text-slate-900">{{ $staff['name'] }}</p>
                                </div>
                                <p class="text-sm text-slate-500">{{ $staff['phone'] }}</p>
                                <div class="mt-1 flex items-center gap-3 text-xs">
                                    <span class="inline-flex items-center gap-1 text-slate-500">
                                        <i class="fa-solid fa-list-check text-slate-400"></i>
                                        {{ $staff['active_orders'] }} pesanan aktif
                                    </span>
                                </div>
                            </div>

                            {{-- Status Badge --}}
                            <div class="flex-shrink-0">
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold
                                             {{ $staff['status'] === 'available'
                                                 ? 'bg-emerald-100 text-emerald-700'
                                                 : 'bg-amber-100 text-amber-700' }}">
                                    <i class="fa-solid {{ $staff['status'] === 'available' ? 'fa-check-circle mr-1' : 'fa-clock mr-1' }}"></i>
                                    {{ $staff['status'] === 'available' ? 'Tersedia' : 'Sibuk' }}
                                </span>
                            </div>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-end gap-3 border-t border-slate-200 px-6 py-4 bg-slate-50 rounded-b-xl">
            <button
                wire:click="closeStaffSelection()"
                class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-100"
            >
                Batal
            </button>
        </div>
    </div>
</div>
</template>
@endif
