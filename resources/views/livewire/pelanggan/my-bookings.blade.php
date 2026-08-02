<div class="space-y-6" wire:poll.5s="refreshBooking">
    {{-- Header --}}
    <div class="rounded-2xl border border-slate-100 bg-gradient-to-r from-amber-500 to-orange-600 p-6 shadow-lg">
        <div class="flex items-center gap-4">
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-white/20 text-xl text-white">
                <i class="fa-solid fa-cow"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-white">Booking Ternak</h2>
                <p class="mt-0.5 text-amber-100">Lacak status booking ternak Anda dari survei hingga selesai</p>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-6">
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
            wire:click="$set('statusFilter', 'survey_scheduled')"
            class="rounded-xl border p-3 text-left transition @if($statusFilter === 'survey_scheduled') border-blue-500 bg-blue-50 @else border-slate-200 bg-white hover:border-slate-300 @endif"
        >
            <p class="text-lg font-bold text-blue-600">{{ $stats['survey_scheduled'] ?? 0 }}</p>
            <p class="text-xs text-slate-500">Survei</p>
        </button>
        <button
            wire:click="$set('statusFilter', 'approved')"
            class="rounded-xl border p-3 text-left transition @if($statusFilter === 'approved') border-green-500 bg-green-50 @else border-slate-200 bg-white hover:border-slate-300 @endif"
        >
            <p class="text-lg font-bold text-green-600">{{ $stats['approved'] }}</p>
            <p class="text-xs text-slate-500">Disetujui</p>
        </button>
        <button
            wire:click="$set('statusFilter', 'ready_for_delivery')"
            class="rounded-xl border p-3 text-left transition @if($statusFilter === 'ready_for_delivery') border-violet-500 bg-violet-50 @else border-slate-200 bg-white hover:border-slate-300 @endif"
        >
            <p class="text-lg font-bold text-violet-600">{{ $stats['ready_for_delivery'] ?? 0 }}</p>
            <p class="text-xs text-slate-500">Siap Kirim</p>
        </button>
        <button
            wire:click="$set('statusFilter', 'rejected')"
            class="rounded-xl border p-3 text-left transition @if($statusFilter === 'rejected') border-red-500 bg-red-50 @else border-slate-200 bg-white hover:border-slate-300 @endif"
        >
            <p class="text-lg font-bold text-red-600">{{ $stats['rejected'] }}</p>
            <p class="text-xs text-slate-500">Ditolak</p>
        </button>
    </div>

    {{-- Workflow Guide --}}
    <div class="rounded-xl border border-amber-100 bg-gradient-to-r from-amber-50 to-orange-50 p-4">
        <p class="text-sm font-semibold text-amber-800 mb-3">Alur Booking:</p>
        <div class="flex items-center gap-2 text-xs text-amber-700 overflow-x-auto pb-1">
            <span class="flex items-center gap-1 shrink-0">
                <span class="w-6 h-6 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center font-bold">1</span>
                Menunggu
            </span>
            <i class="fa-solid fa-arrow-right text-amber-400 shrink-0"></i>
            <span class="flex items-center gap-1 shrink-0">
                <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold">2</span>
                Survei
            </span>
            <i class="fa-solid fa-arrow-right text-amber-400 shrink-0"></i>
            <span class="flex items-center gap-1 shrink-0">
                <span class="w-6 h-6 rounded-full bg-green-100 text-green-700 flex items-center justify-center font-bold">3</span>
                Disetujui
            </span>
            <i class="fa-solid fa-arrow-right text-amber-400 shrink-0"></i>
            <span class="flex items-center gap-1 shrink-0">
                <span class="w-6 h-6 rounded-full bg-violet-100 text-violet-700 flex items-center justify-center font-bold">4</span>
                Siap Kirim
            </span>
            <i class="fa-solid fa-arrow-right text-amber-400 shrink-0"></i>
            <span class="flex items-center gap-1 shrink-0">
                <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">5</span>
                Selesai
            </span>
        </div>
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
            <option value="survey_scheduled">Survei Dijadwalkan</option>
            <option value="approved">Disetujui</option>
            <option value="ready_for_delivery">Siap Dikirim</option>
            <option value="rejected">Ditolak</option>
        </select>
    </div>

    {{-- Bookings List --}}
    <div class="space-y-4">
        @forelse($bookings as $booking)
            @php
                $progress = $this->getBookingProgress($booking);
                $statusInfo = $this->getStatusExplanation($booking->status);
                $owner = $booking->items->first()?->product?->owner;
            @endphp

            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-xs transition hover:shadow-md">
                {{-- Booking Header --}}
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                            <i class="fa-solid fa-cow text-lg"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-slate-900">{{ $booking->order_number }}</p>
                            <p class="text-xs text-slate-500">{{ $booking->created_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="text-right">
                            <p class="text-lg font-bold text-slate-900">Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</p>
                            <p class="text-xs text-slate-500">{{ $booking->total_items }} item</p>
                        </div>
                        <span class="rounded-full px-3 py-1.5 text-xs font-semibold {{ $booking->status_badge_class }}">
                            {{ $statusInfo['title'] }}
                        </span>
                    </div>
                </div>

                {{-- Visual Progress Tracker --}}
                @if(!$progress['isRejected'] && !$progress['isCancelled'])
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
                        <div class="flex items-center gap-2">
                            @foreach($progress['steps'] as $index => $step)
                                <div class="flex flex-1 items-center">
                                    <div class="flex flex-1 items-center gap-2">
                                        <div class="h-6 w-6 rounded-full flex items-center justify-center text-xs font-bold
                                            @if($index <= $progress['currentStep'])
                                                bg-emerald-500 text-white
                                            @else
                                                bg-slate-200 text-slate-500
                                            @endif">
                                            @if($index < $progress['currentStep'])
                                                <i class="fa-solid fa-check text-[10px]"></i>
                                            @else
                                                {{ $index + 1 }}
                                            @endif
                                        </div>
                                        <span class="text-xs font-medium hidden sm:inline @if($index <= $progress['currentStep']) text-emerald-600 @else text-slate-400 @endif">
                                            {{ $step['label'] }}
                                        </span>
                                    </div>
                                    @if(!$loop->last)
                                        <div class="h-0.5 flex-1 @if($index < $progress['currentStep']) bg-emerald-400 @else bg-slate-200 @endif mx-1"></div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    {{-- Rejected/Cancelled State --}}
                    <div class="mt-4 rounded-xl bg-red-50 p-4">
                        <div class="flex items-center gap-2">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-red-100 text-red-600">
                                <i class="fa-solid fa-{{ $statusInfo['icon'] }} text-sm"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-red-800">{{ $statusInfo['title'] }}</p>
                                <p class="text-xs text-red-600">{{ $statusInfo['description'] }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Livestock Preview --}}
                @if($booking->items->count() > 0)
                    <div class="mt-4 flex items-center gap-2 border-t border-slate-100 pt-4">
                        <div class="flex -space-x-2">
                            @foreach($booking->items->take(3) as $item)
                                @if($item->product?->image)
                                    <img
                                        src="{{ Storage::url($item->product->image) }}"
                                        alt="{{ $item->product_name }}"
                                        class="h-10 w-10 rounded-lg border-2 border-white object-cover"
                                    >
                                @else
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg border-2 border-white bg-slate-100">
                                        <i class="fa-solid fa-image text-slate-400"></i>
                                    </div>
                                @endif
                            @endforeach
                            @if($booking->items->count() > 3)
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg border-2 border-white bg-slate-100 text-xs font-semibold text-slate-500">
                                    +{{ $booking->items->count() - 3 }}
                                </div>
                            @endif
                        </div>
                        <p class="text-sm text-slate-500">
                            {{ $booking->items->pluck('product_name')->filter()->implode(', ') ?: '-' }}
                        </p>
                    </div>
                @endif

                {{-- Farmer Contact --}}
                @if($owner)
                    <div class="mt-3 flex items-center justify-between rounded-lg bg-blue-50 p-3">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-user text-blue-400"></i>
                            <span class="text-sm text-blue-700">Peternak: {{ $owner->name }}</span>
                        </div>
                        @if($owner->phone || $booking->customer_phone)
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $owner->phone ?? $booking->customer_phone) }}"
                               target="_blank"
                               class="rounded-lg bg-green-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-green-600 transition">
                                <i class="fa-brands fa-whatsapp mr-1"></i>Hubungi
                            </a>
                        @endif
                    </div>
                @endif

                {{-- Actions --}}
                <div class="mt-4 flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                    <button
                        wire:click="selectBooking({{ $booking->id }})"
                        class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50"
                    >
                        <i class="fa-solid fa-eye mr-1"></i> Detail
                    </button>
                    @if($booking->status === 'pending')
                        <button
                            wire:click="cancelBooking({{ $booking->id }})"
                            wire:confirm="Yakin ingin membatalkan booking ini?"
                            class="rounded-lg border border-red-200 px-4 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50"
                        >
                            <i class="fa-solid fa-times mr-1"></i> Batalkan
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center gap-4 rounded-2xl border border-dashed border-slate-300 bg-slate-50 py-16 text-center">
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                    <i class="fa-solid fa-cow text-3xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-slate-700">Belum ada booking</h3>
                    <p class="mt-1 text-sm text-slate-500">Lihat ternak yang tersedia untuk dibooking</p>
                </div>
                <a href="{{ route('home') }}" class="mt-2 rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">
                    Lihat Ternak
                </a>
            </div>
        @endforelse
    </div>

    {{-- Booking Detail Modal --}}
    @if($selectedBooking)
        @php
            $progress = $this->getBookingProgress($selectedBooking);
            $statusInfo = $this->getStatusExplanation($selectedBooking->status);
            $owner = $selectedBooking->items->first()?->product?->owner;
        @endphp
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

                <div class="p-6 space-y-6">
                    {{-- Visual Progress Tracker --}}
                    @if(!$progress['isRejected'] && !$progress['isCancelled'])
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

                            {{-- Progress Steps --}}
                            <div class="flex items-center gap-2">
                                @foreach($progress['steps'] as $index => $step)
                                    <div class="flex flex-1 items-center">
                                        <div class="flex flex-1 flex-col items-center gap-1">
                                            <div class="h-8 w-8 rounded-full flex items-center justify-center text-xs font-bold
                                                @if($index <= $progress['currentStep'])
                                                    bg-emerald-500 text-white shadow-lg shadow-emerald-200
                                                @else
                                                    bg-slate-200 text-slate-500
                                                @endif">
                                                @if($index < $progress['currentStep'])
                                                    <i class="fa-solid fa-check text-[10px]"></i>
                                                @else
                                                    {{ $index + 1 }}
                                                @endif
                                            </div>
                                            <span class="text-xs font-medium text-center @if($index <= $progress['currentStep']) text-emerald-600 @else text-slate-400 @endif">
                                                {{ $step['label'] }}
                                            </span>
                                        </div>
                                        @if(!$loop->last)
                                            <div class="h-1 flex-1 rounded-full @if($index < $progress['currentStep']) bg-emerald-400 @else bg-slate-200 @endif mb-4 mx-0.5"></div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="rounded-xl bg-red-50 p-5">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100 text-red-600">
                                    <i class="fa-solid fa-{{ $statusInfo['icon'] }}"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-red-800">{{ $statusInfo['title'] }}</p>
                                    <p class="text-sm text-red-600">{{ $statusInfo['description'] }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Farmer Contact Card --}}
                    @if($owner)
                        <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
                            <h4 class="mb-3 flex items-center gap-2 text-sm font-semibold text-blue-900">
                                <i class="fa-solid fa-user text-blue-600"></i>
                                Informasi Peternak
                            </h4>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-slate-900">{{ $owner->name }}</p>
                                    @if($owner->phone)
                                        <p class="text-sm text-slate-600">{{ $owner->phone }}</p>
                                    @endif
                                </div>
                                @if($owner->phone)
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $owner->phone) }}"
                                       target="_blank"
                                       class="rounded-lg bg-green-500 px-4 py-2 text-sm font-medium text-white hover:bg-green-600 transition">
                                        <i class="fa-brands fa-whatsapp mr-2"></i>Hubungi via WhatsApp
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Booked Livestock --}}
                    <div>
                        <h4 class="mb-3 text-sm font-semibold text-slate-700">Ternak yang Dibooking</h4>
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
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Total --}}
                    <div class="flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                        <span class="text-sm font-medium text-slate-700">Total Booking</span>
                        <span class="text-xl font-bold text-emerald-600">Rp {{ number_format($selectedBooking->total_amount, 0, ',', '.') }}</span>
                    </div>

                    {{-- Notes --}}
                    @if($selectedBooking->notes)
                        <div class="rounded-lg border border-blue-100 bg-blue-50 p-4">
                            <p class="text-xs text-slate-500">Catatan Anda</p>
                            <p class="mt-1 text-sm text-slate-700">{{ $selectedBooking->notes }}</p>
                        </div>
                    @endif

                    {{-- Survey Info --}}
                    @if($selectedBooking->admin_notes && str_contains($selectedBooking->admin_notes, 'Survei'))
                        <div class="rounded-lg border border-blue-200 bg-blue-50 p-4">
                            <p class="text-xs text-blue-600 font-semibold"><i class="fa-solid fa-calendar-check mr-1"></i>Info Survei</p>
                            <p class="mt-1 text-sm text-blue-800">{{ Str::of($selectedBooking->admin_notes)->explode("\n")->last() }}</p>
                        </div>
                    @endif

                    {{-- Timeline --}}
                    @if(count($selectedBookingTimeline) > 0)
                        <div>
                            <h4 class="mb-3 text-sm font-semibold text-slate-700">Riwayat Booking</h4>
                            <x-order-timeline :order="$selectedBooking" />
                        </div>
                    @endif
                </div>

                {{-- Footer Actions --}}
                <div class="sticky bottom-0 flex items-center justify-between gap-3 border-t border-slate-100 bg-white px-6 py-4">
                    @if($selectedBooking->status === 'pending')
                        <button
                            wire:click="cancelBooking({{ $selectedBooking->id }})"
                            wire:confirm="Yakin ingin membatalkan booking ini?"
                            class="rounded-lg border border-red-200 px-4 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50"
                        >
                            <i class="fa-solid fa-times mr-2"></i>Batalkan Booking
                        </button>
                    @else
                        <div></div>
                    @endif
                    <button
                        wire:click="closeDetail"
                        class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
