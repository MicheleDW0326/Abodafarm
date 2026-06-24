<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between rounded-2xl border border-slate-100 bg-gradient-to-r from-emerald-600 to-emerald-700 p-6 shadow-lg">
        <div class="flex items-center gap-4">
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-white/20 text-xl text-white">
                <i class="fa-solid fa-bell"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-white">Notifikasi</h2>
                <p class="mt-0.5 text-emerald-100">Pusat pemberitahuan akun Anda</p>
            </div>
        </div>
        @if($this->getUnreadCount() > 0)
            <button
                wire:click="markAllAsRead"
                class="flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-50"
            >
                <i class="fa-solid fa-check-double"></i>
                Tandai Semua Dibaca
            </button>
        @endif
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex flex-wrap gap-2">
            @foreach($filterOptions as $value => $label)
                <button
                    wire:click="$set('filterType', '{{ $value }}')"
                    class="rounded-full px-4 py-2 text-sm font-medium transition @if($filterType === $value) bg-emerald-600 text-white @else border border-slate-200 bg-white text-slate-600 hover:border-emerald-300 hover:text-emerald-600 @endif"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <label class="flex items-center gap-2">
            <input
                type="checkbox"
                wire:model.live="showUnreadOnly"
                class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
            >
            <span class="text-sm text-slate-600">Belum dibaca saja</span>
        </label>
    </div>

    {{-- Notifications List --}}
    @if(count($notifications) > 0)
        <div class="space-y-3">
            @foreach($notifications as $notification)
                <div
                    class="group relative rounded-2xl border p-5 transition @if(!$notification['is_read']) border-emerald-200 bg-emerald-50/50 @else border-slate-100 bg-white @endif hover:shadow-md"
                >
                    <div class="flex gap-4">
                        {{-- Icon --}}
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl {{ $notification['color_class'] }}">
                            @switch($notification['type'])
                                @case('booking_approved')
                                    <i class="fa-solid fa-check-circle text-xl"></i>
                                @break
                                @case('booking_rejected')
                                    <i class="fa-solid fa-times-circle text-xl"></i>
                                @break
                                @case('order_shipped')
                                    <i class="fa-solid fa-truck text-xl"></i>
                                @break
                                @case('order_delivered')
                                    <i class="fa-solid fa-box text-xl"></i>
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
                                <div>
                                    <h4 class="font-semibold text-slate-900 {{ !$notification['is_read'] ? 'text-emerald-700' : '' }}">
                                        {{ $notification['title'] }}
                                        @if(!$notification['is_read'])
                                            <span class="ml-2 inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                                        @endif
                                    </h4>
                                    <p class="mt-1 text-sm text-slate-600">{{ $notification['message'] }}</p>
                                </div>
                                <div class="flex items-center gap-1 opacity-0 transition group-hover:opacity-100">
                                    @if(!$notification['is_read'])
                                        <button
                                            wire:click="markAsRead({{ $notification['id'] }})"
                                            class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                                            title="Tandai sudah dibaca"
                                        >
                                            <i class="fa-solid fa-check"></i>
                                        </button>
                                    @endif
                                    <button
                                        wire:click="deleteNotification({{ $notification['id'] }})"
                                        class="rounded-lg p-2 text-slate-400 transition hover:bg-red-50 hover:text-red-500"
                                        title="Hapus notifikasi"
                                    >
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </div>

                            <p class="mt-2 text-xs text-slate-400">
                                {{ \Carbon\Carbon::parse($notification['created_at'])->diffForHumans() }}
                            </p>

                            {{-- Action Link --}}
                            @if(isset($notification['data']['order_number']))
                                <a
                                    href="{{ route('pelanggan.orders') }}"
                                    class="mt-3 inline-flex items-center gap-1 text-sm font-medium text-emerald-600 transition hover:text-emerald-700"
                                >
                                    Lihat Pesanan
                                    <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            @endif
                            @if(isset($notification['data']['booking_code']))
                                <a
                                    href="{{ route('pelanggan.bookings') }}"
                                    class="mt-3 inline-flex items-center gap-1 text-sm font-medium text-emerald-600 transition hover:text-emerald-700"
                                >
                                    Lihat Booking
                                    <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Unread indicator bar --}}
                    @if(!$notification['is_read'])
                        <div class="absolute left-0 top-0 h-full w-1 rounded-l-2xl bg-emerald-500"></div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Clear All --}}
        <div class="flex justify-center pt-4">
            <button
                wire:click="clearAll"
                wire:confirm="Yakin ingin menghapus semua notifikasi?"
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
                    @else
                        Notifikasi akan muncul di sini
                    @endif
                </p>
            </div>
        </div>
    @endif
</div>
