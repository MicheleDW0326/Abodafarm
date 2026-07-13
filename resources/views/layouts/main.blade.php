<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ filled($title ?? null) ? $title . ' - ' . config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
    </title>

    <link rel="icon" href="/icons/IconTitle.png" type="image/png">
    <link rel="apple-touch-icon" href="/icons/IconTitle.png">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=outfit:400,500,600,700" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    @if (config('googlemaps.api_key'))
        <script
            src="https://maps.googleapis.com/maps/api/js?key={{ config('googlemaps.api_key') }}&libraries=places&loading=async"
            async defer></script>
    @endif
</head>

<body class="bg-[#F5F7FA] font-['Outfit'] text-slate-900 antialiased" x-data="{
    sidebarOpen: false,
    sidebarCollapsed: false,
    contentReady: false,
    accountMenuOpen: false,
    activeRoute: '',
    init() {
        const saved = localStorage.getItem('abodafarm.sidebarCollapsed');
        this.sidebarCollapsed = saved === '1';

        // Get initial route
        this.activeRoute = window.location.pathname;

        requestAnimationFrame(() => {
            this.contentReady = true;
        });

        // Update active route on navigation
        window.addEventListener('livewire:navigating', (event) => {
            if (event.detail?.url) {
                this.activeRoute = new URL(event.detail.url, window.location.origin).pathname;
            }
        });

        window.addEventListener('livewire:navigated', () => {
            this.activeRoute = window.location.pathname;
        });
    },
    toggleSidebar() {
        this.sidebarCollapsed = !this.sidebarCollapsed;
        localStorage.setItem('abodafarm.sidebarCollapsed', this.sidebarCollapsed ? '1' : '0');
    },
    isRouteActive(routePattern) {
        if (!this.activeRoute || !routePattern) return false;

        // Pattern comes as \/admin\/dashboard, convert \/ to / for regex
        const cleanPattern = routePattern.replace(/\\\//g, '\\/');
        const pathRegex = new RegExp('^' + cleanPattern, 'i');

        return pathRegex.test(this.activeRoute);
    }
}"
    @click.outside="accountMenuOpen = false">

    <!-- Splash Screen - untuk halaman penting -->
    @livewire('splash-screen')

    <div class="min-h-screen">
        @include('layouts.app.sidebar')

        <div class="transition-all duration-300 ease-out lg:ml-(--sidebar-width)"
            :style="sidebarCollapsed ? '--sidebar-width: 86px;' : '--sidebar-width: 260px;'"
            :class="contentReady ? 'translate-y-0 opacity-100' : 'translate-y-1 opacity-0'">
            <header
                class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-slate-200 bg-white px-4 sm:px-6">
                <div class="flex items-center gap-3">
                    <button type="button"
                        class="grid size-9 place-content-center rounded-lg border border-slate-200 text-slate-600 lg:hidden"
                        @click="sidebarOpen = true">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            aria-hidden="true">
                            <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" />
                        </svg>
                    </button>

                    <div>
                        <h1 class="text-lg font-bold text-slate-900">
                            {{ $title ?? (auth()->user()?->hasRole('peternak') ? 'Peternak' : (auth()->user()?->hasRole('admin') ? 'Admin' : 'Dashboard')) }}
                        </h1>
                        <p class="text-xs text-slate-400">Selamat datang kembali, {{ auth()->user()?->name }}!</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 sm:gap-3">
                    <div class="relative hidden md:block">
                        <input type="text" placeholder="Cari..."
                            class="w-52 rounded-lg border border-slate-200 bg-slate-50 py-2 pl-9 pr-4 text-sm text-slate-700 placeholder:text-slate-400 focus:border-[#1A7FA8] focus:outline-none" />
                        <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            aria-hidden="true">
                            <circle cx="11" cy="11" r="7" />
                            <path d="m20 20-3.5-3.5" stroke-linecap="round" />
                        </svg>
                    </div>

                    @livewire('notification-dropdown')

                    <!-- FCM Token Manager - Hidden component -->
                    @livewire('save-fcm-token')

                    <div
                        class="hidden items-center gap-2 rounded-lg bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-600 lg:flex">
                        <svg class="size-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" aria-hidden="true">
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
    @include('sweetalert::alert')
    @include('components.alerts.sweet-alert')

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.addEventListener('refresh-notifications', () => {
                if (window.livewire) {
                    window.livewire.dispatch('refresh-notifications');
                }
            });
        });
    </script>
    @stack('scripts')
</body>

</html>
