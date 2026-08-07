<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">
                Pengiriman
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Kelola dan lacak pengiriman pesanan
            </p>
        </div>
    </div>

    {{-- Placeholder Content --}}
    <div class="rounded-2xl border border-slate-100 bg-white p-12 shadow-xs">
        <div class="flex flex-col items-center justify-center text-center">
            <div class="rounded-full bg-violet-50 p-6 text-violet-300 mb-4">
                <i class="fa-solid fa-truck-fast text-5xl"></i>
            </div>
            <h2 class="text-xl font-bold text-slate-700 mb-2">Modul Pengiriman</h2>
            <p class="text-sm text-slate-500 max-w-md mb-6">
                Fitur pengelolaan pengiriman akan segera hadir. Di fase ini, Anda dapat melihat ringkasan operasional di dashboard.
            </p>
            <div class="flex flex-wrap items-center justify-center gap-3">
                <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-4 py-2 text-sm text-slate-600">
                    <i class="fa-solid fa-box text-slate-400"></i>
                    Lacak Pesanan
                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700">Segera</span>
                </span>
                <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-4 py-2 text-sm text-slate-600">
                    <i class="fa-solid fa-map-location-dot text-slate-400"></i>
                    Update Lokasi
                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700">Segera</span>
                </span>
                <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-4 py-2 text-sm text-slate-600">
                    <i class="fa-solid fa-camera text-slate-400"></i>
                    Bukti Kirim
                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700">Segera</span>
                </span>
            </div>
        </div>
    </div>

    {{-- Coming Soon Feature Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/50 p-6 text-center">
            <div class="mx-auto mb-3 flex size-12 items-center justify-center rounded-full bg-violet-100 text-violet-500">
                <i class="fa-solid fa-truck-moving text-xl"></i>
            </div>
            <h3 class="font-semibold text-slate-700">Pelacakan Real-time</h3>
            <p class="mt-1 text-xs text-slate-400">Lacak lokasi pengiriman secara real-time</p>
        </div>
        <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/50 p-6 text-center">
            <div class="mx-auto mb-3 flex size-12 items-center justify-center rounded-full bg-blue-100 text-blue-500">
                <i class="fa-solid fa-file-invoice text-xl"></i>
            </div>
            <h3 class="font-semibold text-slate-700">Cetak Label</h3>
            <p class="mt-1 text-xs text-slate-400">Cetak label pengiriman untuk setiap pesanan</p>
        </div>
        <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/50 p-6 text-center">
            <div class="mx-auto mb-3 flex size-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-500">
                <i class="fa-solid fa-clipboard-check text-xl"></i>
            </div>
            <h3 class="font-semibold text-slate-700">Konfirmasi Terima</h3>
            <p class="mt-1 text-xs text-slate-400">Konfirmasi penerimaan paket oleh pelanggan</p>
        </div>
    </div>
</div>
