<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Notifikasi</h2>
            <p class="mt-1 text-sm text-slate-500">{{ $unreadCount }} notifikasi belum dibaca</p>
        </div>
        @if($unreadCount > 0)
            <button
                type="button"
                wire:click="markAllAsRead"
                class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
            >
                <i class="fa-solid fa-check-double"></i>
                Tandai Semua Dibaca
            </button>
        @endif
    </div>

    <div class="space-y-3">
        @forelse($notifications as $notification)
            <div
                class="rounded-xl border p-4 transition @if(!$notification->is_read) border-emerald-200 bg-emerald-50/50 @else border-slate-100 bg-white @endif"
                wire:click="markAsRead({{ $notification->id }})"
            >
                <div class="flex items-start gap-3">
                    <div class="mt-1 rounded-full p-2 @if(!$notification->is_read) bg-emerald-100 text-emerald-600 @else bg-slate-100 text-slate-500 @endif">
                        <i class="{{ $notification->icon }}"></i>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-start justify-between">
                            <h4 class="font-semibold text-slate-900">{{ $notification->title }}</h4>
                            <span class="text-xs text-slate-400">{{ $notification->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="mt-1 text-sm text-slate-600">{{ $notification->message }}</p>
                    </div>
                    @if(!$notification->is_read)
                        <span class="size-2 rounded-full bg-emerald-500"></span>
                    @endif
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-slate-100 bg-white py-12 text-center">
                <i class="fa-solid fa-bell-slash text-5xl text-slate-200"></i>
                <p class="mt-4 text-slate-500">Tidak ada notifikasi</p>
            </div>
        @endforelse
    </div>
</div>
