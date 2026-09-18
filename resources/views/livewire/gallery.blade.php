<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-emerald-50/30"
     x-data="{
         showContent: false,
         parallaxOffset: 0,
         activeFilter: 'all',
         hoveredCard: null,
         showLoginModal: false,
         init() {
             setTimeout(() => this.showContent = true, 100);

             window.addEventListener('scroll', () => {
                 this.parallaxOffset = window.scrollY * 0.3;
             });
         },
         isFavorited(productId) {
             return $wire.favoritedIds.includes(productId);
         },
         setType(type) {
             const parent = this.$el.closest('[wire\\:id]');
             if (parent && parent.__livewire) {
                 parent.__livewire.set('typeFilter', type);
             }
         },
         setCategory(id) {
             const parent = this.$el.closest('[wire\\:id]');
             if (parent && parent.__livewire) {
                 parent.__livewire.set('selectedCategory', id);
             }
         },
         clearFilters() {
             const parent = this.$el.closest('[wire\\:id]');
             if (parent && parent.__livewire) {
                 parent.__livewire.call('clearFilters');
             }
         }
     }"
     @show-login-required.window="showLoginModal = true">

    {{-- Login Required Modal --}}
    <div x-show="showLoginModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[100] flex items-center justify-center bg-gradient-to-br from-slate-900/80 via-slate-900/70 to-emerald-900/60 backdrop-blur-md p-4"
         @click.self="showLoginModal = false"
         @keydown.escape.window="showLoginModal = false">
        <div x-show="showLoginModal"
             x-transition:enter="transition ease-out duration-500"
             x-transition:enter-start="opacity-0 scale-75 translate-y-8"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-75 translate-y-8"
             @click.stop
             class="relative w-full max-w-md overflow-hidden rounded-3xl bg-white shadow-2xl shadow-emerald-500/30">

            {{-- Animated Background Elements --}}
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-20 -right-20 size-40 rounded-full bg-emerald-100/50 animate-pulse"></div>
                <div class="absolute -bottom-20 -left-20 size-40 rounded-full bg-amber-100/50 animate-pulse" style="animation-delay: 0.5s;"></div>
                <div class="absolute top-1/2 left-1/2 size-60 rounded-full bg-gradient-to-br from-emerald-200/20 to-amber-200/20 blur-3xl -translate-x-1/2 -translate-y-1/2"></div>
            </div>

            <div class="relative p-8 text-center">
                {{-- Icon --}}
                <div class="relative mx-auto mb-6">
                    <div class="flex size-20 items-center justify-center rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 shadow-xl shadow-emerald-500/40">
                        <svg class="size-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </div>
                    <div class="absolute -top-2 -right-2 flex size-8 items-center justify-center rounded-full bg-amber-400 text-lg shadow-lg">
                        🔒
                    </div>
                </div>

                {{-- Title & Description --}}
                <h3 class="text-2xl font-bold text-slate-900">
                    Masuk untuk Menandai Favorit
                </h3>
                <p class="mt-3 text-slate-600 leading-relaxed">
                    Simpan produk favorit Anda dengan mudah. Daftar sekarang dan nikmati kemudahan dalam mengelola daftar keinginan!
                </p>

                {{-- Benefits --}}
                <div class="mt-6 grid grid-cols-3 gap-3">
                    <div class="rounded-xl bg-emerald-50 p-3">
                        <div class="text-2xl mb-1">❤️</div>
                        <p class="text-xs font-medium text-emerald-700">Simpan Favorit</p>
                    </div>
                    <div class="rounded-xl bg-amber-50 p-3">
                        <div class="text-2xl mb-1">🔔</div>
                        <p class="text-xs font-medium text-amber-700">Notifikasi Promo</p>
                    </div>
                    <div class="rounded-xl bg-sky-50 p-3">
                        <div class="text-2xl mb-1">📱</div>
                        <p class="text-xs font-medium text-sky-700">Akses Mudah</p>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="mt-8 flex flex-col gap-3">
                    <a href="{{ route('login') }}"
                       class="group relative inline-flex items-center justify-center gap-2 overflow-hidden rounded-xl bg-gradient-to-r from-emerald-500 via-emerald-600 to-teal-500 px-6 py-4 text-base font-bold text-white shadow-xl shadow-emerald-500/40 transition-all hover:shadow-2xl hover:shadow-emerald-500/50 hover:from-emerald-600 hover:via-emerald-700 hover:to-teal-600 active:scale-[0.98]">
                        <span class="absolute inset-0 size-full animate-spin-slow rounded-full bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full"></span>
                        <svg class="size-5 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                        <span>Masuk Sekarang</span>
                    </a>
                    <a href="{{ route('register') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-xl border-2 border-slate-200 bg-white px-6 py-3 text-sm font-semibold text-slate-700 transition-all hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-700">
                        Belum punya akun?
                        <span class="font-bold">Daftar</span>
                        <svg class="size-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>

                {{-- Close Button --}}
                <button @click="showLoginModal = false"
                        class="absolute right-4 top-4 rounded-full p-2 text-slate-400 transition-all hover:bg-slate-100 hover:text-slate-600">
                    <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Hero Section with Animated Background --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-emerald-600 via-emerald-700 to-teal-700 py-16 md:py-24">
        {{-- Animated Shapes --}}
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -left-20 -top-20 size-72 rounded-full bg-emerald-400/20 blur-3xl animate-pulse"></div>
            <div class="absolute -right-20 top-1/3 size-96 rounded-full bg-teal-400/20 blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
            <div class="absolute bottom-0 left-1/3 size-80 rounded-full bg-emerald-300/20 blur-3xl animate-pulse" style="animation-delay: 2s;"></div>
        </div>

        {{-- Floating Icons --}}
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-20 left-[10%] text-4xl opacity-20 animate-float" style="animation-delay: 0s;">🐄</div>
            <div class="absolute top-32 right-[15%] text-3xl opacity-20 animate-float" style="animation-delay: 0.5s;">🐐</div>
            <div class="absolute bottom-40 left-[20%] text-5xl opacity-15 animate-float" style="animation-delay: 1s;">🐔</div>
            <div class="absolute top-1/2 right-[25%] text-4xl opacity-20 animate-float" style="animation-delay: 1.5s;">🦆</div>
            <div class="absolute bottom-20 right-[10%] text-3xl opacity-15 animate-float" style="animation-delay: 2s;">🌿</div>
        </div>

        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            {{-- Header Content --}}
            <div class="text-center"
                 :class="showContent ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'"
                 style="transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);">
                <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 text-sm font-medium text-white/90 backdrop-blur-sm">
                    <svg class="size-4 text-emerald-300" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 2a6 6 0 0 0-6 6v3.586l-.707.707A1 1 0 0 0 4 14h12a1 1 0 0 0 .707-1.707L16 11.586V8a6 6 0 0 0-6-6ZM10 18a3 3 0 0 1-3-3h6a3 3 0 0 1-3 3Z"/>
                    </svg>
                    Galeri Ternak & Produk Kami
                </span>

                <h1 class="mt-6 text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">
                    <span class="block">Temukan</span>
                    <span class="bg-gradient-to-r from-emerald-200 via-white to-teal-200 bg-clip-text text-transparent">
                        Ternak Berkualitas
                    </span>
                </h1>

                <p class="mx-auto mt-6 max-w-2xl text-lg text-emerald-100/90">
                    Koleksi lengkap ternak organik dan daging segar pilihan dengan jaminan kesehatan dan kualitas terbaik langsung dari peternakan mitra terpercaya
                </p>

                {{-- Stats --}}
                <div class="mt-10 flex flex-wrap justify-center gap-8"
                     :class="showContent ? 'opacity-100' : 'opacity-0'"
                     style="transition-delay: 0.3s; transition: all 0.6s ease;">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-white">{{ $totalCount ?? 0 }}+</div>
                        <div class="text-sm text-emerald-200/80">Produk Tersedia</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-white">{{ count($categories) }}+</div>
                        <div class="text-sm text-emerald-200/80">Kategori</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-white">100%</div>
                        <div class="text-sm text-emerald-200/80">Organik</div>
                    </div>
                </div>
            </div>

            {{-- Search Bar --}}
            <div class="mt-12 max-w-2xl mx-auto"
                 :class="showContent ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'"
                 style="transition-delay: 0.5s; transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);">
                <div class="group relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-5">
                        <svg class="size-5 text-slate-400 transition-colors group-focus-within:text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text"
                           wire:model.live.debounce.300ms="search"
                           placeholder="Cari ternak, kategori, atau lokasi..."
                           class="block w-full rounded-2xl border-0 bg-white/95 py-4 pl-14 pr-32 text-slate-900 shadow-xl shadow-emerald-900/20 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-400/50 transition-all group-hover:shadow-2xl group-hover:shadow-emerald-900/30">
                    <div class="absolute inset-y-0 right-2 flex items-center">
                        <button type="button"
                                wire:click="clearFilters"
                                x-show="$wire.search || $wire.selectedCategory || $wire.typeFilter !== 'all'"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                class="mr-2 rounded-xl bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-200 transition-colors">
                            Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Section --}}
    <div class="border-b border-slate-200/80 bg-white/95 backdrop-blur-xl sticky top-[64px] z-30 shadow-sm">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-3 py-3 lg:flex-row lg:items-center lg:justify-between lg:gap-4">
                {{-- Type Filter Pills --}}
                <div class="flex items-center gap-2">
                    <button @click="setType('all')"
                            class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium transition-all"
                            :class="$wire.typeFilter === 'all' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-500/30' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        </svg>
                        Semua
                    </button>
                    <button @click="setType('live')"
                            class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium transition-all"
                            :class="$wire.typeFilter === 'live' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-500/30' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        Ternak Hidup
                    </button>
                    <button @click="setType('fresh')"
                            class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium transition-all"
                            :class="$wire.typeFilter === 'fresh' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-500/30' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                        </svg>
                        Daging Segar
                    </button>
                </div>

                {{-- Category Dropdown --}}
                @php
                    $selectedCat = collect($categories ?? [])->firstWhere('id', $selectedCategory ?? 0);
                @endphp
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open"
                            class="inline-flex items-center gap-2 rounded-xl border px-4 py-2 text-sm font-medium transition-all"
                            :class="$wire.selectedCategory !== null ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-white border-slate-200 text-slate-700 hover:border-slate-300'">
                        <span>{!! $selectedCat['icon'] ?? '' !!} {{ $selectedCat['name'] ?? 'Semua Kategori' }}</span>
                        <svg class="size-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open" @click.outside="open = false"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="absolute right-0 top-full z-30 mt-2 w-56 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">
                        <div class="p-2">
                            <button @click="setCategory(null); open = false"
                                    class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-all"
                                    :class="$wire.selectedCategory === null ? 'bg-emerald-50 text-emerald-700 font-medium' : 'text-slate-600 hover:bg-slate-50'">
                                Semua Kategori
                            </button>
                            @foreach($categories as $category)
                                <button @click="setCategory({{ $category['id'] }}); open = false"
                                        class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-all"
                                        :class="$wire.selectedCategory === {{ $category['id'] }} ? 'bg-emerald-50 text-emerald-700 font-medium' : 'text-slate-600 hover:bg-slate-50'">
                                    <span>{{ $category['icon'] }}</span>
                                    {{ $category['name'] }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Products Grid --}}
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12">
        {{-- Results Header --}}
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">
                    <span>{{ $totalCount ?? 0 }}</span> Produk Ditemukan
                </h2>
                <p class="mt-1 text-sm text-slate-500" x-show="$wire.search">
                    Hasil pencarian untuk "<span x-text="$wire.search"></span>"
                </p>
            </div>
        </div>

        {{-- Products --}}
        @if($products->count() > 0)
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
                 x-data="{
                     init() {
                         if ('IntersectionObserver' in window) {
                             const observer = new IntersectionObserver((entries) => {
                                 entries.forEach(entry => {
                                     if (entry.isIntersecting) {
                                         const el = entry.target;
                                         el.classList.add('card-visible');
                                         el.style.animationDelay = el.dataset.delay + 'ms';
                                         observer.unobserve(el);
                                     }
                                 });
                             }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

                             document.querySelectorAll('.product-card').forEach(card => {
                                 observer.observe(card);
                             });
                         }
                     }
                 }">
                @foreach($products as $index => $product)
                    <article class="product-card group relative overflow-hidden rounded-3xl bg-white shadow-lg shadow-slate-200/50 transition-all duration-500 hover:shadow-2xl hover:shadow-emerald-500/10"
                             data-delay="{{ $index * 80 }}"
                             data-aos="{{ $index % 2 === 0 ? 'fade-right' : 'fade-left' }}"
                             data-aos-delay="{{ ($index % 4) * 100 }}"
                             x-data="{ isHovered: false }"
                             @mouseenter="isHovered = true"
                             @mouseleave="isHovered = false">

                        {{-- Image Container --}}
                        <div class="relative aspect-[4/3] overflow-hidden bg-slate-100">
                            <img src="{{ $product->image_url }}"
                                 alt="{{ $product->name }}"
                                 class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110"
                                 loading="lazy">

                            {{-- Overlay Gradient --}}
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>

                            {{-- Badges --}}
                            <div class="absolute left-4 top-4 flex flex-col gap-2">
                                @if($product->is_featured)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-400 px-3 py-1 text-xs font-bold text-amber-900 shadow-lg">
                                        <svg class="size-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                        Unggulan
                                    </span>
                                @endif
                                @if($product->type === 'live')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500/90 px-3 py-1 text-xs font-semibold text-white backdrop-blur-sm">
                                        <svg class="size-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        Ternak Hidup
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-rose-500/90 px-3 py-1 text-xs font-semibold text-white backdrop-blur-sm">
                                        <svg class="size-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                                        </svg>
                                        Daging Segar
                                    </span>
                                @endif
                            </div>

                            {{-- Quick Actions Overlay --}}
                            <div class="absolute inset-0 flex items-center justify-center gap-3 opacity-0 transition-all duration-300 group-hover:opacity-100"
                                 :class="isHovered ? 'scale-100' : 'scale-90'">
                                <a href="{{ route('home') }}?product={{ $product->id }}"
                                   class="flex size-12 items-center justify-center rounded-full bg-white/95 text-emerald-600 shadow-lg transition-all hover:bg-emerald-600 hover:text-white hover:scale-110">
                                    <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <button wire:click="toggleFavorite({{ $product->id }})"
                                        x-data="{ animating: false }"
                                        @click="animating = true; setTimeout(() => animating = false, 600)"
                                        class="flex size-12 items-center justify-center rounded-full bg-white/95 shadow-lg transition-all hover:scale-110"
                                        :class="isFavorited({{ $product->id }}) ? 'text-rose-500 hover:bg-rose-500 hover:text-white' : 'text-slate-600 hover:bg-rose-500 hover:text-white'">
                                    <svg x-show="!isFavorited({{ $product->id }})"
                                         class="size-5 transition-transform hover:scale-110"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                    </svg>
                                    <svg x-show="isFavorited({{ $product->id }})"
                                         x-transition:enter="transition ease-out duration-300"
                                         x-transition:enter-start="scale-0 rotate-[-180deg]"
                                         x-transition:enter-end="scale-100 rotate-0"
                                         class="size-5"
                                         :class="animating ? 'animate-heartbeat' : ''"
                                         fill="currentColor" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                    </svg>
                                </button>
                            </div>

                            {{-- Status Badge --}}
                            <div class="absolute bottom-4 right-4">
                                <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold backdrop-blur-sm
                                    @if($product->status === 'available')
                                        bg-emerald-100/90 text-emerald-700
                                    @elseif($product->status === 'pre_order')
                                        bg-amber-100/90 text-amber-700
                                    @else
                                        bg-slate-100/90 text-slate-600
                                    @endif">
                                    @if($product->status === 'available')
                                        <span class="relative flex size-2">
                                            <span class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-500 opacity-75"></span>
                                            <span class="relative inline-flex size-2 rounded-full bg-emerald-500"></span>
                                        </span>
                                    @endif
                                    {{ $product->status_label }}
                                </span>
                            </div>
                        </div>

                        {{-- Content --}}
                        <div class="p-5">
                            {{-- Category --}}
                            <p class="text-xs font-medium uppercase tracking-wider text-emerald-600">
                                {{ $product->category?->name ?? 'Uncategorized' }}
                            </p>

                            {{-- Title --}}
                            <h3 class="mt-2 line-clamp-1 text-lg font-bold text-slate-900 transition-colors group-hover:text-emerald-600">
                                {{ $product->name }}
                            </h3>

                            {{-- Specs --}}
                            @if($product->type === 'live' && ($product->weight || $product->age || $product->sex))
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @if($product->weight)
                                        <span class="inline-flex items-center gap-1 rounded-lg bg-slate-100 px-2 py-1 text-xs text-slate-600">
                                            <svg class="size-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                                            </svg>
                                            {{ $product->weight }} Kg
                                        </span>
                                    @endif
                                    @if($product->age)
                                        <span class="inline-flex items-center gap-1 rounded-lg bg-slate-100 px-2 py-1 text-xs text-slate-600">
                                            <svg class="size-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            {{ $product->age }}
                                        </span>
                                    @endif
                                    @if($product->sex)
                                        <span class="inline-flex items-center gap-1 rounded-lg bg-slate-100 px-2 py-1 text-xs text-slate-600">
                                            {{ $product->sex === 'male' ? 'Jantan' : ($product->sex === 'female' ? 'Betina' : 'Campuran') }}
                                        </span>
                                    @endif
                                </div>
                            @endif

                            {{-- Location --}}
                            @if($product->origin)
                                <p class="mt-2 flex items-center gap-1 text-xs text-slate-500">
                                    <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    {{ $product->origin }}
                                </p>
                            @endif

                            {{-- Price & Action --}}
                            <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4">
                                <div>
                                    <p class="text-2xl font-bold text-emerald-600">
                                        {{ $product->formatted_price }}
                                    </p>
                                    @if($product->type === 'live')
                                        <p class="text-xs text-slate-400">Ternak Hidup</p>
                                    @else
                                        <p class="text-xs text-slate-400">per kg</p>
                                    @endif
                                </div>
                                <a href="{{ route('home') }}?product={{ $product->id }}"
                                   class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-emerald-500/30 transition-all hover:bg-emerald-700 hover:shadow-emerald-500/50 active:scale-95">
                                    Detail
                                    <svg class="size-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-12"
                 x-data="{ show: false }"
                 x-init="setTimeout(() => show = true, 500)">
                <div class="flex justify-center" :class="show ? 'opacity-100' : 'opacity-0'" style="transition: all 0.5s ease;">
                    <nav class="flex items-center gap-2">
                        @if($products->onFirstPage())
                            <span class="flex size-10 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
                                <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </span>
                        @else
                            <a href="{{ $products->previousPageUrl() }}"
                               class="flex size-10 items-center justify-center rounded-xl bg-white text-slate-600 shadow-sm ring-1 ring-slate-200 transition-all hover:bg-emerald-50 hover:text-emerald-600 hover:ring-emerald-200">
                                <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </a>
                        @endif

                        @foreach($products->getUrlRange(max(1, $products->currentPage() - 2), min($products->lastPage(), $products->currentPage() + 2)) as $page => $url)
                            @if($page == $products->currentPage())
                                <span class="flex size-10 items-center justify-center rounded-xl bg-emerald-600 text-sm font-bold text-white shadow-lg shadow-emerald-500/30">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}"
                                   class="flex size-10 items-center justify-center rounded-xl bg-white text-sm font-medium text-slate-600 shadow-sm ring-1 ring-slate-200 transition-all hover:bg-emerald-50 hover:text-emerald-600 hover:ring-emerald-200">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach

                        @if($products->hasMorePages())
                            <a href="{{ $products->nextPageUrl() }}"
                               class="flex size-10 items-center justify-center rounded-xl bg-white text-slate-600 shadow-sm ring-1 ring-slate-200 transition-all hover:bg-emerald-50 hover:text-emerald-600 hover:ring-emerald-200">
                                <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        @else
                            <span class="flex size-10 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
                                <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </span>
                        @endif
                    </nav>
                </div>

                <p class="mt-4 text-center text-sm text-slate-500">
                    Menampilkan {{ $products->firstItem() }} - {{ $products->lastItem() }} dari {{ $products->total() }} produk
                </p>
            </div>
        @else
            {{-- Empty State --}}
            <div class="flex flex-col items-center justify-center py-20"
                 x-data="{ show: false }"
                 x-init="setTimeout(() => show = true, 200)"
                 :class="show ? 'opacity-100 scale-100' : 'opacity-0 scale-95'"
                 style="transition: all 0.5s ease;">
                <div class="size-24 rounded-full bg-slate-100 flex items-center justify-center mb-6">
                    <svg class="size-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-slate-900">Tidak Ada Produk</h3>
                <p class="mt-2 text-center text-slate-500 max-w-md">
                    Maaf, tidak ada produk yang sesuai dengan pencarian atau filter Anda. Coba ubah kriteria pencarian.
                </p>
                <button wire:click="clearFilters"
                        class="mt-6 inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-6 py-3 text-sm font-semibold text-white shadow-lg transition-all hover:bg-emerald-700">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Reset Filter
                </button>
            </div>
        @endif
    </div>

    {{-- Features Section --}}
    <section class="bg-gradient-to-br from-slate-900 via-slate-800 to-emerald-900/20 py-20"
             x-data="{
                 init() {
                     if ('IntersectionObserver' in window) {
                         const observer = new IntersectionObserver((entries) => {
                             entries.forEach(entry => {
                                 if (entry.isIntersecting) {
                                     entry.target.classList.add('visible');
                                     observer.unobserve(entry.target);
                                 }
                             });
                         }, { threshold: 0.2 });

                         document.querySelectorAll('.feature-card').forEach(card => {
                             observer.observe(card);
                         });
                     } else {
                         document.querySelectorAll('.feature-card').forEach(card => {
                             card.classList.add('visible');
                         });
                     }
                 }
             }">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 feature-card">
                <h2 class="text-3xl font-bold text-white">Kenapa Memilih AbodaFarm?</h2>
                <p class="mt-4 text-lg text-slate-300">Komitmen kami untuk kualitas dan kepercayaan</p>
            </div>

            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
                @php
                    $features = [
                        ['icon' => 'shield-check', 'title' => '100% Organik', 'desc' => 'Ternak dipelihara secara alami dengan pakan organik berkualitas'],
                        ['icon' => 'heart', 'title' => 'Sehat & Terawat', 'desc' => 'Pemeriksaan kesehatan berkala oleh tenaga ahli'],
                        ['icon' => 'truck', 'title' => 'Pengiriman Aman', 'desc' => 'Standar pengiriman tinggi untuk kenyamanan ternak'],
                        ['icon' => 'badge-check', 'title' => 'Garansi Kualitas', 'desc' => 'Jaminan kesehatan dan kondisi ternak saat diterima'],
                    ];
                @endphp

                @foreach($features as $index => $feature)
                    <div class="feature-card group relative rounded-3xl bg-white/5 p-8 backdrop-blur-sm transition-all duration-500 hover:bg-white/10"
                         style="transition-delay: {{ $index * 100 }}ms"
                         data-aos="fade-up"
                         data-aos-delay="{{ $index * 100 }}">
                        <div class="absolute inset-0 rounded-3xl bg-gradient-to-br from-emerald-500/10 to-transparent opacity-0 transition-opacity group-hover:opacity-100"></div>
                        <div class="relative">
                            <div class="flex size-14 items-center justify-center rounded-2xl bg-emerald-500/20 text-emerald-400 mb-6 group-hover:scale-110 transition-transform">
                                @if($feature['icon'] === 'shield-check')
                                    <svg class="size-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                @elseif($feature['icon'] === 'heart')
                                    <svg class="size-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                    </svg>
                                @elseif($feature['icon'] === 'truck')
                                    <svg class="size-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6m0 0L8 11m5 5l5-5m-6 6l-3-3m3 3l3-3"/>
                                    </svg>
                                @else
                                    <svg class="size-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                    </svg>
                                @endif
                            </div>
                            <h3 class="text-xl font-bold text-white">{{ $feature['title'] }}</h3>
                            <p class="mt-2 text-slate-400">{{ $feature['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA Section --}}
    <section class="bg-gradient-to-r from-emerald-600 to-teal-600 py-16">
        <div class="mx-auto max-w-4xl px-4 text-center">
            <h2 class="text-3xl font-bold text-white">Siap Memulai?</h2>
            <p class="mt-4 text-lg text-emerald-100">
                Hubungi kami sekarang untuk konsultasi gratis tentang kebutuhan ternak Anda
            </p>
            <div class="mt-8 flex flex-wrap justify-center gap-4">
                <a href="{{ route('contact') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3 font-bold text-emerald-700 shadow-xl transition-all hover:bg-emerald-50 hover:shadow-2xl">
                    <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    Hubungi Kami
                </a>
                <a href="{{ route('home') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-emerald-700 px-6 py-3 font-bold text-white shadow-xl transition-all hover:bg-emerald-800">
                    Kembali ke Beranda
                </a>
            </div>
        </div>
    </section>

    @livewire('home.footer')
</div>

@push('styles')
<style>
    @keyframes float {
        0%, 100% { transform: translateY(0) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(5deg); }
    }
    .animate-float {
        animation: float 6s ease-in-out infinite;
    }

    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }
    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    /* Scroll Animation */
    .product-card {
        opacity: 0;
        transform: translateY(30px);
        transition: opacity 0.6s ease-out, transform 0.6s ease-out;
    }

    .product-card.card-visible {
        opacity: 1;
        transform: translateY(0);
    }

    /* Heart Animation */
    @keyframes heartbeat {
        0% { transform: scale(1); }
        15% { transform: scale(1.25); }
        30% { transform: scale(1); }
        45% { transform: scale(1.15); }
        60% { transform: scale(1); }
    }

    .animate-heartbeat {
        animation: heartbeat 0.6s ease-in-out;
    }

    @keyframes spin-slow {
        from { transform: translateX(-100%) rotate(0deg); }
        to { transform: translateX(200%) rotate(360deg); }
    }

    .animate-spin-slow {
        animation: spin-slow 2s linear infinite;
    }

    /* Feature Cards Scroll Animation */
    .feature-card {
        opacity: 0;
        transform: translateY(40px);
        transition: opacity 0.7s ease-out, transform 0.7s ease-out;
    }

    .feature-card.visible {
        opacity: 1;
        transform: translateY(0);
    }
</style>
@endpush
