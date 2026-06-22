@php
    $user = auth()->user();
    $roleLabel = $user?->getRoleNames()->first();

    // Hierarchical menu structure with submenus
    $menuItems = [];

    if ($user?->hasRole('admin')) {
        $menuItems = [
            ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'circle-gauge', 'submenu' => null],
            ['route' => 'admin.products', 'label' => 'Produk', 'icon' => 'tag', 'submenu' => null],
            ['route' => 'admin.orders', 'label' => 'Pesanan', 'icon' => 'shoping-bag', 'submenu' => null],
            ['route' => 'admin.customers', 'label' => 'Pelanggan', 'icon' => 'users-round', 'submenu' => null],
            ['route' => 'admin.finance', 'label' => 'Keuangan', 'icon' => 'circle-dolar-sign', 'submenu' => null],
            ['route' => 'admin.staff', 'label' => 'Pengguna', 'icon' => 'user-round-cog', 'submenu' => null],
            ['route' => 'admin.settings', 'label' => 'Pengaturan', 'icon' => 'landmark', 'submenu' => null],
        ];
    } elseif ($user?->hasRole('staf')) {
        $menuItems = [
            ['route' => 'staff.dashboard', 'label' => 'Dashboard', 'icon' => 'circle-gauge', 'submenu' => null],
        ];
    } elseif ($user?->hasRole('peternak')) {
        $menuItems = [
            ['route' => 'peternak.dashboard', 'label' => 'Dashboard', 'icon' => 'circle-gauge', 'submenu' => null],
            ['route' => 'peternak.livestock', 'label' => 'Ternak', 'icon' => 'farmer.sidebar.ternak', 'submenu' => null],
            ['route' => 'peternak.orders', 'label' => 'Pesanan', 'icon' => 'shoping-bag', 'submenu' => null],
            ['route' => 'peternak.shipments', 'label' => 'Pengiriman', 'icon' => 'farmer.sidebar.pengiriman', 'submenu' => null],
            ['route' => 'peternak.sales', 'label' => 'Penjualan', 'icon' => 'farmer.sidebar.penjualan', 'submenu' => null],
            ['route' => 'peternak.wallet', 'label' => 'Dompet', 'icon' => 'farmer.sidebar.dompet', 'submenu' => null],
            ['route' => 'peternak.profile', 'label' => 'Profil', 'icon' => 'farmer.sidebar.profil', 'submenu' => null],
            ['route' => 'peternak.notifications', 'label' => 'Notifikasi', 'icon' => 'bell', 'submenu' => null],
            ['route' => 'peternak.settings', 'label' => 'Pengaturan', 'icon' => 'farmer.sidebar.pengaturan', 'submenu' => null],
        ];
    } elseif ($user?->hasRole('pelanggan')) {
        $menuItems = [
            ['route' => 'pelanggan.dashboard', 'label' => 'Dashboard', 'icon' => 'circle-gauge', 'submenu' => null],
        ];
    }

    // Filter and process menu items
    $menuItems = collect($menuItems)->map(function ($item) {
        if (isset($item['submenu']) && is_array($item['submenu'])) {
            // Filter submenu routes that exist
            $item['submenu'] = collect($item['submenu'])
                ->filter(fn($sub) => Route::has($sub['route']))
                ->map(fn($sub) => [...$sub, 'url' => route($sub['route'])])
                ->values()
                ->all();
            // Remove if no valid submenus
            if (empty($item['submenu'])) {
                return null;
            }
        } else {
            // Single menu item
            if (!Route::has($item['route'])) {
                return null;
            }
            $item['url'] = route($item['route']);
        }
        return $item;
    })->filter()->values()->all();

    $homeRoute = url('/');

    if ($user?->hasRole('admin') && Route::has('admin.dashboard')) {
        $homeRoute = route('admin.dashboard');
    } elseif ($user?->hasRole('staf') && Route::has('staff.dashboard')) {
        $homeRoute = route('staff.dashboard');
    } elseif ($user?->hasRole('peternak') && Route::has('peternak.dashboard')) {
        $homeRoute = route('peternak.dashboard');
    } elseif ($user?->hasRole('pelanggan') && Route::has('pelanggan.dashboard')) {
        $homeRoute = route('pelanggan.dashboard');
    } elseif (Route::has('dashboard')) {
        $homeRoute = route('dashboard');
    }
@endphp

