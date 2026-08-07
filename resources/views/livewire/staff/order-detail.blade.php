<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('staff.orders.list') }}"
                   class="rounded-lg border border-slate-200 bg-white p-2 text-slate-600 transition-colors hover:bg-slate-50">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">
                        {{ $order->order_number }}
                    </h1>
                    <p class="mt-1 text-sm text-slate-500">
                        @if($order->order_type === 'cart')
                            Pembelian - {{ $order->created_at->translatedFormat('d M Y, H:i') }}
                        @else
                            Booking/Inquiry - {{ $order->created_at->translatedFormat('d M Y, H:i') }}
                        @endif
                    </p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center rounded-full px-3 py-1.5 text-sm font-medium {{ $this->getStatusBadgeClass($order->status) }}">
                {{ $this->getStatusLabel($order->status) }}
            </span>
            @if($order->order_type === 'cart')
                <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1.5 text-sm font-medium text-emerald-700">
                    <i class="fa-solid fa-shopping-cart mr-1.5"></i>Pembelian
                </span>
            @else
                <span class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1.5 text-sm font-medium text-amber-700">
                    <i class="fa-solid fa-calendar-check mr-1.5"></i>Booking
                </span>
            @endif
        </div>
    </div>

    {{-- Info / Action Banner --}}
    @if($order->order_type === 'cart')
        @if(in_array($order->status, ['paid', 'approved', 'processing', 'ready_to_ship']) && $order->isAssignedTo(auth()->id()))
            <div class="rounded-xl border border-violet-200 bg-violet-50 p-4">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="rounded-full bg-violet-100 p-2 text-violet-600">
                            <i class="fa-solid fa-truck-fast text-lg"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-violet-800">Pesanan Siap Dikirim</p>
                            <p class="text-sm text-violet-600">Pesanan ini siap untuk dimulai pengirimannya.</p>
                        </div>
                    </div>
                    <button
                        wire:click="$dispatch('open-start-delivery', { order: {{ $order->id }} })"
                        class="rounded-lg bg-violet-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-violet-700 flex items-center gap-2"
                    >
                        <i class="fa-solid fa-truck"></i>
                        Mulai Kirim
                    </button>
                </div>
            </div>
        @elseif($order->status === 'shipped')
            <div class="rounded-xl border border-purple-200 bg-purple-50 p-4">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="rounded-full bg-purple-100 p-2 text-purple-600">
                            <i class="fa-solid fa-truck text-lg"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-purple-800">Sedang Dalam Pengiriman</p>
                            <p class="text-sm text-purple-600">
                                @if($order->estimated_delivery_time)
                                    Estimasi tiba: {{ $this->formattedEstimatedDelivery }}
                                @else
                                    Pesanan sedang dalam perjalanan.
                                @endif
                            </p>
                        </div>
                    </div>
                    @if($order->isAssignedTo(auth()->id()))
                        <button
                            wire:click="$dispatch('open-complete-delivery', { order: {{ $order->id }} })"
                            class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-emerald-700 flex items-center gap-2"
                        >
                            <i class="fa-solid fa-check-circle"></i>
                            Selesaikan Pengiriman
                        </button>
                    @endif
                </div>
            </div>
        @endif
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Main Content --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- Order Items --}}
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center gap-3">
                    <div class="rounded-xl bg-emerald-100 p-2.5 text-emerald-600">
                        <i class="fa-solid fa-box text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Item Pesanan</h3>
                        <p class="text-xs text-slate-500">{{ $order->items->count() }} item</p>
                    </div>
                </div>

                <div class="space-y-4">
                    @forelse($order->items as $item)
                        <div class="flex items-start gap-4 rounded-xl border border-slate-100 bg-slate-50 p-4">
                            {{-- Product Image --}}
                            <div class="flex-shrink-0">
                                @if($item->product && $item->product->image)
                                    <img src="{{ asset('storage/' . $item->product->image) }}"
                                         alt="{{ $item->product_name }}"
                                         class="size-20 rounded-xl object-cover" />
                                @elseif($item->product_image)
                                    <img src="{{ asset('storage/' . $item->product_image) }}"
                                         alt="{{ $item->product_name }}"
                                         class="size-20 rounded-xl object-cover" />
                                @else
                                    <div class="flex size-20 items-center justify-center rounded-xl bg-slate-200">
                                        <i class="fa-solid fa-image text-2xl text-slate-400"></i>
                                    </div>
                                @endif
                            </div>

                            {{-- Product Info --}}
                            <div class="flex-1 min-w-0">
                                <h4 class="font-semibold text-slate-900">{{ $item->product_name }}</h4>
                                @if($item->product?->category)
                                    <p class="text-xs text-slate-500">{{ $item->product->category->name }}</p>
                                @endif
                                <div class="mt-2 flex items-center gap-4 text-sm text-slate-600">
                                    <span>
                                        <i class="fa-solid fa-hashtag mr-1 text-slate-400"></i>
                                        Qty: {{ $item->quantity }}
                                    </span>
                                    <span>
                                        <i class="fa-solid fa-tag mr-1 text-slate-400"></i>
                                        Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>

                            {{-- Subtotal --}}
                            <div class="text-right">
                                <p class="text-lg font-bold text-slate-900">
                                    Rp {{ number_format($item->subtotal ?? ($item->unit_price * $item->quantity), 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-slate-200 p-8 text-center">
                            <i class="fa-solid fa-box-open text-4xl text-slate-300 mb-2"></i>
                            <p class="text-sm text-slate-500">Tidak ada item dalam pesanan ini</p>
                        </div>
                    @endforelse
                </div>

                {{-- Order Summary --}}
                <div class="mt-6 rounded-xl border-t border-slate-200 bg-slate-50 p-4">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-600">Subtotal ({{ $order->total_items }} item)</span>
                            <span class="font-medium text-slate-900">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-600">Biaya Pengiriman</span>
                            <span class="font-medium text-slate-900">
                                @if($order->shipping_cost > 0)
                                    Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}
                                @else
                                    <span class="text-emerald-600">Gratis</span>
                                @endif
                            </span>
                        </div>
                        <div class="flex items-center justify-between border-t border-slate-200 pt-2">
                            <span class="text-base font-bold text-slate-900">Total</span>
                            <span class="text-xl font-bold text-emerald-600">
                                Rp {{ number_format($order->total_amount + ($order->shipping_cost ?? 0), 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Order Timeline --}}
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center gap-3">
                    <div class="rounded-xl bg-slate-100 p-2.5 text-slate-600">
                        <i class="fa-solid fa-clock-rotate-left text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Riwayat Pesanan</h3>
                        <p class="text-xs text-slate-500">{{ $order->statusLogs->count() }} perubahan status</p>
                    </div>
                </div>

                <x-order-timeline :order="$order" />
            </div>

            {{-- Customer Notes --}}
            @if($order->notes)
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="rounded-xl bg-blue-100 p-2.5 text-blue-600">
                            <i class="fa-solid fa-comment text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Catatan Pelanggan</h3>
                        </div>
                    </div>
                    <div class="rounded-xl bg-blue-50 p-4">
                        <p class="text-sm text-slate-700">{{ $order->notes }}</p>
                    </div>
                </div>
            @endif

            {{-- Internal Notes (Admin Notes) --}}
            @if($order->admin_notes)
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="rounded-xl bg-amber-100 p-2.5 text-amber-600">
                            <i class="fa-solid fa-lock text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Catatan Internal</h3>
                            <p class="text-xs text-slate-500">Hanya terlihat oleh admin dan staff</p>
                        </div>
                    </div>
                    <div class="rounded-xl bg-amber-50 p-4">
                        <p class="text-sm text-slate-700">{{ $order->admin_notes }}</p>
                    </div>
                </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Customer Information --}}
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center gap-3">
                    <div class="rounded-xl bg-slate-100 p-2.5 text-slate-600">
                        <i class="fa-solid fa-user text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Informasi Pelanggan</h3>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <p class="text-xs text-slate-500">Nama</p>
                        <p class="font-medium text-slate-900">{{ $order->customer_name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">Telepon</p>
                        <p class="font-medium text-slate-900">{{ $order->customer_phone ?? '-' }}</p>
                    </div>
                    @if($order->user)
                        <div>
                            <p class="text-xs text-slate-500">Akun</p>
                            <p class="font-medium text-slate-900">{{ $order->user->name }}</p>
                            <p class="text-xs text-slate-500">{{ $order->user->email }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Shipping Address --}}
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center gap-3">
                    <div class="rounded-xl bg-violet-100 p-2.5 text-violet-600">
                        <i class="fa-solid fa-map-marker-alt text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Alamat Pengiriman</h3>
                    </div>
                </div>

                @if($order->customer_address)
                    <div class="rounded-xl bg-slate-50 p-4">
                        <p class="text-sm text-slate-700 whitespace-pre-line">{{ $order->customer_address }}</p>
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-slate-200 p-4 text-center">
                        <i class="fa-solid fa-map-pin text-2xl text-slate-300 mb-2"></i>
                        <p class="text-sm text-slate-500">Tidak ada alamat pengiriman</p>
                    </div>
                @endif

                {{-- Preferred Delivery Date --}}
                @if($order->preferred_delivery_date)
                    <div class="mt-4">
                        <p class="text-xs text-slate-500">Tanggal Pengiriman Diinginkan</p>
                        <p class="font-medium text-slate-900">
                            {{ $order->preferred_delivery_date->translatedFormat('d M Y') }}
                        </p>
                    </div>
                @endif

                {{-- Pickup Location --}}
                @if($order->pickup_location)
                    <div class="mt-4">
                        <p class="text-xs text-slate-500">Lokasi Penjemputan</p>
                        <p class="font-medium text-slate-900">{{ $order->pickup_location }}</p>
                    </div>
                @endif
            </div>

            {{-- Payment Information --}}
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center gap-3">
                    <div class="rounded-xl bg-green-100 p-2.5 text-green-600">
                        <i class="fa-solid fa-credit-card text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Informasi Pembayaran</h3>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <p class="text-xs text-slate-500">Status Pembayaran</p>
                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $this->getPaymentBadgeClass($order->payment_status_enum?->value) }}">
                            {{ $order->payment_status_label ?? $order->payment_status_enum?->label() ?? 'Menunggu' }}
                        </span>
                    </div>

                    @if($order->payment)
                        <div>
                            <p class="text-xs text-slate-500">Metode Pembayaran</p>
                            <p class="font-medium text-slate-900">
                                {{ $order->payment->payment_method ?? '-' }}
                            </p>
                        </div>
                        @if($order->payment->payment_date)
                            <div>
                                <p class="text-xs text-slate-500">Tanggal Pembayaran</p>
                                <p class="font-medium text-slate-900">
                                    {{ \Carbon\Carbon::parse($order->payment->payment_date)->translatedFormat('d M Y, H:i') }}
                                </p>
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Assignment Information --}}
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center gap-3">
                    <div class="rounded-xl bg-emerald-100 p-2.5 text-emerald-600">
                        <i class="fa-solid fa-user-check text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Informasi Tugas</h3>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <p class="text-xs text-slate-500">Ditugaskan Kepada</p>
                        @if($order->assignedStaff)
                            <div class="flex items-center gap-2 mt-1">
                                <div class="flex size-6 items-center justify-center rounded-full bg-emerald-100 text-[10px] font-bold text-emerald-700">
                                    {{ strtoupper(substr($order->assignedStaff->name, 0, 1)) }}
                                </div>
                                <p class="font-medium text-slate-900">{{ $order->assignedStaff->name }}</p>
                            </div>
                        @else
                            <p class="font-medium text-slate-400">Belum ditugaskan</p>
                        @endif
                    </div>

                    <div>
                        <p class="text-xs text-slate-500">Ditugaskan Oleh</p>
                        @if($order->assignedByUser)
                            <div class="flex items-center gap-2 mt-1">
                                <div class="flex size-6 items-center justify-center rounded-full bg-slate-100 text-[10px] font-bold text-slate-600">
                                    {{ strtoupper(substr($order->assignedByUser->name, 0, 1)) }}
                                </div>
                                <p class="font-medium text-slate-900">{{ $order->assignedByUser->name }}</p>
                            </div>
                        @else
                            <p class="font-medium text-slate-400">-</p>
                        @endif
                    </div>

                    @if($order->assigned_at)
                        <div>
                            <p class="text-xs text-slate-500">Tanggal Penugasan</p>
                            <p class="font-medium text-slate-900">
                                {{ $order->assigned_at->translatedFormat('d M Y, H:i') }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Shipping Information --}}
            @if($order->tracking_number || $order->shipping_carrier || $order->delivery_started_at)
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="rounded-xl bg-purple-100 p-2.5 text-purple-600">
                            <i class="fa-solid fa-truck text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Informasi Pengiriman</h3>
                            @if($order->status === 'shipped')
                                <span class="inline-flex items-center rounded-full bg-purple-100 px-2 py-0.5 text-xs font-medium text-purple-700 mt-0.5">
                                    <i class="fa-solid fa-circle text-[6px] mr-1 animate-pulse"></i>
                                    Sedang Dikirim
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-4">
                        @if($order->courier_name)
                            <div>
                                <p class="text-xs text-slate-500">Nama Kurir</p>
                                <p class="font-medium text-slate-900">{{ $order->courier_name }}</p>
                            </div>
                        @endif

                        @if($order->vehicle_info)
                            <div>
                                <p class="text-xs text-slate-500">Kendaraan</p>
                                <p class="font-medium text-slate-900">{{ $order->vehicle_info }}</p>
                            </div>
                        @endif

                        @if($order->shipping_carrier)
                            <div>
                                <p class="text-xs text-slate-500">Kurir/Pengirim</p>
                                <p class="font-medium text-slate-900">{{ $order->shipping_carrier }}</p>
                            </div>
                        @endif

                        @if($order->tracking_number)
                            <div>
                                <p class="text-xs text-slate-500">Nomor Resi</p>
                                <p class="font-medium text-slate-900">{{ $order->tracking_number }}</p>
                            </div>
                        @endif

                        @if($order->delivery_started_at)
                            <div>
                                <p class="text-xs text-slate-500">Pengiriman Dimulai</p>
                                <p class="font-medium text-slate-900">
                                    {{ $order->delivery_started_at->translatedFormat('d M Y, H:i') }}
                                </p>
                            </div>
                        @endif

                        @if($order->estimated_delivery_time)
                            <div class="rounded-lg bg-violet-50 p-3 border border-violet-100">
                                <p class="text-xs text-violet-500">Estimasi Tiba</p>
                                <p class="font-semibold text-violet-800">
                                    {{ $this->formattedEstimatedDelivery }}
                                </p>
                            </div>
                        @endif

                        @if($order->delivery_notes)
                            <div>
                                <p class="text-xs text-slate-500">Catatan Pengiriman</p>
                                <p class="font-medium text-slate-900 whitespace-pre-line">{{ $order->delivery_notes }}</p>
                            </div>
                        @endif

                        @if($order->shipped_at && !$order->delivery_started_at)
                            <div>
                                <p class="text-xs text-slate-500">Tanggal Dikirim</p>
                                <p class="font-medium text-slate-900">
                                    {{ $order->shipped_at->translatedFormat('d M Y, H:i') }}
                                </p>
                            </div>
                        @endif

                        @if($order->delivered_at)
                            <div>
                                <p class="text-xs text-slate-500">Tanggal Diterima</p>
                                <p class="font-medium text-slate-900">
                                    {{ $order->delivered_at->translatedFormat('d M Y, H:i') }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Delivery Proof Section --}}
            @if($order->status === 'delivered' && $order->hasDeliveryProof())
                <div class="rounded-xl border border-emerald-200 bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="rounded-xl bg-emerald-100 p-2.5 text-emerald-600">
                            <i class="fa-solid fa-clipboard-check text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Bukti Pengiriman</h3>
                        </div>
                    </div>

                    <div class="space-y-4">
                        {{-- Delivery Photo --}}
                        @if($order->delivery_photo)
                            <div>
                                <p class="text-xs text-slate-500 mb-2">Foto Bukti</p>
                                <a href="{{ $order->getDeliveryPhotoUrl() }}"
                                   target="_blank"
                                   class="block overflow-hidden rounded-xl border border-slate-200 hover:border-emerald-300 transition-colors">
                                    <img src="{{ $order->getDeliveryPhotoUrl() }}"
                                         alt="Bukti Pengiriman"
                                         class="max-h-40 w-full object-cover" />
                                </a>
                            </div>
                        @endif

                        {{-- Receiver Name --}}
                        @if($order->receiver_name)
                            <div>
                                <p class="text-xs text-slate-500">Nama Penerima</p>
                                <p class="font-medium text-slate-900">{{ $order->receiver_name }}</p>
                            </div>
                        @endif

                        {{-- Delivery Completed Time --}}
                        @if($order->delivery_completed_at)
                            <div>
                                <p class="text-xs text-slate-500">Waktu Penyelesaian</p>
                                <p class="font-medium text-slate-900">
                                    {{ $order->delivery_completed_at->translatedFormat('d M Y, H:i') }}
                                </p>
                            </div>
                        @endif

                        {{-- Delivery Note --}}
                        @if($order->delivery_note)
                            <div>
                                <p class="text-xs text-slate-500">Catatan</p>
                                <p class="font-medium text-slate-900 whitespace-pre-line">{{ $order->delivery_note }}</p>
                            </div>
                        @endif

                        {{-- GPS Location --}}
                        @if($order->hasGpsLocation())
                            <div>
                                <p class="text-xs text-slate-500 mb-1">Lokasi GPS</p>
                                <div class="rounded-lg bg-slate-50 p-3">
                                    <p class="text-xs text-slate-600 mb-2">
                                        <i class="fa-solid fa-map-pin mr-1 text-emerald-500"></i>
                                        {{ number_format($order->delivery_latitude, 6) }}, {{ number_format($order->delivery_longitude, 6) }}
                                    </p>
                                    <a href="https://www.google.com/maps?q={{ $order->delivery_latitude }},{{ $order->delivery_longitude }}"
                                       target="_blank"
                                       class="inline-flex items-center gap-1 text-xs font-medium text-emerald-600 hover:text-emerald-700">
                                        <i class="fa-solid fa-external-link-alt"></i>
                                        Lihat di Google Maps
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Completion Section (for completed orders) --}}
            @if($order->status === 'completed')
                <div class="rounded-xl border border-indigo-200 bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="rounded-xl bg-indigo-100 p-2.5 text-indigo-600">
                            <i class="fa-solid fa-flag-checkered text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Informasi Penyelesaian</h3>
                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700 mt-0.5">
                                <i class="fa-solid fa-check-circle mr-1"></i>
                                Pesanan Selesai
                            </span>
                        </div>
                    </div>

                    <div class="space-y-4">
                        @if($order->completed_at)
                            <div>
                                <p class="text-xs text-slate-500">Waktu Penyelesaian</p>
                                <p class="font-medium text-slate-900">
                                    {{ $order->completed_at->translatedFormat('d M Y, H:i') }}
                                </p>
                            </div>
                        @endif

                        <div>
                            <p class="text-xs text-slate-500">Metode</p>
                            <div class="flex items-center gap-2 mt-1">
                                @if($order->completed_automatically)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
                                        <i class="fa-solid fa-robot"></i>
                                        Otomatis
                                    </span>
                                @elseif($order->completed_by_customer)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                        <i class="fa-solid fa-user"></i>
                                        Pelanggan
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                        <i class="fa-solid fa-hand"></i>
                                        Manual
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Start Delivery Modal --}}
@livewire(\App\Livewire\Staff\StartDelivery::class)

{{-- Complete Delivery Modal --}}
@livewire(\App\Livewire\Staff\CompleteDelivery::class)
