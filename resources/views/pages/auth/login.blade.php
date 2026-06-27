<x-layouts::auth.abodafarm :title="__('Masuk')" eyebrow="Selamat datang kembali"
    headline="Masuk untuk lanjut belanja bahan segar hari ini."
    description="Akses riwayat pesanan, simpan favorit, dan checkout lebih cepat dengan akun AbodaFarm kamu."
    image="https://images.unsplash.com/photo-1543852786-1cf6624b9987?auto=format&fit=crop&w=1200&q=80">
    <div class="space-y-8" x-data="{
        showPassword: false,
        showAlert: false,
        alertType: 'error',
        alertTitle: '',
        alertMessage: '',

        triggerAlert(type, title, message) {
            this.alertType = type;
            this.alertTitle = title;
            this.alertMessage = message;
            this.showAlert = true;
        },

        closeAlert() {
            this.showAlert = false;
        },

        init() {
            @if($errors->any() || session('error'))
                setTimeout(() => {
                    this.triggerAlert(
                        'error',
                        @if($errors->has('email')) 'Email Tidak Valid'
                        @elseif($errors->has('password')) 'Kata Sandi Salah'
                        @else 'Login Gagal' @endif,
                        @if($errors->has('email')) '{{ addslashes($errors->first('email')) }}'
                        @elseif($errors->has('password')) '{{ addslashes($errors->first('password')) }}'
                        @else '{{ addslashes(session('error')) }}' @endif
                    );
                }, 100);
            @endif
        }
    }">

        {{-- Error Alert Popup --}}
        @include('components.auth.error-popup')

        <div>
            <h1 class="text-3xl font-black tracking-tight text-slate-900">Masuk ke akun</h1>
            <p class="mt-2 text-sm text-slate-500">Gunakan email dan kata sandi untuk masuk ke AbodaFarm.</p>
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}" class="space-y-5" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf

            <div>
                <label for="email" class="mb-1.5 block text-sm font-semibold text-slate-700">Alamat email</label>
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M3 8.25A2.25 2.25 0 015.25 6h13.5A2.25 2.25 0 0121 8.25v7.5A2.25 2.25 0 0118.75 18H5.25A2.25 2.25 0 013 15.75v-7.5z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M3.75 7.5l7.05 5.04c.73.52 1.72.52 2.45 0l7.05-5.04" />
                    </svg>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                        autocomplete="email" placeholder="nama@email.com"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-10 py-3.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10">
                </div>
            </div>

            <div>
                <div class="mb-1.5 flex items-center justify-between">
                    <label for="password" class="block text-sm font-semibold text-slate-700">Kata sandi</label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}"
                            class="text-sm font-medium text-sky-700 hover:text-sky-800">Lupa sandi?</a>
                    @endif
                </div>
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M16 11V8a4 4 0 10-8 0v3m-1 0a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2H7z" />
                    </svg>
                    <input id="password" :type="showPassword ? 'text' : 'password'" name="password" required
                        autocomplete="current-password" placeholder="Masukkan kata sandi"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-10 py-3.5 pr-12 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10">
                    <button type="button" @click="showPassword = !showPassword"
                        class="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                        aria-label="Toggle password visibility">
                        <svg x-show="!showPassword" class="size-4" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M3.98 8.223A10.477 10.477 0 001.5 12c1.73 4.034 5.76 7 10.5 7 4.74 0 8.77-2.966 10.5-7a10.5 10.5 0 00-4.605-5.44" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M14.121 14.121a3 3 0 11-4.243-4.243 3 3 0 014.243 4.243z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3l18 18" />
                        </svg>
                        <svg x-show="showPassword" class="size-4" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
            </div>

            <label class="flex items-center gap-3 text-sm text-slate-600">
                <input name="remember" type="checkbox" @checked(old('remember'))
                    class="size-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                Ingat saya di perangkat ini
            </label>

            <button type="submit"
                class="inline-flex w-full items-center justify-center rounded-2xl bg-sky-600 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-sky-600/20 transition hover:bg-sky-700">
                Masuk sekarang
            </button>
        </form>

        @if (Route::has('register'))
            <p class="text-center text-sm text-slate-500">
                Belum punya akun?
                <a href="{{ route('register') }}" class="font-semibold text-sky-700 hover:text-sky-800">Daftar
                    sekarang</a>
            </p>
        @endif
    </div>
</x-layouts::auth.abodafarm>
