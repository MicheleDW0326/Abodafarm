<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">
                Notifikasi
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Notifikasi dan pemberitahuan untuk Anda
            </p>
        </div>
        <button type="button"
                class="inline-flex items-center gap-2 rounded-xl bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 transition-all hover:bg-emerald-100">
            <i class="fa-solid fa-check-double"></i>
            Tandai Semua Dibaca
        </button>
    </div>

    {{-- Notifications List Placeholder --}}
    <div class="rounded-2xl border border-slate-100 bg-white shadow-xs">
        <div class="border-b border-slate-100 px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="rounded-xl bg-slate-100 p-2 text-slate-600">
                        <i class="fa-solid fa-bell text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Semua Notifikasi</h3>
                        <p class="text-xs text-slate-400">0 notifikasi belum dibaca</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-12">
            <div class="flex flex-col items-center justify-center text-center">
                <div class="rounded-full bg-slate-50 p-6 text-slate-300 mb-4">
                    <i class="fa-solid fa-bell-slash text-5xl"></i>
                </div>
                <h2 class="text-xl font-bold text-slate-700 mb-2">Tidak Ada Notifikasi</h2>
                <p class="text-sm text-slate-500 max-w-md">
                    Notifikasi untuk staff akan muncul di sini. Anda akan menerima pemberitahuan tentang pesanan dan aktivitas baru.
                </p>
            </div>
        </div>
    </div>
</div>
