<div x-data="{
    cartItems: JSON.parse(localStorage.getItem('abodafarm_cart') || '[]'),
    showToast: false,
    toastText: '',
    toastTimer: null,
    activeBanner: 0,
    bannerInterval: null,

    init() {
        // Banner auto-rotate
        this.bannerInterval = setInterval(() => {
            this.activeBanner = (this.activeBanner + 1) % {{ count($heroBanners) }};
        }, 5000);

        window.addEventListener('storage', (e) => {
            if (e.key === 'abodafarm_cart') {
                this.cartItems = JSON.parse(e.newValue || '[]');
            }
        });
    },

    showAddToast(name) {
        this.toastText = `${name} ditambahkan ke keranjang`;
        this.showToast = true;
        clearTimeout(this.toastTimer);
        this.toastTimer = setTimeout(() => {
            this.showToast = false;
        }, 2200);
    },

    addToCart(product) {
        const key = 'product_' + product.id + '_supply';
        const existing = this.cartItems.find(item => item.key === key);

        if (existing) {
            existing.qty++;
        } else {
            this.cartItems.push({
                key: key,
                product_id: product.id,
                name: product.name,
                price: product.current_price,
                image: product.image,
                type: 'supply',
                qty: 1
            });
        }

        this.persistCart();
        this.showAddToast(product.name);
        this.$dispatch('cart-updated');
    },

    persistCart() {
        localStorage.setItem('abodafarm_cart', JSON.stringify(this.cartItems));
    },

    get cartCount() {
        return this.cartItems.reduce((sum, item) => sum + item.qty, 0);
    }
}" x-on:cart-item-added.window="showAddToast($event.detail.name)"
    class="bg-slate-50 text-slate-900">

    <!-- Toast Notification -->
    <div x-show="showToast" x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed bottom-6 right-6 z-50 rounded-xl border border-emerald-200 bg-white px-5 py-3 shadow-lg shadow-emerald-100">
        <div class="flex items-center gap-3">
            <div class="flex size-8 items-center justify-center rounded-full bg-emerald-100">
                <svg class="size-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <span class="text-sm font-medium text-slate-700" x-text="toastText"></span>
        </div>
    </div>

    <!-- Hero Banner Carousel -->
    <section class="relative overflow-hidden bg-gradient-to-br from-emerald-600 to-emerald-800">
        <div class="relative mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8 lg:py-20">
            <template x-for="(banner, index) in {{ json_encode($heroBanners) }}" :key="index">
                <div x-show="activeBanner === index"
                     x-transition:enter="transition ease-out duration-500"
                     x-transition:enter-start="opacity-0 translate-x-8"
                     x-transition:enter-end="opacity-100 translate-x-0"
                     class="grid gap-8 lg:grid-cols-2 lg:items-center">
                    <!-- Content -->
                    <div class="text-white">
                        <span class="mb-3 inline-block rounded-full bg-white/20 px-3 py-1 text-xs font-semibold uppercase tracking-wide backdrop-blur-sm"
                              x-text="banner.badge"></span>
                        <h1 class="text-3xl font-bold leading-tight sm:text-4xl lg:text-5xl" x-text="banner.title"></h1>
                        <p class="mt-4 max-w-lg text-base text-white/80 sm:text-lg" x-text="banner.description"></p>
                        <div class="mt-8 flex flex-wrap gap-4">
                            <a href="#products"
                               class="inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3 text-sm font-semibold text-emerald-700 shadow-lg transition-all hover:scale-105 hover:bg-emerald-50">
                                <i class="fa-solid fa-bag-shopping"></i>
                                Belanja Sekarang
                            </a>
                            <a href="{{ route('home') }}"
                               class="inline-flex items-center gap-2 rounded-xl border-2 border-white/30 px-6 py-3 text-sm font-semibold text-white transition-all hover:border-white/50 hover:bg-white/10">
                                <i class="fa-solid fa-cow"></i>
                                Lihat Ternak
                            </a>
                        </div>
                    </div>
                    <!-- Image -->
                    <div class="relative">
                        <div class="aspect-square overflow-hidden rounded-3xl shadow-2xl">
                            <img :src="banner.image" :alt="banner.title" class="size-full object-cover"
                                 x-show="activeBanner === index">
                        </div>
                        <!-- Decorative elements -->
                        <div class="absolute -bottom-4 -right-4 size-24 rounded-full bg-amber-400 opacity-20"></div>
                        <div class="absolute -top-4 -left-4 size-16 rounded-full bg-white opacity-10"></div>
                    </div>
                </div>
            </template>

            <!-- Banner Dots -->
            <div class="mt-8 flex justify-center gap-2">
                <template x-for="(banner, index) in {{ count($heroBanners) }}" :key="index">
                    <button @click="activeBanner = index"
                            :class="activeBanner === index ? 'size-3 bg-white' : 'size-2 bg-white/40'"
                            class="rounded-full transition-all"></button>
                </template>
            </div>
        </div>
    </section>

    <!-- Promotional Banner -->
    <section class="mx-auto -mt-6 max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-2xl border border-amber-200 bg-gradient-to-r from-amber-50 to-orange-50 p-4 shadow-sm">
            <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                <div class="flex items-center gap-4">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                        <i class="fa-solid fa-truck-fast text-xl"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-amber-800">{{ $promotionalBanner['title'] }}</p>
                        <p class="text-sm text-amber-600">{{ $promotionalBanner['subtitle'] }}</p>
                    </div>
                </div>
                <a href="#products"
                   class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-amber-600">
                    Belanja Sekarang
                </a>
            </div>
        </div>
    </section>

    <!-- Search and Filters -->
    <section class="mx-auto mt-8 max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <!-- Search Bar -->
            <div class="relative flex-1">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari produk..."
                       class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-11 pr-4 text-sm shadow-sm transition focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                <svg class="pointer-events-none absolute left-4 top-1/2 size-5 -translate-y-1/2 text-slate-400"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>

            <!-- Sort and Filter Buttons -->
            <div class="flex items-center gap-3">
                <!-- Sort Dropdown -->
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open"
                            class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50">
                        <i class="fa-solid fa-sort text-slate-400"></i>
                        <span>Urutkan</span>
                        <i class="fa-solid fa-chevron-down text-xs text-slate-400"></i>
                    </button>
                    <div x-show="open" @click.outside="open = false"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="absolute right-0 top-full z-20 mt-2 w-48 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg">
                        <div class="py-1">
                            <button wire:click="setSort('newest')"
                                    class="flex w-full items-center gap-2 px-4 py-2.5 text-sm text-slate-700 hover:bg-emerald-50 hover:text-emerald-700">
                                <i class="fa-solid fa-clock text-slate-400"></i> Terbaru
                            </button>
                            <button wire:click="setSort('price_low')"
                                    class="flex w-full items-center gap-2 px-4 py-2.5 text-sm text-slate-700 hover:bg-emerald-50 hover:text-emerald-700">
                                <i class="fa-solid fa-arrow-up text-slate-400"></i> Harga Terendah
                            </button>
                            <button wire:click="setSort('price_high')"
                                    class="flex w-full items-center gap-2 px-4 py-2.5 text-sm text-slate-700 hover:bg-emerald-50 hover:text-emerald-700">
                                <i class="fa-solid fa-arrow-down text-slate-400"></i> Harga Tertinggi
                            </button>
                            <button wire:click="setSort('rating')"
                                    class="flex w-full items-center gap-2 px-4 py-2.5 text-sm text-slate-700 hover:bg-emerald-50 hover:text-emerald-700">
                                <i class="fa-solid fa-star text-slate-400"></i> Tertinggi Rating
                            </button>
                            <button wire:click="setSort('best_selling')"
                                    class="flex w-full items-center gap-2 px-4 py-2.5 text-sm text-slate-700 hover:bg-emerald-50 hover:text-emerald-700">
                                <i class="fa-solid fa-fire text-slate-400"></i> Terlaris
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Filter Toggle -->
                <button wire:click="$toggle('showFilters')"
                        :class="$wire.showFilters ? 'bg-emerald-100 text-emerald-700 border-emerald-300' : 'bg-white text-slate-700 border-slate-200'"
                        class="flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-medium shadow-sm transition hover:bg-slate-50">
                    <i class="fa-solid fa-sliders"></i>
                    Filter
                    @if($this->getActiveFiltersCount() > 0)
                        <span class="flex size-5 items-center justify-center rounded-full bg-emerald-500 text-xs font-bold text-white">
                            {{ $this->getActiveFiltersCount() }}
                        </span>
                    @endif
                </button>
            </div>
        </div>

        <!-- Filter Panel -->
        @if($showFilters)
            <div wire:transition.enter="transition ease-out duration-300"
                 wire:transition-enter-start="opacity-0 -translate-y-2"
                 wire:transition-enter-end="opacity-100 translate-y-0"
                 class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-wrap items-center gap-4">
                    <!-- Price Range Filter -->
                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Harga</label>
                        <div class="flex flex-wrap gap-2">
                            <button wire:click="setPriceRange('all')"
                                    :class="$wire.priceRange === 'all' ? 'bg-emerald-100 text-emerald-700 border-emerald-300' : 'bg-slate-50 text-slate-600 border-slate-200'"
                                    class="rounded-lg border px-3 py-1.5 text-xs font-medium transition hover:border-emerald-300">
                                Semua
                            </button>
                            <button wire:click="setPriceRange('0-50k')"
                                    :class="$wire.priceRange === '0-50k' ? 'bg-emerald-100 text-emerald-700 border-emerald-300' : 'bg-slate-50 text-slate-600 border-slate-200'"
                                    class="rounded-lg border px-3 py-1.5 text-xs font-medium transition hover:border-emerald-300">
                                < Rp 50rb
                            </button>
                            <button wire:click="setPriceRange('50k-100k')"
                                    :class="$wire.priceRange === '50k-100k' ? 'bg-emerald-100 text-emerald-700 border-emerald-300' : 'bg-slate-50 text-slate-600 border-slate-200'"
                                    class="rounded-lg border px-3 py-1.5 text-xs font-medium transition hover:border-emerald-300">
                                Rp 50rb - 100rb
                            </button>
                            <button wire:click="setPriceRange('100k-250k')"
                                    :class="$wire.priceRange === '100k-250k' ? 'bg-emerald-100 text-emerald-700 border-emerald-300' : 'bg-slate-50 text-slate-600 border-slate-200'"
                                    class="rounded-lg border px-3 py-1.5 text-xs font-medium transition hover:border-emerald-300">
                                Rp 100rb - 250rb
                            </button>
                            <button wire:click="setPriceRange('250k-500k')"
                                    :class="$wire.priceRange === '250k-500k' ? 'bg-emerald-100 text-emerald-700 border-emerald-300' : 'bg-slate-50 text-slate-600 border-slate-200'"
                                    class="rounded-lg border px-3 py-1.5 text-xs font-medium transition hover:border-emerald-300">
                                Rp 250rb - 500rb
                            </button>
                            <button wire:click="setPriceRange('500k+')"
                                    :class="$wire.priceRange === '500k+' ? 'bg-emerald-100 text-emerald-700 border-emerald-300' : 'bg-slate-50 text-slate-600 border-slate-200'"
                                    class="rounded-lg border px-3 py-1.5 text-xs font-medium transition hover:border-emerald-300">
                                > Rp 500rb
                            </button>
                        </div>
                    </div>

                    <!-- Clear Filters -->
                    @if($this->getActiveFiltersCount() > 0)
                        <button wire:click="clearFilters"
                                class="ml-auto flex items-center gap-1 text-sm font-medium text-red-600 hover:text-red-700">
                            <i class="fa-solid fa-times-circle"></i>
                            Hapus Filter
                        </button>
                    @endif
                </div>
            </div>
        @endif
    </section>

    <!-- Categories -->
    <section class="mx-auto mt-8 max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-slate-900">Kategori</h2>
            <button wire:click="clearFilters"
                    class="text-sm text-emerald-600 hover:text-emerald-700">
                Lihat Semua
            </button>
        </div>

        <div class="mt-4 flex gap-3 overflow-x-auto pb-2 scrollbar-hide">
            <button wire:click="selectCategory(null)"
                    :class="!$wire.selectedCategory ? 'bg-emerald-600 text-white shadow-md shadow-emerald-200' : 'bg-white text-slate-700 hover:bg-emerald-50'"
                    class="flex shrink-0 items-center gap-2 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium shadow-sm transition">
                <i class="fa-solid fa-border-all"></i>
                Semua Produk
            </button>
            @foreach($categories as $category)
                <button wire:click="selectCategory({{ $category['id'] }})"
                        :class="$wire.selectedCategory === {{ $category['id'] }} ? 'bg-emerald-600 text-white shadow-md shadow-emerald-200' : 'bg-white text-slate-700 hover:bg-emerald-50'"
                        class="flex shrink-0 items-center gap-2 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium shadow-sm transition">
                    @if($category['icon'])
                        <i class="{{ $category['icon'] }}"></i>
                    @endif
                    {{ $category['name'] }}
                    @if(isset($categoryCounts[$category['id']]) && $categoryCounts[$category['id']] > 0)
                        <span class="rounded-full bg-white/20 px-2 py-0.5 text-xs"
                              :class="$wire.selectedCategory === {{ $category['id'] }} ? 'bg-white/30' : 'bg-slate-100'">
                            {{ $categoryCounts[$category['id']] }}
                        </span>
                    @endif
                </button>
            @endforeach
        </div>
    </section>

    <!-- Products Section -->
    <section id="products" class="mx-auto mt-10 max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-900">Produk Tersedia</h2>
            <span class="text-sm text-slate-500">{{ $products->total() }} produk</span>
        </div>

        @if($products->count() > 0)
            <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach($products as $product)
                    <article class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                        <!-- Product Image -->
                        <div class="relative aspect-square overflow-hidden bg-slate-100">
                            <a href="{{ route('shop.product', $product->slug) }}">
                                @if($product->image_url)
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                         class="size-full object-cover transition-transform duration-500 group-hover:scale-105">
                                @else
                                    <div class="flex size-full items-center justify-center">
                                        <i class="fa-solid fa-box text-4xl text-slate-300"></i>
                                    </div>
                                @endif
                            </a>

                            <!-- Badges -->
                            <div class="absolute left-3 top-3 flex flex-col gap-2">
                                @if($product->has_discount)
                                    <span class="rounded-lg bg-red-500 px-2 py-1 text-xs font-bold text-white shadow">
                                        -{{ $product->discount_percentage }}%
                                    </span>
                                @endif
                                @if($product->is_featured)
                                    <span class="rounded-lg bg-amber-400 px-2 py-1 text-xs font-bold text-amber-900 shadow">
                                        <i class="fa-solid fa-star mr-1"></i>Unggulan
                                    </span>
                                @endif
                            </div>

                            <!-- Stock Badge -->
                            <div class="absolute right-3 top-3">
                                @if($product->stock_status === 'out_of_stock')
                                    <span class="rounded-lg bg-slate-800 px-2 py-1 text-xs font-semibold text-white">
                                        Stok Habis
                                    </span>
                                @elseif($product->stock_status === 'low_stock')
                                    <span class="rounded-lg bg-amber-500 px-2 py-1 text-xs font-semibold text-white">
                                        Stok Terbatas
                                    </span>
                                @endif
                            </div>

                            <!-- Wishlist Button -->
                            <button class="absolute bottom-3 right-3 flex size-9 items-center justify-center rounded-full bg-white/90 text-slate-400 shadow-sm backdrop-blur-sm transition-all hover:text-red-500 hover:scale-110">
                                <i class="fa-solid fa-heart"></i>
                            </button>
                        </div>

                        <!-- Product Info -->
                        <div class="p-4">
                            <!-- Category -->
                            <p class="text-xs font-medium uppercase tracking-wide text-emerald-600">
                                {{ $product->category?->name ?? 'Lainnya' }}
                            </p>

                            <!-- Name -->
                            <a href="{{ route('shop.product', $product->slug) }}">
                                <h3 class="mt-1 line-clamp-2 text-sm font-semibold text-slate-900 transition group-hover:text-emerald-600">
                                    {{ $product->name }}
                                </h3>
                            </a>

                            <!-- Rating -->
                            <div class="mt-2 flex items-center gap-2">
                                <div class="flex items-center gap-0.5">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fa-solid fa-star text-xs {{ $i <= ($product->rating ?? 0) ? 'text-amber-400' : 'text-slate-200' }}"></i>
                                    @endfor
                                </div>
                                <span class="text-xs text-slate-500">({{ $product->total_reviews ?? 0 }})</span>
                            </div>

                            <!-- Price -->
                            <div class="mt-3">
                                @if($product->has_discount)
                                    <span class="text-lg font-bold text-red-600">
                                        {{ $product->formatted_current_price }}
                                    </span>
                                    <span class="ml-2 text-sm text-slate-400 line-through">
                                        {{ $product->formatted_price }}
                                    </span>
                                @else
                                    <span class="text-lg font-bold text-emerald-600">
                                        {{ $product->formatted_current_price }}
                                    </span>
                                @endif
                                @if($product->unit)
                                    <span class="text-xs text-slate-500">/ {{ $product->unit }}</span>
                                @endif
                            </div>

                            <!-- Add to Cart Button -->
                            <button
                                @if($product->stock <= 0) disabled @endif
                                @click="addToCart({{ json_encode([
                                    'id' => $product->id,
                                    'name' => $product->name,
                                    'price' => $product->current_price,
                                    'image' => $product->image_url,
                                    'type' => 'supply'
                                ]) }})"
                                class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl py-2.5 text-sm font-semibold transition-all {{ $product->stock > 0 ? 'bg-emerald-600 text-white hover:bg-emerald-700 active:scale-[0.98]' : 'bg-slate-100 text-slate-400 cursor-not-allowed' }}">
                                <i class="fa-solid fa-cart-plus"></i>
                                {{ $product->stock > 0 ? 'Tambah ke Keranjang' : 'Stok Habis' }}
                            </button>
                        </div>
                    </article>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-10">
                {{ $products->links('pagination.tailwind') }}
            </div>
        @else
            <!-- Empty State -->
            <div class="flex flex-col items-center gap-4 rounded-2xl border border-dashed border-slate-300 bg-slate-50 py-16 text-center">
                <div class="flex size-20 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                    <i class="fa-solid fa-box-open text-3xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-slate-700">Belum ada produk</h3>
                    <p class="mt-1 text-sm text-slate-500">Coba ubah filter atau kata kunci pencarian</p>
                </div>
                <button wire:click="clearFilters"
                        class="mt-2 rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">
                    Reset Filter
                </button>
            </div>
        @endif
    </section>

    <!-- Features Section -->
    <section class="mx-auto mt-16 max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-sm">
                <div class="mx-auto flex size-14 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                    <i class="fa-solid fa-truck-fast text-2xl"></i>
                </div>
                <h3 class="mt-4 font-semibold text-slate-900">Pengiriman Cepat</h3>
                <p class="mt-2 text-sm text-slate-600">Dikirim dalam 1-3 hari kerja</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-sm">
                <div class="mx-auto flex size-14 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                    <i class="fa-solid fa-shield-halved text-2xl"></i>
                </div>
                <h3 class="mt-4 font-semibold text-slate-900">Produk Original</h3>
                <p class="mt-2 text-sm text-slate-600">100% produk berkualitas terjamin</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-sm">
                <div class="mx-auto flex size-14 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                    <i class="fa-solid fa-headset text-2xl"></i>
                </div>
                <h3 class="mt-4 font-semibold text-slate-900"> Dukungan 24/7</h3>
                <p class="mt-2 text-sm text-slate-600">Tim kami siap membantu kapan saja</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-sm">
                <div class="mx-auto flex size-14 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                    <i class="fa-solid fa-rotate-left text-2xl"></i>
                </div>
                <h3 class="mt-4 font-semibold text-slate-900">Garansi 7 Hari</h3>
                <p class="mt-2 text-sm text-slate-600">Garansi pengembalian produk</p>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="mx-auto mt-16 max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-3xl bg-gradient-to-r from-emerald-600 to-emerald-700 p-8 shadow-xl sm:p-12">
            <div class="grid gap-8 lg:grid-cols-2 lg:items-center">
                <div class="text-white">
                    <h2 class="text-2xl font-bold sm:text-3xl">Butuh Bantuan?</h2>
                    <p class="mt-3 text-emerald-100">Hubungi tim kami untuk konsultasi kebutuhan peternakan Anda.</p>
                    <a href="{{ route('contact') }}"
                       class="mt-6 inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3 text-sm font-semibold text-emerald-700 shadow-lg transition hover:bg-emerald-50">
                        <i class="fa-solid fa-phone"></i>
                        Hubungi Kami
                    </a>
                </div>
                <div class="hidden justify-end lg:flex">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="rounded-2xl bg-white/10 p-4 text-center backdrop-blur-sm">
                            <p class="text-3xl font-bold text-white">500+</p>
                            <p class="text-sm text-emerald-200">Produk</p>
                        </div>
                        <div class="rounded-2xl bg-white/10 p-4 text-center backdrop-blur-sm">
                            <p class="text-3xl font-bold text-white">1000+</p>
                            <p class="text-sm text-emerald-200">Pelanggan</p>
                        </div>
                        <div class="rounded-2xl bg-white/10 p-4 text-center backdrop-blur-sm">
                            <p class="text-3xl font-bold text-white">50+</p>
                            <p class="text-sm text-emerald-200">Mitra</p>
                        </div>
                        <div class="rounded-2xl bg-white/10 p-4 text-center backdrop-blur-sm">
                            <p class="text-3xl font-bold text-white">4.9</p>
                            <p class="text-sm text-emerald-200">Rating</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="mt-16 border-t border-slate-200 bg-white py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <div class="flex items-center gap-2">
                        <div class="flex size-10 items-center justify-center rounded-lg bg-emerald-600 text-white">
                            <i class="fa-solid fa-cow text-lg"></i>
                        </div>
                        <span class="text-lg font-bold text-slate-900">AbodaFarm</span>
                    </div>
                    <p class="mt-4 text-sm text-slate-600">
                        Toko online terpercaya untuk kebutuhan peternakan dan perlengkapan pertanian.
                    </p>
                </div>
                <div>
                    <h4 class="font-semibold text-slate-900">Tautan Cepat</h4>
                    <ul class="mt-4 space-y-2 text-sm text-slate-600">
                        <li><a href="{{ route('home') }}" class="hover:text-emerald-600">Beranda</a></li>
                        <li><a href="{{ route('shop') }}" class="hover:text-emerald-600">Toko</a></li>
                        <li><a href="{{ route('about') }}" class="hover:text-emerald-600">Tentang Kami</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-emerald-600">Kontak</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-slate-900">Kategori</h4>
                    <ul class="mt-4 space-y-2 text-sm text-slate-600">
                        @foreach($categories as $category)
                            <li><a href="#" class="hover:text-emerald-600">{{ $category['name'] }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-slate-900">Kontak</h4>
                    <ul class="mt-4 space-y-2 text-sm text-slate-600">
                        <li class="flex items-center gap-2">
                            <i class="fa-solid fa-phone text-emerald-600"></i>
                            +62 812-3456-7890
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fa-solid fa-envelope text-emerald-600"></i>
                            info@abodafarm.com
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fa-solid fa-location-dot text-emerald-600"></i>
                            Sulawesi Utara, Indonesia
                        </li>
                    </ul>
                </div>
            </div>
            <div class="mt-8 border-t border-slate-100 pt-8 text-center text-sm text-slate-500">
                <p>&copy; {{ date('Y') }} AbodaFarm. All rights reserved.</p>
            </div>
        </div>
    </footer>
</div>
