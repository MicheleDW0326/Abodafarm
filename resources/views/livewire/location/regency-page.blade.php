<div>
    {{-- SEO Meta & Computed Properties Defaults --}}
    @php
        $regSeoData = $seoData ?? [];
        $regStats = $stats ?? ['farmers' => 0, 'products' => 0, 'districts' => 0];

        // Computed properties with defaults
        $regDistricts = $districts ?? collect();
        $regFarmers = $farmers ?? collect();
        $regFeaturedProducts = $featuredProducts ?? collect();
        $regRelatedLocations = $relatedLocations ?? [];
        $regLocalSchema = $localBusinessSchema ?? null;

        // SEO
        $seoTitle = $seo['title'] ?? $regSeoData['title'] ?? ($regency ?? 'Kabupaten') . ' - AbodaFarm';
        $seoDescription = $seo['description'] ?? $regSeoData['description'] ?? null;
        $seoKeywords = $seo['keywords'] ?? $regSeoData['keywords'] ?? null;
        $seoBreadcrumbs = $seo['breadcrumbs'] ?? $regSeoData['breadcrumbs'] ?? null;
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
                <a href="/peternak/{{ Str::slug($province) }}" class="text-slate-500 hover:text-emerald-600">{{ $province }}</a>
                <i class="fa-solid fa-chevron-right text-xs text-slate-400"></i>
                <span class="font-medium text-slate-900">{{ $regency }}</span>
            </nav>
        </div>
    </div>

    {{-- Hero Section --}}
    <section class="bg-gradient-to-br from-emerald-600 to-emerald-800 py-12 text-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="space-y-4">
                    <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1 text-sm">
                        <i class="fa-solid fa-map-pin"></i>
                        <span>{{ $province }}</span>
                    </div>
                    <h1 class="text-3xl font-bold">Peternak di {{ $regency }}</h1>
                    <p class="text-emerald-100">
                        {{ $regStats['farmers'] }} peternak dengan {{ $regStats['products'] }} produk berkualitas
                    </p>
                </div>
                <div class="flex gap-4">
                    <a href="#peternak" class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-2.5 font-semibold text-emerald-700 transition hover:bg-emerald-50">
                        <i class="fa-solid fa-users"></i>
                        Lihat Peternak
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Districts Section --}}
    @if($regDistricts->isNotEmpty())
    <section class="py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-6 text-xl font-bold text-slate-900">Jelajahi per Kecamatan</h2>
            <div class="flex flex-wrap gap-3">
                @foreach($regDistricts as $district)
                    <a href="#"
                       class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-emerald-200 hover:text-emerald-600 hover:shadow-sm">
                        <span>{{ $district->district }}</span>
                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-500">{{ $district->farm_count }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Farmers Section --}}
    @if($regFarmers->isNotEmpty())
    <section id="peternak" class="bg-slate-50 py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-6 text-xl font-bold text-slate-900">Peternak di {{ $regency }}</h2>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach($regFarmers as $farmer)
                    <a href="{{ route('farmer.public-profile', $farmer->user?->name ?? $farmer->user_id) }}"
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
                            <h3 class="font-semibold text-slate-900 group-hover:text-emerald-600">{{ $farmer->farm_name }}</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ $farmer->district }}</p>
                            @if($farmer->rating)
                                <div class="mt-2 flex items-center gap-2">
                                    <div class="flex items-center gap-0.5">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fa-solid fa-star text-xs {{ $i <= round($farmer->rating) ? 'text-amber-400' : 'text-slate-200' }}"></i>
                                        @endfor
                                    </div>
                                    <span class="text-sm text-slate-600">{{ number_format($farmer->rating, 1) }}</span>
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
    @if($regFeaturedProducts->isNotEmpty())
    <section class="py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-6 text-xl font-bold text-slate-900">Produk dari {{ $regency }}</h2>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($regFeaturedProducts as $product)
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
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Related Locations --}}
    @if(count($regRelatedLocations) > 0)
    <section class="bg-slate-50 py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-4 text-lg font-semibold text-slate-900">Lokasi Lainnya di {{ $province }}</h2>
            <div class="flex flex-wrap gap-2">
                @foreach($regRelatedLocations as $location)
                    <a href="{{ $location['url'] }}"
                       class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-600 transition hover:border-emerald-200 hover:text-emerald-600">
                        <i class="fa-solid fa-map-marker-alt text-emerald-400"></i>
                        {{ $location['name'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- LocalBusiness Schema --}}
    @if($regLocalSchema)
    <script type="application/ld+json">
        {!! json_encode($regLocalSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    @endif
</div>
