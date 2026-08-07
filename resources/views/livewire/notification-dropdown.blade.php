<div x-data="{ open: false }" @click.outside="open = false" class="relative">
    <button
        type="button"
        class="relative grid size-9 place-content-center rounded-lg text-slate-600 transition hover:bg-slate-100"
        @click="open = !open"
    >
        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M9.5 19a2.5 2.5 0 0 0 5 0" stroke-linecap="round" />
        </svg>
        @if($unreadCount > 0)
            <span class="absolute right-1.5 top-1.5 flex size-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        class="absolute right-0 top-full z-50 mt-2 w-80 rounded-xl border border-slate-200 bg-white shadow-xl"
    >
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
            <h3 class="text-sm font-semibold text-slate-900">Notifikasi</h3>
            @if($unreadCount > 0)
                <button
                    type="button"
                    wire:click="markAllAsRead"
                    class="text-xs text-emerald-600 hover:text-emerald-700"
                >
                    Tandai semua dibaca
                </button>
            @endif
        </div>

        <div class="max-h-80 overflow-y-auto">
            @forelse($notifications as $notification)
                @php
                    $isUnread = !$notification['is_read'];
                    $iconClass = $notification['icon'] ?? 'fa-solid fa-bell';

                    // Override icon based on type
                    if ($notification['source'] === 'customer') {
                        $iconClass = match($notification['type']) {
                            'payment_success' => 'fa-solid fa-credit-card text-green-500',
                            'order_status' => 'fa-solid fa-truck text-blue-500',
                            'booking_approved' => 'fa-solid fa-circle-check text-green-500',
                            'booking_rejected' => 'fa-solid fa-circle-xmark text-red-500',
                            'order_shipped' => 'fa-solid fa-box text-purple-500',
                            'order_delivered' => 'fa-solid fa-check-double text-emerald-500',
                            default => 'fa-solid fa-bell text-slate-500',
                        };
                    }
                @endphp
                <div
                    class="flex items-start gap-3 border-b border-slate-50 px-4 py-3 transition hover:bg-slate-50 {{ $isUnread ? 'bg-emerald-50/50' : '' }}"
                    @if($isUnread) wire:click="markAsRead({{ $notification['id'] }}, '{{ $notification['source'] }}')" @endif
                >
                    <div class="mt-0.5 rounded-full p-1.5 {{ $isUnread ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-500' }}">
                        <i class="{{ $iconClass }} text-sm"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-900 line-clamp-1">{{ $notification['title'] }}</p>
                        <p class="text-xs text-slate-500 line-clamp-2">{{ $notification['message'] }}</p>
                        <p class="mt-1 text-xs text-slate-400">{{ \Carbon\Carbon::parse($notification['created_at'])->diffForHumans() }}</p>
                    </div>
                    @if($isUnread)
                        <span class="size-2 rounded-full bg-emerald-500 mt-2"></span>
                    @endif
                </div>
            @empty
                <div class="px-4 py-8 text-center">
                    <i class="fa-solid fa-bell-slash text-3xl text-slate-300"></i>
                    <p class="mt-2 text-sm text-slate-500">Tidak ada notifikasi</p>
                </div>
            @endforelse
        </div>

        @php
            $notificationRoute = match(true) {
                auth()->user()?->hasRole('admin') => 'admin.notifications',
                auth()->user()?->hasRole('staf') => 'staff.notifications',
                auth()->user()?->hasRole('peternak') => 'peternak.notifications',
                default => 'pelanggan.notifications',
            };
        @endphp
        <a
            href="{{ route($notificationRoute) }}"
            class="block border-t border-slate-100 px-4 py-3 text-center text-sm font-medium text-emerald-600 hover:bg-slate-50"
        >
            Lihat semua notifikasi
        </a>
    </div>
</div>
