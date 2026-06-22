<div class="space-y-6">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Pengaturan Akun</h2>
        <p class="mt-1 text-sm text-slate-500">Kelola informasi akun Anda</p>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Profile Form --}}
        <div class="rounded-2xl border border-slate-100 bg-white p-6">
            <h3 class="mb-4 text-lg font-semibold text-slate-900">Informasi Akun</h3>

            <form wire:submit="updateProfile" class="space-y-4">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Nama</label>
                    <input type="text" wire:model="name" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Email</label>
                    <input type="email" wire:model="email" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        {{-- Password Form --}}
        <div class="rounded-2xl border border-slate-100 bg-white p-6">
            <h3 class="mb-4 text-lg font-semibold text-slate-900">Ubah Password</h3>

            <form wire:submit="updatePassword" class="space-y-4">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Password Saat Ini</label>
                    <input type="password" wire:model="currentPassword" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Password Baru</label>
                    <input type="password" wire:model="newPassword" minlength="8" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                    <p class="mt-1 text-xs text-slate-400">Minimal 8 karakter</p>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Konfirmasi Password Baru</label>
                    <input type="password" wire:model="confirmPassword" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                        Ubah Password
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Account Info --}}
    <div class="rounded-2xl border border-slate-100 bg-white p-6">
        <h3 class="mb-4 text-lg font-semibold text-slate-900">Informasi Tambahan</h3>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <p class="text-xs text-slate-500">Role</p>
                <p class="mt-1 font-medium text-slate-900">
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-sm text-emerald-700">
                        <i class="fa-solid fa-hat-cowboy mr-1"></i>
                        {{ auth()->user()->getRoleNames()->first() ?? 'Peternak' }}
                    </span>
                </p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Terdaftar</p>
                <p class="mt-1 font-medium text-slate-900">{{ auth()->user()->created_at->format('d M Y') }}</p>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="fixed bottom-4 right-4 z-50 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-medium text-white shadow-lg">
            <i class="fa-solid fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="fixed bottom-4 right-4 z-50 rounded-xl bg-red-600 px-4 py-3 text-sm font-medium text-white shadow-lg">
            <i class="fa-solid fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
    @endif
</div>
