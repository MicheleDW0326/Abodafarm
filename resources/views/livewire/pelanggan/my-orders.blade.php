<div class="space-y-6">
    {{-- Header --}}
    <div class="rounded-2xl border border-slate-100 bg-gradient-to-r from-emerald-600 to-emerald-700 p-6 shadow-lg">
        <div class="flex items-center gap-4">
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-white/20 text-xl text-white">
                <i class="fa-solid fa-shopping-bag"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-white">Pesanan Daging Segar</h2>
                <p class="mt-0.5 text-emerald-100">Lacak pesanan Anda dari pembayaran hingga pengiriman</p>
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-5">
        <button
            wire:click="$set('statusFilter', 'all')"
            class="rounded-xl border p-3 text-left transition @if($statusFilter === 'all') border-emerald-500 bg-emerald-50 @else border-slate-200 bg-white hover:border-slate-300 @endif"
        >
            <p class="text-lg font-bold text-slate-900">{{ $stats['total'] }}</p>
            <p class="text-xs text-slate-500">Semua</p>
        </button>
        <button
            wire:click="$set('statusFilter', 'pending')"
            class="rounded-xl border p-3 text-left transition @if($statusFilter === 'pending') border-amber-500 bg-amber-50 @else border-slate-200 bg-white hover:border-slate-300 @endif"
        >
            <p class="text-lg font-bold text-amber-600">{{ $stats['pending'] }}</p>
            <p class="text-xs text-slate-500">Menunggu</p>
        </button>
        <button
            wire:click="$set('statusFilter', 'processing')"
            class="rounded-xl border p-3 text-left transition @if($statusFilter === 'processing') border-blue-500 bg-blue-50 @else border-slate-200 bg-white hover:border-slate-300 @endif"
        >
            <p class="text-lg font-bold text-blue-600">{{ $stats['processing'] }}</p>
            <p class="text-xs text-slate-500">Diproses</p>
        </button>
        <button
            wire:click="$set('statusFilter', 'shipped')"
            class="rounded-xl border p-3 text-left transition @if($statusFilter === 'shipped') border-purple-500 bg-purple-50 @else border-slate-200 bg-white hover:border-slate-300 @endif"
        >
            <p class="text-lg font-bold text-purple-600">{{ $stats['shipped'] }}</p>
            <p class="text-xs text-slate-500">Dikirim</p>
        </button>
        <button
            wire:click="$set('statusFilter', 'delivered')"
            class="rounded-xl border p-3 text-left transition @if($statusFilter === 'delivered') border-emerald-500 bg-emerald-50 @else border-slate-200 bg-white hover:border-slate-300 @endif"
        >
            <p class="text-lg font-bold text-emerald-600">{{ $stats['completed'] }}</p>
            <p class="text-xs text-slate-500">Selesai</p>
        </button>
    </div>

    {{-- Search --}}
    <div class="relative">
        <input
            type="text"
            wire:model.live.debounce.300ms="searchQuery"
            placeholder="Cari nomor pesanan..."
            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 pl-10 text-sm transition focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
        >
        <i class="fa-solid fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
    </div>

    {{-- Orders List --}}
    <div class="space-y-4">
        @forelse($orders as $order)
            @php
                $paymentProgress = $this->getPaymentProgress($order);
                $deliveryProgress = $this->getDeliveryProgress($order);
                $statusInfo = $this->getStatusExplanation($order->status);
            @endphp

            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-xs transition hover:shadow-md">
                {{-- Order Header --}}
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-rose-50 text-rose-600">
                            <i class="fa-solid fa-drumstick-bite text-lg"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-slate-900">{{ $order->order_number }}</p>
                            <p class="text-xs text-slate-500">{{ $order->created_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="text-right">
                            <p class="text-lg font-bold text-slate-900">{{ $formatRupiah($order->total_amount) }}</p>
                            <p class="text-xs text-slate-500">{{ $order->total_items }} item</p>
                        </div>
                        <span class="rounded-full px-3 py-1.5 text-xs font-semibold {{ $order->status_badge_class }}">
                            {{ $statusInfo['label'] ?? $order->status_label }}
                        </span>
                    </div>
                </div>

                {{-- Visual Progress Tracker --}}
                <div class="mt-4 rounded-xl bg-slate-50 p-4">
                    {{-- Status Message --}}
                    <div class="mb-3 flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-{{ $statusInfo['color'] }}-100 text-{{ $statusInfo['color'] }}-600">
                            <i class="fa-solid fa-{{ $statusInfo['icon'] }} text-sm"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ $statusInfo['title'] }}</p>
                            <p class="text-xs text-slate-500">{{ $statusInfo['description'] }}</p>
                        </div>
                    </div>

                    {{-- Progress Steps --}}
                    <div class="mt-4">
                        {{-- Payment Progress (only show if not paid yet) --}}
                        @if(!$paymentProgress['isComplete'])
                            <div class="mb-4">
                                <p class="mb-2 text-xs font-medium text-slate-500 uppercase tracking-wide">
                                    <i class="fa-solid fa-credit-card mr-1"></i>Pembayaran
                                </p>
                                <div class="flex items-center gap-2">
                                    @foreach($paymentProgress['steps'] as $key => $step)
                                        <div class="flex flex-1 items-center">
                                            <div class="flex flex-1 items-center gap-2">
                                                <div class="h-6 w-6 rounded-full flex items-center justify-center text-xs font-bold
                                                    @if($loop->iteration - 1 <= $paymentProgress['currentStep'])
                                                        bg-emerald-500 text-white
                                                    @else
                                                        bg-slate-200 text-slate-500
                                                    @endif">
                                                    @if($loop->iteration - 1 < $paymentProgress['currentStep'])
                                                        <i class="fa-solid fa-check text-[10px]"></i>
                                                    @else
                                                        {{ $loop->iteration }}
                                                    @endif
                                                </div>
                                                <span class="text-xs font-medium @if($loop->iteration - 1 <= $paymentProgress['currentStep']) text-emerald-600 @else text-slate-400 @endif">
                                                    {{ $step['label'] }}
                                                </span>
                                            </div>
                                            @if(!$loop->last)
                                                <div class="h-0.5 flex-1 @if($loop->iteration - 1 < $paymentProgress['currentStep']) bg-emerald-400 @else bg-slate-200 @endif mx-1"></div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Delivery Progress --}}
                        <div>
                            <p class="mb-2 text-xs font-medium text-slate-500 uppercase tracking-wide">
                                <i class="fa-solid fa-truck mr-1"></i>Pengiriman
                            </p>
                            <div class="flex items-center gap-2">
                                @foreach($deliveryProgress['steps'] as $key => $step)
                                    <div class="flex flex-1 items-center">
                                        <div class="flex flex-1 items-center gap-2">
                                            <div class="h-6 w-6 rounded-full flex items-center justify-center text-xs font-bold
                                                @if($loop->iteration - 1 <= $deliveryProgress['currentStep'])
                                                    bg-emerald-500 text-white
                                                @else
                                                    bg-slate-200 text-slate-500
                                                @endif">
                                                @if($loop->iteration - 1 < $deliveryProgress['currentStep'])
                                                    <i class="fa-solid fa-check text-[10px]"></i>
                                                @else
                                                    {{ $loop->iteration }}
                                                @endif
                                            </div>
                                            <span class="text-xs font-medium @if($loop->iteration - 1 <= $deliveryProgress['currentStep']) text-emerald-600 @else text-slate-400 @endif">
                                                {{ $step['label'] }}
                                            </span>
                                        </div>
                                        @if(!$loop->last)
                                            <div class="h-0.5 flex-1 @if($loop->iteration - 1 < $deliveryProgress['currentStep']) bg-emerald-400 @else bg-slate-200 @endif mx-1"></div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Tracking Number (if shipped) --}}
                    @if($order->tracking_number && in_array($order->status, ['shipped', 'delivered', 'completed']))
                        <div class="mt-3 flex items-center justify-between rounded-lg bg-white p-2">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-barcode text-slate-400"></i>
                                <span class="text-xs text-slate-600">Resi: <span class="font-mono font-medium">{{ $order->tracking_number }}</span></span>
                            </div>
                            @if($order->shipping_carrier)
                                <span class="text-xs text-slate-500">{{ $order->shipping_carrier }}</span>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Order Items Preview --}}
                @if($order->items->count() > 0)
                    <div class="mt-4 flex items-center gap-2 border-t border-slate-100 pt-4">
                        <div class="flex -space-x-2">
                            @foreach($order->items->take(3) as $item)
                                @if($item->product && $item->product->image)
                                    <img
                                        src="{{ $item->product->image_url }}"
                                        alt="{{ $item->product_name }}"
                                        class="h-10 w-10 rounded-lg border-2 border-white object-cover"
                                    >
                                @else
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg border-2 border-white bg-slate-100">
                                        <i class="fa-solid fa-image text-slate-400"></i>
                                    </div>
                                @endif
                            @endforeach
                            @if($order->items->count() > 3)
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg border-2 border-white bg-slate-100 text-xs font-semibold text-slate-500">
                                    +{{ $order->items->count() - 3 }}
                                </div>
                            @endif
                        </div>
                        <p class="text-sm text-slate-500">
                            @foreach($order->items->take(2) as $item)
                                <span>{{ $item->product_name }}</span>@if(!$loop->last), @endif
                            @endforeach
                            @if($order->items->count() > 2)
                                <span>dan {{ $order->items->count() - 2 }} lainnya</span>
                            @endif
                        </p>
                    </div>
                @endif

                {{-- Actions --}}
                <div class="mt-4 flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                    {{-- Confirm Order Received Button (for delivered orders) --}}
                    @if($order->status === 'delivered')
                        <button
                            wire:click="$dispatch('open-confirm-delivery', { order: {{ $order->id }} })"
                            class="rounded-lg bg-gradient-to-r from-emerald-500 to-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition-all hover:from-emerald-600 hover:to-emerald-700 hover:shadow-xl"
                        >
                            <i class="fa-solid fa-check-circle mr-1"></i> Konfirmasi Diterima
                        </button>
                    @endif

                    {{-- Completed Badge (for completed orders) --}}
                    @if($order->status === 'completed')
                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1.5 text-sm font-semibold text-emerald-700">
                            <i class="fa-solid fa-check-double mr-1"></i> Selesai
                        </span>
                    @endif

                    <button
                        wire:click="selectOrder({{ $order->id }})"
                        class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50"
                    >
                        <i class="fa-solid fa-eye mr-1"></i> Detail
                    </button>
                    @if($order->status === 'pending')
                        <a
                            href="{{ route('checkout', ['order_id' => $order->id]) }}"
                            class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700"
                        >
                            <i class="fa-solid fa-credit-card mr-1"></i> Bayar
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center gap-4 rounded-2xl border border-dashed border-slate-300 bg-slate-50 py-16 text-center">
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                    <i class="fa-solid fa-shopping-bag text-3xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-slate-700">Belum ada pesanan daging</h3>
                    <p class="mt-1 text-sm text-slate-500">Jelajahi produk daging segar kami</p>
                </div>
                <a href="{{ route('home') }}" class="mt-2 rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">
                    Jelajahi Produk
                </a>
            </div>
        @endforelse
    </div>

    {{-- Detail Modal --}}
    @if($selectedOrder)
        @php
            $paymentProgress = $this->getPaymentProgress($selectedOrder);
            $deliveryProgress = $this->getDeliveryProgress($selectedOrder);
            $statusInfo = $this->getStatusExplanation($selectedOrder->status);
        @endphp
        <div
            x-data="{ open: @entangle('selectedOrderId') }"
            x-show="$wire.selectedOrderId"
            x-on:keydown.escape.window="$wire.closeDetail()"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4"
        >
            <div
                x-show="$wire.selectedOrderId"
                x-transition
                class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white shadow-xl"
            >
                {{-- Modal Header --}}
                <div class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-100 bg-white px-6 py-4">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Detail Pesanan</h3>
                        <p class="text-sm text-slate-500">{{ $selectedOrder->order_number }}</p>
                    </div>
                    <button
                        wire:click="closeDetail"
                        class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                    >
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>

                {{-- Modal Content --}}
                <div class="space-y-6 p-6">
                    {{-- Visual Progress Tracker --}}
                    <div class="rounded-xl border border-slate-100 bg-gradient-to-r from-slate-50 to-slate-100 p-5">
                        {{-- Status Banner --}}
                        <div class="mb-4 flex items-center gap-3 rounded-lg bg-{{ $statusInfo['color'] }}-50 p-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-{{ $statusInfo['color'] }}-100 text-{{ $statusInfo['color'] }}-600">
                                <i class="fa-solid fa-{{ $statusInfo['icon'] }}"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-slate-900">{{ $statusInfo['title'] }}</p>
                                <p class="text-sm text-slate-600">{{ $statusInfo['description'] }}</p>
                            </div>
                        </div>

                        {{-- Payment Progress --}}
                        @if(!$paymentProgress['isComplete'])
                            <div class="mb-5">
                                <p class="mb-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                    <i class="fa-solid fa-credit-card mr-1"></i>Pembayaran
                                </p>
                                <div class="flex items-center gap-2">
                                    @foreach($paymentProgress['steps'] as $key => $step)
                                        <div class="flex flex-1 items-center">
                                            <div class="flex flex-1 flex-col items-center gap-1">
                                                <div class="h-8 w-8 rounded-full flex items-center justify-center text-xs font-bold
                                                    @if($loop->iteration - 1 <= $paymentProgress['currentStep'])
                                                        bg-emerald-500 text-white shadow-lg shadow-emerald-200
                                                    @else
                                                        bg-slate-200 text-slate-500
                                                    @endif">
                                                    @if($loop->iteration - 1 < $paymentProgress['currentStep'])
                                                        <i class="fa-solid fa-check text-[10px]"></i>
                                                    @else
                                                        {{ $loop->iteration }}
                                                    @endif
                                                </div>
                                                <span class="text-xs font-medium text-center @if($loop->iteration - 1 <= $paymentProgress['currentStep']) text-emerald-600 @else text-slate-400 @endif">
                                                    {{ $step['label'] }}
                                                </span>
                                            </div>
                                            @if(!$loop->last)
                                                <div class="h-1 flex-1 rounded-full @if($loop->iteration - 1 < $paymentProgress['currentStep']) bg-emerald-400 @else bg-slate-200 @endif mb-4 mx-0.5"></div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Delivery Progress --}}
                        <div>
                            <p class="mb-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                <i class="fa-solid fa-truck mr-1"></i>Pengiriman
                            </p>
                            <div class="flex items-center gap-2">
                                @foreach($deliveryProgress['steps'] as $key => $step)
                                    <div class="flex flex-1 items-center">
                                        <div class="flex flex-1 flex-col items-center gap-1">
                                            <div class="h-8 w-8 rounded-full flex items-center justify-center text-xs font-bold
                                                @if($loop->iteration - 1 <= $deliveryProgress['currentStep'])
                                                    bg-emerald-500 text-white shadow-lg shadow-emerald-200
                                                @else
                                                    bg-slate-200 text-slate-500
                                                @endif">
                                                @if($loop->iteration - 1 < $deliveryProgress['currentStep'])
                                                    <i class="fa-solid fa-check text-[10px]"></i>
                                                @else
                                                    {{ $loop->iteration }}
                                                @endif
                                            </div>
                                            <span class="text-xs font-medium text-center @if($loop->iteration - 1 <= $deliveryProgress['currentStep']) text-emerald-600 @else text-slate-400 @endif">
                                                {{ $step['label'] }}
                                            </span>
                                        </div>
                                        @if(!$loop->last)
                                            <div class="h-1 flex-1 rounded-full @if($loop->iteration - 1 < $deliveryProgress['currentStep']) bg-emerald-400 @else bg-slate-200 @endif mb-4 mx-0.5"></div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Tracking Info --}}
                        @if($selectedOrder->tracking_number)
                            <div class="mt-4 flex items-center justify-between rounded-lg bg-white p-3">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-barcode text-slate-400"></i>
                                    <span class="text-sm text-slate-600">No. Resi:</span>
                                    <span class="font-mono font-medium text-slate-900">{{ $selectedOrder->tracking_number }}</span>
                                </div>
                                @if($selectedOrder->shipping_carrier)
                                    <span class="text-sm text-slate-500">{{ $selectedOrder->shipping_carrier }}</span>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- Items --}}
                    <div>
                        <h4 class="mb-3 font-semibold text-slate-700">Item Pesanan</h4>
                        <div class="space-y-3">
                            @foreach($selectedOrder->items as $item)
                                <div class="flex items-center gap-3 rounded-xl border border-slate-100 p-3">
                                    @if($item->product && $item->product->image)
                                        <img
                                            src="{{ $item->product->image_url }}"
                                            alt="{{ $item->product_name }}"
                                            class="h-14 w-14 rounded-lg object-cover"
                                        >
                                    @else
                                        <div class="flex h-14 w-14 items-center justify-center rounded-lg bg-slate-100">
                                            <i class="fa-solid fa-image text-slate-400"></i>
                                        </div>
                                    @endif
                                    <div class="flex-1">
                                        <p class="font-medium text-slate-900">{{ $item->product_name }}</p>
                                        <p class="text-xs text-slate-500">{{ $item->quantity }}x × {{ $formatRupiah($item->unit_price) }}</p>
                                    </div>
                                    <p class="font-semibold text-slate-900">{{ $formatRupiah($item->subtotal) }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Summary --}}
                    <div class="rounded-xl bg-slate-50 p-4">
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-slate-500">Subtotal</span>
                                <span class="font-medium text-slate-900">{{ $formatRupiah($selectedOrder->total_amount) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Ongkos Kirim</span>
                                <span class="font-medium text-slate-900">-</span>
                            </div>
                            <div class="flex justify-between border-t border-slate-200 pt-2 text-base">
                                <span class="font-semibold text-slate-900">Total</span>
                                <span class="font-bold text-emerald-600">{{ $formatRupiah($selectedOrder->total_amount) }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Shipping Address --}}
                    @if($selectedOrder->customer_address)
                        <div>
                            <h4 class="mb-2 font-semibold text-slate-700">Alamat Pengiriman</h4>
                            <div class="rounded-xl border border-slate-100 p-4">
                                <p class="font-medium text-slate-900">{{ $selectedOrder->customer_name }}</p>
                                <p class="text-sm text-slate-500">{{ $selectedOrder->customer_phone }}</p>
                                <p class="mt-1 text-sm text-slate-600">{{ $selectedOrder->customer_address }}</p>
                            </div>
                        </div>
                    @endif

                    {{-- Timeline --}}
                    <div>
                        <h4 class="mb-3 font-semibold text-slate-700">Riwayat Pesanan</h4>
                        <x-order-timeline :order="$selectedOrder" />
                    </div>

                    {{-- Delivery Proof Section (for delivered orders) --}}
                    @if($selectedOrder->status === 'delivered' && ($selectedOrder->receiver_name || $selectedOrder->delivery_photo))
                        <div class="rounded-xl bg-emerald-50 p-4 border border-emerald-100">
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
                                        <p class="text-xs text-emerald-600">Diterima Oleh</p>
                                        <p class="font-medium text-emerald-900">{{ $selectedOrder->receiver_name }}</p>
                                    </div>
                                @endif

                                @if($selectedOrder->delivery_completed_at)
                                    <div>
                                        <p class="text-xs text-emerald-600">Waktu Penerimaan</p>
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
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Modal Footer --}}
                <div class="sticky bottom-0 flex items-center justify-end gap-3 border-t border-slate-100 bg-white px-6 py-4">
                    <button
                        wire:click="closeDetail"
                        class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50"
                    >
                        Tutup
                    </button>
                    @if($selectedOrder->status === 'pending')
                        <a
                            href="{{ route('checkout', ['order_id' => $selectedOrder->id]) }}"
                            class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700"
                        >
                            <i class="fa-solid fa-credit-card mr-1"></i> Bayar Sekarang
                        </a>
                    @endif
                    @if($selectedOrder->status === 'delivered')
                        <button
                            wire:click="$dispatch('open-confirm-delivery', { order: {{ $selectedOrder->id }} })"
                            class="rounded-lg bg-gradient-to-r from-emerald-500 to-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition-all hover:from-emerald-600 hover:to-emerald-700 hover:shadow-xl"
                        >
                            <i class="fa-solid fa-check-circle mr-1"></i> Konfirmasi Diterima
                        </button>
                    @endif
                    @if($selectedOrder->status === 'completed')
                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-4 py-2 text-sm font-semibold text-emerald-700">
                            <i class="fa-solid fa-check-double mr-1"></i> Pesanan Selesai
                        </span>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
