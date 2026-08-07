{{--
    Custom 404 Error Page - SEO Friendly

    Features:
    - Proper HTTP 404 status
    - SEO meta tags (noindex for error pages)
    - Friendly error message
    - Back to home button
    - Search products
    - Popular categories
    - Latest products
--}}

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">

    <title>Halaman Tidak Ditemukan | AbodaFarm</title>
    <meta name="description" content="Maaf, halaman yang Anda cari tidak ditemukan. Jelajahi produk peternakan organik berkualitas di AbodaFarm.">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Open Graph --}}
    <meta property="og:title" content="Halaman Tidak Ditemukan | AbodaFarm">
    <meta property="og:description" content="Maaf, halaman yang Anda cari tidak ditemukan. Jelajahi produk peternakan organik berkualitas.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="AbodaFarm">

    {{-- Favicon --}}
    <link rel="icon" href="/icons/IconTitle.png" type="image/png">

    {{-- Font --}}
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=outfit:400,500,600,700&display=swap" rel="stylesheet">

    {{-- Tailwind CSS (with fallback inline styles for offline) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        if (typeof tailwind !== 'undefined') {
            tailwind.config = {
                theme: {
                    extend: {
                        fontFamily: {
                            sans: ['Outfit', 'system-ui', 'sans-serif'],
                        },
                        colors: {
                            emerald: {
                                50: '#ecfdf5',
                            100: '#d1fae5',
                            200: '#a7f3d0',
                            300: '#6ee7b7',
                            400: '#34d399',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                        }
                    }
                }
            }
        }
    </script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        body {
            font-family: 'Outfit', system-ui, sans-serif;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .float-animation {
            animation: float 3s ease-in-out infinite;
        }

        @keyframes pulse-slow {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }

        .pulse-slow {
            animation: pulse-slow 2s ease-in-out infinite;
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-emerald-50 via-white to-green-50">

    {{-- Main Content --}}
    <main class="min-h-screen flex flex-col">

        {{-- Header --}}
        <header class="bg-white border-b border-slate-200 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center justify-between">
                    <a href="/" class="flex items-center gap-3">
                        <img src="/icons/MainLogo.png" alt="AbodaFarm" class="h-10">
                    </a>
                    <a href="/"
                       class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                        <i class="fa-solid fa-home"></i>
                        Kembali ke Beranda
                    </a>
                </div>
            </div>
        </header>

        {{-- Error Section --}}
        <section class="flex-1 flex items-center justify-center px-4 py-16">
            <div class="text-center max-w-2xl mx-auto">

                {{-- Animated Illustration --}}
                <div class="relative mb-8">
                    <div class="float-animation">
                        <div class="w-32 h-32 mx-auto bg-gradient-to-br from-emerald-100 to-green-100 rounded-full flex items-center justify-center">
                            <i class="fa-solid fa-cow text-6xl text-emerald-400"></i>
                        </div>
                    </div>
                    <div class="absolute -top-2 -right-2 w-12 h-12 bg-amber-400 rounded-full flex items-center justify-center shadow-lg">
                        <i class="fa-solid fa-question text-white text-xl"></i>
                    </div>
                </div>

                {{-- Error Code --}}
                <h1 class="text-9xl font-black text-emerald-600 tracking-tight mb-4">404</h1>

                {{-- Friendly Message --}}
                <h2 class="text-2xl sm:text-3xl font-bold text-slate-800 mb-4">
                    Oops! Ternak Ini Lari ke Lainnya
                </h2>
                <p class="text-slate-600 mb-8 max-w-md mx-auto">
                    Maaf, halaman yang Anda cari tidak ditemukan. Mungkin sudah dipindahkan atau tidak lagi tersedia.
                </p>

                {{-- Search Box --}}
                <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-6 mb-8">
                    <h3 class="text-lg font-semibold text-slate-800 mb-4">
                        <i class="fa-solid fa-magnifying-glass text-emerald-600 mr-2"></i>
                        Cari Produk
                    </h3>
                    <form action="/shop" method="GET" class="flex gap-3">
                        <div class="flex-1 relative">
                            <input type="text"
                                   name="q"
                                   placeholder="Cari sapi, kambing, pakan..."
                                   class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none transition"
                                   value="{{ request('q') }}">
                            <i class="fa-solid fa-search absolute right-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        </div>
                        <button type="submit"
                                class="px-6 py-3 bg-emerald-600 text-white font-semibold rounded-xl hover:bg-emerald-700 transition">
                            Cari
                        </button>
                    </form>
                </div>

                {{-- Quick Links --}}
                <div class="flex flex-wrap justify-center gap-3">
                    <a href="/shop"
                       class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 transition hover:border-emerald-300 hover:text-emerald-700 hover:shadow-sm">
                        <i class="fa-solid fa-shop"></i>
                        Toko
                    </a>
                    <a href="/tentang-kami"
                       class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 transition hover:border-emerald-300 hover:text-emerald-700 hover:shadow-sm">
                        <i class="fa-solid fa-info-circle"></i>
                        Tentang Kami
                    </a>
                    <a href="/kontak"
                       class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 transition hover:border-emerald-300 hover:text-emerald-700 hover:shadow-sm">
                        <i class="fa-solid fa-phone"></i>
                        Hubungi Kami
                    </a>
                </div>
            </div>
        </section>

        {{-- Popular Categories --}}
        @if(isset($categories) && $categories->isNotEmpty())
        <section class="bg-white py-12 border-t border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h3 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                    <i class="fa-solid fa-tags text-emerald-600"></i>
                    Kategori Populer
                </h3>
                <div class="flex flex-wrap gap-3">
                    @foreach($categories as $category)
                        <a href="/shop?category={{ $category->slug }}"
                           class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-emerald-100 hover:text-emerald-700">
                            @if($category->icon)
                                <i class="{{ $category->icon }}"></i>
                            @endif
                            {{ $category->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        {{-- Latest Products --}}
        @if(isset($products) && $products->isNotEmpty())
        <section class="bg-slate-50 py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h3 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                    <i class="fa-solid fa-clock text-emerald-600"></i>
                    Produk Terbaru
                </h3>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach($products as $product)
                        <a href="{{ route('shop.product', $product->slug) }}"
                           class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:shadow-lg">
                            <div class="aspect-square overflow-hidden bg-gradient-to-br from-slate-100 to-slate-200">
                                @if($product->image_url)
                                    <img src="{{ $product->image_url }}"
                                         alt="{{ $product->name }}"
                                         class="h-full w-full object-cover transition group-hover:scale-105">
                                @else
                                    <div class="flex h-full items-center justify-center">
                                        <i class="fa-solid fa-image text-4xl text-slate-300"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="p-4">
                                <p class="text-xs text-emerald-600">{{ $product->category?->name ?? 'Produk' }}</p>
                                <h4 class="mt-1 font-semibold text-slate-900 line-clamp-2 group-hover:text-emerald-600">
                                    {{ $product->name }}
                                </h4>
                                <p class="mt-2 font-bold text-emerald-700">{{ $product->formatted_current_price ?? 'Hubungi kami' }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
                <div class="mt-8 text-center">
                    <a href="/shop"
                       class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-6 py-3 font-semibold text-white transition hover:bg-emerald-700">
                        <i class="fa-solid fa-arrow-right"></i>
                        Lihat Semua Produk
                    </a>
                </div>
            </div>
        </section>
        @endif

        {{-- Footer --}}
        <footer class="bg-slate-900 text-white py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <p class="text-slate-400">
                    &copy; {{ date('Y') }} AbodaFarm. Pasar Ternak Organik Terpercaya.
                </p>
            </div>
        </footer>
    </main>

    {{-- HTTP Status (hidden but proper) --}}
    <script>
        // Ensure proper HTTP status is sent
        if (typeof window !== 'undefined') {
            document.addEventListener('DOMContentLoaded', function() {
                // The HTTP status is handled server-side
                // This script is for client-side behavior only
            });
        }
    </script>
</body>
</html>
