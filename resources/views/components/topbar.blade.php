<header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/95 backdrop-blur-lg"
    x-data="{ activeRoute: '', mobileOpen: false, cartOpen: false }"
    x-init="activeRoute = window.location.pathname; window.addEventListener('livewire:navigated', () => activeRoute = window.location.pathname)">

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        <!-- Main Row -->
        <div class="flex h-16 items-center justify-between gap-4">

            <!-- Logo & Brand -->
            <a href="{{ url('/') }}" class="flex items-center gap-2.5 group flex-shrink-0">
                <img src="/icons/MainLogo.png" alt="AbodaFarm Logo" class="h-9 w-auto object-contain transition-transform duration-300 group-hover:scale-105">
                <span class="text-lg font-bold tracking-tight text-slate-800">AbodaFarm</span>
            </a>

            <!-- Desktop Navigation - Centered -->
            <nav class="hidden items-center gap-1 lg:flex">
                <a href="{{ url('/') }}"
                    class="rounded-lg px-4 py-2 text-sm font-medium transition-all duration-200"
                    :class="activeRoute === '/' ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-700'">
                    Beranda
                </a>
                <a href="{{ route('gallery') }}"
                    class="rounded-lg px-4 py-2 text-sm font-medium transition-all duration-200"
                    :class="activeRoute.includes('/galeri') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-700'">
                    Galeri
                </a>
                <a href="{{ route('shop') }}"
                    class="rounded-lg px-4 py-2 text-sm font-medium transition-all duration-200"
                    :class="activeRoute.includes('/shop') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-700'">
                    Toko
                </a>
                <a href="{{ route('about') }}"
                    class="rounded-lg px-4 py-2 text-sm font-medium transition-all duration-200"
                    :class="activeRoute.includes('/about') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-700'">
                    Tentang
                </a>
                <a href="{{ route('contact') }}"
                    class="rounded-lg px-4 py-2 text-sm font-medium transition-all duration-200"
                    :class="activeRoute.includes('/contact') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-emerald-700'">
                    Kontak
                </a>
            </nav>

            <!-- Desktop Actions -->
            <div class="hidden items-center gap-3 lg:flex">

                <!-- Smart Search -->
                <div class="w-72 xl:w-80">
                    <livewire:smart-search />
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50/50 p-1">

                    <!-- Wishlist -->
                    <a href="{{ auth()->check() ? route('pelanggan.favorites') : route('login') }}"
                        class="relative flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition-all hover:bg-white hover:text-rose-600 hover:shadow-sm"
                        title="Wishlist">
                        <x-icon name="svg-wishlist" class="w-[18px] h-[18px] text-rose-500" />
                        <span class="hidden xl:inline">Wishlist</span>
                        <span x-show="wishlistCount > 0"
                            x-text="wishlistCount"
                            x-data="{ wishlistCount: 0 }"
                            x-init="fetch('{{ route('api.favorites.count') }}').then(r => r.json()).then(d => wishlistCount = d.count).catch(() => {})"
                            class="flex h-4 w-4 items-center justify-center rounded-full bg-rose-500 text-[9px] font-bold text-white">
                        </span>
                    </a>

                    <!-- Cart -->
                    <button @click="$dispatch('toggle-cart')"
                        class="relative flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition-all hover:bg-white hover:text-emerald-600 hover:shadow-sm"
                        title="Keranjang">
                        <x-icon name="svg-cart" class="w-[18px] h-[18px]" />
                        <span class="hidden xl:inline">Keranjang</span>
                        <span x-show="cartCount > 0"
                            x-text="cartCount"
                            class="flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 text-[9px] font-bold text-white">
                        </span>
                    </button>

                    @auth
                        <!-- Dashboard -->
                        <a href="{{ route('dashboard') }}"
                            class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition-all hover:bg-white hover:text-emerald-600 hover:shadow-sm"
                            title="Dashboard">
                            <x-icon name="layout-dashboard" class="w-[18px] h-[18px]" />
                            <span class="hidden xl:inline">Dashboard</span>
                        </a>
                    @else
                        <!-- Login -->
                        <a href="{{ route('login') }}"
                            class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition-all hover:bg-white hover:text-emerald-600 hover:shadow-sm"
                            title="Masuk">
                            <x-icon name="user" class="w-[18px] h-[18px]" />
                            <span class="hidden xl:inline">Masuk</span>
                        </a>
                    @endauth
                </div>

                <!-- CTA Button -->
                <a href="{{ route('shop') }}"
                    class="flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:from-emerald-700 hover:to-emerald-800 hover:shadow-md">
                    <x-icon name="svg-search" class="w-4 h-4" />
                    <span>Belanja</span>
                </a>

                <!-- Help Button -->
                <button @click="document.dispatchEvent(new CustomEvent('toggle-help'))"
                    class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-slate-50/50 text-slate-500 transition-all hover:bg-white hover:text-emerald-600 hover:shadow-sm"
                    title="Bantuan">
                    <x-icon name="help-circle" class="w-5 h-5" />
                </button>
            </div>

            <!-- Mobile Menu Toggle -->
            <button @click="mobileOpen = !mobileOpen" class="rounded-xl border border-slate-200 bg-slate-50 p-2.5 lg:hidden"
                aria-label="Toggle menu">
                <svg x-show="!mobileOpen" xmlns="http://www.w3.org/2000/svg" class="size-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg x-show="mobileOpen" xmlns="http://www.w3.org/2000/svg" class="size-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div x-show="mobileOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2"
        class="border-t border-slate-100 bg-white px-4 py-5 lg:hidden">

        <!-- Mobile Search -->
        <div class="mb-5">
            <livewire:smart-search />
        </div>

        <!-- Mobile Navigation -->
        <nav class="flex flex-col gap-1">
            <a href="{{ url('/') }}" @click="mobileOpen = false"
                class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition"
                :class="activeRoute === '/' ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-50'">
                <x-icon name="home" class="w-5 h-5" />
                Beranda
            </a>
            <a href="{{ route('gallery') }}" @click="mobileOpen = false"
                class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition"
                :class="activeRoute.includes('/galeri') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-50'">
                <x-icon name="images" class="w-5 h-5" />
                Galeri
            </a>
            <a href="{{ route('shop') }}" @click="mobileOpen = false"
                class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition"
                :class="activeRoute.includes('/shop') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-50'">
                <x-icon name="store" class="w-5 h-5" />
                Toko
            </a>
            <a href="{{ route('about') }}" @click="mobileOpen = false"
                class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition"
                :class="activeRoute.includes('/about') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-50'">
                <x-icon name="info" class="w-5 h-5" />
                Tentang Kami
            </a>
            <a href="{{ route('contact') }}" @click="mobileOpen = false"
                class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition"
                :class="activeRoute.includes('/contact') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-50'">
                <x-icon name="mail" class="w-5 h-5" />
                Kontak
            </a>
        </nav>

        <!-- Mobile Divider -->
        <div class="my-4 border-t border-slate-200"></div>

        <!-- Mobile Actions -->
        <div class="flex flex-col gap-2">
            @auth
                <a href="{{ route('dashboard') }}" @click="mobileOpen = false"
                    class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    <x-icon name="layout-dashboard" class="w-5 h-5" />
                    Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" @click="mobileOpen = false"
                    class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    <x-icon name="user" class="w-5 h-5" />
                    Masuk / Daftar
                </a>
            @endauth

            <!-- Wishlist & Cart in one row -->
            <div class="flex gap-2">
                <a href="{{ auth()->check() ? route('pelanggan.favorites') : route('login') }}"
                    class="flex flex-1 items-center justify-center gap-2 rounded-xl border border-rose-200 bg-white px-4 py-3 text-sm font-medium text-slate-700 hover:bg-rose-50 hover:text-rose-600">
                    <x-icon name="svg-wishlist" class="w-[18px] h-[18px] text-rose-500" />
                    Wishlist
                </a>
                <button @click="mobileOpen = false; $dispatch('toggle-cart')"
                    class="flex flex-1 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    <x-icon name="svg-cart" class="w-[18px] h-[18px]" />
                    Keranjang
                    <span x-show="cartCount > 0" x-text="'(' + cartCount + ')'" class="rounded-full bg-emerald-500 px-2 py-0.5 text-xs font-bold text-white"></span>
                </button>
            </div>

            <a href="{{ route('shop') }}" @click="mobileOpen = false"
                class="flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-700 px-4 py-3.5 text-sm font-semibold text-white">
                <x-icon name="svg-search" class="w-4 h-4" />
                Mulai Belanja
            </a>

            <button @click="document.dispatchEvent(new CustomEvent('toggle-help')); mobileOpen = false"
                class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
                <x-icon name="help-circle" class="w-5 h-5" />
                Pusat Bantuan
            </button>
        </div>
    </div>
</header>
