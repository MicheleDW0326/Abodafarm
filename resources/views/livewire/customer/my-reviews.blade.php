<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between rounded-2xl border border-slate-100 bg-gradient-to-r from-amber-500 to-orange-500 p-6 shadow-lg">
        <div class="flex items-center gap-4">
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-white/20 text-xl text-white">
                <i class="fa-solid fa-star"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-white">Ulasan Saya</h2>
                <p class="mt-0.5 text-amber-100">Kelola ulasan produk dan peternak</p>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap items-center gap-3 rounded-xl border border-slate-100 bg-white p-4 shadow-xs">
        <button
            wire:click="$set('filter', 'all')"
            class="rounded-lg px-4 py-2 text-sm font-medium transition @if($filter === 'all') bg-amber-500 text-white @else text-slate-600 hover:bg-slate-100 @endif"
        >
            Semua
        </button>
        <button
            wire:click="$set('filter', 'product')"
            class="rounded-lg px-4 py-2 text-sm font-medium transition @if($filter === 'product') bg-amber-500 text-white @else text-slate-600 hover:bg-slate-100 @endif"
        >
            <i class="fa-solid fa-drumstick-bite mr-1"></i>
            Produk
        </button>
        <button
            wire:click="$set('filter', 'farmer')"
            class="rounded-lg px-4 py-2 text-sm font-medium transition @if($filter === 'farmer') bg-amber-500 text-white @else text-slate-600 hover:bg-slate-100 @endif"
        >
            <i class="fa-solid fa-cow mr-1"></i>
            Peternak
        </button>
    </div>

    {{-- Reviews List --}}
    @if($reviews->count() > 0)
        <div class="space-y-4">
            @foreach($reviews as $review)
                <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                    @if($editingReviewId === $review->id)
                        {{-- Edit Form --}}
                        <form wire:submit="saveEdit" class="space-y-4">
                            <div class="flex items-center justify-between">
                                <h3 class="font-semibold text-slate-900">Edit Ulasan</h3>
                                <button type="button" wire:click="cancelEdit" class="text-slate-400 hover:text-slate-600">
                                    <i class="fa-solid fa-times"></i>
                                </button>
                            </div>

                            {{-- Star Rating Input --}}
                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-700">Rating</label>
                                <div class="flex gap-1" x-data="{ rating: {{ $editRating }} }">
                                    @for($i = 1; $i <= 5; $i++)
                                        <button
                                            type="button"
                                            @click="rating = {{ $i }}; $wire.editRating = {{ $i }}"
                                            :class="rating >= {{ $i }} ? 'text-amber-400' : 'text-slate-300'"
                                            class="text-2xl transition hover:scale-110"
                                        >
                                            <i class="fa{{ $i <= $editRating ? '-solid' : '-regular' }} fa-star"></i>
                                        </button>
                                    @endfor
                                </div>
                            </div>

                            {{-- Title --}}
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">Judul (opsional)</label>
                                <input
                                    type="text"
                                    wire:model="editTitle"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-amber-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20"
                                    placeholder="Ringkasan pengalaman Anda"
                                >
                            </div>

                            {{-- Content --}}
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">Ulasan</label>
                                <textarea
                                    wire:model="editContent"
                                    rows="4"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-amber-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20"
                                    placeholder="Ceritakan pengalaman Anda..."
                                ></textarea>
                            </div>

                            <div class="flex justify-end gap-3">
                                <button
                                    type="button"
                                    wire:click="cancelEdit"
                                    class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    class="rounded-xl bg-amber-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-amber-600"
                                >
                                    Simpan
                                </button>
                            </div>
                        </form>
                    @else
                        {{-- Review Display --}}
                        <div class="flex items-start gap-4">
                            {{-- Type Icon --}}
                            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full @if($review->review_type === 'product') bg-orange-100 text-orange-600 @else bg-emerald-100 text-emerald-600 @endif">
                                <i class="fa-solid {{ $review->review_type === 'product' ? 'fa-drumstick-bite' : 'fa-cow' }}"></i>
                            </div>

                            <div class="flex-1">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold text-slate-900">
                                                @if($review->review_type === 'product')
                                                    {{ $review->product?->name ?? 'Produk' }}
                                                @else
                                                    {{ $review->farmer?->name ?? 'Peternak' }}
                                                @endif
                                            </span>
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">
                                                <i class="fa-solid fa-circle-check text-[8px]"></i>
                                                Verified Purchase
                                            </span>
                                        </div>

                                        {{-- Star Rating Display --}}
                                        <div class="mt-1 flex items-center gap-1">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fa{{ $i <= $review->rating ? '-solid' : '-regular' }} fa-star text-sm {{ $i <= $review->rating ? 'text-amber-400' : 'text-slate-300' }}"></i>
                                            @endfor
                                            <span class="ml-1 text-sm text-slate-500">{{ $review->rating }}/5</span>
                                        </div>
                                    </div>

                                    <span class="text-xs text-slate-400">
                                        {{ $review->created_at->format('d M Y') }}
                                    </span>
                                </div>

                                @if($review->title)
                                    <p class="mt-2 font-medium text-slate-800">{{ $review->title }}</p>
                                @endif

                                @if($review->content)
                                    <p class="mt-1 text-sm text-slate-600">{{ $review->content }}</p>
                                @endif

                                {{-- Photos --}}
                                @if($review->photos->count() > 0)
                                    <div class="mt-3 flex gap-2">
                                        @foreach($review->photos as $photo)
                                            <a href="{{ $photo->url }}" target="_blank" class="group">
                                                <img
                                                    src="{{ $photo->url }}"
                                                    alt="Review photo"
                                                    class="h-16 w-16 rounded-lg object-cover transition group-hover:opacity-80"
                                                >
                                            </a>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Actions --}}
                                @if($review->canBeEdited())
                                    <div class="mt-3 flex items-center gap-2 border-t border-slate-100 pt-3">
                                        <button
                                            wire:click="startEdit({{ $review->id }})"
                                            class="flex items-center gap-1 text-xs font-medium text-slate-500 transition hover:text-amber-600"
                                        >
                                            <i class="fa-solid fa-edit"></i>
                                            Edit
                                        </button>
                                        <button
                                            wire:click="deleteReview({{ $review->id }})"
                                            wire:confirm="Yakin ingin menghapus ulasan ini?"
                                            class="flex items-center gap-1 text-xs font-medium text-slate-500 transition hover:text-red-600"
                                        >
                                            <i class="fa-solid fa-trash"></i>
                                            Hapus
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        {{-- Empty State --}}
        <div class="flex flex-col items-center gap-4 rounded-2xl border border-dashed border-slate-300 bg-slate-50 py-16 text-center">
            <div class="relative">
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                    <i class="fa-solid fa-star text-3xl"></i>
                </div>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-slate-700">Belum ada ulasan</h3>
                <p class="mt-1 text-sm text-slate-500">Selesaikan transaksi untuk memberikan ulasan.</p>
            </div>
            <a
                href="{{ route('pelanggan.orders') }}"
                class="rounded-xl bg-amber-500 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-600"
            >
                Lihat Pesanan Saya
            </a>
        </div>
    @endif
</div>
