<div>
    {{-- SEO Meta & Computed Properties Defaults --}}
    @php
        $provinceName = $province ?? 'Provinsi';
        $provinceStats = $stats ?? ['farmers' => 0, 'products' => 0, 'regencies' => 0];
        $provinceSeoData = $seoData ?? [];
        $provinceBreadcrumb = $provinceSeoData['breadcrumbs'][2]['name'] ?? $provinceName;

        // Computed properties with defaults
        $provinceRegencies = $regencies ?? collect();
        $provinceFarmers = $farmers ?? collect();
        $provinceFeaturedProducts = $featuredProducts ?? collect();
        $provinceLatestProducts = $latestProducts ?? collect();
        $provinceRelatedLocations = $relatedLocations ?? [];
        $provinceLocalSchema = $localBusinessSchema ?? null;

        // SEO
        $seoTitle = $seo['title'] ?? $provinceName . ' - AbodaFarm';
        $seoDescription = $seo['description'] ?? $provinceSeoData['description'] ?? null;
        $seoKeywords = $seo['keywords'] ?? $provinceSeoData['keywords'] ?? null;
        $seoBreadcrumbs = $seo['breadcrumbs'] ?? $provinceSeoData['breadcrumbs'] ?? null;
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
                <a href="/peternak" class="text-slate-500 hover:text-emerald-600">Peternak</a>
                <i class="fa-solid fa-chevron-right text-xs text-slate-400"></i>
                <span class="font-medium text-slate-900">{{ $provinceName }}</span>
            </nav>
        </div>
    </div>

    {{-- Hero Section --}}
    <section class="bg-gradient-to-br from-emerald-600 to-emerald-800 py-16 text-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-2">
                <div class="space-y-6">
                    <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1 text-sm">
                        <i class="fa-solid fa-map-marker-alt"></i>
                        <span>{{ $provinceStats['regencies'] }} Kabupaten/Kota</span>
                    </div>
                    <h1 class="text-4xl font-bold">Peternak & Ternak di {{ $provinceBreadcrumb }}</h1>
                    <p class="text-lg text-emerald-100">
                        Temukan {{ $provinceStats['farmers'] }} peternak terpercaya dengan {{ $provinceStats['products'] }} produk ternak berkualitas di {{ $provinceBreadcrumb }}.
                    </p>
                    <div class="flex flex-wrap gap-4">
                        <a href="#peternak" class="inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3 font-semibold text-emerald-700 transition hover:bg-emerald-50">
                            <i class="fa-solid fa-users"></i>
                            Lihat Peternak
                        </a>
                        <a href="#produk" class="inline-flex items-center gap-2 rounded-xl border border-white/30 bg-white/10 px-6 py-3 font-semibold text-white transition hover:bg-white/20">
                            <i class="fa-solid fa-shopping-bag"></i>
                            Belanja Ternak
                        </a>
                    </div>
                </div>
                <div class="hidden lg:block">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="rounded-2xl bg-white/10 p-6 text-center backdrop-blur">
                            <p class="text-4xl font-bold">{{ $provinceStats['farmers'] }}</p>
                            <p class="text-emerald-100">Peternak</p>
                        </div>
                        <div class="rounded-2xl bg-white/10 p-6 text-center backdrop-blur">
                            <p class="text-4xl font-bold">{{ $provinceStats['products'] }}</p>
                            <p class="text-emerald-100">Produk</p>
                        </div>
                        <div class="rounded-2xl bg-white/10 p-6 text-center backdrop-blur">
                            <p class="text-4xl font-bold">{{ $provinceStats['regencies'] }}</p>
                            <p class="text-emerald-100">Wilayah</p>
                        </div>
                        <div class="rounded-2xl bg-white/10 p-6 text-center backdrop-blur">
                            <p class="text-4xl font-bold">
                                <i class="fa-solid fa-star text-amber-400"></i>
                            </p>
                            <p class="text-emerald-100">Terpercaya</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Kabupaten/Kota Section --}}
    @if($provinceRegencies->isNotEmpty())
    <section class="py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-2xl font-bold text-slate-900">Jelajahi per Kabupaten/Kota</h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($provinceRegencies as $regency)
                    <a href="/peternak/{{ Str::slug($province) }}/{{ Str::slug($regency->regency) }}"
                       class="group flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-emerald-200 hover:shadow-md">
                        <div>
                            <h3 class="font-semibold text-slate-900 group-hover:text-emerald-600">{{ $regency->regency }}</h3>
                            <p class="text-sm text-slate-500">{{ $regency->farm_count }} peternak</p>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($regency->avg_rating)
                                <span class="flex items-center gap-1 text-sm text-amber-500">
                                    <i class="fa-solid fa-star text-xs"></i>
                                    {{ number_format($regency->avg_rating, 1) }}
                                </span>
                            @endif
                            <i class="fa-solid fa-chevron-right text-slate-400 transition group-hover:translate-x-1 group-hover:text-emerald-600"></i>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Farmers Section --}}
    @if($provinceFarmers->isNotEmpty())
    <section id="peternak" class="bg-slate-50 py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-end justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">Peternak Terdekat</h2>
                    <p class="mt-1 text-slate-600">Peternak terpercaya di {{ $provinceBreadcrumb }}</p>
                </div>
                <a href="#" class="text-sm font-semibold text-emerald-600 hover:text-emerald-700">Lihat semua</a>
            </div>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach($provinceFarmers as $farmer)
                    <a href="{{ route('peternak.profile', $farmer->user?->username ?? $farmer->user_id) }}"
                       class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:shadow-lg">
                        <div class="relative aspect-[4/3]">
                            <img src="{{ $farmer->cover_image ? asset('storage/' . $farmer->cover_image) : ($farmer->image ? asset('storage/' . $farmer->image) : 'https://images.unsplash.com/photo-1500595046743-cd271d694d30?w=400') }}"
                                 alt="{{ $farmer->farm_name }}"
                                 class="h-full w-full object-cover"
                                 loading="lazy">
                            @if($farmer->is_verified)
                                <span class="absolute right-3 top-3 flex items-center gap-1 rounded-full bg-emerald-500 px-2 py-1 text-xs font-medium text-white">
                                    <i class="fa-solid fa-check"></i>
                                    Terverifikasi
                                </span>
                            @endif
                        </div>
                        <div class="p-4">
                            <div class="flex items-center gap-2">
                                @if($farmer->logo)
                                    <img src="{{ asset('storage/' . $farmer->logo) }}"
                                         alt="{{ $farmer->farm_name }} Logo"
                                         class="size-8 rounded-full object-cover">
                                @endif
                                <div class="min-w-0 flex-1">
                                    <h3 class="truncate font-semibold text-slate-900 group-hover:text-emerald-600">{{ $farmer->farm_name }}</h3>
                                    <p class="truncate text-xs text-slate-500">{{ $farmer->full_address }}</p>
                                </div>
                            </div>
                            @if($farmer->rating)
                                <div class="mt-3 flex items-center gap-2">
                                    <div class="flex items-center gap-1">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fa-solid fa-star text-xs {{ $i <= round($farmer->rating) ? 'text-amber-400' : 'text-slate-200' }}"></i>
                                        @endfor
                                    </div>
                                    <span class="text-sm text-slate-600">{{ number_format($farmer->rating, 1) }}</span>
                                    <span class="text-xs text-slate-400">({{ $farmer->total_reviews }})</span>
                                </div>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Featured Products --}}
    @if($provinceFeaturedProducts->isNotEmpty())
    <section id="produk" class="py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-end justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">Produk Unggulan</h2>
                    <p class="mt-1 text-slate-600">Produk terbaik dari peternak di {{ $provinceBreadcrumb }}</p>
                </div>
                <a href="#" class="text-sm font-semibold text-emerald-600 hover:text-emerald-700">Lihat semua</a>
            </div>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($provinceFeaturedProducts as $product)
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
                            </div>
                            <div class="p-4">
                                <p class="text-xs text-emerald-600">{{ $product->category?->name }}</p>
                                <h3 class="mt-1 line-clamp-2 font-semibold text-slate-900">{{ $product->name }}</h3>
                                <p class="mt-2 font-bold text-emerald-700">{{ $product->formatted_current_price }}</p>
                                @if($product->has_discount)
                                    <p class="text-xs text-slate-400 line-through">{{ $product->formatted_price }}</p>
                                @endif
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Related Locations --}}
    @if(count($provinceRelatedLocations) > 0)
    <section class="bg-slate-50 py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-2xl font-bold text-slate-900">Lokasi Lainnya</h2>
            <div class="flex flex-wrap gap-3">
                @foreach($provinceRelatedLocations as $location)
                    <a href="{{ $location['url'] }}"
                       class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-emerald-200 hover:text-emerald-600">
                        <i class="fa-solid fa-map-marker-alt text-emerald-500"></i>
                        {{ $location['name'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- LocalBusiness Schema (hidden) --}}
    @if($provinceLocalSchema)
    <script type="application/ld+json">
        {!! json_encode($provinceLocalSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    @endif
</div>
