<header class="sticky top-0 z-40 border-b border-emerald-100/80 bg-white/85 backdrop-blur">

    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
        <a href="{{ url('/') }}" class="flex items-center gap-3">
            <span
                class="grid size-10 place-content-center rounded-xl bg-linear-to-br from-emerald-600 to-amber-500 text-lg font-bold text-white">AF</span>
            <span class="text-xl font-semibold tracking-tight">AbodaFarm</span>

        </a>

        <nav class="hidden items-center gap-8 text-sm font-medium lg:flex">
            <a href="{{ url('/') }}" class="transition hover:text-emerald-700">Beranda</a>
            <a href="{{ route('home') }}#ternak" class="transition hover:text-emerald-700">Ternak</a>
            <a href="{{ route('about') }}" class="transition hover:text-emerald-700">Tentang Kami</a>
            <a href="{{ route('contact') }}" class="transition hover:text-emerald-700">Kontak</a>
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
                @livewire('test-notification')
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
            <a href="#produk"
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
            <a href="#kategori" @click="mobileOpen = false">Kategori</a>
            <a href="#produk" @click="mobileOpen = false">Produk</a>
            <a href="#keunggulan" @click="mobileOpen = false">Keunggulan</a>
            <a href="#faq" @click="mobileOpen = false">FAQ</a>
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
