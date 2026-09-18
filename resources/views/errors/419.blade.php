<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">

    <title>Sesi Kadaluarsa | AbodaFarm</title>

    <link rel="icon" href="/icons/IconTitle.png" type="image/png">

    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=outfit:400,500,600,700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'system-ui', 'sans-serif'],
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
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-emerald-50 via-white to-green-50">

    <main class="min-h-screen flex items-center justify-center px-4">
        <div class="text-center max-w-md mx-auto">

            <div class="w-24 h-24 mx-auto mb-6 bg-gradient-to-br from-amber-100 to-amber-200 rounded-full flex items-center justify-center">
                <i class="fa-solid fa-clock-rotate-left text-4xl text-amber-500"></i>
            </div>

            <h1 class="text-7xl font-black text-emerald-600 tracking-tight mb-4">419</h1>

            <h2 class="text-2xl font-bold text-slate-800 mb-3">
                Sesi Anda Sudah Kadaluarsa
            </h2>
            <p class="text-slate-600 mb-8">
                Sepertinya sesi Anda sudah tidak valid. Ini bisa terjadi karena terlalu lama tidak aktif atau sesi login sudah berakhir.
            </p>

            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ url()->previous() }}"
                   class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-6 py-3 font-semibold text-white transition hover:bg-emerald-700">
                    <i class="fa-solid fa-arrow-left"></i>
                    Kembali
                </a>
                <a href="{{ route('login') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-6 py-3 font-semibold text-slate-700 transition hover:bg-slate-50">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    Login Ulang
                </a>
            </div>

        </div>
    </main>

</body>
</html>
