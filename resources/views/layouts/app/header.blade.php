<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-950 text-zinc-100" x-data="{ activeRoute: window.location.pathname }" x-init="window.addEventListener('livewire:navigated', () => activeRoute = window.location.pathname)">
        <div class="min-h-screen">
            <header class="sticky top-0 z-20 border-b border-white/10 bg-zinc-950/90 px-6 py-4 backdrop-blur">
                <div class="mx-auto flex max-w-7xl items-center justify-between gap-4">
                    <x-app-logo href="{{ route('dashboard') }}" wire:navigate />

                    <nav class="hidden items-center gap-2 md:flex">
                        <a href="{{ route('dashboard') }}" wire:navigate
                            class="rounded-full px-4 py-2 text-sm font-medium transition"
                            :class="activeRoute.includes('dashboard') || activeRoute === '{{ route('dashboard', [], false) }}' ? 'bg-white text-zinc-950' : 'text-zinc-300 hover:bg-white/5 hover:text-white'">
                            {{ __('Dashboard') }}
                        </a>
                    </nav>

                    <x-desktop-user-menu />
                </div>
            </header>

            <main class="mx-auto max-w-7xl p-6">
                {{ $slot }}
            </main>
        </div>
        @livewireScripts
    </body>
</html>
