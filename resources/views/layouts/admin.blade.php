<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=outfit:400,500,600,700" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="bg-[#F5F7FA] font-['Outfit'] text-slate-900 antialiased" x-data="{ sidebarOpen: false, sidebarCollapsed: false }">
        @php
            $menuItems = [
                ['route' => 'admin.dashboard', 'label' => 'Dashboard'],
                ['route' => 'admin.products', 'label' => 'Manajemen Produk'],
                ['route' => 'admin.orders', 'label' => 'Manajemen Pesanan'],
                ['route' => 'admin.customers', 'label' => 'Manajemen Pelanggan'],
                ['route' => 'admin.finance', 'label' => 'Laporan Keuangan'],
                ['route' => 'admin.staff', 'label' => 'Manajemen Staf'],
                ['route' => 'admin.settings', 'label' => 'Pengaturan Toko'],
            ];
        @endphp

        <div class="min-h-screen">
            <div class="fixed inset-0 z-30 bg-slate-900/45 lg:hidden" x-show="sidebarOpen" x-cloak x-transition.opacity @click="sidebarOpen = false"></div>

            <aside
                class="fixed inset-y-0 left-0 z-40 flex h-screen flex-col bg-[#0D1B2A] transition-all duration-300"
                :class="sidebarCollapsed ? 'w-21.5' : 'w-65'"
                x-bind:class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            >
                <div class="flex h-16 items-center border-b border-white/10 px-4" :class="sidebarCollapsed ? 'justify-center' : 'justify-start'">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 overflow-hidden" x-bind:class="sidebarCollapsed ? 'w-8 justify-center' : ''">
                        <span class="grid size-8 place-content-center rounded-lg bg-[#1A7FA8] text-sm font-black text-white">FG</span>
                        <span class="text-lg font-black tracking-tight text-white" x-show="!sidebarCollapsed" x-cloak>
                            Fresh<span class="text-[#7DD4F0]">Go</span><span class="ml-1 text-[10px] font-normal uppercase tracking-[0.22em] text-white/40">Admin</span>
                        </span>
                    </a>
                </div>

                <nav class="flex-1 space-y-1 overflow-y-auto px-2 py-4">
                    <button
                        type="button"
                        class="mb-3 flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-white/65 transition hover:bg-white/10 hover:text-white"
                        :class="sidebarCollapsed ? 'justify-center' : ''"
                        @click="sidebarCollapsed = !sidebarCollapsed"
                    >
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" />
                        </svg>
                        <span x-show="!sidebarCollapsed" x-cloak>Ciutkan Menu</span>
                    </button>

                    @foreach ($menuItems as $item)
                        <a
                            href="{{ route($item['route']) }}"
                            class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition"
                            :class="sidebarCollapsed ? 'justify-center' : ''"
                            @class([
                                'bg-[#1A7FA8]/20 text-[#7DD4F0]' => request()->routeIs($item['route']),
                                'text-white/65 hover:bg-white/10 hover:text-white' => ! request()->routeIs($item['route']),
                            ])
                        >
                            <span class="grid size-5 place-content-center rounded-lg bg-white/5 text-[11px] font-bold text-white/80">{{ str($item['label'])->substr(0, 1) }}</span>
                            <span class="whitespace-nowrap" x-show="!sidebarCollapsed" x-cloak>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </nav>

                <div class="border-t border-white/10 p-3" x-show="!sidebarCollapsed" x-cloak>
                    <div class="flex items-center gap-3 rounded-xl px-2 py-2 transition hover:bg-white/10">
                        <span class="grid size-8 place-content-center rounded-full bg-linear-to-br from-[#1A7FA8] to-[#5AA66E] text-xs font-bold text-white">{{ auth()->user()?->initials() }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-white">{{ auth()->user()?->name }}</p>
                            <p class="truncate text-xs text-white/40">Admin</p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-xs font-semibold text-white/60 transition hover:text-white">Logout</button>
                        </form>
                    </div>
                </div>
            </aside>

            <div class="transition-all duration-300" :class="sidebarCollapsed ? 'lg:ml-21.5' : 'lg:ml-65'">
                <header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-slate-200 bg-white px-4 sm:px-6">
                    <div class="flex items-center gap-3">
                        <button type="button" class="grid size-9 place-content-center rounded-lg border border-slate-200 text-slate-600 lg:hidden" @click="sidebarOpen = true">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" />
                            </svg>
                        </button>

                        <div>
                            <h1 class="text-lg font-bold text-slate-900">{{ $title ?? 'Admin' }}</h1>
                            <p class="text-xs text-slate-400">Selamat datang kembali, {{ auth()->user()?->name }}!</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 sm:gap-3">
                        <div class="relative hidden md:block">
                            <input type="text" placeholder="Cari..." class="w-52 rounded-lg border border-slate-200 bg-slate-50 py-2 pl-9 pr-4 text-sm text-slate-700 placeholder:text-slate-400 focus:border-[#1A7FA8] focus:outline-none" />
                            <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <circle cx="11" cy="11" r="7" />
                                <path d="m20 20-3.5-3.5" stroke-linecap="round" />
                            </svg>
                        </div>

                        <button type="button" class="relative grid size-9 place-content-center rounded-lg text-slate-600 transition hover:bg-slate-100">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M9.5 19a2.5 2.5 0 0 0 5 0" stroke-linecap="round" />
                            </svg>
                            <span class="absolute right-2 top-2 size-2 rounded-full bg-red-500"></span>
                        </button>

                        <div class="hidden items-center gap-2 rounded-lg bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-600 lg:flex">
                            <svg class="size-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <rect x="3" y="4" width="18" height="18" rx="2" />
                                <path d="M3 10h18" />
                            </svg>
                            {{ now()->translatedFormat('d M Y') }}
                        </div>
                    </div>
                </header>

                <main class="p-4 sm:p-6">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @livewireScripts
    </body>
</html>
