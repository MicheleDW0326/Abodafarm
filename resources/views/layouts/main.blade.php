<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Search Engine Verification --}}
    <x-webmaster-verification />

    {{-- SEO Meta Tags with Structured Data (Dashboard - noindex, nofollow) --}}
    <x-seo.meta
        :title="isset($seoTitle) ? $seoTitle : ($title ?? null)"
        :description="isset($seoDescription) ? $seoDescription : null"
        :keywords="isset($seoKeywords) ? $seoKeywords : null"
        :image="isset($seoImage) ? $seoImage : null"
        :url="isset($seoCanonical) ? $seoCanonical : null"
        :type="isset($seoType) ? $seoType : 'website'"
        :author="isset($seoAuthor) ? $seoAuthor : null"
        robots="noindex, nofollow"
    />

    {{-- Favicon --}}
    <link rel="icon" href="/icons/IconTitle.png" type="image/png">
    <link rel="apple-touch-icon" href="/icons/IconTitle.png">

    {{-- DNS Prefetch for faster connections --}}
    <link rel="dns-prefetch" href="https://fonts.bunny.net">
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <link rel="dns-prefetch" href="https://maps.googleapis.com">

    {{-- Preconnect to critical third-party domains --}}
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preconnect" href="https://maps.googleapis.com" crossorigin>

    {{-- Font Awesome Icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    {{-- Preload critical font with display=swap for FOIT prevention --}}
    <link
        rel="preload"
        href="https://fonts.bunny.net/css?family=outfit:400,500,600,700&display=swap"
        as="style"
        onload="this.onload=null;this.rel='stylesheet'"
    >
    <noscript>
        <link rel="stylesheet" href="https://fonts.bunny.net/css?family=outfit:400,500,600,700&display=swap">
    </noscript>

    {{-- Preload logo for faster LCP --}}
    <link rel="preload" href="/icons/MainLogo.png" as="image" type="image/png">

    {{-- Critical inline CSS for dashboard skeleton --}}
    <style>
        /* Dashboard skeleton - prevents CLS */
        .skeleton {
            background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
            background-size: 200% 100%;
            animation: skeleton-loading 1.5s infinite;
        }
        @keyframes skeleton-loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        /* Reserve space for sidebar */
        .sidebar-placeholder {
            min-height: 100vh;
            width: 260px;
        }
        /* Reserve space for header */
        .header-placeholder {
            height: 64px;
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
        }
    </style>

    {{-- Pass current route name to JavaScript for sidebar active state detection --}}
    <script>
        window.currentRouteName = '{{ Route::currentRouteName() ?? '' }}';
        window.currentRoutePrefix = '{{ Route::currentRouteName() ? implode('.', array_slice(explode('.', Route::currentRouteName()), 0, -1)) : '' }}';
    </script>

    {{-- Vite Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Google Maps - lazy loaded --}}
    @if (config('googlemaps.api_key'))
        <script>
            window.loadGoogleMaps = function() {
                if (!window.googleMapsLoaded) {
                    const script = document.createElement('script');
                    script.src = 'https://maps.googleapis.com/maps/api/js?key={{ config('googlemaps.api_key') }}&libraries=places&loading=async';
                    script.async = true;
                    script.defer = true;
                    document.head.appendChild(script);
                    window.googleMapsLoaded = true;
                }
            };
        </script>
    @endif

    @livewireStyles

    {{-- Analytics & Tracking Scripts --}}
    <x-analytics />
</head>

{{-- Add skeleton placeholder to prevent CLS --}}
<body class="bg-[#F5F7FA] font-['Outfit'] text-slate-900 antialiased" x-data="{
    sidebarOpen: false,
    sidebarCollapsed: false,
    contentReady: false,
    accountMenuOpen: false,
    collapsedFlyout: null,

    init() {
        const saved = localStorage.getItem('abodafarm.sidebarCollapsed');
        this.sidebarCollapsed = saved === '1';

        requestAnimationFrame(() => {
            this.contentReady = true;
        });
    },
    toggleSidebar() {
        this.sidebarCollapsed = !this.sidebarCollapsed;
        this.collapsedFlyout = null;
        localStorage.setItem('abodafarm.sidebarCollapsed', this.sidebarCollapsed ? '1' : '0');
    },

    openFlyout(index, event) {
        event.stopPropagation();
        this.collapsedFlyout = this.collapsedFlyout === index ? null : index;
    },

    closeFlyout() {
        this.collapsedFlyout = null;
    },

    isRouteActive(routeName) {
        if (!routeName || !window.currentRouteName) return false;
        return window.currentRouteName === routeName || window.currentRouteName.startsWith(routeName + '.');
    },

    isAnyRouteActive(routeNames) {
        if (!routeNames || !window.currentRouteName) return false;
        return routeNames.some(name => window.currentRouteName === name || window.currentRouteName.startsWith(name + '.'));
    }
}"
    @click.outside="accountMenuOpen = false; collapsedFlyout = null">

    {{-- Google Tag Manager (noscript) --}}
    <x-analytics-body-start />

    <!-- Splash Screen -->
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
                            @php
                                $roleTitle = $title ?? null;
                                if (!$roleTitle && auth()->check()) {
                                    $user = auth()->user();
                                    if ($user->hasRole('admin')) {
                                        $roleTitle = 'Admin';
                                    } elseif ($user->hasRole('staf')) {
                                        $roleTitle = 'Staff';
                                    } elseif ($user->hasRole('peternak')) {
                                        $roleTitle = 'Peternak';
                                    } else {
                                        $roleTitle = 'Dashboard';
                                    }
                                }
                                if (!$roleTitle) {
                                    $roleTitle = 'Dashboard';
                                }
                            @endphp
                            {{ $roleTitle }}
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

                    {{-- Notification dropdown --}}
                    @livewire('notification-dropdown')

                    {{-- FCM Token Manager --}}
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

    {{-- Global Staff Delivery Modals - lazy loaded --}}
    @auth
        @if(auth()->user()->hasRole('staf'))
            @livewire(\App\Livewire\Staff\StartDelivery::class)
            @livewire(\App\Livewire\Staff\CompleteDelivery::class)
        @endif
        @if(auth()->user()->hasRole('pelanggan'))
            @livewire(\App\Livewire\Pelanggan\ConfirmDelivery::class)
        @endif
    @endauth

    @livewireScripts
    @include('sweetalert::alert')
    @include('components.alerts.sweet-alert')

    {{-- Defer non-critical initialization --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.addEventListener('refresh-notifications', () => {
                if (window.livewire) {
                    window.livewire.dispatch('refresh-notifications');
                }
            });
        });

        // Global Livewire error handler
        window.addEventListener('livewire:init', () => {
            Livewire.hook('message.failed', (message, retry) => {
                console.error('Livewire message failed:', message);
                // Show user-friendly error
                if (window.Swal) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: 'Terjadi kesalahan koneksi. Silakan refresh halaman.',
                        showConfirmButton: false,
                        timer: 5000
                    });
                }
                // Retry the request
                retry();
            });
        });
    </script>
    @stack('scripts')
</body>

</html>
