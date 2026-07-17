<header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/95 backdrop-blur-lg"
    x-data="{ activeRoute: '' }"
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
            <nav class="hidden items-center gap-0.5 lg:flex">
                <a href="{{ url('/') }}"
                    class="rounded-lg px-3.5 py-2 text-sm font-medium transition-all duration-200"
                    :class="activeRoute === '/' ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:text-emerald-700'">
                    Beranda
                </a>
                <a href="{{ route('gallery') }}"
                    class="rounded-lg px-3.5 py-2 text-sm font-medium transition-all duration-200"
                    :class="activeRoute.includes('/galeri') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:text-emerald-700'">
                    Galeri
                </a>
                <a href="{{ route('shop') }}"
                    class="rounded-lg px-3.5 py-2 text-sm font-medium transition-all duration-200"
                    :class="activeRoute.includes('/shop') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:text-emerald-700'">
                    Toko
                </a>
                <a href="{{ route('about') }}"
                    class="rounded-lg px-3.5 py-2 text-sm font-medium transition-all duration-200"
                    :class="activeRoute.includes('/about') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:text-emerald-700'">
                    Tentang Kami
                </a>
                <a href="{{ route('contact') }}"
                    class="rounded-lg px-3.5 py-2 text-sm font-medium transition-all duration-200"
                    :class="activeRoute.includes('/contact') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:text-emerald-700'">
                    Kontak
                </a>
            </nav>

            <!-- Desktop Actions -->
            <div class="hidden items-center gap-2 lg:flex">

                <!-- Search Bar -->
                <div class="relative">
                    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="M21 21l-4.35-4.35"/>
                        </svg>
                    </span>
                    <input type="search" wire:model.live.debounce.300ms="search"
                        placeholder="Cari produk..."
                        class="h-9 w-48 rounded-lg border border-slate-200 bg-slate-50 pl-9 pr-4 text-sm text-slate-700 outline-none transition-all placeholder:text-slate-400 focus:border-emerald-300 focus:bg-white focus:ring-2 focus:ring-emerald-100/50 lg:w-56">
                </div>

                @auth
                    <a href="{{ route('dashboard') }}"
                        class="flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition-all hover:border-slate-300 hover:bg-slate-50">
                        <x-icon name="layout-dashboard" class="w-4 h-4" />
                        <span class="hidden xl:inline">Dashboard</span>
                    </a>
                @else
                    <a href="{{ route('login') }}"
                        class="flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition-all hover:border-slate-300 hover:bg-slate-50">
                        <x-icon name="user" class="w-4 h-4" />
                        <span class="hidden xl:inline">Masuk</span>
                    </a>
                @endauth

                <!-- Cart Button -->
                <button @click="cartOpen = true"
                    class="relative flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition-all hover:border-slate-300 hover:bg-slate-50">
                    <x-icon name="shoping-bag" class="w-4 h-4" />
                    <span class="hidden xl:inline">Keranjang</span>
                    <span x-show="cartCount > 0"
                        x-text="cartCount"
                        class="flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 text-[9px] font-bold text-white">
                    </span>
                </button>

                <!-- Shop CTA -->
                <a href="{{ route('shop') }}"
                    class="flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:bg-emerald-700 hover:shadow-md">
                    <x-icon name="store" class="w-4 h-4" />
                    <span class="hidden xl:inline">Belanja</span>
                </a>

                <!-- Help Button -->
                <button @click="document.dispatchEvent(new CustomEvent('toggle-help'))"
                    class="flex items-center justify-center rounded-lg border border-slate-200 bg-white p-2 text-slate-600 transition-all hover:border-slate-300 hover:bg-slate-50 hover:text-emerald-600"
                    aria-label="Bantuan">
                    <x-icon name="help-circle" class="w-4 h-4" />
                </button>
            </div>

            <!-- Mobile Menu Toggle -->
            <button @click="mobileOpen = !mobileOpen" class="rounded-lg border border-slate-200 bg-white p-2 md:hidden"
                aria-label="Toggle menu">
                <svg x-show="!mobileOpen" xmlns="http://www.w3.org/2000/svg" class="size-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg x-show="mobileOpen" xmlns="http://www.w3.org/2000/svg" class="size-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div x-show="mobileOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2"
        class="border-t border-slate-100 bg-white px-4 py-5 md:hidden">

        <!-- Mobile Search -->
        <div class="relative mb-4">
            <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="M21 21l-4.35-4.35"/>
                </svg>
            </span>
            <input type="search" wire:model.live.debounce.300ms="search"
                placeholder="Cari produk..."
                class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-10 pr-4 text-sm text-slate-700 outline-none transition-all placeholder:text-slate-400 focus:border-emerald-300 focus:bg-white focus:ring-2 focus:ring-emerald-100/50">
        </div>

        <!-- Mobile Navigation -->
        <nav class="flex flex-col gap-1">
            <a href="{{ url('/') }}" @click="mobileOpen = false"
                class="flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium transition"
                :class="activeRoute === '/' ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-50'">
                <x-icon name="home" class="w-5 h-5" />
                Beranda
            </a>
            <a href="{{ route('gallery') }}" @click="mobileOpen = false"
                class="flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium transition"
                :class="activeRoute.includes('/galeri') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-50'">
                <x-icon name="images" class="w-5 h-5" />
                Galeri
            </a>
            <a href="{{ route('shop') }}" @click="mobileOpen = false"
                class="flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium transition"
                :class="activeRoute.includes('/shop') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-50'">
                <x-icon name="store" class="w-5 h-5" />
                Toko
            </a>
            <a href="{{ route('about') }}" @click="mobileOpen = false"
                class="flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium transition"
                :class="activeRoute.includes('/about') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-50'">
                <x-icon name="info" class="w-5 h-5" />
                Tentang Kami
            </a>
            <a href="{{ route('contact') }}" @click="mobileOpen = false"
                class="flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium transition"
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
                    class="flex items-center gap-3 rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    <x-icon name="layout-dashboard" class="w-5 h-5" />
                    Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" @click="mobileOpen = false"
                    class="flex items-center gap-3 rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    <x-icon name="user" class="w-5 h-5" />
                    Masuk
                </a>
            @endauth

            <button @click="mobileOpen = false; cartOpen = true"
                class="flex items-center gap-3 rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
                <x-icon name="shoping-bag" class="w-5 h-5" />
                Keranjang
                <span x-show="cartCount > 0" x-text="'(' + cartCount + ')'" class="ml-auto rounded-full bg-emerald-500 px-2 py-0.5 text-xs font-bold text-white"></span>
            </button>

            <a href="{{ route('shop') }}" @click="mobileOpen = false"
                class="flex items-center gap-3 rounded-lg bg-emerald-600 px-4 py-3 text-sm font-semibold text-white">
                <x-icon name="store" class="w-5 h-5" />
                Belanja Sekarang
            </a>

            <button @click="document.dispatchEvent(new CustomEvent('toggle-help')); mobileOpen = false"
                class="flex items-center gap-3 rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
                <x-icon name="help-circle" class="w-5 h-5" />
                Bantuan
            </button>
        </div>
    </div>
</header>
