<header class="sticky top-0 z-40 border-b border-emerald-100/80 bg-white/85 backdrop-blur"
    x-data="{ activeRoute: '' }"
    x-init="activeRoute = window.location.pathname; window.addEventListener('livewire:navigated', () => activeRoute = window.location.pathname)">

    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
        <a href="{{ url('/') }}" class="flex items-center gap-3">
            <span
                class="grid size-10 place-content-center rounded-xl bg-linear-to-br from-emerald-600 to-amber-500 text-lg font-bold text-white">AF</span>
            <span class="text-xl font-semibold tracking-tight">AbodaFarm</span>

        </a>

        <nav class="hidden items-center gap-8 text-sm font-medium lg:flex">
            <a href="{{ url('/') }}"
                class="relative transition hover:text-emerald-700"
                :class="activeRoute === '/' ? 'text-emerald-700' : 'text-slate-600'">
                Beranda
                <span x-show="activeRoute === '/'" class="absolute -bottom-1 left-0 h-0.5 w-full rounded-full bg-emerald-500"></span>
            </a>
            <a href="{{ route('gallery') }}"
                class="relative transition hover:text-emerald-700"
                :class="activeRoute.includes('/galeri') ? 'text-emerald-700' : 'text-slate-600'">
                Galeri
                <span x-show="activeRoute.includes('/galeri')" class="absolute -bottom-1 left-0 h-0.5 w-full rounded-full bg-emerald-500"></span>
            </a>
            <a href="{{ route('shop') }}"
                class="relative transition hover:text-emerald-700"
                :class="activeRoute.includes('/shop') ? 'text-emerald-700' : 'text-slate-600'">
                Toko
                <span x-show="activeRoute.includes('/shop')" class="absolute -bottom-1 left-0 h-0.5 w-full rounded-full bg-emerald-500"></span>
            </a>
            <a href="{{ route('about') }}"
                class="relative transition hover:text-emerald-700"
                :class="activeRoute.includes('/about') ? 'text-emerald-700' : 'text-slate-600'">
                Tentang Kami
                <span x-show="activeRoute.includes('/about')" class="absolute -bottom-1 left-0 h-0.5 w-full rounded-full bg-emerald-500"></span>
            </a>
            <a href="{{ route('contact') }}"
                class="relative transition hover:text-emerald-700"
                :class="activeRoute.includes('/contact') ? 'text-emerald-700' : 'text-slate-600'">
                Kontak
                <span x-show="activeRoute.includes('/contact')" class="absolute -bottom-1 left-0 h-0.5 w-full rounded-full bg-emerald-500"></span>
            </a>
        </nav>

        <div class="hidden items-center gap-3 lg:flex">
            <div class="relative hidden xl:block">
                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">⌕</span>
                <input type="search" wire:model.live.debounce.300ms="search"
                    placeholder="Cari ternak, kategori, lokasi"
                    class="h-11 w-72 rounded-2xl border border-emerald-100 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none ring-0 placeholder:text-slate-400 focus:border-emerald-300 focus:ring-2 focus:ring-emerald-100">
            </div>
            @auth
                <a href="{{ route('dashboard') }}"
                    class="rounded-xl border border-sky-200 px-4 py-2 text-sm font-medium text-sky-700 transition hover:bg-sky-50">Dashboard</a>
            @else
                <a href="{{ route('login') }}"
                    class="rounded-xl border border-sky-200 px-4 py-2 text-sm font-medium text-sky-700 transition hover:bg-sky-50">Masuk</a>
            @endauth
            <button @click="cartOpen = true"
                class="relative rounded-xl border border-sky-200 bg-white px-4 py-2 text-sm font-semibold text-sky-700 transition hover:bg-sky-50">
                Keranjang
                <span x-show="cartCount > 0"
                    x-text="cartCount"
                    class="absolute -right-2 -top-2 grid size-5 place-content-center rounded-full bg-emerald-500 text-[11px] font-bold text-white"></span>
            </button>
            <a href="{{ route('shop') }}"
                class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-emerald-300/50 transition hover:bg-emerald-700">Belanja</a>
            <button onclick="Livewire.emitTo('side-panel','toggle')"
                class="ml-2 rounded-xl border border-sky-200 bg-white px-3 py-2 text-sm font-semibold text-sky-700 transition hover:bg-sky-50"
                aria-label="Bantuan">Bantuan</button>

        </div>

        <button @click="mobileOpen = !mobileOpen" class="rounded-lg border border-sky-200 p-2 md:hidden"
            aria-label="Toggle menu">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>

    <div x-show="mobileOpen" x-transition class="border-t border-sky-100 bg-white px-4 py-4 md:hidden">
        <div class="flex flex-col gap-3 text-sm font-medium">
            <a href="{{ url('/') }}" @click="mobileOpen = false"
                class="flex items-center gap-2 rounded-lg px-3 py-2 transition"
                :class="activeRoute === '/' ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-50'">
                <i class="fa-solid fa-home w-5"></i> Beranda
            </a>
            <a href="{{ route('gallery') }}" @click="mobileOpen = false"
                class="flex items-center gap-2 rounded-lg px-3 py-2 transition"
                :class="activeRoute.includes('/galeri') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-50'">
                <i class="fa-solid fa-images w-5"></i> Galeri
            </a>
            <a href="{{ route('shop') }}" @click="mobileOpen = false"
                class="flex items-center gap-2 rounded-lg px-3 py-2 transition"
                :class="activeRoute.includes('/shop') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-50'">
                <i class="fa-solid fa-store w-5"></i> Toko
            </a>
            <a href="{{ route('about') }}" @click="mobileOpen = false"
                class="flex items-center gap-2 rounded-lg px-3 py-2 transition"
                :class="activeRoute.includes('/about') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-50'">
                <i class="fa-solid fa-info-circle w-5"></i> Tentang Kami
            </a>
            <a href="{{ route('contact') }}" @click="mobileOpen = false"
                class="flex items-center gap-2 rounded-lg px-3 py-2 transition"
                :class="activeRoute.includes('/contact') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-50'">
                <i class="fa-solid fa-envelope w-5"></i> Kontak
            </a>
            @auth
                <a href="{{ route('dashboard') }}" @click="mobileOpen = false"
                    class="mt-2 rounded-lg border border-sky-200 px-3 py-2 text-left">Dashboard</a>
            @else
                <a href="{{ route('login') }}" @click="mobileOpen = false"
                    class="mt-2 rounded-lg border border-sky-200 px-3 py-2 text-left">Masuk</a>
            @endauth
            <button @click="mobileOpen = false; cartOpen = true"
                class="mt-2 rounded-lg border border-sky-200 px-3 py-2 text-left">
                Keranjang <span x-show="cartCount > 0" x-text="'(' + cartCount + ')'"></span>
            </button>
        </div>
    </div>
</header>
