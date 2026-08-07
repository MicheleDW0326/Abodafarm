<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">
                Profil Saya
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Kelola informasi akun dan pengaturan Anda
            </p>
        </div>
    </div>

    {{-- User Profile Card --}}
    <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-xs">
        <div class="flex flex-col items-center text-center sm:flex-row sm:text-left gap-6">
            <div class="relative">
                <div class="flex size-24 items-center justify-center rounded-full bg-gradient-to-br from-emerald-500 to-emerald-600 text-3xl font-bold text-white shadow-lg shadow-emerald-500/30">
                    {{ auth()->user()?->initials() }}
                </div>
                <span class="absolute bottom-1 right-1 flex size-6 items-center justify-center rounded-full bg-emerald-500 text-[10px] font-bold text-white ring-2 ring-white">
                    <i class="fa-solid fa-user"></i>
                </span>
            </div>
            <div class="flex-1">
                <h2 class="text-2xl font-bold text-slate-900">{{ auth()->user()?->name }}</h2>
                <p class="text-sm text-slate-500">{{ auth()->user()?->email }}</p>
                <div class="mt-3 flex flex-wrap items-center justify-center gap-2 sm:justify-start">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                        <i class="fa-solid fa-user-shield"></i>
                        Staff
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                        <i class="fa-solid fa-circle text-[6px] text-emerald-500"></i>
                        Aktif
                    </span>
                </div>
            </div>
            <div class="flex gap-2">
                <button type="button"
                        class="inline-flex items-center gap-2 rounded-xl bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-700 transition-all hover:bg-emerald-100">
                    <i class="fa-solid fa-pen-to-square"></i>
                    Edit Profil
                </button>
            </div>
        </div>
    </div>

    {{-- Profile Information --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Basic Info --}}
        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-xs">
            <div class="mb-4 flex items-center gap-3">
                <div class="rounded-xl bg-slate-100 p-2.5 text-slate-600">
                    <i class="fa-solid fa-user text-lg"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Informasi Dasar</h3>
                    <p class="text-xs text-slate-400">Data diri Anda</p>
                </div>
            </div>

            <div class="space-y-4">
                <div class="flex items-center justify-between rounded-lg bg-slate-50 p-3">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-user text-slate-400"></i>
                        <span class="text-sm text-slate-600">Nama Lengkap</span>
                    </div>
                    <span class="text-sm font-semibold text-slate-900">{{ auth()->user()?->name ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between rounded-lg bg-slate-50 p-3">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-envelope text-slate-400"></i>
                        <span class="text-sm text-slate-600">Email</span>
                    </div>
                    <span class="text-sm font-semibold text-slate-900">{{ auth()->user()?->email ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between rounded-lg bg-slate-50 p-3">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-phone text-slate-400"></i>
                        <span class="text-sm text-slate-600">No. Telepon</span>
                    </div>
                    <span class="text-sm font-semibold text-slate-900">{{ auth()->user()?->phone ?? '-' }}</span>
                </div>
            </div>
        </div>

        {{-- Account Info --}}
        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-xs">
            <div class="mb-4 flex items-center gap-3">
                <div class="rounded-xl bg-slate-100 p-2.5 text-slate-600">
                    <i class="fa-solid fa-shield-halved text-lg"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Informasi Akun</h3>
                    <p class="text-xs text-slate-400">Status dan keamanan akun</p>
                </div>
            </div>

            <div class="space-y-4">
                <div class="flex items-center justify-between rounded-lg bg-slate-50 p-3">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-id-badge text-slate-400"></i>
                        <span class="text-sm text-slate-600">Role</span>
                    </div>
                    <span class="text-sm font-semibold text-slate-900">Staff</span>
                </div>
                <div class="flex items-center justify-between rounded-lg bg-slate-50 p-3">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-calendar-plus text-slate-400"></i>
                        <span class="text-sm text-slate-600">Bergabung</span>
                    </div>
                    <span class="text-sm font-semibold text-slate-900">
                        {{ auth()->user()?->created_at ? auth()->user()->created_at->translatedFormat('d M Y') : '-' }}
                    </span>
                </div>
                <div class="flex items-center justify-between rounded-lg bg-slate-50 p-3">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-clock text-slate-400"></i>
                        <span class="text-sm text-slate-600">Login Terakhir</span>
                    </div>
                    <span class="text-sm font-semibold text-slate-900">
                        {{ auth()->user()?->last_login_at ? auth()->user()->last_login_at->diffForHumans() : '-' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Settings --}}
    <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-xs">
        <div class="mb-4 flex items-center gap-3">
            <div class="rounded-xl bg-slate-100 p-2.5 text-slate-600">
                <i class="fa-solid fa-gear text-lg"></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-900">Pengaturan Cepat</h3>
                <p class="text-xs text-slate-400">Akses cepat ke pengaturan akun</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <button type="button"
                    class="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50 p-4 text-left transition-all hover:border-emerald-200 hover:bg-emerald-50">
                <div class="rounded-lg bg-emerald-100 p-2.5 text-emerald-600">
                    <i class="fa-solid fa-key text-lg"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-700">Ubah Password</p>
                    <p class="text-xs text-slate-400">Update kata sandi</p>
                </div>
            </button>

            <button type="button"
                    class="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50 p-4 text-left transition-all hover:border-blue-200 hover:bg-blue-50">
                <div class="rounded-lg bg-blue-100 p-2.5 text-blue-600">
                    <i class="fa-solid fa-bell text-lg"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-700">Notifikasi</p>
                    <p class="text-xs text-slate-400">Pengaturan notifikasi</p>
                </div>
            </button>

            <button type="button"
                    class="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50 p-4 text-left transition-all hover:border-violet-200 hover:bg-violet-50">
                <div class="rounded-lg bg-violet-100 p-2.5 text-violet-600">
                    <i class="fa-solid fa-display text-lg"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-700">Tampilan</p>
                    <p class="text-xs text-slate-400">Pengaturan UI</p>
                </div>
            </button>

            <button type="button"
                    class="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50 p-4 text-left transition-all hover:border-red-200 hover:bg-red-50">
                <div class="rounded-lg bg-red-100 p-2.5 text-red-600">
                    <i class="fa-solid fa-right-from-bracket text-lg"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-700">Keluar</p>
                    <p class="text-xs text-slate-400">Logout akun</p>
                </div>
            </button>
        </div>
    </div>
</div>
