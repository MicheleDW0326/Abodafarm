<div>
    {{-- SEO Meta & Computed Properties Defaults --}}
    @php
        $catLocationName = $location ?? 'Lokasi';
        $catCategoryName = $category ?? 'Kategori';
        $catSeoData = $seoData ?? [];
        $catStats = $stats ?? ['products' => 0];

        // Computed properties with defaults
        $catProducts = $products ?? collect();
        $catFeaturedProducts = $featuredProducts ?? collect();

        // SEO
        $seoTitle = $seo['title'] ?? $catSeoData['title'] ?? $catCategoryName . ' di ' . $catLocationName . ' - AbodaFarm';
        $seoDescription = $seo['description'] ?? $catSeoData['description'] ?? null;
        $seoKeywords = $seo['keywords'] ?? $catSeoData['keywords'] ?? null;
        $seoBreadcrumbs = $seo['breadcrumbs'] ?? $catSeoData['breadcrumbs'] ?? null;
    @endphp
    <x-seo.meta
        :title="$seoTitle"
        :description="$seoDescription"
        :keywords="$seoKeywords"
        :schema="['organization', 'website']"
        :breadcrumbs="$seoBreadcrumbs"
    />

    {{-- Breadcrumb --}}
    <div class="border-b border-slate-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
            <nav class="flex items-center gap-2 text-sm">
                <a href="/" class="text-slate-500 hover:text-emerald-600">Beranda</a>
                <i class="fa-solid fa-chevron-right text-xs text-slate-400"></i>
                <a href="/{{ $catCategoryName }}" class="text-slate-500 hover:text-emerald-600">{{ ucfirst($catCategoryName) }}</a>
                <i class="fa-solid fa-chevron-right text-xs text-slate-400"></i>
                <span class="font-medium text-slate-900">{{ $catLocationName }}</span>
            </nav>
        </div>
    </div>

    {{-- Hero Section --}}
    <section class="bg-gradient-to-br from-emerald-600 to-emerald-800 py-12 text-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-4 flex items-center gap-3">
                <i class="fa-solid fa-{{ $catCategoryName === 'ternak' ? 'cow' : ($catCategoryName === 'daging' ? 'drumstick-bite' : 'wheat-awn') }} text-2xl"></i>
                <span class="text-emerald-200">{{ ucfirst($catCategoryName) }}</span>
            </div>
            <h1 class="text-3xl font-bold">{{ $catSeoData['title'] ?? $catCategoryName . ' di ' . $catLocationName }}</h1>
            <p class="mt-2 text-emerald-100">{{ $catStats['products'] }} produk tersedia</p>
        </div>
    </section>

    {{-- Products Grid --}}
    <section class="py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-center justify-between">
                <h2 class="text-xl font-bold text-slate-900">Semua Produk</h2>
                <span class="text-sm text-slate-500">{{ $catStats['products'] }} hasil</span>
            </div>

            @if($catProducts->isNotEmpty())
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach($catProducts as $product)
                        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:shadow-lg">
                            <a href="{{ route('shop.product', $product->slug) }}" class="block">
                                <div class="relative aspect-square">
                                    <img src="{{ $product->image_url ?? 'https://images.unsplash.com/photo-1548199973-03ccebb453c0?w=400' }}"
                                         alt="{{ $product->name }}"
                                         class="h-full w-full object-cover"
                                         loading="lazy">
                                    @if($product->has_discount)
                                        <span class="absolute left-3 top-3 rounded-lg bg-red-500 px-2 py-1 text-xs font-bold text-white">
                                            -{{ $product->discount_percentage }}%
                                        </span>
                                    @endif
                                    @if($product->is_featured)
                                        <span class="absolute right-3 top-3 rounded-lg bg-amber-400 px-2 py-1 text-xs font-bold text-amber-900">
                                            <i class="fa-solid fa-star mr-1"></i>Unggulan
                                        </span>
                                    @endif
                                </div>
                                <div class="p-4">
                                    <p class="text-xs text-emerald-600">{{ $product->category?->name }}</p>
                                    <h3 class="mt-1 line-clamp-2 font-semibold text-slate-900">{{ $product->name }}</h3>

                                    @if($product->type === 'live')
                                        <div class="mt-2 grid grid-cols-2 gap-2 text-xs text-slate-500">
                                            @if($product->weight)
                                                <span>Berat: {{ $product->weight }} kg</span>
                                            @endif
                                            @if($product->origin)
                                                <span>Asal: {{ $product->origin }}</span>
                                            @endif
                                        </div>
                                    @endif

                                    <div class="mt-3 flex items-baseline justify-between">
                                        <p class="font-bold text-emerald-700">{{ $product->formatted_current_price }}</p>
                                        @if($product->has_discount)
                                            <p class="text-xs text-slate-400 line-through">{{ $product->formatted_price }}</p>
                                        @endif
                                    </div>

                                    @if($product->owner?->farmProfile)
                                        <div class="mt-3 flex items-center gap-2 border-t border-slate-100 pt-3">
                                            @if($product->owner->farmProfile->logo)
                                                <img src="{{ asset('storage/' . $product->owner->farmProfile->logo) }}"
                                                     alt="Farm Logo"
                                                     class="size-5 rounded-full object-cover">
                                            @endif
                                            <span class="text-xs text-slate-500">{{ $product->owner->farmProfile->farm_name }}</span>
                                        </div>
                                    @endif
                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>

                {{-- Pagination --}}
                @if($catProducts->hasPages())
                    <div class="mt-8">
                        {{ $catProducts->links() }}
                    </div>
                @endif
            @else
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-12 text-center">
                    <i class="fa-solid fa-box-open text-4xl text-slate-300"></i>
                    <h3 class="mt-4 text-lg font-semibold text-slate-700">Tidak ada produk</h3>
                    <p class="mt-2 text-slate-500">Belum ada produk {{ $category }} di {{ $location }}</p>
                </div>
            @endif
        </div>
    </section>

    {{-- Featured Products --}}
    @if($catFeaturedProducts->isNotEmpty())
    <section class="bg-slate-50 py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-6 text-xl font-bold text-slate-900">Produk Rekomendasi</h2>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($catFeaturedProducts as $product)
                    <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:shadow-lg">
                        <a href="{{ route('shop.product', $product->slug) }}" class="block">
                            <div class="relative aspect-square">
                                <img src="{{ $product->image_url ?? 'https://images.unsplash.com/photo-1548199973-03ccebb453c0?w=400' }}"
                                     alt="{{ $product->name }}"
                                     class="h-full w-full object-cover"
                                     loading="lazy">
                            </div>
                            <div class="p-4">
                                <p class="text-xs text-emerald-600">{{ $product->category?->name }}</p>
                                <h3 class="mt-1 line-clamp-2 font-semibold text-slate-900">{{ $product->name }}</h3>
                                <p class="mt-2 font-bold text-emerald-700">{{ $product->formatted_current_price }}</p>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
    @endif
</div>
