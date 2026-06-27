<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between rounded-2xl border border-slate-100 bg-gradient-to-r from-slate-700 to-slate-800 p-6 shadow-lg">
        <div class="flex items-center gap-4">
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-white/20 text-xl text-white">
                <i class="fa-solid fa-bell"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-white">Notifikasi Sistem</h2>
                <p class="mt-0.5 text-slate-300">Pantau semua aktivitas dan notifikasi</p>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-4 rounded-xl bg-white/10 px-4 py-2">
                <div class="text-center">
                    <p class="text-2xl font-bold text-white">{{ $stats['total'] }}</p>
                    <p class="text-xs text-slate-300">Total</p>
                </div>
                <div class="h-10 w-px bg-white/20"></div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-amber-400">{{ $stats['unread'] }}</p>
                    <p class="text-xs text-slate-300">Belum Dibaca</p>
                </div>
            </div>
            @if($stats['unread'] > 0)
                <button
                    wire:click="markAllAsRead"
                    class="flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100"
                >
                    <i class="fa-solid fa-check-double"></i>
                    Tandai Semua Dibaca
                </button>
            @endif
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex flex-wrap gap-2">
            <button
                wire:click="$set('filterSource', 'all')"
                class="rounded-full px-4 py-2 text-sm font-medium transition @if($filterSource === 'all') bg-slate-700 text-white @else border border-slate-200 bg-white text-slate-600 hover:border-slate-300 @endif"
            >
                Semua
            </button>
            <button
                wire:click="$set('filterSource', 'customer')"
                class="rounded-full px-4 py-2 text-sm font-medium transition @if($filterSource === 'customer') bg-blue-600 text-white @else border border-slate-200 bg-white text-slate-600 hover:border-blue-300 hover:text-blue-600 @endif"
            >
                <i class="fa-solid fa-user mr-1"></i>
                Pelanggan
            </button>
            <button
                wire:click="$set('filterSource', 'peternak')"
                class="rounded-full px-4 py-2 text-sm font-medium transition @if($filterSource === 'peternak') bg-emerald-600 text-white @else border border-slate-200 bg-white text-slate-600 hover:border-emerald-300 hover:text-emerald-600 @endif"
            >
                <i class="fa-solid fa-tractor mr-1"></i>
                Peternak
            </button>
        </div>

        <div class="flex items-center gap-3">
            {{-- Search --}}
            <div class="relative">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="searchQuery"
                    placeholder="Cari notifikasi..."
                    class="h-10 w-64 rounded-xl border border-slate-200 bg-white pl-10 pr-4 text-sm focus:border-slate-300 focus:outline-none focus:ring-2 focus:ring-slate-100"
                >
                <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            </div>

            {{-- Type Filter --}}
            <select
                wire:model.live="filterType"
                class="h-10 rounded-xl border border-slate-200 bg-white px-4 text-sm focus:border-slate-300 focus:outline-none"
            >
                @foreach($filterOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>

            {{-- Unread Only --}}
            <label class="flex items-center gap-2">
                <input
                    type="checkbox"
                    wire:model.live="showUnreadOnly"
                    class="h-4 w-4 rounded border-slate-300 text-slate-600 focus:ring-slate-500"
                >
                <span class="text-sm text-slate-600">Belum dibaca</span>
            </label>
        </div>
    </div>

    {{-- Notifications List --}}
    @if(count($notifications) > 0)
        <div class="space-y-3">
            @foreach($notifications as $notification)
                <div
                    class="group relative rounded-2xl border p-5 transition @if(!$notification['is_read']) border-amber-200 bg-amber-50/50 @else border-slate-100 bg-white @endif hover:shadow-md"
                >
                    <div class="flex gap-4">
                        {{-- Icon --}}
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl {{ $notification['color_class'] ?? 'bg-slate-100 text-slate-600' }}">
                            @switch($notification['type'])
                                @case('payment_success')
                                    <i class="fa-solid fa-credit-card text-xl"></i>
                                @break
                                @case('payment_failed')
                                    <i class="fa-solid fa-circle-xmark text-xl"></i>
                                @break
                                @case('booking_approved')
                                    <i class="fa-solid fa-check-circle text-xl"></i>
                                @break
                                @case('booking_rejected')
                                    <i class="fa-solid fa-times-circle text-xl"></i>
                                @break
                                @case('new_booking')
                                    <i class="fa-solid fa-calendar-plus text-xl"></i>
                                @break
                                @case('order_shipped')
                                    <i class="fa-solid fa-truck text-xl"></i>
                                @break
                                @case('order_delivered')
                                    <i class="fa-solid fa-box text-xl"></i>
                                @break
                                @case('product_published')
                                    <i class="fa-solid fa-check text-xl"></i>
                                @break
                                @case('product_disabled')
                                    <i class="fa-solid fa-ban text-xl"></i>
                                @break
                                @case('new_farmer_registration')
                                    <i class="fa-solid fa-user-plus text-xl"></i>
                                @break
                                @case('promo')
                                    <i class="fa-solid fa-gift text-xl"></i>
                                @break
                                @default
                                    <i class="fa-solid fa-bell text-xl"></i>
                            @endswitch
                        </div>

                        {{-- Content --}}
                        <div class="flex-1">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $notification['source'] === 'customer' ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700' }}">
                                        {{ $notification['source'] === 'customer' ? 'Pelanggan' : 'Peternak' }}
                                    </span>
                                    <h4 class="font-semibold text-slate-900 {{ !$notification['is_read'] ? 'text-amber-700' : '' }}">
                                        {{ $notification['title'] }}
                                        @if(!$notification['is_read'])
                                            <span class="ml-2 inline-flex h-2 w-2 rounded-full bg-amber-500"></span>
                                        @endif
                                    </h4>
                                </div>
                                <div class="flex items-center gap-1 opacity-0 transition group-hover:opacity-100">
                                    @if(!$notification['is_read'])
                                        <button
                                            wire:click="markAsRead({{ $notification['id'] }}, '{{ $notification['source'] }}')"
                                            class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                                            title="Tandai sudah dibaca"
                                        >
                                            <i class="fa-solid fa-check"></i>
                                        </button>
                                    @endif
                                    <button
                                        wire:click="deleteNotification({{ $notification['id'] }}, '{{ $notification['source'] }}')"
                                        class="rounded-lg p-2 text-slate-400 transition hover:bg-red-50 hover:text-red-500"
                                        title="Hapus notifikasi"
                                    >
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </div>

                            <p class="mt-1 text-sm text-slate-600">{{ $notification['message'] }}</p>

                            <p class="mt-2 text-xs text-slate-400">
                                {{ \Carbon\Carbon::parse($notification['created_at'])->translatedFormat('d M Y, H:i') }}
                                &bull;
                                {{ \Carbon\Carbon::parse($notification['created_at'])->diffForHumans() }}
                            </p>

                            {{-- Action Link --}}
                            @if(isset($notification['data']['order_number']))
                                <div class="mt-3 flex gap-2">
                                    <a
                                        href="#"
                                        class="inline-flex items-center gap-1 text-sm font-medium text-slate-600 transition hover:text-slate-800"
                                    >
                                        <i class="fa-solid fa-receipt"></i>
                                        Order #{{ $notification['data']['order_number'] }}
                                    </a>
                                </div>
                            @endif
                            @if(isset($notification['data']['user_id']))
                                <div class="mt-3 flex gap-2">
                                    <a
                                        href="#"
                                        class="inline-flex items-center gap-1 text-sm font-medium text-slate-600 transition hover:text-slate-800"
                                    >
                                        <i class="fa-solid fa-user"></i>
                                        User #{{ $notification['data']['user_id'] }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Unread indicator bar --}}
                    @if(!$notification['is_read'])
                        <div class="absolute left-0 top-0 h-full w-1 rounded-l-2xl bg-amber-500"></div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Clear All --}}
        <div class="flex justify-center pt-4">
            <button
                wire:click="clearAll"
                wire:confirm="Yakin ingin menghapus semua notifikasi? Tindakan ini tidak dapat dibatalkan."
                class="rounded-lg border border-red-200 px-4 py-2 text-sm font-medium text-red-500 transition hover:bg-red-50"
            >
                <i class="fa-solid fa-trash mr-2"></i>
                Hapus Semua Notifikasi
            </button>
        </div>
    @else
        <div class="flex flex-col items-center gap-4 rounded-2xl border border-dashed border-slate-300 bg-slate-50 py-16 text-center">
            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                <i class="fa-solid fa-bell-slash text-3xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-slate-700">Tidak ada notifikasi</h3>
                <p class="mt-1 text-sm text-slate-500">
                    @if($showUnreadOnly)
                        Semua notifikasi sudah dibaca
                    @elseif($searchQuery)
                        Tidak ditemukan "{{ $searchQuery }}"
                    @else
                        Notifikasi sistem akan muncul di sini
                    @endif
                </p>
            </div>
        </div>
    @endif
</div>
