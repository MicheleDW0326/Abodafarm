<div class="space-y-6">
    {{-- Header --}}
    <div>
        <h2 class="text-xl font-bold text-slate-900">Booking Saya</h2>
        <p class="mt-1 text-sm text-slate-500">Lacak status booking ternak Anda</p>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <article class="rounded-xl border border-slate-100 bg-white p-4">
            <p class="text-sm text-slate-500">Total Booking</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $stats['total'] }}</p>
        </article>
        <article class="rounded-xl border border-slate-100 bg-white p-4">
            <p class="text-sm text-slate-500">Menunggu</p>
            <p class="mt-1 text-2xl font-bold text-amber-600">{{ $stats['pending'] }}</p>
        </article>
        <article class="rounded-xl border border-slate-100 bg-white p-4">
            <p class="text-sm text-slate-500">Disetujui</p>
            <p class="mt-1 text-2xl font-bold text-emerald-600">{{ $stats['approved'] }}</p>
        </article>
        <article class="rounded-xl border border-slate-100 bg-white p-4">
            <p class="text-sm text-slate-500">Ditolak</p>
            <p class="mt-1 text-2xl font-bold text-red-600">{{ $stats['rejected'] }}</p>
        </article>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col gap-4 rounded-xl border border-slate-100 bg-white p-4 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <input
                type="text"
                wire:model.live.debounce.300ms="searchQuery"
                placeholder="Cari kode booking..."
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
            <option value="approved">Disetujui</option>
            <option value="rejected">Ditolak</option>
        </select>
    </div>

    {{-- Bookings List --}}
    <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                        <th class="px-4 py-3 font-semibold">Kode Booking</th>
                        <th class="px-4 py-3 font-semibold">Tanggal</th>
                        <th class="px-4 py-3 font-semibold">Ternak</th>
                        <th class="px-4 py-3 font-semibold">Peternak</th>
                        <th class="px-4 py-3 font-semibold">Total</th>
                        <th class="px-4 py-3 font-semibold">Status</th>
                        <th class="px-4 py-3 font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($bookings as $booking)
                        <tr class="cursor-pointer transition hover:bg-slate-50" wire:click="selectBooking({{ $booking->id }})">
                            <td class="px-4 py-3">
                                <span class="font-semibold text-slate-900">{{ $booking->order_number }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-600">
                                {{ $booking->created_at->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-sm text-slate-900">
                                    {{ $booking->items->pluck('product_name')->filter()->implode(', ') ?: '-' }}
                                </p>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $owner = $booking->items->first()?->product?->owner;
                                @endphp
                                @if($owner)
                                    <p class="text-sm font-medium text-slate-900">{{ $owner->name }}</p>
                                @else
                                    <span class="text-sm text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm font-semibold text-emerald-600">
                                    Rp {{ number_format($booking->total_amount, 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $booking->status_badge_class }}">
                                    {{ $booking->booking_status_label }}
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
                                <i class="fa-regular fa-calendar-check text-5xl text-slate-200"></i>
                                <p class="mt-4 text-slate-500">Belum ada booking</p>
                                <a href="/" class="mt-2 inline-block text-sm text-emerald-600 hover:underline">
                                    Lihat Ternak Tersedia
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Booking Detail Modal --}}
    @if($selectedBooking)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4" wire:click.self="closeDetail">
            <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white shadow-xl" wire:click.stop>
                <div class="sticky top-0 flex items-center justify-between border-b border-slate-100 bg-white px-6 py-4">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Detail Booking</h3>
                        <p class="text-sm text-slate-500">{{ $selectedBooking->order_number }}</p>
                    </div>
                    <button type="button" wire:click="closeDetail" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="p-6">
                    {{-- Status --}}
                    <div class="mb-6">
                        <span class="rounded-lg border px-4 py-2 text-sm font-semibold {{ $selectedBooking->status_badge_class }}">
                            {{ $selectedBooking->booking_status_label }}
                        </span>
                    </div>

                    {{-- Booking Info --}}
                    <div class="mb-6 rounded-xl border border-slate-100 bg-slate-50 p-4">
                        <h4 class="mb-3 text-sm font-semibold text-slate-900">Informasi Booking</h4>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <p class="text-xs text-slate-500">Tanggal Booking</p>
                                <p class="text-sm font-medium text-slate-900">{{ $selectedBooking->created_at->format('d M Y, H:i') }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500">Status</p>
                                <p class="text-sm font-medium text-slate-900">{{ $selectedBooking->booking_status_label }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Booked Livestock --}}
                    <div class="mb-6">
                        <h4 class="mb-3 text-sm font-semibold text-slate-900">Ternak yang Dibooking</h4>
                        <div class="space-y-3">
                            @foreach($selectedBooking->items as $item)
                                <div class="rounded-xl border border-slate-100 bg-white p-4">
                                    <div class="flex items-start gap-4">
                                        @if($item->product?->image)
                                            <img src="{{ Storage::url($item->product->image) }}" alt="" class="size-20 rounded-lg object-cover">
                                        @else
                                            <div class="flex size-20 items-center justify-center rounded-lg bg-slate-100">
                                                <i class="fa-solid fa-cow text-2xl text-slate-400"></i>
                                            </div>
                                        @endif
                                        <div class="flex-1">
                                            <p class="font-semibold text-slate-900">{{ $item->product_name }}</p>
                                            <p class="mt-1 text-xl font-bold text-emerald-600">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</p>

                                            @if($item->product)
                                                <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500">
                                                    @if($item->product->breed)
                                                        <span><i class="fa-solid fa-tag mr-1"></i>{{ $item->product->breed }}</span>
                                                    @endif
                                                    @if($item->product->weight)
                                                        <span><i class="fa-solid fa-weight-scale mr-1"></i>{{ $item->product->weight }} Kg</span>
                                                    @endif
                                                    @if($item->product->age)
                                                        <span><i class="fa-solid fa-calendar mr-1"></i>{{ $item->product->age }}</span>
                                                    @endif
                                                </div>

                                                @php
                                                    $owner = $item->product->owner;
                                                @endphp
                                                @if($owner)
                                                    <div class="mt-2 rounded-lg bg-blue-50 p-2">
                                                        <p class="text-xs text-blue-600">
                                                            <i class="fa-solid fa-user mr-1"></i>
                                                            Peternak: {{ $owner->name }}
                                                        </p>
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Total --}}
                    <div class="mb-6 flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                        <span class="text-sm font-medium text-slate-700">Total Booking</span>
                        <span class="text-xl font-bold text-emerald-600">Rp {{ number_format($selectedBooking->total_amount, 0, ',', '.') }}</span>
                    </div>

                    {{-- Notes --}}
                    @if($selectedBooking->notes)
                        <div class="mb-6 rounded-lg border border-blue-100 bg-blue-50 p-4">
                            <p class="text-xs text-slate-500">Catatan Anda</p>
                            <p class="mt-1 text-sm text-slate-700">{{ $selectedBooking->notes }}</p>
                        </div>
                    @endif

                    {{-- Info --}}
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <div class="flex items-start gap-3">
                            <i class="fa-solid fa-circle-info text-slate-400 mt-0.5"></i>
                            <div class="text-sm text-slate-600">
                                @if($selectedBooking->status === 'pending')
                                    <p class="font-medium">Menunggu Konfirmasi</p>
                                    <p class="mt-1">Peternak sedang meninjau booking Anda. Anda akan mendapat notifikasi setelah peternak menyetujui atau menolak.</p>
                                @elseif($selectedBooking->status === 'approved')
                                    <p class="font-medium text-emerald-600">Booking Disetujui!</p>
                                    <p class="mt-1">Silakan hubungi peternak melalui WhatsApp untuk koordinasi selanjutnya.</p>
                                @elseif($selectedBooking->status === 'rejected')
                                    <p class="font-medium text-red-600">Booking Ditolak</p>
                                    <p class="mt-1">Mohon maaf, peternak tidak dapat memenuhi booking ini. Anda dapat mencoba booking ternak lain.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
