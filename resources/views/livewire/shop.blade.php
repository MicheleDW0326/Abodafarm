<div x-data="{
    showToast: false,
    toastText: '',
    toastIcon: 'check',
    toastTimer: null,
    toastType: 'success',
    activeBanner: 0,
    bannerInterval: null,
    quickViewProduct: null,
    showQuickView: false,
    bannerCount: {{ count($heroBanners ?? []) }},
    wishlistProductIds: JSON.parse(localStorage.getItem('abodafarm_wishlist') || '[]'),

    init() {
        // Banner auto-rotate
        this.bannerInterval = setInterval(() => {
            this.activeBanner = (this.activeBanner + 1) % this.bannerCount;
        }, 5000);

        window.addEventListener('storage', (e) => {
            if (e.key === 'abodafarm_wishlist') {
                this.wishlistProductIds = JSON.parse(e.newValue || '[]');
            }
        });

        // Fetch wishlist from server if logged in
        @auth
        fetch('/api/favorites/count')
            .then(r => r.json())
            .then(d => {
                if (d.count !== undefined) {
                    this.fetchWishlistIds();
                }
            })
            .catch(() => {});
        @endauth
    },

    async fetchWishlistIds() {
        // This would fetch the actual product IDs from the server
    },

    isInWishlist(productId) {
        return this.wishlistProductIds.includes(productId);
    },

    async toggleWishlist(productId, event) {
        event.stopPropagation();

        @guest
        this.showAddToast('Silakan login untuk menyimpan ke wishlist', 'heart', 'warning');
        return;
        @endguest

        try {
            const response = await fetch('/api/favorites/toggle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content
                },
                body: JSON.stringify({ product_id: productId })
            });

            const data = await response.json();

            if (data.success) {
                if (data.is_favorited) {
                    if (!this.wishlistProductIds.includes(productId)) {
                        this.wishlistProductIds.push(productId);
                    }
                    this.showAddToast('Ditambahkan ke wishlist', 'heart', 'success');
                } else {
                    this.wishlistProductIds = this.wishlistProductIds.filter(id => id !== productId);
                    this.showAddToast('Dihapus dari wishlist', 'heart', 'info');
                }
                localStorage.setItem('abodafarm_wishlist', JSON.stringify(this.wishlistProductIds));
                window.dispatchEvent(new CustomEvent('wishlist-updated'));
            }
        } catch (error) {
            console.error('Wishlist error:', error);
            this.showAddToast('Gagal memperbarui wishlist', 'times', 'error');
        }
    },

    showAddToast(message, icon = 'check', type = 'success') {
        this.toastType = type;
        this.toastIcon = icon;
        this.toastText = message;
        this.showToast = true;
        clearTimeout(this.toastTimer);
        this.toastTimer = setTimeout(() => {
            this.showToast = false;
        }, 3000);
    },

    openQuickView(product) {
        this.quickViewProduct = product;
        this.showQuickView = true;
        document.body.style.overflow = 'hidden';
    },

    closeQuickView() {
        this.showQuickView = false;
        this.quickViewProduct = null;
        document.body.style.overflow = '';
    },

    addToCart(product, event = null) {
        if (event) {
            event.stopPropagation();
        }

        // Get product type (fresh, supply, live)
        const productType = product.type || 'supply';
        const key = 'product_' + product.id + '_' + productType;

        // Dispatch add-to-cart event - cart-drawer component will handle the rest
        window.dispatchEvent(new CustomEvent('add-to-cart', {
            detail: {
                id: product.id,
                name: product.name,
                price: product.current_price || product.price,
                image: product.image,
                type: productType,
                key: key
            }
        }));

        this.showAddToast(product.name + ' ditambahkan ke keranjang');
    }
}" x-on:cart-item-added.window="showAddToast($event.detail.name)"
    class="bg-gradient-to-b from-slate-50 via-white to-slate-50 text-slate-900">

    <!-- Topbar -->
    @include('components.topbar')

    <!-- Toast Notification -->
    <div x-show="showToast"
         x-transition:enter="transition ease-out duration-400"
         x-transition:enter-start="opacity-0 translate-y-6 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-250"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         class="fixed bottom-6 right-6 z-[100]"
         :class="{
             'border-emerald-200/50 shadow-emerald-500/10': toastType === 'success',
             'border-amber-200/50 shadow-amber-500/10': toastType === 'warning',
             'border-red-200/50 shadow-red-500/10': toastType === 'error'
         }">
        <div class="flex items-center gap-3 rounded-2xl border bg-white/95 px-5 py-3.5 shadow-xl backdrop-blur-md"
             :class="{
                 'from-emerald-400 to-emerald-600': toastType === 'success',
                 'from-amber-400 to-orange-500': toastType === 'warning',
                 'from-red-400 to-rose-600': toastType === 'error'
             }">
            <div class="flex size-9 items-center justify-center rounded-xl bg-gradient-to-br shadow-lg">
                <template x-if="toastType === 'success'">
                    <svg class="size-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                    </svg>
                </template>
                <template x-if="toastType === 'warning'">
                    <svg class="size-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </template>
                <template x-if="toastType === 'error'">
                    <svg class="size-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </template>
            </div>
            <div>
                <p class="text-sm font-semibold text-slate-800" x-text="toastText"></p>
                <p class="text-xs"
                   :class="{
                       'text-emerald-600': toastType === 'success',
                       'text-amber-600': toastType === 'warning',
                       'text-red-600': toastType === 'error'
                   }"
                   x-text="toastType === 'success' ? 'Ditambahkan ke keranjang' : (toastType === 'warning' ? 'Peringatan' : 'Gagal')"></p>
            </div>
        </div>
    </div>

    <!-- Quick View Modal -->
    <div x-show="showQuickView"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[90] overflow-y-auto"
         @keydown.escape.window="closeQuickView()">
        <!-- Backdrop -->
        <div class="min-h-screen px-4 py-8 flex items-center justify-center"
             @click="closeQuickView()">
            <!-- Modal Content -->
            <div x-show="showQuickView"
                 x-transition:enter="transition ease-out duration-400"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-250"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                 class="relative w-full max-w-4xl bg-white rounded-3xl shadow-2xl overflow-hidden"
                 @click.stop>
                <template x-if="quickViewProduct">
                    <div class="grid md:grid-cols-2">
                        <!-- Image -->
                        <div class="relative aspect-square md:aspect-auto md:min-h-[500px] bg-gradient-to-br from-slate-100 to-slate-200">
                            <img x-show="quickViewProduct.image"
                                 :src="quickViewProduct.image"
                                 :alt="quickViewProduct.name"
                                 class="absolute inset-0 w-full h-full object-cover">
                            <div x-show="!quickViewProduct.image"
                                 class="absolute inset-0 flex items-center justify-center">
                                <i class="fa-solid fa-image text-6xl text-slate-300"></i>
                            </div>
                            <!-- Badges -->
                            <div class="absolute left-4 top-4 flex flex-col gap-2">
                                <template x-if="quickViewProduct.has_discount">
                                    <span class="rounded-xl bg-gradient-to-r from-red-500 to-rose-500 px-3 py-1.5 text-xs font-bold text-white shadow-lg">
                                        <span x-text="'-' + quickViewProduct.discount_percent + '%'"></span>
                                    </span>
                                </template>
                                <template x-if="quickViewProduct.is_featured">
                                    <span class="rounded-xl bg-gradient-to-r from-amber-400 to-orange-400 px-3 py-1.5 text-xs font-bold text-amber-900 shadow-lg">
                                        <i class="fa-solid fa-star mr-1"></i>Unggulan
                                    </span>
                                </template>
                            </div>
                            <!-- Close Button -->
                            <button @click="closeQuickView()"
                                    class="absolute right-4 top-4 flex size-10 items-center justify-center rounded-full bg-white/90 text-slate-600 shadow-lg backdrop-blur-sm transition-all hover:bg-white hover:text-slate-900 hover:scale-110">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                        <!-- Info -->
                        <div class="relative p-6 md:p-8 flex flex-col">
                            <!-- Category -->
                            <p class="text-xs font-semibold uppercase tracking-widest text-emerald-600" x-text="quickViewProduct.category || 'Lainnya'"></p>

                            <!-- Name -->
                            <h3 class="mt-2 text-xl font-bold text-slate-900" x-text="quickViewProduct.name"></h3>

                            <!-- Rating -->
                            <div class="mt-3 flex items-center gap-2">
                                <div class="flex items-center gap-1">
                                    <template x-for="i in 5" :key="i">
                                        <i class="fa-solid fa-star text-sm"
                                           :class="i <= (quickViewProduct.rating || 0) ? 'text-amber-400' : 'text-slate-200'"></i>
                                    </template>
                                </div>
                                <span class="text-sm text-slate-500">(<span x-text="quickViewProduct.total_reviews || 0"></span> ulasan)</span>
                            </div>

                            <!-- Price -->
                            <div class="mt-5">
                                <template x-if="quickViewProduct.has_discount">
                                    <div class="flex items-baseline gap-3">
                                        <span class="text-3xl font-bold bg-gradient-to-r from-red-500 to-rose-600 bg-clip-text text-transparent" x-text="quickViewProduct.formatted_current_price"></span>
                                        <span class="text-lg text-slate-400 line-through" x-text="quickViewProduct.formatted_price"></span>
                                    </div>
                                </template>
                                <template x-if="!quickViewProduct.has_discount">
                                    <span class="text-3xl font-bold text-emerald-600" x-text="quickViewProduct.formatted_current_price"></span>
                                </template>
                                <template x-if="quickViewProduct.unit">
                                    <span class="ml-1 text-sm text-slate-500">/ <span x-text="quickViewProduct.unit"></span></span>
                                </template>
                            </div>

                            <!-- Stock Status -->
                            <div class="mt-4 flex items-center gap-2">
                                <template x-if="quickViewProduct.stock > 10">
                                    <div class="flex items-center gap-2">
                                        <span class="size-2 rounded-full bg-emerald-500"></span>
                                        <span class="text-sm text-emerald-600 font-medium">Stok Tersedia</span>
                                    </div>
                                </template>
                                <template x-if="quickViewProduct.stock > 0 && quickViewProduct.stock <= 10">
                                    <div class="flex items-center gap-2">
                                        <span class="size-2 rounded-full bg-amber-500"></span>
                                        <span class="text-sm text-amber-600 font-medium">Stok Terbatas (<span x-text="quickViewProduct.stock"></span> tersisa)</span>
                                    </div>
                                </template>
                                <template x-if="!quickViewProduct.stock || quickViewProduct.stock <= 0">
                                    <div class="flex items-center gap-2">
                                        <span class="size-2 rounded-full bg-slate-400"></span>
                                        <span class="text-sm text-slate-500">Stok Habis</span>
                                    </div>
                                </template>
                            </div>

                            <!-- Description -->
                            <div class="mt-6 flex-1">
                                <p class="text-sm leading-relaxed text-slate-600" x-text="quickViewProduct.description || 'Tidak ada deskripsi produk.'"></p>
                            </div>

                            <!-- Action Buttons -->
                            <div class="mt-8 flex flex-col gap-3">
                                <a :href="'/shop/' + quickViewProduct.slug"
                                   class="flex w-full items-center justify-center gap-2 rounded-xl border-2 border-emerald-600 bg-white px-6 py-3.5 text-sm font-bold text-emerald-600 transition-all hover:bg-emerald-50 active:scale-[0.98]">
                                    <i class="fa-solid fa-eye"></i>
                                    Lihat Detail Produk
                                </a>
                                <template x-if="quickViewProduct.type === 'live'">
                                    <a :href="'/shop/' + quickViewProduct.slug"
                                       class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-green-500 to-green-600 px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-green-500/30 hover:shadow-xl hover:shadow-green-500/40 hover:from-green-600 hover:to-green-700 active:scale-[0.98]">
                                        <i class="fa-solid fa-calendar-check"></i>
                                        <span x-text="quickViewProduct.stock && quickViewProduct.stock > 0 ? 'Booking Ternak' : 'Stok Habis'"></span>
                                    </a>
                                </template>
                                <template x-if="quickViewProduct.type !== 'live'">
                                    <button @click="addToCart(quickViewProduct)"
                                            :disabled="!quickViewProduct.stock || quickViewProduct.stock <= 0"
                                            class="flex w-full items-center justify-center gap-2 rounded-xl px-6 py-3.5 text-sm font-bold transition-all"
                                            :class="quickViewProduct.stock && quickViewProduct.stock > 0
                                                ? 'bg-gradient-to-r from-emerald-500 to-emerald-600 text-white shadow-lg shadow-emerald-500/30 hover:shadow-xl hover:shadow-emerald-500/40 hover:from-emerald-600 hover:to-emerald-700 active:scale-[0.98]'
                                                : 'bg-slate-100 text-slate-400 cursor-not-allowed'">
                                        <i class="fa-solid fa-cart-plus"></i>
                                        <span x-text="quickViewProduct.stock && quickViewProduct.stock > 0 ? 'Tambah ke Keranjang' : 'Stok Habis'"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Hero Banner Carousel -->
    <section class="relative overflow-hidden bg-gradient-to-br from-emerald-600 via-emerald-700 to-teal-700">
        <!-- Decorative Background Elements -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -right-40 size-96 rounded-full bg-white/5 blur-3xl"></div>
            <div class="absolute -bottom-40 -left-40 size-96 rounded-full bg-teal-400/10 blur-3xl"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 size-[600px] rounded-full bg-emerald-400/5 blur-3xl"></div>
        </div>

        <div class="relative mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-20 lg:px-8 lg:py-24">
            <!-- Single Banner Container -->
            <div class="relative">
                @foreach($heroBanners ?? [] as $index => $banner)
                    <div x-show="activeBanner === {{ $index }}"
                         x-transition:enter="transition ease-out duration-500"
                         x-transition:enter-start="opacity-0 translate-x-8"
                         x-transition:enter-end="opacity-100 translate-x-0"
                         x-transition:leave="transition ease-in duration-400"
                         x-transition:leave-start="opacity-100 translate-x-0"
                         x-transition:leave-end="opacity-0 -translate-x-8"
                         class="grid gap-10 lg:grid-cols-2 lg:items-center"
                         :class="activeBanner === {{ $index }} ? '' : 'hidden'">
                        <!-- Content -->
                        <div class="text-white">
                            <span class="mb-4 inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wide backdrop-blur-sm">
                                <span class="size-2 rounded-full bg-amber-400 animate-pulse"></span>
                                {{ $banner['badge'] }}
                            </span>
                            <h1 class="text-4xl font-black leading-tight tracking-tight sm:text-5xl lg:text-6xl">{{ $banner['title'] }}</h1>
                            <p class="mt-5 max-w-lg text-base leading-relaxed text-white/80 sm:text-lg">{{ $banner['description'] }}</p>
                            <div class="mt-10 flex flex-wrap gap-4">
                                <a href="#products"
                                   class="group inline-flex items-center gap-3 rounded-2xl bg-white px-7 py-4 text-sm font-bold text-emerald-700 shadow-xl shadow-emerald-900/20 transition-all hover:shadow-2xl hover:shadow-emerald-900/30 hover:-translate-y-0.5 active:translate-y-0">
                                    <i class="fa-solid fa-bag-shopping transition-transform group-hover:scale-110"></i>
                                    Belanja Sekarang
                                    <i class="fa-solid fa-arrow-right ml-1 transition-transform group-hover:translate-x-1"></i>
                                </a>
                                <a href="{{ route('home') }}"
                                   class="group inline-flex items-center gap-3 rounded-2xl border-2 border-white/30 bg-white/10 px-7 py-4 text-sm font-bold text-white backdrop-blur-sm transition-all hover:bg-white/20 hover:border-white/50">
                                    <i class="fa-solid fa-cow"></i>
                                    Lihat Ternak
                                </a>
                            </div>
                        </div>
                        <!-- Image -->
                        <div class="relative hidden lg:block">
                            <div class="relative overflow-hidden rounded-3xl shadow-2xl shadow-emerald-900/30">
                                <x-image
                                    src="{{ $banner['image'] }}"
                                    alt="{{ $banner['title'] }}"
                                    type="banner"
                                    width="600"
                                    height="450"
                                    class="w-full aspect-[4/3] object-cover"
                                />
                                <!-- Overlay Gradient -->
                                <div class="absolute inset-0 bg-gradient-to-t from-emerald-900/30 to-transparent"></div>
                            </div>
                            <!-- Decorative elements -->
                            <div class="absolute -bottom-6 -right-6 size-32 rounded-full bg-amber-400/20 blur-2xl"></div>
                            <div class="absolute -top-6 -left-6 size-24 rounded-full bg-white/10 blur-2xl"></div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Banner Navigation -->
            <div class="mt-12 flex items-center justify-center gap-4">
                @foreach($heroBanners ?? [] as $index => $banner)
                    <button @click="activeBanner = {{ $index }}"
                            class="transition-all duration-300 ease-out"
                            :class="activeBanner === {{ $index }} ? 'w-8 h-3 bg-white rounded-full shadow-lg shadow-white/50' : 'w-3 h-3 bg-white/40 rounded-full hover:bg-white/60'">
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Bottom Wave -->
        <div class="absolute bottom-0 left-0 right-0">
            <svg viewBox="0 0 1440 100" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full">
                <path d="M0 50L60 45C120 40 240 30 360 35C480 40 600 60 720 65C840 70 960 60 1080 50C1200 40 1320 30 1380 25L1440 20V100H1380C1320 100 1200 100 1080 100C960 100 840 100 720 100C600 100 480 100 360 100C240 100 120 100 60 100H0V50Z" fill="white"/>
            </svg>
        </div>
    </section>

    <!-- Promotional Banner -->
    <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-2xl border border-amber-200/50 bg-gradient-to-r from-amber-50 via-orange-50 to-amber-50 shadow-lg shadow-amber-100/50">
            <div class="flex flex-col items-center justify-between gap-4 p-5 sm:flex-row sm:p-6">
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <div class="flex size-14 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 text-white shadow-lg shadow-amber-200/50">
                            <i class="fa-solid fa-truck-fast text-2xl"></i>
                        </div>
                        <div class="absolute -right-1 -top-1 size-4 rounded-full bg-red-500 animate-ping"></div>
                        <div class="absolute -right-1 -top-1 size-4 rounded-full bg-red-500"></div>
                    </div>
                    <div>
                        <p class="text-base font-bold text-amber-900">{{ ($promotionalBanner ?? [])['title'] }}</p>
                        <p class="mt-0.5 text-sm text-amber-700">{{ ($promotionalBanner ?? [])['subtitle'] }}</p>
                    </div>
                </div>
                <a href="#products"
                   class="group flex items-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-amber-200/50 transition-all hover:shadow-xl hover:shadow-amber-200/50 hover:-translate-y-0.5 active:translate-y-0">
                    Klaim Sekarang
                    <i class="fa-solid fa-arrow-right transition-transform group-hover:translate-x-1"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Search and Filters -->
    <section class="mx-auto mt-10 max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
            <div class="flex flex-col gap-4 p-5 lg:flex-row lg:items-center lg:justify-between">
                <!-- Search Bar -->
                <div class="relative flex-1">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari produk yang Anda butuhkan..."
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3.5 pl-12 pr-4 text-sm transition-all focus:border-emerald-400 focus:bg-white focus:outline-none focus:ring-4 focus:ring-emerald-500/10">
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
                                class="flex items-center gap-2.5 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition-all hover:border-slate-300 hover:bg-slate-50">
                            <i class="fa-solid fa-sort text-emerald-500"></i>
                            <span>Urutkan</span>
                            <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform" :class="open ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="open" @click.outside="open = false"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             class="absolute right-0 top-full z-30 mt-2 w-56 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-200/50">
                            <div class="p-2">
                                <p class="px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-400">Urutkan Berdasarkan</p>
                                <button wire:click="setSort('newest')"
                                        class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-700 transition-all hover:bg-emerald-50 hover:text-emerald-700">
                                    <i class="fa-solid fa-clock-rotate-left text-slate-400"></i> Terbaru
                                </button>
                                <button wire:click="setSort('price_low')"
                                        class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-700 transition-all hover:bg-emerald-50 hover:text-emerald-700">
                                    <i class="fa-solid fa-arrow-up text-slate-400"></i> Harga Terendah
                                </button>
                                <button wire:click="setSort('price_high')"
                                        class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-700 transition-all hover:bg-emerald-50 hover:text-emerald-700">
                                    <i class="fa-solid fa-arrow-down text-slate-400"></i> Harga Tertinggi
                                </button>
                                <button wire:click="setSort('rating')"
                                        class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-700 transition-all hover:bg-emerald-50 hover:text-emerald-700">
                                    <i class="fa-solid fa-star text-slate-400"></i> Tertinggi Rating
                                </button>
                                <button wire:click="setSort('best_selling')"
                                        class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-700 transition-all hover:bg-emerald-50 hover:text-emerald-700">
                                    <i class="fa-solid fa-fire text-slate-400"></i> Terlaris
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Toggle -->
                    <button wire:click="$toggle('showFilters')"
                            :class="$wire.showFilters ? 'bg-emerald-600 text-white border-emerald-600 shadow-lg shadow-emerald-200/50' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50'"
                            class="flex items-center gap-2.5 rounded-xl border px-4 py-2.5 text-sm font-medium shadow-sm transition-all">
                        <i class="fa-solid fa-sliders"></i>
                        Filter
                        @if($this->getActiveFiltersCount() > 0)
                            <span class="flex size-5 items-center justify-center rounded-full bg-white text-xs font-bold"
                                  :class="$wire.showFilters ? 'text-emerald-600' : 'text-emerald-600'">
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
                     class="border-t border-slate-100 bg-gradient-to-b from-slate-50 to-white p-5">
                    <!-- Active Filter Chips -->
                    @if(count($this->getActiveFiltersList()) > 0)
                        <div class="mb-4 flex flex-wrap items-center gap-2">
                            <span class="text-xs font-semibold uppercase tracking-wide text-slate-400">Filter Aktif:</span>
                            @foreach($this->getActiveFiltersList() as $filter)
                                <button wire:click="removeFilter('{{ $filter['key'] }}')"
                                        class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-200">
                                    {{ $filter['label'] }}
                                    <i class="fa-solid fa-xmark text-[10px]"></i>
                                </button>
                            @endforeach
                        </div>
                    @endif

                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 xl:grid-cols-4">
                        <!-- Price Range Filter -->
                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-500">Rentang Harga</label>
                            <div class="flex flex-wrap gap-2">
                                <button wire:click="setPriceRange('all')"
                                        :class="$wire.priceRange === 'all' ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-slate-600 border-slate-200 hover:border-emerald-300 hover:bg-emerald-50'"
                                        class="rounded-lg border px-3 py-1.5 text-xs font-medium transition-all">
                                    Semua
                                </button>
                                <button wire:click="setPriceRange('0-50k')"
                                        :class="$wire.priceRange === '0-50k' ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-slate-600 border-slate-200 hover:border-emerald-300 hover:bg-emerald-50'"
                                        class="rounded-lg border px-3 py-1.5 text-xs font-medium transition-all">
                                    < Rp 50rb
                                </button>
                                <button wire:click="setPriceRange('50k-100k')"
                                        :class="$wire.priceRange === '50k-100k' ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-slate-600 border-slate-200 hover:border-emerald-300 hover:bg-emerald-50'"
                                        class="rounded-lg border px-3 py-1.5 text-xs font-medium transition-all">
                                    50-100rb
                                </button>
                                <button wire:click="setPriceRange('100k-250k')"
                                        :class="$wire.priceRange === '100k-250k' ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-slate-600 border-slate-200 hover:border-emerald-300 hover:bg-emerald-50'"
                                        class="rounded-lg border px-3 py-1.5 text-xs font-medium transition-all">
                                    100-250rb
                                </button>
                                <button wire:click="setPriceRange('250k-500k')"
                                        :class="$wire.priceRange === '250k-500k' ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-slate-600 border-slate-200 hover:border-emerald-300 hover:bg-emerald-50'"
                                        class="rounded-lg border px-3 py-1.5 text-xs font-medium transition-all">
                                    250-500rb
                                </button>
                                <button wire:click="setPriceRange('500k+')"
                                        :class="$wire.priceRange === '500k+' ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-slate-600 border-slate-200 hover:border-emerald-300 hover:bg-emerald-50'"
                                        class="rounded-lg border px-3 py-1.5 text-xs font-medium transition-all">
                                    > 500rb
                                </button>
                            </div>
                        </div>

                        <!-- Location Filter -->
                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-500">Lokasi</label>
                            <div x-data="{ open: false }" class="relative">
                                <button @click="open = !open"
                                        class="flex w-full items-center justify-between rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-700 transition hover:border-emerald-300">
                                    <span>{{ $location ? ($locations[$location] ?? $location) : 'Semua Lokasi' }}</span>
                                    <i class="fa-solid fa-chevron-down text-[10px]" :class="open ? 'rotate-180' : ''"></i>
                                </button>
                                <div x-show="open" @click.outside="open = false"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 translate-y-2"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     class="absolute left-0 top-full z-20 mt-1 w-full max-h-48 overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-lg">
                                    <button wire:click="setLocation(null)"
                                            class="block w-full px-3 py-2 text-left text-xs text-slate-600 hover:bg-emerald-50"
                                            :class="!$wire.location ? 'bg-emerald-50 text-emerald-700 font-semibold' : ''">
                                        Semua Lokasi
                                    </button>
                                    @foreach($locations ?? [] as $key => $name)
                                        <button wire:click="setLocation('{{ $key }}')"
                                                class="block w-full px-3 py-2 text-left text-xs text-slate-600 hover:bg-emerald-50"
                                                :class="$wire.location === '{{ $key }}' ? 'bg-emerald-50 text-emerald-700 font-semibold' : ''">
                                            {{ $name }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Weight Filter -->
                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-500">Berat</label>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($weightRanges ?? [] as $key => $label)
                                    <button wire:click="setWeightRange('{{ $key }}')"
                                            :class="$wire.weightRange === '{{ $key }}' ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-slate-600 border-slate-200 hover:border-emerald-300'"
                                            class="rounded-lg border px-2.5 py-1.5 text-xs font-medium transition-all">
                                        {{ $label }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <!-- Age Filter -->
                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-500">Umur</label>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($ageRanges ?? [] as $key => $label)
                                    <button wire:click="setAgeRange('{{ $key }}')"
                                            :class="$wire.ageRange === '{{ $key }}' ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-slate-600 border-slate-200 hover:border-emerald-300'"
                                            class="rounded-lg border px-2.5 py-1.5 text-xs font-medium transition-all">
                                        {{ $label }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <!-- Gender Filter -->
                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-500">Jenis Kelamin</label>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($genderOptions ?? [] as $key => $label)
                                    <button wire:click="setGender('{{ $key }}')"
                                            :class="$wire.gender === '{{ $key }}' ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-slate-600 border-slate-200 hover:border-emerald-300'"
                                            class="rounded-lg border px-3 py-1.5 text-xs font-medium transition-all">
                                        {{ $label }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <!-- Rating Filter -->
                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-500">Rating</label>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($ratingOptions ?? [] as $rating => $label)
                                    <button wire:click="setMinRating({{ $rating }})"
                                            :class="$wire.minRating === {{ $rating }} ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-slate-600 border-slate-200 hover:border-emerald-300'"
                                            class="flex items-center gap-1 rounded-lg border px-3 py-1.5 text-xs font-medium transition-all">
                                        <i class="fa-solid fa-star text-amber-400"></i> {{ $label }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <!-- Organic Filter -->
                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-500">Tipe</label>
                            <button wire:click="setOrganicOnly(!$wire.organicOnly)"
                                    :class="$wire.organicOnly ? 'bg-green-600 text-white border-green-600' : 'bg-white text-slate-600 border-slate-200 hover:border-green-300'"
                                    class="flex items-center gap-2 rounded-lg border px-3 py-2 text-xs font-medium transition-all">
                                <i class="fa-solid fa-leaf"></i>
                                {{ $organicOnly ? 'Organic Only' : 'Tampilkan Organic' }}
                            </button>
                        </div>
                    </div>

                    <!-- Clear Filters -->
                    @if($this->getActiveFiltersCount() > 0)
                        <div class="mt-4 flex justify-end border-t border-slate-100 pt-4">
                            <button wire:click="clearFilters"
                                    class="flex items-center gap-2 text-sm font-semibold text-red-500 transition hover:text-red-600">
                                <i class="fa-solid fa-times-circle"></i>
                                Hapus Semua Filter
                            </button>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </section>

    <!-- Product Type Tabs -->
    <section class="mx-auto mt-10 max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                    <i class="fa-solid fa-tags"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Tipe Produk</h2>
                    <p class="text-sm text-slate-500">Filter berdasarkan tipe produk</p>
                </div>
            </div>
        </div>

        <!-- Type Tabs -->
        <div class="mt-5 flex flex-wrap items-center gap-3">
            <button wire:click="setProductType('all')"
                    :class="$wire.productType === 'all' ? 'bg-gradient-to-r from-emerald-500 to-emerald-600 text-white shadow-lg shadow-emerald-200/50 border-transparent' : 'bg-white text-slate-700 hover:bg-emerald-50 border-slate-200'"
                    class="group flex items-center gap-2.5 rounded-xl border px-5 py-3 text-sm font-semibold shadow-sm transition-all duration-300">
                <i class="fa-solid fa-border-all transition-transform group-hover:scale-110"></i>
                Semua
                <span class="rounded-full px-2 py-0.5 text-xs font-bold"
                      :class="$wire.productType === 'all' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600'">
                    {{ $typeCounts['all'] ?? 0 }}
                </span>
            </button>
            <button wire:click="setProductType('live')"
                    :class="$wire.productType === 'live' ? 'bg-gradient-to-r from-green-500 to-green-600 text-white shadow-lg shadow-green-200/50 border-transparent' : 'bg-white text-slate-700 hover:bg-green-50 border-slate-200'"
                    class="group flex items-center gap-2.5 rounded-xl border px-5 py-3 text-sm font-semibold shadow-sm transition-all duration-300">
                <i class="fa-solid fa-cow transition-transform group-hover:scale-110"></i>
                Ternak
                <span class="rounded-full px-2 py-0.5 text-xs font-bold"
                      :class="$wire.productType === 'live' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600'">
                    {{ $typeCounts['live'] ?? 0 }}
                </span>
            </button>
            <button wire:click="setProductType('fresh')"
                    :class="$wire.productType === 'fresh' ? 'bg-gradient-to-r from-red-500 to-red-600 text-white shadow-lg shadow-red-200/50 border-transparent' : 'bg-white text-slate-700 hover:bg-red-50 border-slate-200'"
                    class="group flex items-center gap-2.5 rounded-xl border px-5 py-3 text-sm font-semibold shadow-sm transition-all duration-300">
                <i class="fa-solid fa-drumstick-bite transition-transform group-hover:scale-110"></i>
                Daging Segar
                <span class="rounded-full px-2 py-0.5 text-xs font-bold"
                      :class="$wire.productType === 'fresh' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600'">
                    {{ $typeCounts['fresh'] ?? 0 }}
                </span>
            </button>
            <button wire:click="setProductType('supply')"
                    :class="$wire.productType === 'supply' ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg shadow-blue-200/50 border-transparent' : 'bg-white text-slate-700 hover:bg-blue-50 border-slate-200'"
                    class="group flex items-center gap-2.5 rounded-xl border px-5 py-3 text-sm font-semibold shadow-sm transition-all duration-300">
                <i class="fa-solid fa-wheat-awn transition-transform group-hover:scale-110"></i>
                Pakan & Perlengkapan
                <span class="rounded-full px-2 py-0.5 text-xs font-bold"
                      :class="$wire.productType === 'supply' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600'">
                    {{ $typeCounts['supply'] ?? 0 }}
                </span>
            </button>
        </div>
    </section>

    <!-- Categories -->
    <section class="mx-auto mt-8 max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-xl bg-slate-100 text-slate-600">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Kategori</h2>
                    <p class="text-sm text-slate-500">Pilih kategori yang Anda cari</p>
                </div>
            </div>
        </div>

        <!-- Category Pills -->
        <div class="mt-5 flex flex-wrap items-center gap-2">
            <button wire:click="selectCategory(null)"
                    :class="!$wire.selectedCategory ? 'bg-gradient-to-r from-emerald-500 to-emerald-600 text-white shadow-lg shadow-emerald-200/50 border-transparent' : 'bg-white text-slate-700 hover:bg-emerald-50 border-slate-200'"
                    class="group flex items-center gap-2.5 rounded-xl border px-5 py-3 text-sm font-semibold shadow-sm transition-all duration-300">
                <i class="fa-solid fa-border-all transition-transform group-hover:scale-110"></i>
                Semua Kategori
            </button>
            @foreach($categories ?? [] as $category)
                <button wire:click="selectCategory({{ $category['id'] }})"
                        :class="$wire.selectedCategory === {{ $category['id'] }} ? 'bg-gradient-to-r from-emerald-500 to-emerald-600 text-white shadow-lg shadow-emerald-200/50 border-transparent' : 'bg-white text-slate-700 hover:bg-emerald-50 border-slate-200'"
                        class="group flex items-center gap-2.5 rounded-xl border px-5 py-3 text-sm font-semibold shadow-sm transition-all duration-300">
                    @if($category['icon'])
                        <i class="{{ $category['icon'] }} transition-transform group-hover:scale-110"></i>
                    @endif
                    {{ $category['name'] }}
                    @if(isset($categoryCounts[$category['id']]) && $categoryCounts[$category['id']] > 0)
                        <span class="rounded-full px-2 py-0.5 text-xs font-bold"
                              :class="$wire.selectedCategory === {{ $category['id'] }} ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600'">
                            {{ $categoryCounts[$category['id']] }}
                        </span>
                    @endif
                </button>
            @endforeach
        </div>
    </section>

    <!-- Products Section -->
    <section id="products" class="mx-auto mt-12 max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <h2 class="text-xl font-bold text-slate-900">Produk Tersedia</h2>
                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">{{ $products->total() }} item</span>
            </div>
        </div>

        @if($products->count() > 0)
            <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach($products as $index => $product)
                    <article class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm transition-all duration-300 hover:shadow-xl hover:shadow-slate-200/50 hover:-translate-y-2"
                        data-aos="{{ $index % 2 === 0 ? 'fade-right' : 'fade-left' }}"
                        data-aos-delay="{{ ($index % 4) * 100 }}"
                             @click="openQuickView({{ json_encode([
                                'id' => $product->id,
                                'name' => $product->name,
                                'slug' => $product->slug,
                                'type' => $product->type,
                                'description' => $product->description,
                                'price' => $product->price,
                                'current_price' => $product->current_price,
                                'formatted_price' => $product->formatted_price,
                                'formatted_current_price' => $product->formatted_current_price,
                                'has_discount' => $product->has_discount,
                                'discount_percent' => $product->discount_percentage,
                                'stock' => $product->stock,
                                'stock_status' => $product->stock_status,
                                'image' => $product->image_url,
                                'rating' => $product->rating ?? 0,
                                'total_reviews' => $product->total_reviews ?? 0,
                                'unit' => $product->unit,
                                'category' => $product->category?->name ?? 'Lainnya',
                                'is_featured' => $product->is_featured,
                            ]) }})">
                        <!-- Product Image -->
                        <div class="relative aspect-square overflow-hidden bg-gradient-to-br from-slate-100 to-slate-200 cursor-pointer">
                            <a href="{{ route('shop.product', $product->slug) }}" @click.stop class="block">
                                @if($product->image_url)
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                         class="size-full object-cover transition-transform duration-500 group-hover:scale-110">
                                @else
                                    <div class="flex size-full items-center justify-center">
                                        <i class="fa-solid fa-box text-5xl text-slate-300"></i>
                                    </div>
                                @endif
                            </a>

                            <!-- Gradient Overlay on Hover -->
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>

                            <!-- Badges -->
                            <div class="absolute left-3 top-3 flex flex-col gap-2">
                                @if($product->type === 'live')
                                    <span class="rounded-xl bg-gradient-to-r from-green-500 to-green-600 px-2.5 py-1 text-xs font-bold text-white shadow-lg">
                                        <i class="fa-solid fa-cow mr-1"></i>Ternak
                                    </span>
                                @elseif($product->type === 'fresh')
                                    <span class="rounded-xl bg-gradient-to-r from-red-500 to-red-600 px-2.5 py-1 text-xs font-bold text-white shadow-lg">
                                        <i class="fa-solid fa-drumstick-bite mr-1"></i>Daging Segar
                                    </span>
                                @elseif($product->type === 'supply')
                                    <span class="rounded-xl bg-gradient-to-r from-blue-500 to-blue-600 px-2.5 py-1 text-xs font-bold text-white shadow-lg">
                                        <i class="fa-solid fa-wheat-awn mr-1"></i>Pakan
                                    </span>
                                @endif
                                @if($product->has_discount)
                                    <span class="rounded-xl bg-gradient-to-r from-red-500 to-rose-500 px-2.5 py-1 text-xs font-bold text-white shadow-lg">
                                        -{{ $product->discount_percentage }}%
                                    </span>
                                @endif
                                @if($product->is_featured)
                                    <span class="rounded-xl bg-gradient-to-r from-amber-400 to-orange-400 px-2.5 py-1 text-xs font-bold text-amber-900 shadow-lg">
                                        <i class="fa-solid fa-star mr-1"></i>Unggulan
                                    </span>
                                @endif
                            </div>

                            <!-- Stock Badge -->
                            <div class="absolute right-3 top-3">
                                @if($product->stock_status === 'out_of_stock')
                                    <span class="rounded-xl bg-slate-800/90 backdrop-blur-sm px-2.5 py-1 text-xs font-semibold text-white">
                                        Stok Habis
                                    </span>
                                @elseif($product->stock_status === 'low_stock')
                                    <span class="rounded-xl bg-amber-500/90 backdrop-blur-sm px-2.5 py-1 text-xs font-semibold text-white">
                                        Stok Terbatas
                                    </span>
                                @endif
                            </div>

                            <!-- Action Buttons (visible on hover) -->
                            <div class="absolute bottom-3 left-3 right-3 flex gap-2 opacity-0 transition-all duration-300 translate-y-2 group-hover:opacity-100 group-hover:translate-y-0">
                                <button @click="openQuickView({{ json_encode([
                                    'id' => $product->id,
                                    'name' => $product->name,
                                    'slug' => $product->slug,
                                    'type' => $product->type,
                                    'description' => $product->description,
                                    'price' => $product->price,
                                    'current_price' => $product->current_price,
                                    'formatted_price' => $product->formatted_price,
                                    'formatted_current_price' => $product->formatted_current_price,
                                    'has_discount' => $product->has_discount,
                                    'discount_percent' => $product->discount_percentage,
                                    'stock' => $product->stock,
                                    'stock_status' => $product->stock_status,
                                    'image' => $product->image_url,
                                    'rating' => $product->rating ?? 0,
                                    'total_reviews' => $product->total_reviews ?? 0,
                                    'unit' => $product->unit,
                                    'category' => $product->category?->name ?? 'Lainnya',
                                    'is_featured' => $product->is_featured,
                                ]) }})"
                                        class="flex-1 rounded-xl bg-white/95 py-2.5 text-sm font-semibold text-slate-700 backdrop-blur-sm transition-all hover:bg-white hover:text-emerald-600">
                                    <i class="fa-solid fa-eye mr-1"></i> Quick View
                                </button>
                            </div>

                            <!-- Wishlist Button -->
                            <button
                                x-data="{ isWishlist: {{ auth()->check() && auth()->user()->favoriteProducts()->where('products.id', $product->id)->exists() ? 'true' : 'false' }} }"
                                x-init="$watch('wishlistProductIds', value => { isWishlist = value.includes({{ $product->id }}) })"
                                @click="toggleWishlist({{ $product->id }}, $event)"
                                :class="isWishlist === 'true' || isWishlist === true ? 'text-rose-500' : 'text-slate-400'"
                                class="absolute bottom-3 right-3 flex size-10 items-center justify-center rounded-full bg-white/95 shadow-lg backdrop-blur-sm transition-all hover:scale-110 opacity-0 group-hover:opacity-100"
                            >
                                <i :class="isWishlist === 'true' || isWishlist === true ? 'fa-solid fa-heart' : 'fa-regular fa-heart'"></i>
                            </button>
                        </div>

                        <!-- Product Info -->
                        <div class="p-4">
                            <!-- Category -->
                            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600">
                                {{ $product->category?->name ?? 'Lainnya' }}
                            </p>

                            <!-- Name -->
                            <a href="{{ route('shop.product', $product->slug) }}" @click.stop>
                                <h3 class="mt-1.5 line-clamp-2 text-sm font-bold leading-snug text-slate-900 transition group-hover:text-emerald-600">
                                    {{ $product->name }}
                                </h3>
                            </a>

                            <!-- Rating -->
                            <div class="mt-2.5 flex items-center gap-2">
                                <div class="flex items-center gap-0.5">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fa-solid fa-star text-xs {{ $i <= ($product->rating ?? 0) ? 'text-amber-400' : 'text-slate-200' }}"></i>
                                    @endfor
                                </div>
                                <span class="text-xs font-medium text-slate-500">({{ $product->total_reviews ?? 0 }})</span>
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

                            <!-- Action Button (Different for each product type) -->
                            @if($product->type === 'live')
                                {{-- Livestock: Booking button --}}
                                <a href="{{ route('shop.product', $product->slug) }}"
                                   @click.stop
                                   class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl py-2.5 text-sm font-bold transition-all bg-gradient-to-r from-green-500 to-green-600 text-white shadow-lg shadow-green-200/50 hover:shadow-xl hover:shadow-green-200/50 hover:from-green-600 hover:to-green-700 active:scale-[0.98]">
                                    <i class="fa-solid fa-calendar-check"></i>
                                    Booking Ternak
                                </a>
                            @elseif($product->type === 'fresh')
                                {{-- Fresh meat: Add to cart --}}
                                <button
                                    @click="addToCart({{ json_encode([
                                        'id' => $product->id,
                                        'name' => $product->name,
                                        'price' => $product->current_price,
                                        'image' => $product->image_url,
                                        'type' => 'fresh'
                                    ]) }}, $event)"
                                    @if($product->stock <= 0) disabled @endif
                                    class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl py-2.5 text-sm font-bold transition-all {{ $product->stock > 0 ? 'bg-gradient-to-r from-red-500 to-red-600 text-white shadow-lg shadow-red-200/50 hover:shadow-xl hover:shadow-red-200/50 hover:from-red-600 hover:to-red-700 active:scale-[0.98]' : 'bg-slate-100 text-slate-400 cursor-not-allowed' }}">
                                    <i class="fa-solid fa-cart-plus"></i>
                                    {{ $product->stock > 0 ? 'Tambah ke Keranjang' : 'Stok Habis' }}
                                </button>
                            @else
                                {{-- Supply: Add to cart --}}
                                <button
                                    @click="addToCart({{ json_encode([
                                        'id' => $product->id,
                                        'name' => $product->name,
                                        'price' => $product->current_price,
                                        'image' => $product->image_url,
                                        'type' => 'supply'
                                    ]) }}, $event)"
                                    @if($product->stock <= 0) disabled @endif
                                    class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl py-2.5 text-sm font-bold transition-all {{ $product->stock > 0 ? 'bg-gradient-to-r from-emerald-500 to-emerald-600 text-white shadow-lg shadow-emerald-200/50 hover:shadow-xl hover:shadow-emerald-200/50 hover:from-emerald-600 hover:to-emerald-700 active:scale-[0.98]' : 'bg-slate-100 text-slate-400 cursor-not-allowed' }}">
                                    <i class="fa-solid fa-cart-plus"></i>
                                    {{ $product->stock > 0 ? 'Tambah ke Keranjang' : 'Stok Habis' }}
                                </button>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-12">
                {{ $products->links('vendor.livewire.tailwind') }}
            </div>
        @else
            <!-- Empty State -->
            <div class="flex flex-col items-center gap-5 rounded-3xl border-2 border-dashed border-slate-300 bg-gradient-to-b from-slate-50 to-white py-20 text-center">
                <div class="relative">
                    <div class="flex size-24 items-center justify-center rounded-3xl bg-gradient-to-br from-slate-100 to-slate-200 text-slate-400">
                        <i class="fa-solid fa-box-open text-5xl"></i>
                    </div>
                    <div class="absolute -bottom-1 -right-1 flex size-8 items-center justify-center rounded-full bg-amber-100 text-amber-500">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-slate-700">Tidak ada produk ditemukan</h3>
                    <p class="mt-2 max-w-sm text-sm leading-relaxed text-slate-500">Coba ubah filter atau kata kunci pencarian untuk menemukan produk yang Anda cari.</p>
                </div>
                <button wire:click="clearFilters"
                        class="group mt-2 flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-200/50 transition-all hover:shadow-xl hover:-translate-y-0.5 active:translate-y-0">
                    <i class="fa-solid fa-rotate-left transition-transform group-hover:-rotate-180"></i>
                    Reset Filter
                </button>
            </div>
        @endif
    </section>

    <!-- Features Section -->
    <section class="mx-auto mt-20 max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="rounded-3xl bg-gradient-to-br from-slate-100 via-white to-emerald-50/50 p-8 shadow-inner sm:p-10">
            <div class="text-center mb-10">
                <h2 class="text-2xl font-bold text-slate-900">Kenapa Memilih AbodaFarm?</h2>
                <p class="mt-2 text-slate-600">Keunggulan yang kami tawarkan untuk Anda</p>
            </div>
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <div class="group relative overflow-hidden rounded-2xl border border-slate-200/50 bg-white p-6 text-center shadow-sm transition-all duration-300 hover:shadow-lg hover:-translate-y-1"
                    data-aos="fade-up" data-aos-delay="0">
                    <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-emerald-400 to-teal-400 opacity-0 transition-opacity group-hover:opacity-100"></div>
                    <div class="mx-auto flex size-16 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-100 to-emerald-200 text-emerald-600 shadow-lg shadow-emerald-200/50 transition-transform group-hover:scale-110">
                        <i class="fa-solid fa-truck-fast text-2xl"></i>
                    </div>
                    <h3 class="mt-5 font-bold text-slate-900">Pengiriman Cepat</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">Dikirim dalam 1-3 hari kerja ke seluruh wilayah</p>
                </div>
                <div class="group relative overflow-hidden rounded-2xl border border-slate-200/50 bg-white p-6 text-center shadow-sm transition-all duration-300 hover:shadow-lg hover:-translate-y-1"
                    data-aos="fade-up" data-aos-delay="100">
                    <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-emerald-400 to-teal-400 opacity-0 transition-opacity group-hover:opacity-100"></div>
                    <div class="mx-auto flex size-16 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-100 to-amber-200 text-amber-600 shadow-lg shadow-amber-200/50 transition-transform group-hover:scale-110">
                        <i class="fa-solid fa-shield-halved text-2xl"></i>
                    </div>
                    <h3 class="mt-5 font-bold text-slate-900">Produk Original</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">100% produk berkualitas terjamin original</p>
                </div>
                <div class="group relative overflow-hidden rounded-2xl border border-slate-200/50 bg-white p-6 text-center shadow-sm transition-all duration-300 hover:shadow-lg hover:-translate-y-1"
                    data-aos="fade-up" data-aos-delay="200">
                    <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-emerald-400 to-teal-400 opacity-0 transition-opacity group-hover:opacity-100"></div>
                    <div class="mx-auto flex size-16 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-100 to-blue-200 text-blue-600 shadow-lg shadow-blue-200/50 transition-transform group-hover:scale-110">
                        <i class="fa-solid fa-headset text-2xl"></i>
                    </div>
                    <h3 class="mt-5 font-bold text-slate-900">Dukungan 24/7</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">Tim kami siap membantu kapan saja</p>
                </div>
                <div class="group relative overflow-hidden rounded-2xl border border-slate-200/50 bg-white p-6 text-center shadow-sm transition-all duration-300 hover:shadow-lg hover:-translate-y-1"
                    data-aos="fade-up" data-aos-delay="300">
                    <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-emerald-400 to-teal-400 opacity-0 transition-opacity group-hover:opacity-100"></div>
                    <div class="mx-auto flex size-16 items-center justify-center rounded-2xl bg-gradient-to-br from-purple-100 to-purple-200 text-purple-600 shadow-lg shadow-purple-200/50 transition-transform group-hover:scale-110">
                        <i class="fa-solid fa-rotate-left text-2xl"></i>
                    </div>
                    <h3 class="mt-5 font-bold text-slate-900">Garansi 7 Hari</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">Garansi pengembalian produk mudah</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="mx-auto mt-20 max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-600 via-emerald-700 to-teal-700 p-8 shadow-2xl shadow-emerald-500/20 sm:p-12">
            <!-- Decorative Elements -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute -top-24 -right-24 size-64 rounded-full bg-white/5 blur-3xl"></div>
                <div class="absolute -bottom-24 -left-24 size-64 rounded-full bg-teal-400/10 blur-3xl"></div>
                <div class="absolute top-1/2 right-1/4 size-48 rounded-full bg-amber-400/10 blur-2xl"></div>
            </div>

            <div class="relative grid gap-10 lg:grid-cols-2 lg:items-center">
                <div class="text-white">
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wide backdrop-blur-sm">
                        <i class="fa-solid fa-headset"></i>
                        Butuh Bantuan?
                    </span>
                    <h2 class="mt-5 text-3xl font-black tracking-tight sm:text-4xl lg:text-5xl">Hubungi Tim Kami</h2>
                    <p class="mt-4 max-w-lg text-base leading-relaxed text-emerald-100">Konsultasi gratis untuk kebutuhan peternakan dan pertanian Anda. Tim ahli kami siap membantu 24/7.</p>
                    <div class="mt-8 flex flex-wrap gap-4">
                        <a href="{{ route('contact') }}"
                           class="group inline-flex items-center gap-3 rounded-2xl bg-white px-7 py-4 text-sm font-bold text-emerald-700 shadow-xl shadow-emerald-900/20 transition-all hover:shadow-2xl hover:-translate-y-0.5 active:translate-y-0">
                            <i class="fa-solid fa-phone transition-transform group-hover:scale-110"></i>
                            Hubungi Kami
                            <i class="fa-solid fa-arrow-right ml-1 transition-transform group-hover:translate-x-1"></i>
                        </a>
                        <a href="https://wa.me/6281234567890"
                           class="group inline-flex items-center gap-3 rounded-2xl border-2 border-white/30 bg-white/10 px-7 py-4 text-sm font-bold text-white backdrop-blur-sm transition-all hover:bg-white/20 hover:border-white/50">
                            <i class="fa-brands fa-whatsapp text-lg transition-transform group-hover:scale-110"></i>
                            WhatsApp
                        </a>
                    </div>
                </div>
                <div class="hidden justify-end lg:flex">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="group relative overflow-hidden rounded-2xl bg-white/10 p-6 text-center backdrop-blur-sm transition-all hover:bg-white/20">
                            <p class="text-4xl font-black text-white">500+</p>
                            <p class="mt-1 text-sm font-medium text-emerald-200">Produk</p>
                            <div class="absolute -bottom-1 left-0 right-0 h-1 bg-gradient-to-r from-amber-400 to-orange-400 opacity-0 transition-opacity group-hover:opacity-100"></div>
                        </div>
                        <div class="group relative overflow-hidden rounded-2xl bg-white/10 p-6 text-center backdrop-blur-sm transition-all hover:bg-white/20">
                            <p class="text-4xl font-black text-white">1000+</p>
                            <p class="mt-1 text-sm font-medium text-emerald-200">Pelanggan</p>
                            <div class="absolute -bottom-1 left-0 right-0 h-1 bg-gradient-to-r from-amber-400 to-orange-400 opacity-0 transition-opacity group-hover:opacity-100"></div>
                        </div>
                        <div class="group relative overflow-hidden rounded-2xl bg-white/10 p-6 text-center backdrop-blur-sm transition-all hover:bg-white/20">
                            <p class="text-4xl font-black text-white">50+</p>
                            <p class="mt-1 text-sm font-medium text-emerald-200">Mitra</p>
                            <div class="absolute -bottom-1 left-0 right-0 h-1 bg-gradient-to-r from-amber-400 to-orange-400 opacity-0 transition-opacity group-hover:opacity-100"></div>
                        </div>
                        <div class="group relative overflow-hidden rounded-2xl bg-white/10 p-6 text-center backdrop-blur-sm transition-all hover:bg-white/20">
                            <p class="text-4xl font-black text-white">4.9</p>
                            <p class="mt-1 text-sm font-medium text-emerald-200">Rating</p>
                            <div class="absolute -bottom-1 left-0 right-0 h-1 bg-gradient-to-r from-amber-400 to-orange-400 opacity-0 transition-opacity group-hover:opacity-100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="mt-20 border-t border-slate-200 bg-gradient-to-b from-white to-slate-50 py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <div class="flex items-center gap-3">
                        <img src="/icons/MainLogo.png" alt="AbodaFarm Logo" class="h-12 w-auto object-contain">
                        <span class="text-xl font-black text-slate-900">Aboda<span class="text-emerald-600">Farm</span></span>
                    </div>
                    <p class="mt-4 text-sm leading-relaxed text-slate-600">
                        Toko online terpercaya untuk kebutuhan peternakan dan perlengkapan pertanian berkualitas.
                    </p>
                    <div class="mt-4 flex gap-3">
                        <a href="#" class="flex size-9 items-center justify-center rounded-lg bg-slate-100 text-slate-500 transition-all hover:bg-emerald-100 hover:text-emerald-600">
                            <i class="fa-brands fa-facebook-f"></i>
                        </a>
                        <a href="#" class="flex size-9 items-center justify-center rounded-lg bg-slate-100 text-slate-500 transition-all hover:bg-emerald-100 hover:text-emerald-600">
                            <i class="fa-brands fa-instagram"></i>
                        </a>
                        <a href="#" class="flex size-9 items-center justify-center rounded-lg bg-slate-100 text-slate-500 transition-all hover:bg-emerald-100 hover:text-emerald-600">
                            <i class="fa-brands fa-tiktok"></i>
                        </a>
                    </div>
                </div>
                <div>
                    <h4 class="flex items-center gap-2 font-bold text-slate-900">
                        <i class="fa-solid fa-link text-emerald-600"></i>
                        Tautan Cepat
                    </h4>
                    <ul class="mt-4 space-y-2.5 text-sm text-slate-600">
                        <li><a href="{{ route('home') }}" class="group flex items-center gap-2 transition hover:text-emerald-600">
                            <i class="fa-solid fa-chevron-right text-xs text-slate-400 transition-transform group-hover:translate-x-1 group-hover:text-emerald-500"></i>
                            Beranda
                        </a></li>
                        <li><a href="{{ route('shop') }}" class="group flex items-center gap-2 transition hover:text-emerald-600">
                            <i class="fa-solid fa-chevron-right text-xs text-slate-400 transition-transform group-hover:translate-x-1 group-hover:text-emerald-500"></i>
                            Produk
                        </a></li>
                        <li><a href="{{ route('about') }}" class="group flex items-center gap-2 transition hover:text-emerald-600">
                            <i class="fa-solid fa-chevron-right text-xs text-slate-400 transition-transform group-hover:translate-x-1 group-hover:text-emerald-500"></i>
                            Tentang Kami
                        </a></li>
                        <li><a href="{{ route('contact') }}" class="group flex items-center gap-2 transition hover:text-emerald-600">
                            <i class="fa-solid fa-chevron-right text-xs text-slate-400 transition-transform group-hover:translate-x-1 group-hover:text-emerald-500"></i>
                            Kontak
                        </a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="flex items-center gap-2 font-bold text-slate-900">
                        <i class="fa-solid fa-layer-group text-emerald-600"></i>
                        Kategori
                    </h4>
                    <ul class="mt-4 space-y-2.5 text-sm text-slate-600">
                        @foreach($categories ?? [] as $category)
                            <li><a href="#" class="group flex items-center gap-2 transition hover:text-emerald-600">
                                <i class="fa-solid fa-chevron-right text-xs text-slate-400 transition-transform group-hover:translate-x-1 group-hover:text-emerald-500"></i>
                                {{ $category['name'] }}
                            </a></li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <h4 class="flex items-center gap-2 font-bold text-slate-900">
                        <i class="fa-solid fa-address-book text-emerald-600"></i>
                        Kontak
                    </h4>
                    <ul class="mt-4 space-y-3 text-sm text-slate-600">
                        <li class="flex items-start gap-3">
                            <div class="flex size-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                                <i class="fa-solid fa-phone text-xs"></i>
                            </div>
                            <span>+62 812-3456-7890</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="flex size-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                                <i class="fa-solid fa-envelope text-xs"></i>
                            </div>
                            <span>info@abodafarm.com</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="flex size-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                                <i class="fa-solid fa-location-dot text-xs"></i>
                            </div>
                            <span>Sulawesi Utara, Indonesia</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="mt-12 flex flex-col items-center justify-between gap-4 border-t border-slate-100 pt-8 text-sm text-slate-500 sm:flex-row">
                <p>&copy; {{ date('Y') }} AbodaFarm. All rights reserved.</p>
                <div class="flex items-center gap-6">
                    <a href="#" class="transition hover:text-emerald-600">Kebijakan Privasi</a>
                    <a href="#" class="transition hover:text-emerald-600">Syarat & Ketentuan</a>
                </div>
            </div>
        </div>
    </footer>
</div>
