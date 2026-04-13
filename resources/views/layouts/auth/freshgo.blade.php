@props([
    'title' => config('app.name', 'FreshGo'),
    'eyebrow' => 'FreshGo Family',
    'headline' => 'Belanja segar lebih cepat, aman, dan praktis.',
    'description' => 'Masuk atau daftar untuk menikmati pilihan unggas dan seafood segar dengan pengalaman yang rapi, modern, dan mudah dipakai.',
    'image' => 'https://images.unsplash.com/photo-1547592180-85f173990554?auto=format&fit=crop&w=1200&q=80',
    'badges' => [],
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-slate-950 antialiased">
        <div class="grid min-h-screen lg:grid-cols-2">
            <aside class="relative hidden overflow-hidden bg-slate-950 lg:flex lg:flex-col lg:justify-between">
                <img src="{{ $image }}" alt="FreshGo background" class="absolute inset-0 h-full w-full object-cover object-center opacity-70">
                <div class="absolute inset-0 bg-linear-to-br from-slate-950/90 via-sky-900/65 to-emerald-900/65"></div>

                <div class="relative z-10 flex items-center justify-between px-10 pt-10">
                    <a href="{{ route('home') }}" class="flex items-center gap-3">
                        <span class="grid size-11 place-content-center rounded-2xl border border-white/20 bg-white/10 text-lg font-bold text-white backdrop-blur">FG</span>
                        <div>
                            <p class="text-xl font-black tracking-tight text-white">Fresh<span class="text-sky-200">Go</span></p>
                            <p class="text-xs uppercase tracking-[0.24em] text-white/50">Fresh market daily</p>
                        </div>
                    </a>
                </div>

                <div class="relative z-10 flex h-full flex-col justify-end px-10 pb-12 pt-16 text-white">
                    <div class="mb-6 h-1 w-14 rounded-full bg-emerald-400"></div>
                    <p class="text-sm font-semibold uppercase tracking-[0.28em] text-sky-200">{{ $eyebrow }}</p>
                    <h1 class="mt-4 max-w-xl text-5xl font-black leading-tight text-white">
                        {{ $headline }}
                    </h1>
                    <p class="mt-5 max-w-lg text-sm leading-7 text-white/70">
                        {{ $description }}
                    </p>

                    <div class="mt-8 grid max-w-lg gap-4 sm:grid-cols-3">
                        <div class="rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur">
                            <p class="text-sm font-semibold text-white">100% Halal</p>
                            <p class="mt-1 text-xs leading-5 text-white/60">Proses bersih dan terjaga</p>
                        </div>
                        <div class="rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur">
                            <p class="text-sm font-semibold text-white">Pengiriman Harian</p>
                            <p class="mt-1 text-xs leading-5 text-white/60">Cepat dan terjadwal</p>
                        </div>
                        <div class="rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur">
                            <p class="text-sm font-semibold text-white">Stok Segar</p>
                            <p class="mt-1 text-xs leading-5 text-white/60">Diproses setiap hari</p>
                        </div>
                    </div>
                </div>
            </aside>

            <main class="flex items-center justify-center bg-white px-6 py-10 sm:px-10 lg:px-8">
                <div class="w-full max-w-md">
                    <div class="mb-8 flex items-center gap-3 lg:hidden">
                        <a href="{{ route('home') }}" class="flex items-center gap-3">
                            <span class="grid size-10 place-content-center rounded-2xl bg-linear-to-br from-teal-500 to-sky-500 text-lg font-bold text-white">FG</span>
                            <span>
                                <span class="block text-xl font-black tracking-tight text-slate-900">Fresh<span class="text-sky-600">Go</span></span>
                                <span class="block text-xs uppercase tracking-[0.24em] text-slate-400">Fresh market daily</span>
                            </span>
                        </a>
                    </div>

                    {{ $slot }}
                </div>
            </main>
        </div>

        @livewireScripts
    </body>
</html>
