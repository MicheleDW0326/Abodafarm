<div class="mx-auto max-w-2xl space-y-6">
    {{-- Header --}}
    <div class="rounded-2xl border border-slate-100 bg-gradient-to-r from-emerald-600 to-emerald-700 p-6 shadow-lg">
        <div class="flex items-center gap-4">
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-white/20 text-2xl font-bold text-white">
                {{ auth()->user()->initials() }}
            </div>
            <div>
                <h2 class="text-xl font-bold text-white">{{ auth()->user()->name }}</h2>
                <p class="mt-0.5 text-emerald-100">Kelola informasi profil Anda</p>
            </div>
        </div>
    </div>

    {{-- Profile Form --}}
    <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-xs">
        <div class="mb-6">
            <h3 class="text-lg font-bold text-slate-900">Informasi Profil</h3>
            <p class="mt-1 text-sm text-slate-500">Perbarui informasi akun Anda</p>
        </div>

        <form wire:submit="updateProfile" class="space-y-4">
            {{-- Name --}}
            <div>
                <label for="name" class="block text-sm font-medium text-slate-700">Nama Lengkap</label>
                <input
                    type="text"
                    id="name"
                    wire:model="name"
                    class="mt-1 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                    placeholder="Masukkan nama lengkap"
                >
                @error('name')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                <input
                    type="email"
                    id="email"
                    wire:model="email"
                    class="mt-1 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                    placeholder="Masukkan email"
                >
                @error('email')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Phone --}}
            <div>
                <label for="phone" class="block text-sm font-medium text-slate-700">Nomor WhatsApp</label>
                <input
                    type="tel"
                    id="phone"
                    wire:model="phone"
                    class="mt-1 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                    placeholder="08xxxxxxxxxx"
                >
                @error('phone')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Joined Date --}}
            <div class="rounded-xl bg-slate-50 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-700">Tanggal Bergabung</p>
                        <p class="mt-0.5 text-sm text-slate-500">{{ auth()->user()->created_at->format('d M Y') }}</p>
                    </div>
                    <i class="fa-solid fa-calendar-day text-slate-400"></i>
                </div>
            </div>

            <button
                type="submit"
                wire:loading.attr="disabled"
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-50"
            >
                <span wire:loading.remove wire:target="updateProfile">
                    <i class="fa-solid fa-save"></i>
                </span>
                <span wire:loading wire:target="updateProfile">
                    <i class="fa-solid fa-spinner fa-spin"></i>
                </span>
                {{ $isUpdating ? 'Menyimpan...' : 'Simpan Perubahan' }}
            </button>
        </form>
    </div>

    {{-- Change Password --}}
    <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-xs">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-slate-900">Ubah Password</h3>
                <p class="mt-1 text-sm text-slate-500">Update password akun Anda</p>
            </div>
            <button
                type="button"
                wire:click="togglePasswordForm"
                class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50"
            >
                {{ $showPasswordForm ? 'Batal' : 'Ubah Password' }}
            </button>
        </div>

        @if($showPasswordForm)
            <form wire:submit="updatePassword" class="space-y-4">
                {{-- Current Password --}}
                <div>
                    <label for="current_password" class="block text-sm font-medium text-slate-700">Password Saat Ini</label>
                    <div class="relative mt-1">
                        <input
                            type="password"
                            id="current_password"
                            wire:model="current_password"
                            class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                            placeholder="Masukkan password saat ini"
                        >
                    </div>
                    @error('current_password')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- New Password --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">Password Baru</label>
                    <input
                        type="password"
                        id="password"
                        wire:model="password"
                        class="mt-1 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                        placeholder="Minimal 8 karakter"
                    >
                    @error('password')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Confirm Password --}}
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Konfirmasi Password</label>
                    <input
                        type="password"
                        id="password_confirmation"
                        wire:model="password_confirmation"
                        class="mt-1 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                        placeholder="Ulangi password baru"
                    >
                    @error('password_confirmation')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-50"
                >
                    <span wire:loading.remove wire:target="updatePassword">
                        <i class="fa-solid fa-key"></i>
                    </span>
                    <span wire:loading wire:target="updatePassword">
                        <i class="fa-solid fa-spinner fa-spin"></i>
                    </span>
                    {{ $isUpdatingPassword ? 'Menyimpan...' : 'Update Password' }}
                </button>
            </form>
        @else
            <div class="flex items-center gap-3 rounded-xl bg-slate-50 p-4">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-200 text-slate-500">
                    <i class="fa-solid fa-lock"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-700">Password terenkripsi</p>
                    <p class="text-xs text-slate-500">Klik "Ubah Password" untuk memperbarui</p>
                </div>
            </div>
        @endif
    </div>
</div>
