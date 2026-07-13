<div class="space-y-6">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Pengaturan Akun</h2>
        <p class="mt-1 text-sm text-slate-500">Kelola informasi akun Anda</p>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Profile Form --}}
        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-lg font-semibold text-slate-900">Informasi Akun</h3>

            <form wire:submit="updateProfile" class="space-y-4">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Nama</label>
                    <input type="text" wire:model="name" required
                        class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm transition focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Email</label>
                    <input type="email" wire:model="email" required
                        class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm transition focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit"
                        class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 hover:shadow-md">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        {{-- Password Form --}}
        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-lg font-semibold text-slate-900">Ubah Password</h3>

            <form wire:submit="updatePassword" class="space-y-4">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Password Saat Ini</label>
                    <input type="password" wire:model="currentPassword" required
                        class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm transition focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Password Baru</label>
                    <input type="password" wire:model="newPassword" minlength="8" required
                        class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm transition focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                    <p class="mt-1 text-xs text-slate-400">Minimal 8 karakter</p>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Konfirmasi Password Baru</label>
                    <input type="password" wire:model="confirmPassword" required
                        class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm transition focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit"
                        class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 hover:shadow-md">
                        Ubah Password
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Account Info --}}
    <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
        <h3 class="mb-4 text-lg font-semibold text-slate-900">Informasi Akun</h3>

        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <p class="text-xs text-slate-500 uppercase tracking-wide">Role</p>
                <p class="mt-1 font-medium text-slate-900">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-sm text-emerald-700">
                        <i class="fa-solid fa-shield-halved"></i>
                        {{ auth()->user()->getRoleNames()->first() ?? 'Admin' }}
                    </span>
                </p>
            </div>
            <div>
                <p class="text-xs text-slate-500 uppercase tracking-wide">Terdaftar</p>
                <p class="mt-1 font-medium text-slate-900">{{ auth()->user()->created_at->format('d M Y') }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500 uppercase tracking-wide">Status</p>
                <p class="mt-1 font-medium text-slate-900">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-3 py-1 text-sm text-green-700">
                        <span class="size-1.5 rounded-full bg-green-500"></span>
                        Aktif
                    </span>
                </p>
            </div>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4"
            class="fixed bottom-6 right-6 z-50 flex items-center gap-3 rounded-xl bg-emerald-600 px-5 py-3.5 text-sm font-medium text-white shadow-lg shadow-emerald-600/30">
            <i class="fa-solid fa-check-circle text-lg"></i>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4"
            class="fixed bottom-6 right-6 z-50 flex items-center gap-3 rounded-xl bg-red-600 px-5 py-3.5 text-sm font-medium text-white shadow-lg shadow-red-600/30">
            <i class="fa-solid fa-exclamation-circle text-lg"></i>
            {{ session('error') }}
        </div>
    @endif
</div>
