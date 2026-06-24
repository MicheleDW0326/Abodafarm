<div class="space-y-6">
    {{-- Header --}}
    <div class="rounded-2xl border border-slate-100 bg-gradient-to-r from-emerald-600 to-emerald-700 p-6 shadow-lg">
        <div class="flex items-center gap-4">
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-white/20 text-xl text-white">
                <i class="fa-solid fa-heart"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-white">Favorit Saya</h2>
                <p class="mt-0.5 text-emerald-100">Koleksi produk favorit Anda</p>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="flex items-center gap-2">
        @foreach($typeOptions as $value => $label)
            <button
                wire:click="$set('typeFilter', '{{ $value }}')"
                class="rounded-full px-4 py-2 text-sm font-medium transition @if($typeFilter === $value) bg-emerald-600 text-white @else border border-slate-200 bg-white text-slate-600 hover:border-emerald-300 hover:text-emerald-600 @endif"
            >
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Favorites Grid --}}
    @if(count($favorites) > 0)
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach($favorites as $item)
                @php $product = $item['product']; @endphp
                @if($product)
                    <article class="group overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-xs transition hover:-translate-y-1 hover:shadow-lg">
                        {{-- Image --}}
                        <div class="relative aspect-square overflow-hidden bg-slate-100">
                            @if($product->image)
                                <img
                                    src="{{ $product->image_url }}"
                                    alt="{{ $product->name }}"
                                    class="h-full w-full object-cover transition group-hover:scale-105"
                                >
                            @else
                                <div class="flex h-full w-full items-center justify-center text-slate-300">
                                    <i class="fa-solid fa-image text-4xl"></i>
                                </div>
                            @endif

                            {{-- Type Badge --}}
                            <span class="absolute left-3 top-3 rounded-full bg-white/90 px-2.5 py-1 text-xs font-semibold text-slate-700 shadow-sm">
                                @if($product->type === 'live')
                                    <i class="fa-solid fa-cow mr-1 text-emerald-600"></i> Ternak
                                @else
                                    <i class="fa-solid fa-drumstick-bite mr-1 text-rose-600"></i> Daging
                                @endif
                            </span>

                            {{-- Remove Button --}}
                            <button
                                wire:click="removeFavorite({{ $product->id }})"
                                wire:confirm="Hapus dari favorit?"
                                class="absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-full bg-white/90 text-red-500 shadow-sm transition hover:bg-red-50 hover:text-red-600"
                            >
                                <i class="fa-solid fa-heart text-red-500"></i>
                            </button>
                        </div>

                        {{-- Content --}}
                        <div class="p-4">
                            <h3 class="font-semibold text-slate-900 line-clamp-1">{{ $product->name }}</h3>

                            @if($product->type === 'live')
                                <p class="mt-1 text-xs text-slate-500">
                                    @if($product->breed)
                                        <span class="mr-2">{{ $product->breed }}</span>
                                    @endif
                                    @if($product->sex)
                                        <span class="rounded bg-slate-100 px-1.5 py-0.5">{{ $product->sex_label }}</span>
                                    @endif
                                </p>
                            @else
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $product->weight_per_unit ? $product->weight_per_unit . ' kg/unit' : '' }}
                                </p>
                            @endif

                            <div class="mt-3 flex items-center justify-between">
                                <p class="text-lg font-bold text-emerald-600">
                                    {{ $product->formatted_price }}
                                </p>
                                <a
                                    href="{{ route('home') }}?product={{ $product->slug }}"
                                    class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-emerald-700"
                                >
                                    Detail
                                </a>
                            </div>
                        </div>
                    </article>
                @endif
            @endforeach
        </div>
    @else
        <div class="flex flex-col items-center gap-4 rounded-2xl border border-dashed border-slate-300 bg-slate-50 py-16 text-center">
            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                <i class="fa-solid fa-heart text-3xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-slate-700">Belum ada favorit</h3>
                <p class="mt-1 text-sm text-slate-500">Tambahkan produk ke favorit untuk kemudahan akses</p>
            </div>
            <a href="{{ route('home') }}" class="mt-2 rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">
                Jelajahi Produk
            </a>
        </div>
    @endif
</div>
