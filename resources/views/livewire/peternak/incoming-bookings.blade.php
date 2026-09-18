<div class="space-y-6" wire:poll.5s="refreshBooking">
    {{-- Header --}}
    <div>
        <h2 class="text-xl font-bold text-slate-900">Booking Masuk</h2>
        <p class="mt-1 text-sm text-slate-500">Kelola booking ternak dari pelanggan</p>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
        <div class="rounded-xl border border-slate-100 bg-white p-4">
            <p class="text-sm text-slate-500">Total Booking</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $stats['total'] }}</p>
        </div>
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
            <p class="text-sm text-amber-700">Menunggu</p>
            <p class="mt-1 text-2xl font-bold text-amber-600">{{ $stats['pending'] }}</p>
        </div>
        <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
            <p class="text-sm text-blue-700">Survei Dijadwalkan</p>
            <p class="mt-1 text-2xl font-bold text-blue-600">{{ $stats['survey_scheduled'] ?? 0 }}</p>
        </div>
        <div class="rounded-xl border border-green-200 bg-green-50 p-4">
            <p class="text-sm text-green-700">Disetujui</p>
            <p class="mt-1 text-2xl font-bold text-green-600">{{ $stats['approved'] }}</p>
        </div>
        <div class="rounded-xl border border-violet-200 bg-violet-50 p-4">
            <p class="text-sm text-violet-700">Siap Dikirim</p>
            <p class="mt-1 text-2xl font-bold text-violet-600">{{ $stats['ready_for_delivery'] ?? 0 }}</p>
        </div>
    </div>

    {{-- Workflow Guide --}}
    <div class="rounded-xl border border-slate-200 bg-gradient-to-r from-slate-50 to-slate-100 p-4">
        <p class="text-sm font-semibold text-slate-700 mb-3">Alur Kerja Booking:</p>
        <div class="flex items-center gap-2 text-xs text-slate-600 overflow-x-auto pb-1">
            <span class="flex items-center gap-1 shrink-0">
                <span
                    class="w-5 h-5 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center font-bold">1</span>
                Review
            </span>
            <i class="fa-solid fa-arrow-right text-slate-400 shrink-0"></i>
            <span class="flex items-center gap-1 shrink-0">
                <span
                    class="w-5 h-5 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold">2</span>
                Survei
            </span>
            <i class="fa-solid fa-arrow-right text-slate-400 shrink-0"></i>
            <span class="flex items-center gap-1 shrink-0">
                <span
                    class="w-5 h-5 rounded-full bg-green-100 text-green-700 flex items-center justify-center font-bold">3</span>
                Setuju
            </span>
            <i class="fa-solid fa-arrow-right text-slate-400 shrink-0"></i>
            <span class="flex items-center gap-1 shrink-0">
                <span
                    class="w-5 h-5 rounded-full bg-violet-100 text-violet-700 flex items-center justify-center font-bold">4</span>
                Siap Kirim
            </span>
            <i class="fa-solid fa-arrow-right text-slate-400 shrink-0"></i>
            <span class="flex items-center gap-1 shrink-0">
                <span
                    class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">5</span>
                Selesai
            </span>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col gap-4 rounded-xl border border-slate-100 bg-white p-4 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <input type="text" wire:model.live.debounce.300ms="searchQuery"
                placeholder="Cari kode booking atau nama..."
                class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2 pl-10 pr-4 text-sm placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20" />
            <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
        </div>

        <select wire:model.live="statusFilter"
            class="cursor-pointer rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
            <option value="all">Semua Status</option>
            <option value="pending">Menunggu</option>
            <option value="survey_scheduled">Survei Dijadwalkan</option>
            <option value="approved">Disetujui</option>
            <option value="ready_for_delivery">Siap Dikirim</option>
            <option value="rejected">Ditolak</option>
        </select>
    </div>

    {{-- Bookings List --}}
    <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                        <th class="px-4 py-3 font-semibold">Kode</th>
                        <th class="px-4 py-3 font-semibold">Tanggal</th>
                        <th class="px-4 py-3 font-semibold">Pelanggan</th>
                        <th class="px-4 py-3 font-semibold">Ternak</th>
                        <th class="px-4 py-3 font-semibold">Total</th>
                        <th class="px-4 py-3 font-semibold">Status</th>
                        <th class="px-4 py-3 font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($bookings as $booking)
                        <tr class="cursor-pointer transition hover:bg-slate-50"
                            wire:click="selectBooking({{ $booking->id }})">
                            <td class="px-4 py-3">
                                <span class="font-semibold text-slate-900">{{ $booking->order_number }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-600">
                                {{ $booking->placed_at?->format('d M Y') ?? $booking->created_at->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3">
                                <div>
                                    <p class="text-sm font-medium text-slate-900">{{ $booking->customer_name }}</p>
                                    <p class="text-xs text-slate-500">{{ $booking->customer_phone }}</p>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-sm text-slate-900">
                                    {{ $booking->items->pluck('product_name')->filter()->implode(', ') ?: '-' }}
                                </p>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm font-semibold text-emerald-600">
                                    Rp {{ number_format($booking->total_amount, 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $booking->booking_status_badge_class }}">
                                    {{ $booking->booking_status_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <button type="button"
                                    class="cursor-pointer rounded-lg p-2 text-emerald-600 transition hover:bg-emerald-50"
                                    title="Lihat Detail">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center">
                                <i class="fa-regular fa-calendar-check text-5xl text-slate-200"></i>
                                <p class="mt-4 text-slate-500">Belum ada booking masuk</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Booking Detail Modal --}}
    @if ($selectedBooking)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4"
            wire:click.self="closeDetail">
            <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white shadow-xl" wire:click.stop>
                <div
                    class="sticky top-0 flex items-center justify-between border-b border-slate-100 bg-white px-6 py-4">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Detail Booking</h3>
                        <p class="text-sm text-slate-500">{{ $selectedBooking->order_number }}</p>
                    </div>
                    <button type="button" wire:click="closeDetail"
                        class="cursor-pointer rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="p-6">
                    {{-- Status --}}
                    <div class="mb-6">
                        <span
                            class="rounded-lg border px-4 py-2 text-sm font-semibold {{ $selectedBooking->booking_status_badge_class }}">
                            {{ $selectedBooking->booking_status_label }}
                        </span>
                    </div>

                    {{-- Quick Actions based on status --}}
                    @if ($selectedBooking->status === 'pending')
                        <div class="mb-6 grid grid-cols-2 gap-3">
                            <button type="button" wire:click="openSurveyModal({{ $selectedBooking->id }})"
                                wire:loading.attr="disabled"
                                class="cursor-pointer flex items-center justify-center gap-2 rounded-lg bg-blue-600 py-3 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fa-solid fa-calendar-check" wire:loading.remove wire:target="openSurveyModal,approveBooking"></i>
                                <i class="fa-solid fa-spinner fa-spin" wire:loading wire:target="openSurveyModal"></i>
                                Jadwalkan Survei
                            </button>
                            <button type="button" wire:click="approveBooking({{ $selectedBooking->id }})"
                                wire:loading.attr="disabled"
                                class="cursor-pointer flex items-center justify-center gap-2 rounded-lg bg-green-600 py-3 text-sm font-semibold text-white transition hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fa-solid fa-check" wire:loading.remove wire:target="openSurveyModal,approveBooking"></i>
                                <i class="fa-solid fa-spinner fa-spin" wire:loading wire:target="approveBooking"></i>
                                Setujui Langsung
                            </button>
                        </div>
                    @endif

                    @if (in_array($selectedBooking->status, ['approved', 'survey_scheduled']))
                        <div class="mb-6">
                            <button type="button" wire:click="markReadyForDelivery({{ $selectedBooking->id }})"
                                wire:loading.attr="disabled"
                                class="cursor-pointer flex w-full items-center justify-center gap-2 rounded-lg bg-violet-600 py-3 text-sm font-semibold text-white transition hover:bg-violet-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fa-solid fa-truck-fast" wire:loading.remove wire:target="markReadyForDelivery"></i>
                                <i class="fa-solid fa-spinner fa-spin" wire:loading wire:target="markReadyForDelivery"></i>
                                Tandai Siap Kirim/Ambil
                            </button>
                        </div>
                    @endif

                    {{-- Customer Info --}}
                    <div class="mb-6 rounded-xl border border-slate-100 bg-slate-50 p-4">
                        <h4 class="mb-3 text-sm font-semibold text-slate-900">Informasi Pelanggan</h4>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <p class="text-xs text-slate-500">Nama Lengkap</p>
                                <p class="text-sm font-medium text-slate-900">{{ $selectedBooking->customer_name }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500">No. WhatsApp</p>
                                <p class="text-sm font-medium text-slate-900">
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $selectedBooking->customer_phone) }}"
                                        target="_blank" class="text-emerald-600 hover:underline">
                                        {{ $selectedBooking->customer_phone ?? '-' }}
                                    </a>
                                </p>
                            </div>
                            <div class="sm:col-span-2">
                                <p class="text-xs text-slate-500">Alamat</p>
                                <p class="text-sm font-medium text-slate-900">
                                    {{ $selectedBooking->customer_address ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Booked Livestock --}}
                    <div class="mb-6">
                        <h4 class="mb-3 text-sm font-semibold text-slate-900">Ternak yang Dibooking</h4>
                        <div class="space-y-3">
                            @foreach ($selectedBooking->items as $item)
                                <div class="rounded-xl border border-slate-100 bg-white p-4">
                                    <div class="flex items-start gap-4">
                                        @if ($item->product?->image)
                                            <img src="{{ Storage::url($item->product->image) }}" alt=""
                                                class="size-20 rounded-lg object-cover">
                                        @else
                                            <div
                                                class="flex size-20 items-center justify-center rounded-lg bg-slate-100">
                                                <i class="fa-solid fa-cow text-2xl text-slate-400"></i>
                                            </div>
                                        @endif
                                        <div class="flex-1">
                                            <p class="font-semibold text-slate-900">{{ $item->product_name }}</p>
                                            <p class="mt-1 text-xl font-bold text-emerald-600">Rp
                                                {{ number_format($item->unit_price, 0, ',', '.') }}</p>

                                            @if ($item->product)
                                                <div
                                                    class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500">
                                                    @if ($item->product->breed)
                                                        <span><i
                                                                class="fa-solid fa-tag mr-1"></i>{{ $item->product->breed }}</span>
                                                    @endif
                                                    @if ($item->product->weight)
                                                        <span><i
                                                                class="fa-solid fa-weight-scale mr-1"></i>{{ $item->product->weight }}
                                                            Kg</span>
                                                    @endif
                                                    @if ($item->product->age)
                                                        <span><i
                                                                class="fa-solid fa-calendar mr-1"></i>{{ $item->product->age }}</span>
                                                    @endif
                                                    @if ($item->product->origin)
                                                        <span><i
                                                                class="fa-solid fa-location-dot mr-1"></i>{{ $item->product->origin }}</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Total --}}
                    <div
                        class="mb-6 flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                        <span class="text-sm font-medium text-slate-700">Total Booking</span>
                        <span class="text-xl font-bold text-emerald-600">Rp
                            {{ number_format($selectedBooking->total_amount, 0, ',', '.') }}</span>
                    </div>

                    {{-- Notes --}}
                    @if ($selectedBooking->notes)
                        <div class="mb-6 rounded-lg border border-blue-100 bg-blue-50 p-4">
                            <p class="text-xs text-slate-500">Catatan dari Pelanggan</p>
                            <p class="mt-1 text-sm text-slate-700">{{ $selectedBooking->notes }}</p>
                        </div>
                    @endif

                    {{-- Survey Info --}}
                    @if ($selectedBooking->admin_notes && str_contains($selectedBooking->admin_notes, 'Survei'))
                        <div class="mb-6 rounded-lg border border-blue-200 bg-blue-50 p-4">
                            <p class="text-xs text-blue-600 font-semibold"><i
                                    class="fa-solid fa-calendar-check mr-1"></i>Info Survei</p>
                            <p class="mt-1 text-sm text-blue-800">
                                {{ Str::of($selectedBooking->admin_notes)->explode("\n")->last() }}</p>
                        </div>
                    @endif

                    {{-- Timeline --}}
                    <div class="mb-6">
                        <h4 class="mb-3 text-sm font-semibold text-slate-900">Riwayat Booking</h4>
                        <x-order-timeline :order="$selectedBooking" title="" />
                    </div>

                    {{-- Action Buttons --}}
                    @if ($selectedBooking->status === 'pending')
                        <div class="flex gap-3">
                            <button type="button" wire:click="rejectBooking({{ $selectedBooking->id }})"
                                class="cursor-pointer flex-1 rounded-lg border border-red-200 bg-white py-3 text-sm font-semibold text-red-600 transition hover:bg-red-50">
                                <i class="fa-solid fa-xmark mr-2"></i>Tolak Booking
                            </button>
                            <button type="button" wire:click="openSurveyModal({{ $selectedBooking->id }})"
                                class="cursor-pointer flex-1 rounded-lg bg-blue-600 py-3 text-sm font-semibold text-white transition hover:bg-blue-700">
                                <i class="fa-solid fa-calendar-check mr-2"></i>Jadwalkan Survei
                            </button>
                        </div>
                    @else
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 text-center">
                            <i class="fa-solid fa-circle-info text-slate-400 mr-2"></i>
                            <span class="text-sm text-slate-600">
                                @if ($selectedBooking->status === 'approved')
                                    Booking ini telah disetujui. Hubungi pelanggan untuk langkah selanjutnya.
                                @elseif($selectedBooking->status === 'survey_scheduled')
                                    Survei telah dijadwalkan. Hubungi pelanggan untuk konfirmasi.
                                @elseif($selectedBooking->status === 'ready_for_delivery')
                                    Ternak siap untuk dikirim/diambil!
                                @elseif($selectedBooking->status === 'rejected')
                                    Booking ini telah ditolak.
                                @else
                                    Booking telah diproses.
                                @endif
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Survey Scheduling Modal --}}
    @if ($showSurveyModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/50 p-4"
            wire:click.self="closeSurveyModal">
            <div class="w-full max-w-md rounded-2xl bg-white shadow-xl" wire:click.stop>
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <h3 class="text-lg font-bold text-slate-900">
                        <i class="fa-solid fa-calendar-check text-blue-600 mr-2"></i>
                        Jadwalkan Survei
                    </h3>
                    <button type="button" wire:click="closeSurveyModal"
                        class="cursor-pointer rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    <p class="text-sm text-slate-600">
                        Pilih tanggal untuk melakukan survei lapangan sebelum booking disetujui.
                    </p>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Survei</label>
                        <input type="date" wire:model.live="surveyDate"
                            min="{{ now()->tomorrow()->format('Y-m-d') }}"
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Catatan (opsional)</label>
                        <textarea wire:model.live="surveyNotes" rows="3"
                            placeholder="Catatan untuk survei, misalnya lokasi, akses jalan, dll..."
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"></textarea>
                    </div>
                </div>

                <div class="flex gap-3 border-t border-slate-100 px-6 py-4">
                    <button type="button" wire:click="closeSurveyModal"
                        class="cursor-pointer flex-1 rounded-lg border border-slate-200 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="button" wire:click="scheduleSurvey"
                        class="cursor-pointer flex-1 rounded-lg bg-blue-600 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700">
                        <i class="fa-solid fa-check mr-2"></i>Jadwalkan
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
