<div class="space-y-6" wire:poll.10s>
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Operasi Staff</h1>
            <p class="text-sm text-slate-500">Kelola pesanan dari proses hingga pengiriman</p>
        </div>
        @if(count($selectedOrders) > 0)
            <div class="flex items-center gap-3 rounded-lg bg-violet-50 px-4 py-2 border border-violet-200">
                <span class="text-sm font-medium text-violet-700">{{ count($selectedOrders) }} dipilih</span>
                <div class="flex items-center gap-2">
                    @if(in_array($quickFilter, ['ready_to_process', 'processing']))
                        <button
                            wire:click="bulkAction('process')"
                            class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-blue-700"
                        >
                            <i class="fa-solid fa-gear mr-1"></i>Proses
                        </button>
                    @endif
                    @if(in_array($quickFilter, ['processing']))
                        <button
                            wire:click="bulkAction('ready_to_ship')"
                            class="rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-violet-700"
                        >
                            <i class="fa-solid fa-box mr-1"></i>Siap Kirim
                        </button>
                    @endif
                    @if(in_array($quickFilter, ['ready_to_ship']))
                        <button
                            wire:click="bulkAction('ship')"
                            class="rounded-lg bg-purple-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-purple-700"
                        >
                            <i class="fa-solid fa-truck mr-1"></i>Kirim
                        </button>
                    @endif
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

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-5">
        <button
            wire:click="setQuickFilter('ready_to_process')"
            class="rounded-xl border p-4 text-left transition @if($quickFilter === 'ready_to_process') border-amber-500 bg-amber-50 ring-2 ring-amber-200 @else border-slate-200 bg-white hover:border-slate-300 hover:shadow-sm @endif"
        >
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-500">Siap Diproses</p>
                    <p class="mt-1 text-2xl font-bold text-amber-600">{{ $stats['ready_to_process'] }}</p>
                </div>
                <div class="rounded-lg bg-amber-100 p-2">
                    <i class="fa-solid fa-clock text-amber-600"></i>
                </div>
            </div>
        </button>

        <button
            wire:click="setQuickFilter('processing')"
            class="rounded-xl border p-4 text-left transition @if($quickFilter === 'processing') border-blue-500 bg-blue-50 ring-2 ring-blue-200 @else border-slate-200 bg-white hover:border-slate-300 hover:shadow-sm @endif"
        >
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-500">Sedang Diproses</p>
                    <p class="mt-1 text-2xl font-bold text-blue-600">{{ $stats['processing'] }}</p>
                </div>
                <div class="rounded-lg bg-blue-100 p-2">
                    <i class="fa-solid fa-gear text-blue-600"></i>
                </div>
            </div>
        </button>

        <button
            wire:click="setQuickFilter('ready_to_ship')"
            class="rounded-xl border p-4 text-left transition @if($quickFilter === 'ready_to_ship') border-violet-500 bg-violet-50 ring-2 ring-violet-200 @else border-slate-200 bg-white hover:border-slate-300 hover:shadow-sm @endif"
        >
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-500">Siap Dikirim</p>
                    <p class="mt-1 text-2xl font-bold text-violet-600">{{ $stats['ready_to_ship'] }}</p>
                </div>
                <div class="rounded-lg bg-violet-100 p-2">
                    <i class="fa-solid fa-box text-violet-600"></i>
                </div>
            </div>
        </button>

        <button
            wire:click="setQuickFilter('shipped')"
            class="rounded-xl border p-4 text-left transition @if($quickFilter === 'shipped') border-purple-500 bg-purple-50 ring-2 ring-purple-200 @else border-slate-200 bg-white hover:border-slate-300 hover:shadow-sm @endif"
        >
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-500">Sedang Dikirim</p>
                    <p class="mt-1 text-2xl font-bold text-purple-600">{{ $stats['shipped'] }}</p>
                </div>
                <div class="rounded-lg bg-purple-100 p-2">
                    <i class="fa-solid fa-truck text-purple-600"></i>
                </div>
            </div>
        </button>

        <button
            wire:click="setQuickFilter('delivered')"
            class="rounded-xl border p-4 text-left transition @if($quickFilter === 'delivered') border-emerald-500 bg-emerald-50 ring-2 ring-emerald-200 @else border-slate-200 bg-white hover:border-slate-300 hover:shadow-sm @endif"
        >
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-500">Diterima</p>
                    <p class="mt-1 text-2xl font-bold text-emerald-600">{{ $stats['delivered'] }}</p>
                </div>
                <div class="rounded-lg bg-emerald-100 p-2">
                    <i class="fa-solid fa-check-circle text-emerald-600"></i>
                </div>
            </div>
        </button>
    </div>

    {{-- Search & Filter Bar --}}
    <div class="flex flex-col gap-4 rounded-xl border border-slate-200 bg-white p-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="relative flex-1">
            <input
                type="text"
                wire:model.live.debounce.300ms="searchQuery"
                placeholder="Cari pesanan, pelanggan, telepon..."
                class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2 pl-10 pr-4 text-sm placeholder:text-slate-400 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20"
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

        <div class="flex items-center gap-2">
            <select
                wire:model.live="sortField"
                class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-violet-500 focus:outline-none"
            >
                <option value="created_at">Tanggal</option>
                <option value="order_number">Kode Pesanan</option>
                <option value="customer_name">Pelanggan</option>
                <option value="total_amount">Total</option>
            </select>
            <button
                wire:click="sortBy('{{ $sortField }}')"
                class="rounded-lg border border-slate-200 bg-white p-2 hover:bg-slate-50"
                title="{{ $sortDirection === 'asc' ? 'Urutkan Z-A' : 'Urutkan A-Z' }}"
            >
                <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-slate-600"></i>
            </button>
        </div>
    </div>

    {{-- Orders List --}}
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="w-12 px-4 py-3">
                            <input
                                type="checkbox"
                                wire:model.live="selectAll"
                                class="h-4 w-4 rounded border-slate-300 text-violet-600 focus:ring-violet-500"
                            />
                        </th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-900">Pesanan</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-900">Pelanggan</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-900">Tipe</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-900">Item</th>
                        <th class="px-4 py-3 text-right font-semibold text-slate-900">Total</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-900">Status</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-900 w-48">Aksi Cepat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $order)
                        @php
                            $isSelected = in_array((string) $order->id, $selectedOrders);
                            $validNextStatuses = $this->getValidNextStatuses($order);
                            $canProcess = in_array('processing', $validNextStatuses);
                            $canReadyToShip = in_array('ready_to_ship', $validNextStatuses);
                            $canShip = in_array('shipped', $validNextStatuses);
                            $canDeliver = in_array('delivered', $validNextStatuses);
                        @endphp
                        <tr class="transition-colors hover:bg-slate-50 {{ $isSelected ? 'bg-violet-50' : '' }}">
                            <td class="px-4 py-3">
                                <input
                                    type="checkbox"
                                    value="{{ $order->id }}"
                                    wire:model.live="selectedOrders"
                                    class="h-4 w-4 rounded border-slate-300 text-violet-600 focus:ring-violet-500"
                                />
                            </td>
                            <td class="px-4 py-3">
                                <div>
                                    <p class="font-semibold text-slate-900 cursor-pointer hover:text-violet-600" wire:click="viewOrder({{ $order->id }})">
                                        {{ $order->order_number }}
                                    </p>
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
                                        <i class="fa-solid fa-shopping-cart mr-1"></i>Beli
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-700">
                                        <i class="fa-solid fa-circle-question mr-1"></i>Booking
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-slate-700">{{ $order->total_items }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <span class="font-semibold text-slate-900">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $order->status_badge_class }}">
                                    {{ $order->status_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    {{-- Process Button --}}
                                    @if($canProcess)
                                        <button
                                            wire:click="processOrder({{ $order->id }})"
                                            class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-blue-700 transition-colors"
                                            title="Mulai Proses"
                                        >
                                            <i class="fa-solid fa-gear mr-1"></i>Proses
                                        </button>
                                    @endif

                                    {{-- Ready to Ship Button --}}
                                    @if($canReadyToShip)
                                        <button
                                            wire:click="startPacking({{ $order->id }})"
                                            class="rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-violet-700 transition-colors"
                                            title="Siap Dikirim"
                                        >
                                            <i class="fa-solid fa-box mr-1"></i>Siap Kirim
                                        </button>
                                    @endif

                                    {{-- Ship Button --}}
                                    @if($canShip)
                                        <button
                                            wire:click="shipOrder({{ $order->id }})"
                                            class="rounded-lg bg-purple-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-purple-700 transition-colors"
                                            title="Kirim"
                                        >
                                            <i class="fa-solid fa-truck mr-1"></i>Kirim
                                        </button>
                                    @endif

                                    {{-- Delivered Button --}}
                                    @if($canDeliver)
                                        <button
                                            wire:click="markDelivered({{ $order->id }})"
                                            class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-emerald-700 transition-colors"
                                            title="Tandai Diterima"
                                        >
                                            <i class="fa-solid fa-check mr-1"></i>Terima
                                        </button>
                                    @endif

                                    {{-- View Detail --}}
                                    <button
                                        wire:click="viewOrder({{ $order->id }})"
                                        class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors"
                                        title="Lihat Detail"
                                    >
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center text-slate-400">
                                    <i class="fa-solid fa-boxes-stacked text-4xl mb-3"></i>
                                    <p class="font-medium">Tidak ada pesanan</p>
                                    <p class="text-sm">Pesanan yang perlu diproses akan muncul di sini</p>
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
        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4 bg-slate-50">
            <div>
                <h3 class="text-lg font-bold text-slate-900">{{ $selectedOrder->order_number }}</h3>
                <p class="text-sm text-slate-500">{{ $selectedOrder->created_at->format('d M Y, H:i') }}</p>
            </div>
            <button @click="$wire.closeDetailModal()" class="rounded-lg p-2 text-slate-400 hover:bg-slate-200 hover:text-slate-600 transition-colors">
                <i class="fa-solid fa-times text-lg"></i>
            </button>
        </div>

        {{-- Body --}}
        <div class="px-6 py-4 max-h-[60vh] overflow-y-auto space-y-6">
            {{-- Quick Action Buttons --}}
            @php
                $modalValidStatuses = $this->getValidNextStatuses($selectedOrder);
            @endphp
            <div class="flex flex-wrap gap-2">
                @if(in_array('processing', $modalValidStatuses))
                    <button
                        wire:click="processOrder({{ $selectedOrder->id }}); $wire.closeDetailModal()"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                    >
                        <i class="fa-solid fa-gear mr-2"></i>Mulai Proses
                    </button>
                @endif
                @if(in_array('ready_to_ship', $modalValidStatuses))
                    <button
                        wire:click="startPacking({{ $selectedOrder->id }}); $wire.closeDetailModal()"
                        class="rounded-lg bg-violet-600 px-4 py-2 text-sm font-medium text-white hover:bg-violet-700"
                    >
                        <i class="fa-solid fa-box mr-2"></i>Siap Dikirim
                    </button>
                @endif
                @if(in_array('shipped', $modalValidStatuses))
                    <button
                        wire:click="shipOrder({{ $selectedOrder->id }}); $wire.closeDetailModal()"
                        class="rounded-lg bg-purple-600 px-4 py-2 text-sm font-medium text-white hover:bg-purple-700"
                    >
                        <i class="fa-solid fa-truck mr-2"></i>Kirim Sekarang
                    </button>
                @endif
                @if(in_array('delivered', $modalValidStatuses))
                    <button
                        wire:click="markDelivered({{ $selectedOrder->id }}); $wire.closeDetailModal()"
                        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700"
                    >
                        <i class="fa-solid fa-check mr-2"></i>Tandai Diterima
                    </button>
                @endif
            </div>

            {{-- Status & Type Badges --}}
            <div class="flex items-center gap-3">
                @if($selectedOrder->order_type === 'cart')
                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-sm font-medium text-emerald-700">
                        <i class="fa-solid fa-shopping-cart mr-1"></i>Pembelian
                    </span>
                @else
                    <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-sm font-medium text-amber-700">
                        <i class="fa-solid fa-circle-question mr-1"></i>Booking
                    </span>
                @endif
                <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium {{ $selectedOrder->status_badge_class }}">
                    {{ $selectedOrder->status_label }}
                </span>
            </div>

            {{-- Customer Info --}}
            <div class="rounded-lg bg-slate-50 p-4">
                <h4 class="mb-3 font-semibold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-user text-slate-400"></i>
                    Informasi Pelanggan
                </h4>
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
                        <p class="font-medium text-slate-900">{{ $selectedOrder->customer_address ?? '-' }}</p>
                    </div>
                </div>
            </div>

            {{-- Order Items --}}
            <div>
                <h4 class="mb-3 font-semibold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-box text-slate-400"></i>
                    Item Pesanan
                </h4>
                <div class="divide-y divide-slate-100 rounded-lg border border-slate-200">
                    @forelse($selectedOrder->items as $item)
                        <div class="flex items-center justify-between p-3">
                            <div class="flex items-center gap-3">
                                @if($item->product_image)
                                    <img src="{{ asset('storage/' . $item->product_image) }}" alt="" class="size-12 rounded-lg object-cover" />
                                @else
                                    <div class="size-12 rounded-lg bg-slate-200 flex items-center justify-center">
                                        <i class="fa-solid fa-image text-slate-400"></i>
                                    </div>
                                @endif
                                <div>
                                    <p class="font-medium text-slate-900">{{ $item->product_name }}</p>
                                    <p class="text-xs text-slate-500">{{ $item->quantity ?? 1 }} x Rp {{ number_format($item->unit_price, 0, ',', '.') }}</p>
                                </div>
                            </div>
                            <p class="font-semibold text-slate-900">Rp {{ number_format($item->subtotal ?? ($item->unit_price * ($item->quantity ?? 1)), 0, ',', '.') }}</p>
                        </div>
                    @empty
                        <p class="py-4 text-center text-sm text-slate-500">Tidak ada item</p>
                    @endforelse
                </div>
            </div>

            {{-- Summary --}}
            <div class="rounded-lg bg-violet-50 p-4 border border-violet-200">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-600">Total ({{ $selectedOrder->total_items }} item)</span>
                    <span class="text-2xl font-bold text-violet-700">Rp {{ number_format($selectedOrder->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>

            {{-- Notes --}}
            @if($selectedOrder->notes)
                <div class="rounded-lg bg-blue-50 p-4 border border-blue-200">
                    <p class="text-xs text-slate-500 mb-1">Catatan</p>
                    <p class="text-sm text-slate-700">{{ $selectedOrder->notes }}</p>
                </div>
            @endif

            {{-- Timeline --}}
            <div>
                <h4 class="mb-3 font-semibold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-slate-400"></i>
                    Riwayat Pesanan
                </h4>
                <x-order-timeline :order="$selectedOrder" />
            </div>
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-end gap-3 border-t border-slate-200 px-6 py-4 bg-slate-50">
            <button @click="$wire.closeDetailModal()" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>
</template>
@endif

{{-- Alert Script --}}
@script
<script>
    window.addEventListener('showAlert', (event) => {
        const { type, title, message } = event.detail[0];
        const bgColor = type === 'success' ? 'bg-emerald-500' : 'bg-red-500';

        const alert = document.createElement('div');
        alert.className = `fixed top-4 right-4 z-[99999] ${bgColor} text-white px-6 py-4 rounded-lg shadow-lg flex items-center gap-3 animate-slide-in`;
        alert.innerHTML = `
            <i class="fa-solid ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} text-lg"></i>
            <div>
                <p class="font-semibold">${title}</p>
                <p class="text-sm opacity-90">${message}</p>
            </div>
        `;
        document.body.appendChild(alert);

        setTimeout(() => {
            alert.classList.add('animate-slide-out');
            setTimeout(() => alert.remove(), 300);
        }, 3000);
    });
</script>
<style>
    @keyframes slide-in {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slide-out {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    .animate-slide-in { animation: slide-in 0.3s ease-out; }
    .animate-slide-out { animation: slide-out 0.3s ease-in; }
</style>
@endscript
