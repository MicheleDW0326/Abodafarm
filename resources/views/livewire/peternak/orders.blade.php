<div class="space-y-6">
    {{-- Header --}}
    <div>
        <h2 class="text-xl font-bold text-slate-900">Pesanan Masuk</h2>
        <p class="mt-1 text-sm text-slate-500">Lihat dan kelola pesanan untuk ternak Anda</p>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <article class="rounded-xl border border-slate-100 bg-white p-4">
            <p class="text-sm text-slate-500">Total Pesanan</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $stats['total'] }}</p>
        </article>
        <article class="rounded-xl border border-slate-100 bg-white p-4">
            <p class="text-sm text-slate-500">Menunggu</p>
            <p class="mt-1 text-2xl font-bold text-amber-600">{{ $stats['pending'] }}</p>
        </article>
        <article class="rounded-xl border border-slate-100 bg-white p-4">
            <p class="text-sm text-slate-500">Diproses</p>
            <p class="mt-1 text-2xl font-bold text-blue-600">{{ $stats['processing'] }}</p>
        </article>
        <article class="rounded-xl border border-slate-100 bg-white p-4">
            <p class="text-sm text-slate-500">Dikirim</p>
            <p class="mt-1 text-2xl font-bold text-purple-600">{{ $stats['shipped'] }}</p>
        </article>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col gap-4 rounded-xl border border-slate-100 bg-white p-4 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <input
                type="text"
                wire:model.live.debounce.300ms="searchQuery"
                placeholder="Cari kode pesanan atau nama..."
                class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2 pl-10 pr-4 text-sm placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
            />
            <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
        </div>

        <select
            wire:model.live="statusFilter"
            class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
        >
            <option value="all">Semua Status</option>
            <option value="pending">Menunggu</option>
            <option value="processing">Diproses</option>
            <option value="shipped">Dikirim</option>
            <option value="delivered">Selesai</option>
            <option value="cancelled">Batal</option>
        </select>
    </div>

    {{-- Orders List --}}
    <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                        <th class="px-4 py-3 font-semibold">Kode</th>
                        <th class="px-4 py-3 font-semibold">Tanggal</th>
                        <th class="px-4 py-3 font-semibold">Pembeli</th>
                        <th class="px-4 py-3 font-semibold">Ternak</th>
                        <th class="px-4 py-3 font-semibold">Total</th>
                        <th class="px-4 py-3 font-semibold">Status</th>
                        <th class="px-4 py-3 font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($orders as $order)
                        @php
                            $statusClass = match($order->status) {
                                'pending' => 'bg-amber-50 text-amber-700',
                                'processing' => 'bg-blue-50 text-blue-700',
                                'shipped' => 'bg-purple-50 text-purple-700',
                                'delivered' => 'bg-emerald-50 text-emerald-700',
                                'cancelled' => 'bg-red-50 text-red-700',
                                default => 'bg-slate-50 text-slate-700',
                            };
                            $statusLabel = match($order->status) {
                                'pending' => 'Menunggu',
                                'processing' => 'Diproses',
                                'shipped' => 'Dikirim',
                                'delivered' => 'Selesai',
                                'cancelled' => 'Batal',
                                default => $order->status,
                            };
                        @endphp
                        <tr class="transition hover:bg-slate-50 cursor-pointer" wire:click="selectOrder({{ $order->id }})">
                            <td class="px-4 py-3">
                                <span class="font-semibold text-slate-900">{{ $order->order_number }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-600">
                                {{ $order->placed_at?->format('d M Y') ?? '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <div>
                                    <p class="text-sm font-medium text-slate-900">{{ $order->customer_name }}</p>
                                    <p class="text-xs text-slate-500">{{ $order->customer_phone }}</p>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-sm text-slate-900">
                                    {{ $order->items->pluck('product_name')->filter()->implode(', ') }}
                                </p>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm font-semibold text-emerald-600">
                                    Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <button
                                    type="button"
                                    class="rounded-lg p-2 text-emerald-600 transition hover:bg-emerald-50"
                                    title="Lihat Detail"
                                >
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center">
                                <i class="fa-solid fa-inbox text-5xl text-slate-200"></i>
                                <p class="mt-4 text-slate-500">Belum ada pesanan</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Order Detail Modal --}}
    @if($selectedOrder)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4" wire:click.self="closeDetail">
            <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white shadow-xl" wire:click.stop>
                <div class="sticky top-0 flex items-center justify-between border-b border-slate-100 bg-white px-6 py-4">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Detail Pesanan</h3>
                        <p class="text-sm text-slate-500">{{ $selectedOrder->order_number }}</p>
                    </div>
                    <button type="button" wire:click="closeDetail" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="p-6">
                    {{-- Status --}}
                    <div class="mb-6">
                        @php
                            $statusClass = match($selectedOrder->status) {
                                'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
                                'processing' => 'bg-blue-50 text-blue-700 border-blue-200',
                                'shipped' => 'bg-purple-50 text-purple-700 border-purple-200',
                                'delivered' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'cancelled' => 'bg-red-50 text-red-700 border-red-200',
                                default => 'bg-slate-50 text-slate-700',
                            };
                            $statusLabel = match($selectedOrder->status) {
                                'pending' => 'Menunggu Konfirmasi',
                                'processing' => 'Sedang Diproses',
                                'shipped' => 'Dalam Pengiriman',
                                'delivered' => 'Selesai',
                                'cancelled' => 'Dibatalkan',
                                default => $selectedOrder->status,
                            };
                        @endphp
                        <span class="rounded-lg border px-4 py-2 text-sm font-semibold {{ $statusClass }}">
                            {{ $statusLabel }}
                        </span>
                    </div>

                    {{-- Customer Info --}}
                    <div class="mb-6 rounded-xl border border-slate-100 bg-slate-50 p-4">
                        <h4 class="mb-3 text-sm font-semibold text-slate-900">Informasi Pembeli</h4>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <p class="text-xs text-slate-500">Nama</p>
                                <p class="text-sm font-medium text-slate-900">{{ $selectedOrder->customer_name }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500">No. HP</p>
                                <p class="text-sm font-medium text-slate-900">{{ $selectedOrder->customer_phone ?? '-' }}</p>
                            </div>
                            <div class="sm:col-span-2">
                                <p class="text-xs text-slate-500">Alamat</p>
                                <p class="text-sm font-medium text-slate-900">{{ $selectedOrder->customer_address ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Order Items --}}
                    <div class="mb-6">
                        <h4 class="mb-3 text-sm font-semibold text-slate-900">Ternak yang Dipesan</h4>
                        <div class="space-y-3">
                            @foreach($selectedOrder->items as $item)
                                <div class="flex items-center justify-between rounded-lg border border-slate-100 bg-white p-3">
                                    <div class="flex items-center gap-3">
                                        @if($item->product?->image)
                                            <img src="{{ Storage::url($item->product->image) }}" alt="" class="size-12 rounded-lg object-cover">
                                        @else
                                            <div class="flex size-12 items-center justify-center rounded-lg bg-slate-100">
                                                <i class="fa-solid fa-cow text-slate-400"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <p class="font-medium text-slate-900">{{ $item->product_name }}</p>
                                            <p class="text-xs text-slate-500">Qty: {{ $item->quantity }}</p>
                                        </div>
                                    </div>
                                    <p class="font-semibold text-emerald-600">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Total --}}
                    <div class="flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                        <span class="text-sm font-medium text-slate-700">Total Pembayaran</span>
                        <span class="text-xl font-bold text-emerald-600">Rp {{ number_format($selectedOrder->total_amount, 0, ',', '.') }}</span>
                    </div>

                    {{-- Notes --}}
                    @if($selectedOrder->notes)
                        <div class="mt-4">
                            <p class="text-xs text-slate-500">Catatan Pesanan</p>
                            <p class="text-sm text-slate-700">{{ $selectedOrder->notes }}</p>
                        </div>
                    @endif

                    {{-- Timeline --}}
                    <div class="mt-6">
                        <h4 class="mb-3 text-sm font-semibold text-slate-900">Riwayat Pesanan</h4>
                        <x-order-timeline :order="$selectedOrder" />
                    </div>

                    {{-- Info Note --}}
                    <div class="mt-6 rounded-lg border border-blue-100 bg-blue-50 p-4">
                        <div class="flex items-start gap-3">
                            <i class="fa-solid fa-circle-info text-blue-500 mt-0.5"></i>
                            <div>
                                <p class="text-sm font-medium text-blue-900">Informasi</p>
                                <p class="mt-1 text-xs text-blue-700">
                                    Perubahan status pesanan dilakukan oleh staf/admin. Hubungi kami jika ada pertanyaan.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
