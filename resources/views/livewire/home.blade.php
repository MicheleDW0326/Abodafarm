<div x-data="{
    mobileOpen: false,
    cartOpen: false,
    showToast: false,
    toastText: '',
    toastType: 'success',
    toastTimer: null,
    cartItems: JSON.parse(localStorage.getItem('abodafarm_cart') || '[]'),
    showScrollTop: false,

    showAddToast(name) {
        this.toastText = `${name} ditambahkan ke keranjang`
        this.toastType = 'success'
        this.showToast = true
        clearTimeout(this.toastTimer)
        this.toastTimer = setTimeout(() => {
            this.showToast = false
        }, 2200)
    },

    showAlert(data) {
        this.toastText = data.message || data.detail?.message || ''
        this.toastType = data.type || data.detail?.type || 'success'
        this.showToast = true
        clearTimeout(this.toastTimer)
        this.toastTimer = setTimeout(() => {
            this.showToast = false
        }, 4000)
    },

    addToCart(product) {
        const type = product.type || 'fresh';
        const key = 'product_' + product.id + '_' + type;
        const existing = this.cartItems.find(item => item.key === key);

        if (existing) {
            existing.qty++;
        } else {
            this.cartItems.push({
                key: key,
                product_id: product.id,
                name: product.name,
                price: parseInt(product.price),
                image: product.image,
                type: type,
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

    clearCart() {
        this.cartItems = [];
        localStorage.setItem('abodafarm_cart', '[]');
        this.$dispatch('cart-updated');
    },

    get cartCount() {
        return this.cartItems.reduce((sum, item) => sum + item.qty, 0);
    },

    get cartTotal() {
        return this.cartItems.reduce((sum, item) => sum + (item.price * item.qty), 0);
    },

    init() {
        // Sync with localStorage on page load
        window.addEventListener('storage', (e) => {
            if (e.key === 'abodafarm_cart') {
                this.cartItems = JSON.parse(e.newValue || '[]');
            }
        });

        // Scroll-to-top visibility listener
        const onScroll = () => {
            this.showScrollTop = window.scrollY > 400;
        };
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    },

    scrollToTop() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}" x-on:cart-item-added.window="showAddToast($event.detail.name)"
    x-on:add-to-cart.window="addToCart($event.detail)" x-on:clear-cart.window="clearCart()"
    x-on:toggle-cart.window="cartOpen = !cartOpen"
    x-on:alert.window="showAlert($event.detail)" class="bg-[#f5faf6] text-slate-900">

    @include('components.topbar')

    @include('components.cart-drawer')

    @include('components.toast')

    <livewire:home.hero :hero-slides="$heroSlides" />

    <!-- Statistik singkat -->
    <section class="mx-auto -mt-10 max-w-7xl px-4 sm:px-6 lg:px-8">
        <div
            class="grid gap-4 rounded-4xl border border-emerald-100 bg-white p-5 shadow-[0_20px_60px_rgba(16,185,129,0.08)] sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($farmStats as $stat)
                <article class="rounded-2xl bg-linear-to-br from-emerald-50 to-white p-4">
                    <p class="text-3xl font-black tracking-tight text-emerald-700">{{ $stat['value'] }}</p>
                    <p class="mt-1 text-sm font-medium text-slate-600">{{ $stat['label'] }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <livewire:home.highlights :highlights="$highlights" lazy-on-visible />
    <livewire:home.categories :categories="$categories" lazy-on-visible />

    <!-- Recently Viewed Section -->
    @if (count($recentlyViewed) > 0)
        <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-100 text-violet-600">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Baru Dilihat</h2>
                        <p class="text-sm text-slate-500">Produk yang baru Anda lihat</p>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex gap-4 overflow-x-auto pb-4 scrollbar-hide">
                @foreach ($recentlyViewed as $item)
                    <a href="{{ route('shop.product', $item['slug']) }}" class="group shrink-0">
                        <div
                            class="w-40 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                            <div class="aspect-square overflow-hidden bg-gradient-to-br from-slate-100 to-slate-200">
                                @if ($item['image'])
                                    <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}"
                                        class="size-full object-cover transition-transform duration-500 group-hover:scale-105">
                                @else
                                    <div class="flex size-full items-center justify-center">
                                        <i class="fa-solid fa-image text-3xl text-slate-300"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="p-3">
                                <p class="line-clamp-2 text-xs font-semibold text-slate-900">{{ $item['name'] }}</p>
                                <p class="mt-1 text-sm font-bold text-emerald-600">{{ $item['price'] }}</p>
                                <p class="mt-1 text-[10px] text-slate-400">{{ $item['viewed_at'] ?? 'Baru dilihat' }}</p>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <!-- Continue Shopping Section -->
    @if (count($continueShopping) > 0)
        <livewire:home.product-carousel
            :products="$continueShopping"
            title="Lanjutkan Belanja"
            subtitle="Lanjut dari produk yang pernah Anda lihat"
            icon="fa-solid fa-cart-shopping"
            icon-color="bg-emerald-100 text-emerald-600"
            section-id="continue-shopping"
            :show-view-all="false"
            lazy-on-visible
        />
    @endif

    <!-- Recommended For You Section (Logged in users) -->
    @if (count($recommendedProducts) > 0)
        <livewire:home.product-carousel
            :products="$recommendedProducts"
            title="Rekomendasi Untuk Anda"
            subtitle="Berdasarkan histori belanja Anda"
            icon="fa-solid fa-sparkles"
            icon-color="bg-purple-100 text-purple-600"
            section-id="recommended-for-you"
            lazy-on-visible
        />
    @endif

    <!-- Trending This Week -->
    @if (count($trendingProducts) > 0)
        <livewire:home.product-carousel
            :products="$trendingProducts"
            title="Trending Minggu Ini"
            subtitle="Produk yang sedang populer"
            icon="fa-solid fa-fire text-orange-500"
            icon-color="bg-orange-100 text-orange-600"
            section-id="trending"
            lazy-on-visible
        />
    @endif

    <!-- New Arrivals -->
    @if (count($newArrivals) > 0)
        <livewire:home.product-carousel
            :products="$newArrivals"
            title="Produk Baru"
            subtitle="Baru hadir di AbodaFarm"
            icon="fa-solid fa-bolt"
            icon-color="bg-blue-100 text-blue-600"
            section-id="new-arrivals"
            lazy-on-visible
        />
    @endif

    <!-- Popular Products -->
    @if (count($popularProducts) > 0)
        <livewire:home.product-carousel
            :products="$popularProducts"
            title="Populer"
            subtitle="Paling banyak diminati"
            icon="fa-solid fa-star"
            icon-color="bg-amber-100 text-amber-600"
            section-id="popular"
            lazy-on-visible
        />
    @endif

    <!-- Top Rated -->
    @if (count($topRatedProducts) > 0)
        <livewire:home.product-carousel
            :products="$topRatedProducts"
            title="Pilihan Terbaik"
            subtitle="Produk dengan rating tertinggi"
            icon="fa-solid fa-award"
            icon-color="bg-yellow-100 text-yellow-600"
            section-id="top-rated"
            lazy-on-visible
        />
    @endif

    <!-- Organic Choice -->
    @if (count($organicProducts) > 0)
        <livewire:home.product-carousel
            :products="$organicProducts"
            title="Pilihan Organik"
            subtitle="Daging segar berkualitas premium"
            icon="fa-solid fa-leaf"
            icon-color="bg-green-100 text-green-600"
            section-id="organic-choice"
            lazy-on-visible
        />
    @endif

    <!-- Produk dengan filter yang terhubung ke Home::filteredLiveLivestockProducts -->
    <livewire:home.products :products-live="$this->filteredLiveLivestockProducts" :products-fresh="$this->freshMeatProducts" lazy-on-visible />

    <!-- Proses perawatan dan trust section -->
    <section id="perawatan" class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid gap-8 lg:grid-cols-[1.1fr_0.9fr] lg:items-center">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.25em] text-emerald-700">Proses Perawatan</p>
                <h2 class="mt-3 text-3xl font-semibold text-slate-900 sm:text-4xl">Ternak dirawat sebelum masuk katalog.
                </h2>
                <p class="mt-3 max-w-2xl text-slate-600">Konten ini membantu pembeli memahami standar kualitas AbodaFarm
                    sebelum booking dilakukan.</p>

                <div class="mt-8 space-y-4">
                    @foreach ($careStages as $stage)
                        <article class="rounded-2xl border border-emerald-100 bg-white p-4 shadow-sm">
                            <h3 class="text-lg font-semibold text-slate-900">{{ $stage['title'] }}</h3>
                            <p class="mt-1 text-sm text-slate-600">{{ $stage['description'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div
                    class="rounded-4xl bg-linear-to-br from-emerald-500 to-lime-500 p-6 text-white shadow-xl shadow-emerald-200">
                    <p class="text-sm font-semibold uppercase tracking-[0.25em] text-white/75">Gallery</p>
                    <h3 class="mt-2 text-2xl font-semibold">Foto kandang, perawatan, dan dokter hewan.</h3>
                </div>
                <div class="rounded-4xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-semibold uppercase tracking-[0.25em] text-slate-500">Trust</p>
                    <h3 class="mt-2 text-2xl font-semibold text-slate-900">Riwayat perawatan tersedia saat diperlukan.
                    </h3>
                    <p class="mt-3 text-sm text-slate-600">Cocok untuk transaksi bernilai tinggi yang perlu bukti
                        kondisi ternak.</p>
                </div>
                <div class="rounded-4xl border border-slate-200 bg-white p-6 shadow-sm sm:col-span-2">
                    <p class="text-sm font-semibold uppercase tracking-[0.25em] text-slate-500">Sertifikat</p>
                    <h3 class="mt-2 text-2xl font-semibold text-slate-900">Sertifikat kesehatan dan hasil pemeriksaan
                        bisa diminta saat booking.</h3>
                </div>
            </div>
        </div>
    </section>

    <livewire:home.delivery :delivery-steps="$deliverySteps" lazy-on-visible />
    <livewire:home.testimonials :testimonials="$testimonials" lazy-on-visible />
    <livewire:home.faq :faqs="$faqs" lazy-on-visible />
    <livewire:home.cta lazy-on-visible />
    <livewire:home.footer lazy-on-visible />

    <livewire:side-panel />

    <!-- Scroll to top button -->
    <button x-show="showScrollTop" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-4" @click="scrollToTop()" aria-label="Kembali ke atas"
        class="fixed bottom-24 right-5 z-50 flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-emerald-500 to-emerald-700 text-white shadow-lg shadow-emerald-500/30 transition-all duration-300 hover:scale-110 hover:shadow-emerald-500/50 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M12 19V5M5 12l7-7 7 7" />
        </svg>
    </button>
</div>
