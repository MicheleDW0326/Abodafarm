<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-950 text-zinc-100">
        <div class="min-h-screen lg:grid lg:grid-cols-[280px_1fr]">
            <aside class="border-r border-white/10 bg-zinc-900/95 px-5 py-6 backdrop-blur lg:sticky lg:top-0 lg:h-screen lg:overflow-y-auto">
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />

                <nav class="mt-8 space-y-1 text-sm font-medium">
                    <a href="{{ route('dashboard') }}" wire:navigate class="block rounded-2xl px-4 py-3 transition {{ request()->routeIs('dashboard') ? 'bg-sky-500/15 text-sky-200 ring-1 ring-inset ring-sky-400/30' : 'text-zinc-300 hover:bg-white/5 hover:text-white' }}">
                        {{ __('Dashboard') }}
                    </a>
                </nav>

                <div class="mt-8 rounded-3xl border border-white/10 bg-white/5 p-4 text-sm text-zinc-300">
                    <p class="font-semibold text-white">FreshGo Workspace</p>
                    <p class="mt-2 leading-6 text-zinc-400">Kelola konten, pesanan, dan pengaturan dari panel yang ringkas.</p>
                </div>

                <div class="mt-6 hidden lg:block">
                    <x-desktop-user-menu class="w-full" />
                </div>
            </aside>

            <div class="flex min-h-screen flex-col bg-zinc-950">
                <header class="border-b border-white/10 bg-zinc-950/80 px-5 py-4 backdrop-blur lg:hidden">
                    <div class="flex items-center justify-between gap-4">
                        <x-app-logo href="{{ route('dashboard') }}" wire:navigate />
                        <x-desktop-user-menu />
                    </div>
                </header>

                <main class="flex-1 p-5 sm:p-6 lg:p-8">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @livewireScripts
    </body>
</html>
