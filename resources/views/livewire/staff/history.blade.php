<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">
                Riwayat
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Lihat riwayat pesanan dan aktivitas Anda
            </p>
        </div>
    </div>

    {{-- Placeholder Content --}}
    <div class="rounded-2xl border border-slate-100 bg-white p-12 shadow-xs">
        <div class="flex flex-col items-center justify-center text-center">
            <div class="rounded-full bg-amber-50 p-6 text-amber-300 mb-4">
                <i class="fa-solid fa-clock-rotate-left text-5xl"></i>
            </div>
            <h2 class="text-xl font-bold text-slate-700 mb-2">Riwayat Aktivitas</h2>
            <p class="text-sm text-slate-500 max-w-md mb-6">
                Riwayat pesanan dan aktivitas Anda akan tersedia di sini. Fitur ini akan dikembangkan di fase selanjutnya.
            </p>
            <div class="flex flex-wrap items-center justify-center gap-3">
                <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-4 py-2 text-sm text-slate-600">
                    <i class="fa-solid fa-list-ol text-slate-400"></i>
                    Riwayat Pesanan
                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700">Segera</span>
                </span>
                <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-4 py-2 text-sm text-slate-600">
                    <i class="fa-solid fa-chart-bar text-slate-400"></i>
                    Log Aktivitas
                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700">Segera</span>
                </span>
            </div>
        </div>
    </div>

    {{-- Coming Soon Feature Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/50 p-6 text-center">
            <div class="mx-auto mb-3 flex size-12 items-center justify-center rounded-full bg-amber-100 text-amber-500">
                <i class="fa-solid fa-check-circle text-xl"></i>
            </div>
            <h3 class="font-semibold text-slate-700">Pesanan Selesai</h3>
            <p class="mt-1 text-xs text-slate-400">Riwayat pesanan yang telah selesai</p>
        </div>
        <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/50 p-6 text-center">
            <div class="mx-auto mb-3 flex size-12 items-center justify-center rounded-full bg-red-100 text-red-500">
                <i class="fa-solid fa-xmark-circle text-xl"></i>
            </div>
            <h3 class="font-semibold text-slate-700">Pesanan Dibatalkan</h3>
            <p class="mt-1 text-xs text-slate-400">Riwayat pesanan yang dibatalkan</p>
        </div>
        <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/50 p-6 text-center">
            <div class="mx-auto mb-3 flex size-12 items-center justify-center rounded-full bg-blue-100 text-blue-500">
                <i class="fa-solid fa-chart-line text-xl"></i>
            </div>
            <h3 class="font-semibold text-slate-700">Statistik Pribadi</h3>
            <p class="mt-1 text-xs text-slate-400">Statistik performa Anda sebagai staff</p>
        </div>
        <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/50 p-6 text-center">
            <div class="mx-auto mb-3 flex size-12 items-center justify-center rounded-full bg-purple-100 text-purple-500">
                <i class="fa-solid fa-file-export text-xl"></i>
            </div>
            <h3 class="font-semibold text-slate-700">Export Laporan</h3>
            <p class="mt-1 text-xs text-slate-400">Export laporan aktivitas dalam format Excel</p>
        </div>
    </div>
</div>
