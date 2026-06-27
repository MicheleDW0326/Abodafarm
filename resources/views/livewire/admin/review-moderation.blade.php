<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between rounded-2xl border border-slate-100 bg-gradient-to-r from-purple-600 to-indigo-600 p-6 shadow-lg">
        <div class="flex items-center gap-4">
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-white/20 text-xl text-white">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-white">Moderasi Ulasan</h2>
                <p class="mt-0.5 text-purple-100">Kelola dan moderasi ulasan pengguna</p>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <div class="text-right">
                <p class="text-2xl font-bold text-white">{{ $stats['pending'] }}</p>
                <p class="text-sm text-purple-200">Menunggu</p>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-4 gap-4">
        <div class="rounded-xl border border-slate-100 bg-white p-4">
            <p class="text-sm text-slate-500">Total Ulasan</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $stats['total'] }}</p>
        </div>
        <div class="rounded-xl border border-slate-100 bg-white p-4">
            <p class="text-sm text-slate-500">Menunggu</p>
            <p class="mt-1 text-2xl font-bold text-amber-600">{{ $stats['pending'] }}</p>
        </div>
        <div class="rounded-xl border border-slate-100 bg-white p-4">
            <p class="text-sm text-slate-500">Disetujui</p>
            <p class="mt-1 text-2xl font-bold text-emerald-600">{{ $stats['approved'] }}</p>
        </div>
        <div class="rounded-xl border border-slate-100 bg-white p-4">
            <p class="text-sm text-slate-500">Ditolak</p>
            <p class="mt-1 text-2xl font-bold text-red-600">{{ $stats['rejected'] }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col gap-4 rounded-xl border border-slate-100 bg-white p-4 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <input
                type="text"
                wire:model.live.debounce.300ms="searchQuery"
                placeholder="Cari ulasan..."
                class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2 pl-10 pr-4 text-sm transition focus:border-purple-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500/20"
            >
            <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
        </div>

        <div class="flex gap-2">
            <button
                wire:click="$set('statusFilter', 'pending')"
                class="rounded-lg px-4 py-2 text-sm font-medium transition @if($statusFilter === 'pending') bg-purple-500 text-white @else text-slate-600 hover:bg-slate-100 @endif"
            >
                Menunggu
            </button>
            <button
                wire:click="$set('statusFilter', 'approved')"
                class="rounded-lg px-4 py-2 text-sm font-medium transition @if($statusFilter === 'approved') bg-purple-500 text-white @else text-slate-600 hover:bg-slate-100 @endif"
            >
                Disetujui
            </button>
            <button
                wire:click="$set('statusFilter', 'rejected')"
                class="rounded-lg px-4 py-2 text-sm font-medium transition @if($statusFilter === 'rejected') bg-purple-500 text-white @else text-slate-600 hover:bg-slate-100 @endif"
            >
                Ditolak
            </button>
            <button
                wire:click="$set('statusFilter', 'all')"
                class="rounded-lg px-4 py-2 text-sm font-medium transition @if($statusFilter === 'all') bg-purple-500 text-white @else text-slate-600 hover:bg-slate-100 @endif"
            >
                Semua
            </button>
        </div>
    </div>

    {{-- Reviews List --}}
    @if($reviews->count() > 0)
        <div class="space-y-4">
            @foreach($reviews as $review)
                <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                    <div class="flex items-start gap-4">
                        {{-- Status Badge --}}
                        <div class="flex-shrink-0">
                            @if($review->status === 'pending')
                                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                                    <i class="fa-solid fa-clock"></i>
                                </span>
                            @elseif($review->status === 'approved')
                                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                                    <i class="fa-solid fa-check"></i>
                                </span>
                            @else
                                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100 text-red-600">
                                    <i class="fa-solid fa-times"></i>
                                </span>
                            @endif
                        </div>

                        <div class="flex-1">
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-slate-900">
                                            {{ $review->user?->name ?? 'User tidak ditemukan' }}
                                        </span>
                                        <span class="rounded-full px-2 py-0.5 text-xs font-medium @if($review->status === 'pending') bg-amber-100 text-amber-700 @elseif($review->status === 'approved') bg-emerald-100 text-emerald-700 @else bg-red-100 text-red-700 @endif">
                                            {{ ucfirst($review->status) }}
                                        </span>
                                    </div>
                                    <p class="mt-1 text-sm text-slate-500">
                                        {{ $review->review_type === 'product' ? 'Produk' : 'Peternak' }}:
                                        <span class="font-medium text-slate-700">
                                            {{ $review->product?->name ?? $review->farmer?->name ?? 'Item tidak ditemukan' }}
                                        </span>
                                    </p>
                                </div>
                                <span class="text-xs text-slate-400">
                                    {{ $review->created_at->format('d M Y H:i') }}
                                </span>
                            </div>

                            {{-- Rating --}}
                            <div class="mt-2 flex items-center gap-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fa{{ $i <= $review->rating ? '-solid' : '-regular' }} fa-star text-sm {{ $i <= $review->rating ? 'text-amber-400' : 'text-slate-300' }}"></i>
                                @endfor
                            </div>

                            @if($review->title)
                                <p class="mt-2 font-medium text-slate-800">{{ $review->title }}</p>
                            @endif

                            @if($review->content)
                                <p class="mt-1 text-sm text-slate-600">{{ $review->content }}</p>
                            @endif

                            {{-- Admin Notes --}}
                            @if($review->admin_notes)
                                <div class="mt-2 rounded-lg border border-red-100 bg-red-50 p-3">
                                    <p class="text-xs font-medium text-red-700">Alasan penolakan:</p>
                                    <p class="mt-0.5 text-sm text-red-600">{{ $review->admin_notes }}</p>
                                </div>
                            @endif

                            {{-- Photos --}}
                            @if($review->photos->count() > 0)
                                <div class="mt-3 flex gap-2">
                                    @foreach($review->photos as $photo)
                                        <a href="{{ $photo->url }}" target="_blank" class="group">
                                            <img
                                                src="{{ $photo->url }}"
                                                alt="Review photo"
                                                class="h-12 w-12 rounded-lg object-cover transition group-hover:opacity-80"
                                            >
                                        </a>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Actions --}}
                            @if($review->status === 'pending')
                                <div class="mt-4 flex items-center gap-2 border-t border-slate-100 pt-4">
                                    <button
                                        wire:click="approveReview({{ $review->id }})"
                                        class="flex items-center gap-1 rounded-lg bg-emerald-500 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-emerald-600"
                                    >
                                        <i class="fa-solid fa-check"></i>
                                        Setujui
                                    </button>
                                    <button
                                        wire:click="openRejectModal({{ $review->id }})"
                                        class="flex items-center gap-1 rounded-lg bg-red-500 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-red-600"
                                    >
                                        <i class="fa-solid fa-times"></i>
                                        Tolak
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $reviews->links() }}
        </div>
    @else
        {{-- Empty State --}}
        <div class="flex flex-col items-center gap-4 rounded-2xl border border-dashed border-slate-300 bg-slate-50 py-16 text-center">
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                <i class="fa-solid fa-inbox text-2xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-slate-700">Tidak ada ulasan</h3>
                <p class="mt-1 text-sm text-slate-500">Tidak ada ulasan yang sesuai filter.</p>
            </div>
        </div>
    @endif

    {{-- Reject Modal --}}
    @if($selectedReviewId)
        <div
            x-data="{ open: true }"
            x-show="open"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4"
            x-on:keydown.escape.window="open = false"
        >
            <div
                x-show="open"
                x-transition
                class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl"
            >
                <h3 class="text-lg font-bold text-slate-900">Tolak Ulasan</h3>
                <p class="mt-2 text-sm text-slate-600">Berikan alasan penolakan agar pelanggan memahami mengapa ulasan mereka ditolak.</p>

                <form wire:submit="confirmRejection" class="mt-4 space-y-4">
                    <div>
                        <label for="rejectionReason" class="mb-1 block text-sm font-medium text-slate-700">Alasan Penolakan</label>
                        <textarea
                            id="rejectionReason"
                            wire:model="rejectionReason"
                            rows="3"
                            class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/20"
                            placeholder="Contoh: Konten tidak sesuai dengan kebijakan kami..."
                        ></textarea>
                        @error('rejectionReason')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-3">
                        <button
                            type="button"
                            wire:click="closeRejectModal"
                            class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="rounded-xl bg-red-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-600"
                        >
                            Tolak Ulasan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
