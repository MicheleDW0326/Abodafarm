{{--
    Custom 500 Error Page - SEO Friendly
--}}

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">

    <title>{{ $title ?? 'Terjadi Kesalahan Server' }} | AbodaFarm</title>
    <meta name="description" content="{{ $message ?? 'Maaf, terjadi kesalahan di server kami. Tim kami sedang bekerja untuk memperbaikinya.' }}">
    <link rel="canonical" href="{{ url()->current() }}">

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
                        fontFamily: { sans: ['Outfit', 'system-ui', 'sans-serif'] }
                    }
                }
            }
        }
    </script>

    {{-- Fallback inline CSS for when CDN fails --}}
    <noscript>
        <style>
            .min-h-screen { min-height: 100vh; }
            .flex { display: flex; }
            .flex-col { flex-direction: column; }
            .items-center { align-items: center; }
            .justify-center { justify-content: center; }
            .text-center { text-align: center; }
            .text-red-600 { color: #dc2626; }
            .text-slate-600 { color: #475569; }
            .font-bold { font-weight: 700; }
            .text-6xl { font-size: 3.75rem; }
            body { font-family: 'Outfit', system-ui, sans-serif; margin: 0; }
        </style>
    </noscript>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        body { font-family: 'Outfit', system-ui, sans-serif; }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        .float-animation { animation: float 3s ease-in-out infinite; }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-red-50 via-white to-orange-50">

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
                        <div class="w-32 h-32 mx-auto bg-gradient-to-br from-red-100 to-orange-100 rounded-full flex items-center justify-center">
                            <i class="fa-solid fa-triangle-exclamation text-6xl text-red-400"></i>
                        </div>
                    </div>
                </div>

                {{-- Error Code --}}
                <h1 class="text-9xl font-black text-red-600 tracking-tight mb-4">{{ $code ?? '500' }}</h1>

                {{-- Message --}}
                <h2 class="text-2xl sm:text-3xl font-bold text-slate-800 mb-4">
                    {{ $heading ?? 'Oops! Ada Masalah di Kandang' }}
                </h2>
                <p class="text-slate-600 mb-8 max-w-md mx-auto">
                    {{ $message ?? 'Maaf, terjadi kesalahan di server kami. Tim kami sedang bekerja untuk memperbaikinya.' }}
                </p>

                {{-- Actions --}}
                <div class="flex flex-wrap justify-center gap-3">
                    <a href="/"
                       class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-6 py-3 font-semibold text-white transition hover:bg-emerald-700">
                        <i class="fa-solid fa-home"></i>
                        Kembali ke Beranda
                    </a>
                    <a href="/kontak"
                       class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-6 py-3 font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-700">
                        <i class="fa-solid fa-phone"></i>
                        Hubungi Kami
                    </a>
                </div>
            </div>
        </section>

        {{-- Footer --}}
        <footer class="bg-slate-900 text-white py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <p class="text-slate-400">
                    &copy; {{ date('Y') }} AbodaFarm. Pasar Ternak Organik Terpercaya.
                </p>
            </div>
        </footer>
    </main>
</body>
</html>
