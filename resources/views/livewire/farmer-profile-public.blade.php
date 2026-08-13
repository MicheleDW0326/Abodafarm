<div>
    @php
        $badges = $farmProfile?->active_badges ?? [];
        $seoTitle = $seo['title'] ?? ($farmProfile?->farm_name ?? 'Profil Peternak') . ' - AbodaFarm';
        $seoDescription = $seo['description'] ?? 'Profil peternak terpercaya di AbodaFarm';
        $seoSchema = $seo['schema'] ?? null;
    @endphp

    {{-- SEO Meta Tags --}}
    @section('title', $seoTitle)
    @section('description', $seoDescription)

    {{-- Cover Section --}}
    <div class="relative">
        {{-- Cover Photo --}}
        <div class="h-48 md:h-64 bg-gradient-to-br from-emerald-500 to-green-700 relative overflow-hidden">
            @if($coverImageUrl)
                <img src="{{ $coverImageUrl }}" alt="Cover {{ $farmProfile?->farm_name }}"
                     class="w-full h-full object-cover">
            @else
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500 to-green-700">
                    <div class="absolute inset-0 opacity-20"
                         style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.4\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
                @endif
            </div>
        </div>

        {{-- Profile Card Overlay --}}
        <div class="absolute bottom-0 left-0 right-0 transform translate-y-1/2 px-4 md:px-8">
            <div class="bg-white rounded-2xl shadow-lg p-4 md:p-6">
                <div class="flex items-start gap-4">
                    {{-- Logo --}}
                    <div class="relative">
                        <img src="{{ $logoUrl }}"
                             alt="{{ $farmProfile?->farm_name }}"
                             class="w-20 h-20 md:w-24 md:h-24 rounded-full object-cover border-4 border-white shadow-md">
                        @if($farmProfile?->is_verified)
                            <div class="absolute -bottom-1 -right-1 w-7 h-7 bg-blue-500 rounded-full flex items-center justify-center border-2 border-white">
                                <i class="fa-solid fa-check text-white text-xs"></i>
                            </div>
                        @endif
                    </div>

                    {{-- Farm Info --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h1 class="text-xl md:text-2xl font-bold text-gray-900 truncate">
                                {{ $farmProfile?->farm_name ?? $farmer?->name }}
                            </h1>
                        </div>

                        <div class="flex items-center gap-3 mt-1 text-sm text-gray-600">
                            @if($farmProfile?->is_verified)
                                <span class="inline-flex items-center gap-1 text-blue-600 font-medium">
                                    <i class="fa-solid fa-circle-check"></i>
                                    Terverifikasi
                                </span>
                                <span class="text-gray-300">•</span>
                            @endif
                            <span class="flex items-center gap-1">
                                <i class="fa-solid fa-location-dot text-emerald-500"></i>
                                {{ $location }}
                            </span>
                            <span class="text-gray-300">•</span>
                            <span class="text-gray-500">
                                <i class="fa-solid fa-calendar mr-1"></i>
                                Bergabung {{ $joinedDate }}
                            </span>
                        </div>

                        {{-- Rating & Stats Quick View --}}
                        <div class="flex items-center gap-4 mt-2">
                            @if($averageRating > 0)
                                <div class="flex items-center gap-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= floor($averageRating))
                                            <i class="fa-solid fa-star text-amber-400 text-sm"></i>
                                        @elseif($i - 0.5 <= $averageRating)
                                            <i class="fa-solid fa-star-half-alt text-amber-400 text-sm"></i>
                                        @else
                                            <i class="fa-regular fa-star text-gray-300 text-sm"></i>
                                        @endif
                                    @endfor
                                    <span class="ml-1 font-semibold text-gray-800">{{ number_format($averageRating, 1) }}</span>
                                    <span class="text-gray-500 text-sm">({{ number_format($totalReviews) }} ulasan)</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Quick Stats Desktop --}}
                    <div class="hidden md:flex items-center gap-6 text-center">
                        <div>
                            <div class="text-xl font-bold text-gray-900">{{ number_format($totalProducts) }}</div>
                            <div class="text-xs text-gray-500">Produk</div>
                        </div>
                        <div class="w-px h-8 bg-gray-200"></div>
                        <div>
                            <div class="text-xl font-bold text-gray-900">{{ number_format($farmProfile?->products_sold ?? 0) }}</div>
                            <div class="text-xs text-gray-500">Terjual</div>
                        </div>
                        @if($farmProfile?->response_rate > 0)
                            <div class="w-px h-8 bg-gray-200"></div>
                            <div>
                                <div class="text-xl font-bold text-emerald-600">{{ number_format($farmProfile->response_rate, 0) }}%</div>
                                <div class="text-xs text-gray-500">Respon</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="mt-20 md:mt-24 pb-12 px-4 md:px-8 max-w-7xl mx-auto">
        {{-- Trust Badges --}}
        @if(count($badges) > 0)
            <div class="flex flex-wrap gap-2 mb-6">
                @foreach($badges as $badge)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-medium border {{ $this->getBadgeClass($badge['type']) }}">
                        <i class="{{ $badge['icon'] }}"></i>
                        {{ $badge['name'] }}
                    </span>
                @endforeach
            </div>
        @endif

        {{-- Tab Navigation --}}
        <div class="border-b border-gray-200 mb-6">
            <nav class="flex gap-6 -mb-px">
                <button wire:click="setTab('products')"
                        class="pb-3 px-1 text-sm font-medium transition-colors relative
                        {{ $tab === 'products' ? 'text-emerald-600 border-emerald-600' : 'text-gray-500 hover:text-gray-700 border-transparent' }}">
                    <i class="fa-solid fa-box-open mr-2"></i>
                    Produk
                    <span class="ml-1.5 text-xs bg-gray-100 px-1.5 py-0.5 rounded-full">{{ $totalProducts }}</span>
                </button>
                <button wire:click="setTab('about')"
                        class="pb-3 px-1 text-sm font-medium transition-colors relative
                        {{ $tab === 'about' ? 'text-emerald-600 border-emerald-600' : 'text-gray-500 hover:text-gray-700 border-transparent' }}">
                    <i class="fa-solid fa-info-circle mr-2"></i>
                    Tentang
                </button>
                <button wire:click="setTab('reviews')"
                        class="pb-3 px-1 text-sm font-medium transition-colors relative
                        {{ $tab === 'reviews' ? 'text-emerald-600 border-emerald-600' : 'text-gray-500 hover:text-gray-700 border-transparent' }}">
                    <i class="fa-solid fa-star mr-2"></i>
                    Ulasan
                    <span class="ml-1.5 text-xs bg-gray-100 px-1.5 py-0.5 rounded-full">{{ $totalReviews }}</span>
                </button>
                <button wire:click="setTab('gallery')"
                        class="pb-3 px-1 text-sm font-medium transition-colors relative
                        {{ $tab === 'gallery' ? 'text-emerald-600 border-emerald-600' : 'text-gray-500 hover:text-gray-700 border-transparent' }}">
                    <i class="fa-solid fa-images mr-2"></i>
                    Galeri
                    <span class="ml-1.5 text-xs bg-gray-100 px-1.5 py-0.5 rounded-full">{{ $totalGalleries }}</span>
                </button>
            </nav>
        </div>

        {{-- Tab Content --}}
        <div>
            {{-- Products Tab --}}
            @if($tab === 'products')
                <div>
                    {{-- Filter & Sort Bar --}}
                    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                        <div class="flex flex-wrap gap-2">
                            <button wire:click="setProductFilter('all')"
                                    class="px-4 py-2 rounded-full text-sm font-medium transition-colors
                                    {{ $productFilter === 'all' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                Semua
                            </button>
                            <button wire:click="setProductFilter('ternak')"
                                    class="px-4 py-2 rounded-full text-sm font-medium transition-colors
                                    {{ $productFilter === 'ternak' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                <i class="fa-solid fa-cow mr-1"></i>
                                Ternak
                            </button>
                            <button wire:click="setProductFilter('daging')"
                                    class="px-4 py-2 rounded-full text-sm font-medium transition-colors
                                    {{ $productFilter === 'daging' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                <i class="fa-solid fa-drumstick-bite mr-1"></i>
                                Daging Segar
                            </button>
                            <button wire:click="setProductFilter('pakan')"
                                    class="px-4 py-2 rounded-full text-sm font-medium transition-colors
                                    {{ $productFilter === 'pakan' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                <i class="fa-solid fa-carrot mr-1"></i>
                                Pakan
                            </button>
                        </div>
                        <select wire:change="setProductSort($event.target.value)"
                                class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="newest" {{ $productSort === 'newest' ? 'selected' : '' }}>Terbaru</option>
                            <option value="price_low" {{ $productSort === 'price_low' ? 'selected' : '' }}>Harga Terendah</option>
                            <option value="price_high" {{ $productSort === 'price_high' ? 'selected' : '' }}>Harga Tertinggi</option>
                            <option value="popular" {{ $productSort === 'popular' ? 'selected' : '' }}>Terpopuler</option>
                        </select>
                    </div>

                    {{-- Products Grid --}}
                    @if($products->count() > 0)
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
                            @foreach($products as $product)
                                <a href="{{ route('shop.product', $product->slug) }}"
                                   class="group bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                                    {{-- Product Image --}}
                                    <div class="aspect-square bg-gray-100 relative overflow-hidden">
                                        @if($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}"
                                                 alt="{{ $product->name }}"
                                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-gray-400">
                                                <i class="fa-solid fa-image text-4xl"></i>
                                            </div>
                                        @endif
                                        @if($product->type === 'live')
                                            <div class="absolute top-2 left-2 bg-emerald-500 text-white text-xs px-2 py-1 rounded-full">
                                                <i class="fa-solid fa-cow mr-1"></i>Ternak
                                            </div>
                                        @elseif($product->type === 'fresh')
                                            <div class="absolute top-2 left-2 bg-red-500 text-white text-xs px-2 py-1 rounded-full">
                                                <i class="fa-solid fa-drumstick-bite mr-1"></i>Daging
                                            </div>
                                        @endif
                                    </div>
                                    {{-- Product Info --}}
                                    <div class="p-3">
                                        <h3 class="font-semibold text-gray-900 text-sm line-clamp-2 mb-1">
                                            {{ $product->name }}
                                        </h3>
                                        <div class="flex items-center gap-1 text-xs text-gray-500 mb-2">
                                            @if($product->type === 'live' && $product->weight)
                                                <span>{{ $product->weight }} kg</span>
                                                @if($product->age)
                                                    <span>•</span>
                                                    <span>{{ $product->age }}</span>
                                                @endif
                                            @elseif($product->type === 'fresh')
                                                <span>per unit</span>
                                            @else
                                                <span>{{ $product->stock }} tersedia</span>
                                            @endif
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold text-emerald-600">
                                                Rp {{ number_format($product->price, 0, ',', '.') }}
                                            </span>
                                            @if($product->stock <= 3 && $product->stock > 0)
                                                <span class="text-xs text-amber-600 font-medium">
                                                    Tersisa {{ $product->stock }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12 bg-gray-50 rounded-xl">
                            <i class="fa-solid fa-box-open text-4xl text-gray-300 mb-3"></i>
                            <p class="text-gray-500">Belum ada produk tersedia</p>
                        </div>
                    @endif
                </div>
            @endif

            {{-- About Tab --}}
            @if($tab === 'about')
                <div class="grid md:grid-cols-3 gap-6">
                    {{-- Left Column --}}
                    <div class="md:col-span-2 space-y-6">
                        {{-- About Description --}}
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                            <h2 class="text-lg font-bold text-gray-900 mb-4">
                                <i class="fa-solid fa-info-circle text-emerald-500 mr-2"></i>
                                Tentang {{ $farmProfile?->farm_name ?? 'Peternakan' }}
                            </h2>
                            @if($farmProfile?->description)
                                <p class="text-gray-600 leading-relaxed whitespace-pre-line">
                                    {{ $farmProfile->description }}
                                </p>
                            @else
                                <p class="text-gray-400 italic">Deskripsi belum tersedia</p>
                            @endif
                        </div>

                        {{-- Location --}}
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                            <h2 class="text-lg font-bold text-gray-900 mb-4">
                                <i class="fa-solid fa-map-marker-alt text-emerald-500 mr-2"></i>
                                Lokasi
                            </h2>
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center text-emerald-600 flex-shrink-0">
                                    <i class="fa-solid fa-location-dot"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $farmProfile?->farm_name }}</p>
                                    <p class="text-gray-600 text-sm">{{ $farmProfile?->address }}</p>
                                    <p class="text-gray-500 text-sm">{{ $location }}</p>
                                </div>
                            </div>
                            @if($farmProfile?->map_url)
                                <a href="{{ $farmProfile->map_url }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="mt-4 inline-flex items-center gap-2 text-emerald-600 hover:text-emerald-700 font-medium text-sm">
                                    <i class="fa-solid fa-external-link-alt"></i>
                                    Lihat di Google Maps
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Right Column - Stats & Badges --}}
                    <div class="space-y-6">
                        {{-- Statistics --}}
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                            <h3 class="font-bold text-gray-900 mb-4">Statistik</h3>
                            <div class="space-y-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600 text-sm">Produk Ditawarkan</span>
                                    <span class="font-bold text-gray-900">{{ number_format($totalProducts) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600 text-sm">Produk Terjual</span>
                                    <span class="font-bold text-gray-900">{{ number_format($farmProfile?->products_sold ?? 0) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600 text-sm">Booking Selesai</span>
                                    <span class="font-bold text-gray-900">{{ number_format($farmProfile?->completed_bookings ?? 0) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600 text-sm">Pesanan Selesai</span>
                                    <span class="font-bold text-gray-900">{{ number_format($farmProfile?->completed_orders ?? 0) }}</span>
                                </div>
                                @if($farmProfile?->repeat_customers > 0)
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600 text-sm">Pelanggan Kembali</span>
                                        <span class="font-bold text-emerald-600">{{ number_format($farmProfile->repeat_customers) }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Trust Score --}}
                        @if($farmProfile?->trust_score)
                            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                                <h3 class="font-bold text-gray-900 mb-4">Tingkat Kepercayaan</h3>
                                <div class="flex items-center gap-3">
                                    <div class="relative w-16 h-16">
                                        <svg class="w-16 h-16 transform -rotate-90">
                                            <circle cx="32" cy="32" r="28" stroke="#E5E7EB" stroke-width="6" fill="none"/>
                                            <circle cx="32" cy="32" r="28"
                                                    stroke="{{ $farmProfile->trust_score >= 90 ? '#10B981' : ($farmProfile->trust_score >= 75 ? '#3B82F6' : '#F59E0B') }}"
                                                    stroke-width="6" fill="none"
                                                    stroke-dasharray="{{ round(($farmProfile->trust_score / 100) * 175.9 }} 175.9"/>
                                        </svg>
                                        <span class="absolute inset-0 flex items-center justify-center text-sm font-bold text-gray-900">
                                            {{ round($farmProfile->trust_score) }}%
                                        </span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $farmProfile->trust_score_label }}</p>
                                        <p class="text-xs text-gray-500">Trust Score</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Contact --}}
                        @if($farmProfile?->phone)
                            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                                <h3 class="font-bold text-gray-900 mb-4">Kontak</h3>
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $farmProfile->phone) }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="inline-flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                    <i class="fa-brands fa-whatsapp text-lg"></i>
                                    Hubungi via WhatsApp
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Reviews Tab --}}
            @if($tab === 'reviews')
                <div class="grid md:grid-cols-3 gap-6">
                    {{-- Rating Overview --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <div class="text-center mb-6">
                            <div class="text-5xl font-bold text-gray-900">{{ number_format($averageRating, 1) }}</div>
                            <div class="flex justify-center gap-1 my-2">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= floor($averageRating))
                                        <i class="fa-solid fa-star text-amber-400"></i>
                                    @elseif($i - 0.5 <= $averageRating)
                                        <i class="fa-solid fa-star-half-alt text-amber-400"></i>
                                    @else
                                        <i class="fa-regular fa-star text-gray-300"></i>
                                    @endif
                                @endfor
                            </div>
                            <p class="text-gray-500 text-sm">{{ number_format($totalReviews) }} ulasan</p>
                        </div>

                        {{-- Rating Distribution --}}
                        <div class="space-y-2">
                            @for($i = 5; $i >= 1; $i--)
                                <div class="flex items-center gap-2">
                                    <span class="text-sm text-gray-600 w-8">{{ $i }} <i class="fa-solid fa-star text-xs text-amber-400"></i></span>
                                    <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-amber-400 rounded-full transition-all"
                                             style="width: {{ $ratingDistribution[$i] }}%"></div>
                                    </div>
                                    <span class="text-sm text-gray-500 w-12 text-right">{{ $ratingDistribution[$i] }}%</span>
                                </div>
                            @endfor
                        </div>
                    </div>

                    {{-- Reviews List --}}
                    <div class="md:col-span-2 space-y-4">
                        @if($reviews->count() > 0)
                            @foreach($reviews as $review)
                                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                                    <div class="flex items-start justify-between mb-2">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center text-emerald-600 font-bold">
                                                {{ strtoupper(substr($review->user?->name ?? 'A', 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900">{{ $review->user?->name ?? 'Anonim' }}</p>
                                                <div class="flex items-center gap-2 text-xs text-gray-500">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        @if($i <= $review->rating)
                                                            <i class="fa-solid fa-star text-amber-400 text-xs"></i>
                                                        @else
                                                            <i class="fa-regular fa-star text-gray-300 text-xs"></i>
                                                        @endif
                                                    @endfor
                                                    <span>•</span>
                                                    <span>{{ $review->created_at->diffForHumans() }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        @if($review->order?->order_number)
                                            <span class="text-xs text-gray-400">
                                                #{{ substr($review->order->order_number, 0, 12) }}...
                                            </span>
                                        @endif
                                    </div>
                                    @if($review->title)
                                        <h4 class="font-semibold text-gray-800 mb-1">{{ $review->title }}</h4>
                                    @endif
                                    <p class="text-gray-600 text-sm">{{ $review->content }}</p>
                                    @if($review->photos->count() > 0)
                                        <div class="flex gap-2 mt-3">
                                            @foreach($review->photos->take(4) as $photo)
                                                <img src="{{ asset('storage/' . $photo->path) }}"
                                                     alt="Review photo"
                                                     class="w-16 h-16 rounded-lg object-cover">
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        @else
                            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
                                <i class="fa-solid fa-star text-4xl text-gray-300 mb-3"></i>
                                <p class="text-gray-500">Belum ada ulasan</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Gallery Tab --}}
            @if($tab === 'gallery')
                <div>
                    @if($farmProfile?->galleries->count() > 0)
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            @foreach($farmProfile->galleries as $gallery)
                                <a href="{{ asset('storage/' . $gallery->image) }}"
                                   class="aspect-square rounded-xl overflow-hidden bg-gray-100 group">
                                    <img src="{{ asset('storage/' . $gallery->image) }}"
                                         alt="Galeri {{ $farmProfile?->farm_name }}"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
                            <i class="fa-solid fa-images text-4xl text-gray-300 mb-3"></i>
                            <p class="text-gray-500">Galeri foto belum tersedia</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- Mobile Stats Bar (fixed bottom) --}}
    <div class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 px-4 py-3 z-40">
        <div class="flex items-center justify-around text-center">
            <div>
                <div class="text-lg font-bold text-gray-900">{{ number_format($totalProducts) }}</div>
                <div class="text-xs text-gray-500">Produk</div>
            </div>
            <div class="w-px h-8 bg-gray-200"></div>
            <div>
                <div class="text-lg font-bold text-gray-900">{{ number_format($farmProfile?->products_sold ?? 0) }}</div>
                <div class="text-xs text-gray-500">Terjual</div>
            </div>
            <div class="w-px h-8 bg-gray-200"></div>
            <div>
                <div class="text-lg font-bold text-emerald-600">
                    @if($averageRating > 0)
                        {{ number_format($averageRating, 1) }}
                    @else
                        -
                    @endif
                </div>
                <div class="text-xs text-gray-500">Rating</div>
            </div>
        </div>
    </div>
    <div class="md:hidden h-20"></div>{{-- Spacer for mobile fixed bar --}}

    {{-- JSON-LD Schema for SEO --}}
    @if($seoSchema)
        <script type="application/ld+json">
            {!! json_encode($seoSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
        </script>
    @endif
</div>
