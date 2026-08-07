<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between rounded-2xl border border-emerald-100 bg-gradient-to-r from-emerald-500 to-teal-500 p-6 shadow-lg">
        <div class="flex items-center gap-4">
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-white/20 text-xl text-white">
                <i class="fa-solid fa-star"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-white">Ulasan tentang Saya</h2>
                <p class="mt-0.5 text-emerald-100">Lihat ulasan dari pelanggan</p>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-amber-200 bg-gradient-to-br from-amber-50 to-orange-50 p-5">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                    <i class="fa-solid fa-cow"></i>
                </div>
                <div>
                    <p class="text-sm text-slate-500">Ulasan Peternakan</p>
                    <p class="text-2xl font-bold text-slate-900">{{ $stats['total_farmer_reviews'] }}</p>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <div class="flex items-center">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="fa-solid fa-star text-sm {{ $i <= round($stats['avg_farmer_rating']) ? 'text-amber-400' : 'text-slate-200' }}"></i>
                    @endfor
                </div>
                <span class="text-sm font-medium text-slate-600">{{ number_format($stats['avg_farmer_rating'], 1) }}</span>
            </div>
        </div>

        <div class="rounded-xl border border-orange-200 bg-gradient-to-br from-orange-50 to-red-50 p-5">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-orange-100 text-orange-600">
                    <i class="fa-solid fa-drumstick-bite"></i>
                </div>
                <div>
                    <p class="text-sm text-slate-500">Ulasan Produk</p>
                    <p class="text-2xl font-bold text-slate-900">{{ $stats['total_product_reviews'] }}</p>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <div class="flex items-center">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="fa-solid fa-star text-sm {{ $i <= round($stats['avg_product_rating']) ? 'text-amber-400' : 'text-slate-200' }}"></i>
                    @endfor
                </div>
                <span class="text-sm font-medium text-slate-600">{{ number_format($stats['avg_product_rating'], 1) }}</span>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">Rating Peternakan</p>
            <p class="mt-1 text-3xl font-bold text-amber-500">{{ number_format($stats['avg_farmer_rating'], 1) }}</p>
            <p class="text-xs text-slate-500">dari {{ $stats['total_farmer_reviews'] }} ulasan</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">Rating Produk</p>
            <p class="mt-1 text-3xl font-bold text-orange-500">{{ number_format($stats['avg_product_rating'], 1) }}</p>
            <p class="text-xs text-slate-500">dari {{ $stats['total_product_reviews'] }} ulasan</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap items-center gap-3 rounded-xl border border-slate-100 bg-white p-4 shadow-xs">
        <span class="text-sm font-medium text-slate-700">Filter:</span>
        <button
            wire:click="$set('filter', 'all')"
            class="rounded-lg px-4 py-2 text-sm font-medium transition @if($filter === 'all') bg-emerald-500 text-white @else text-slate-600 hover:bg-emerald-50 @endif"
        >
            Semua
        </button>
        <button
            wire:click="$set('filter', 'farmer')"
            class="rounded-lg px-4 py-2 text-sm font-medium transition @if($filter === 'farmer') bg-emerald-500 text-white @else text-slate-600 hover:bg-emerald-50 @endif"
        >
            <i class="fa-solid fa-cow mr-1"></i>
            Peternakan
        </button>
        <button
            wire:click="$set('filter', 'product')"
            class="rounded-lg px-4 py-2 text-sm font-medium transition @if($filter === 'product') bg-emerald-500 text-white @else text-slate-600 hover:bg-emerald-50 @endif"
        >
            <i class="fa-solid fa-drumstick-bite mr-1"></i>
            Produk
        </button>
    </div>

    {{-- Reviews List --}}
    @if($reviews->count() > 0)
        <div class="space-y-4">
            @foreach($reviews as $review)
                <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                    <div class="flex items-start gap-4">
                        {{-- Avatar --}}
                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 text-white font-bold">
                            {{ strtoupper(substr($review->user?->name ?? 'A', 0, 1)) }}
                        </div>

                        <div class="flex-1">
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-slate-900">{{ $review->user?->name ?? 'Anonim' }}</span>
                                        @if($review->review_type === 'farmer')
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">
                                                <i class="fa-solid fa-cow text-[8px]"></i>
                                                Peternakan
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-full bg-orange-100 px-2 py-0.5 text-xs font-medium text-orange-700">
                                                <i class="fa-solid fa-drumstick-bite text-[8px]"></i>
                                                {{ $review->product?->name ?? 'Produk' }}
                                            </span>
                                        @endif
                                        @if($review->is_verified_purchase)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700">
                                                <i class="fa-solid fa-circle-check text-[8px]"></i>
                                                Verified
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Star Rating --}}
                                    <div class="mt-1 flex items-center gap-1">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fa{{ $i <= $review->rating ? '-solid' : '-regular' }} fa-star text-sm {{ $i <= $review->rating ? 'text-amber-400' : 'text-slate-300' }}"></i>
                                        @endfor
                                        <span class="ml-1 text-sm text-slate-500">{{ $review->rating }}/5</span>
                                    </div>
                                </div>

                                <span class="text-xs text-slate-400">
                                    {{ $review->created_at->diffForHumans() }}
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
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $reviews->links('vendor.livewire.tailwind') }}
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
                <p class="mt-1 text-sm text-slate-500">Ulasan dari pelanggan akan muncul di sini.</p>
            </div>
        </div>
    @endif
</div>