<div class="fixed inset-0 z-30 bg-slate-900/45 lg:hidden" x-show="sidebarOpen" x-cloak x-transition.opacity @click="sidebarOpen = false"></div>

<aside
    class="fixed inset-y-0 left-0 z-40 flex h-screen flex-col overflow-x-hidden bg-gradient-to-b from-slate-900 to-slate-800 transition-all duration-300 ease-out shadow-2xl shadow-slate-900/30"
    :style="sidebarCollapsed ? 'width: 86px; min-width: 86px;' : 'width: 260px; min-width: 260px;'"
    x-bind:class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
>
    <div class="flex h-16 items-center border-b border-white/10 px-4" :class="sidebarCollapsed ? 'justify-center' : 'justify-start'">
        <a href="{{ $homeRoute }}" class="flex w-full items-center" :class="sidebarCollapsed ? 'w-8 justify-center gap-0' : 'justify-start gap-2'">
            <span class="grid size-8 shrink-0 place-content-center rounded-lg bg-gradient-to-br from-emerald-500 to-emerald-600 text-sm font-black text-white shadow-lg shadow-emerald-500/30">AF</span>
            <span class="inline-flex text-lg font-black tracking-tight text-white whitespace-nowrap" x-show="!sidebarCollapsed">
                Aboda<span class="text-emerald-400">Farm</span>
            </span>
        </a>
    </div>

    <nav class="flex-1 overflow-x-hidden px-2 py-4 scrollbar-thin scrollbar-thumb-slate-700 scrollbar-track-transparent hover:scrollbar-thumb-slate-600">
        <button
            type="button"
            class="cursor-pointer mb-3 flex w-full items-center rounded-xl px-3 py-2.5 text-sm font-medium text-white/65 transition hover:bg-white/10 hover:text-white"
            :class="sidebarCollapsed ? 'justify-center gap-0' : 'justify-start gap-3'"
            @click="toggleSidebar()"
        >
            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" />
            </svg>
            <span class="inline-flex whitespace-nowrap" x-show="!sidebarCollapsed">Menu</span>
        </button>

        @if (count($menuItems) === 0)
            <p class="rounded-xl border border-white/10 px-3 py-2 text-xs text-white/55">Belum ada menu untuk role ini.</p>
        @endif

        {{-- Expanded Menu with Submenus --}}
        <div x-show="!sidebarCollapsed" x-cloak class="space-y-1">
            @foreach ($menuItems as $index => $item)
                @php($isActive = isset($item['route']) && (request()->routeIs($item['route']) || request()->routeIs($item['route'] . '.*')))
                @php($hasSubmenu = isset($item['submenu']) && is_array($item['submenu']) && count($item['submenu']) > 0)

                @if ($hasSubmenu)
                    {{-- Parent menu with submenu --}}
                    <div x-data="{ open: {{ $isActive ? 'true' : 'false' }} }" class="space-y-1">
                        <button
                            type="button"
                            class="sidebar-menu-item group relative flex w-full items-center justify-start gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-300 hover:bg-white/10 hover:translate-x-1"
                            :class="open ? 'bg-gradient-to-r from-emerald-500/40 to-emerald-600/20 text-emerald-300' : 'text-white/70 hover:text-white'"
                            @click="open = !open"
                        >
                            @if($isActive)
                            <span class="absolute left-0 top-1/2 -translate-y-1/2 h-8 w-1 rounded-full bg-emerald-400 shadow-lg shadow-emerald-400/50"></span>
                            @endif
                            <span class="grid size-5 shrink-0 place-content-center text-white/70 transition-transform duration-300" :class="open ? 'text-emerald-300' : 'group-hover:scale-110'">
                                @include('livewire.icons.' . str_replace('.', '/', $item['icon']))
                            </span>
                            <span class="flex-1 text-left">{{ $item['label'] }}</span>
                            <svg class="size-4 text-white/50 transition-transform duration-300" :class="open ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="m6 9 6 6 6-6" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>

                        <div x-show="open" x-collapse class="pl-4 space-y-0.5">
                            @foreach ($item['submenu'] as $subItem)
                                @php($subActive = request()->routeIs($subItem['route']) || request()->routeIs($subItem['route'] . '.*'))
                                <a
                                    href="{{ $subItem['url'] }}"
                                    class="sidebar-submenu-item group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200 hover:bg-white/10 hover:translate-x-1"
                                    @class([
                                        'bg-emerald-500/30 text-emerald-300 border-l-2 border-emerald-400' => $subActive,
                                        'text-white/60 hover:text-white border-l-2 border-transparent hover:border-emerald-400/50' => ! $subActive,
                                    ])
                                >
                                    <span class="grid size-4 shrink-0 place-content-center text-white/60 transition-transform duration-300 group-hover:scale-110 group-hover:text-white">
                                        @include('livewire.icons.' . str_replace('.', '/', $subItem['icon']))
                                    </span>
                                    <span>{{ $subItem['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @else
                    {{-- Single menu item --}}
                    <a
                        href="{{ $item['url'] }}"
                        class="sidebar-menu-item group relative flex items-center justify-start gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-300 hover:bg-white/10 hover:translate-x-1"
                        @class([
                            'bg-gradient-to-r from-emerald-500/40 to-emerald-600/20 text-emerald-300 shadow-lg shadow-emerald-500/20' => $isActive,
                            'text-white/70 hover:text-white' => ! $isActive,
                        ])
                    >
                        @if($isActive)
                        <span class="absolute left-0 top-1/2 -translate-y-1/2 h-8 w-1 rounded-full bg-emerald-400 shadow-lg shadow-emerald-400/50"></span>
                        @endif
                        <span class="grid size-5 shrink-0 place-content-center text-white/70 transition-transform duration-300 group-hover:scale-110 group-hover:text-white">
                            @include('livewire.icons.' . $item['icon'])
                        </span>
                        <span class="inline-flex whitespace-nowrap text-white/70">{{ $item['label'] }}</span>
                    </a>
                @endif
            @endforeach
        </div>

        {{-- Collapsed Menu - Icon Grid --}}
        <div x-show="sidebarCollapsed" x-cloak class="space-y-1">
            @foreach ($menuItems as $index => $item)
                @php($isActive = isset($item['route']) && (request()->routeIs($item['route']) || request()->routeIs($item['route'] . '.*')))
                @php($hasSubmenu = isset($item['submenu']) && is_array($item['submenu']) && count($item['submenu']) > 0)

                @if ($hasSubmenu)
                    <div x-data="{ open: false }" class="relative">
                        <button
                            type="button"
                            class="sidebar-menu-item group relative flex items-center justify-center rounded-xl w-10 h-10 mx-auto transition-all duration-300 hover:bg-white/10"
                            :class="open ? 'bg-emerald-500/15 text-emerald-400 ring-1 ring-emerald-400/50' : 'text-white/70 hover:text-white'"
                            @click="open = !open"
                        >
                            <span class="grid size-5 place-content-center text-white/70 transition-transform duration-300 group-hover:scale-110 group-hover:text-white">
                                @include('livewire.icons.' . str_replace('.', '/', $item['icon']))
                            </span>

                            {{-- Tooltip --}}
                            <span class="pointer-events-none absolute left-full top-1/2 z-50 ml-2 -translate-y-1/2 translate-x-2 rounded-lg bg-slate-900 px-2.5 py-1.5 text-xs font-semibold whitespace-nowrap text-white opacity-0 shadow-lg ring-1 ring-white/10 transition-all duration-300 group-hover:translate-x-0 group-hover:opacity-100">
                                {{ $item['label'] }}
                            </span>
                        </button>

                        {{-- Submenu dropdown --}}
                        <div
                            x-show="open"
                            x-cloak
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-2"
                            @click.away="open = false"
                            class="absolute left-full top-0 ml-2 min-w-[160px] rounded-xl bg-slate-800 border border-white/10 shadow-xl py-1.5 z-50"
                        >
                            <div class="px-3 py-1.5 text-xs font-semibold text-white/40 uppercase tracking-wider">{{ $item['label'] }}</div>
                            @foreach ($item['submenu'] as $subItem)
                                @php($subActive = request()->routeIs($subItem['route']) || request()->routeIs($subItem['route'] . '.*'))
                                <a
                                    href="{{ $subItem['url'] }}"
                                    class="flex items-center gap-2.5 px-3 py-2 text-sm transition-colors @if($subActive) bg-emerald-500/30 text-emerald-300 @else text-white/70 hover:bg-white/10 hover:text-white @endif"
                                >
                                    <span class="grid size-4 place-content-center">
                                        @include('livewire.icons.' . str_replace('.', '/', $subItem['icon']))
                                    </span>
                                    {{ $subItem['label'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @else
                    <a
                        href="{{ $item['url'] }}"
                        class="sidebar-menu-item group relative flex items-center justify-center rounded-xl w-10 h-10 mx-auto transition-all duration-300 hover:bg-white/10"
                        @class([
                            'bg-emerald-500/15 text-emerald-400 ring-1 ring-emerald-400/50' => $isActive,
                            'text-white/70 hover:text-white' => ! $isActive,
                        ])
                    >
                        <span class="grid size-5 place-content-center text-white/70 transition-transform duration-300 group-hover:scale-110 group-hover:text-white">
                            @include('livewire.icons.' . $item['icon'])
                        </span>

                        <span class="pointer-events-none absolute left-full top-1/2 z-50 ml-2 -translate-y-1/2 translate-x-2 rounded-lg bg-slate-900 px-2.5 py-1.5 text-xs font-semibold whitespace-nowrap text-white opacity-0 shadow-lg ring-1 ring-white/10 transition-all duration-300 group-hover:translate-x-0 group-hover:opacity-100">
                            {{ $item['label'] }}
                        </span>
                    </a>
                @endif
            @endforeach
        </div>
    </nav>

    <div class="border-t border-white/10 p-3" x-show="!sidebarCollapsed" x-cloak>
        <div class="relative">
            <button type="button" class="w-full flex items-center gap-3 rounded-xl px-2 py-2 transition hover:bg-white/10" @click="accountMenuOpen = !accountMenuOpen">
                <span class="grid size-8 place-content-center rounded-full bg-gradient-to-br from-emerald-500 to-emerald-600 text-xs font-bold text-white shadow-lg shadow-emerald-500/20">{{ auth()->user()?->initials() }}</span>
                <div class="min-w-0 flex-1 text-left">
                    <p class="truncate text-sm font-semibold text-white">{{ auth()->user()?->name }}</p>
                    <p class="truncate text-xs uppercase text-white/40">{{ $roleLabel ?? 'Pengguna' }}</p>
                </div>
                <svg class="size-4 text-white/60 transition" :class="accountMenuOpen ? 'rotate-180' : ''"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="m6 9 6 6 6-6" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>

            <div x-show="accountMenuOpen" x-transition class="absolute bottom-full left-0 right-0 mb-2 rounded-lg border border-white/10 bg-slate-900 shadow-xl overflow-hidden z-50">
                <a href="#" class="block w-full px-4 py-3 text-sm font-medium text-white/80 transition hover:bg-white/10 hover:text-white flex items-center gap-2 group">
                    <svg class="size-4 text-white/60 group-hover:text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 3a4 4 0 1 0 0 8 4 4 0 0 0 0-8z" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    Pengaturan Akun
                </a>
                <form method="POST" action="{{ route('logout') }}" class="block">
                    @csrf
                    <button type="submit" class="w-full px-4 py-3 text-sm font-medium text-white/80 transition hover:bg-white/10 hover:text-white flex items-center gap-2 group">
                        <svg class="size-4 text-white/60 group-hover:text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4m7 14l5-5m0 0l-5-5m5 5H9" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="border-t border-white/10 p-3" x-show="sidebarCollapsed" x-cloak>
        <div class="relative">
            <div x-show="accountMenuOpen" x-transition class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 flex flex-col gap-1 z-50">
                <a href="#" class="account-menu-item group flex items-center justify-center size-10 rounded-lg bg-white/10 text-white/60 transition hover:bg-emerald-500 hover:text-white hover:shadow-lg hover:shadow-emerald-500/50" style="animation-delay: 0ms">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 3a4 4 0 1 0 0 8 4 4 0 0 0 0-8z" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </a>
                <form method="POST" action="{{ route('logout') }}" class="block">
                    @csrf
                    <button type="submit" class="account-menu-item group flex items-center justify-center size-10 rounded-lg bg-white/10 text-white/60 transition hover:bg-red-500/20 hover:text-red-400 hover:shadow-lg hover:shadow-red-500/30" style="animation-delay: 100ms">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4m7 14l5-5m0 0l-5-5m5 5H9" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                </form>
            </div>

            <button type="button" class="w-full flex items-center justify-center" @click="accountMenuOpen = !accountMenuOpen">
                <span class="grid size-10 place-content-center rounded-full bg-gradient-to-br from-emerald-500 to-emerald-600 text-sm font-bold text-white transition hover:shadow-lg hover:shadow-emerald-500/50">
                    {{ auth()->user()?->initials() }}
                </span>
            </button>
        </div>
    </div>
</aside>
