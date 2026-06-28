<x-layouts::auth.abodafarm :title="__('Daftar')" eyebrow="Gabung AbodaFarm Family"
    headline="Buat akun dan nikmati akses ke ternak unggulan dari peternak lokal."
    description="Dapatkan akses ke riwayat perawatan, sertifikat ternak, dan pengalaman pasar ternak yang lebih terpercaya."
    image="https://images.unsplash.com/photo-1543852786-1cf6624b9987?auto=format&fit=crop&w=1200&q=80">
    <div class="space-y-8" x-data="{
        showPassword: false,
        showConfirm: false,
        showAlert: false,
        alertType: 'error',
        alertTitle: '',
        alertMessage: '',

        // Password strength tracking
        password: '',
        confirmPassword: '',
        passwordStrength: 0,
        strengthLabel: '',
        strengthColor: '',

        triggerAlert(type, title, message) {
            this.alertType = type;
            this.alertTitle = title;
            this.alertMessage = message;
            this.showAlert = true;
        },

        closeAlert() {
            this.showAlert = false;
        },

        calculateStrength() {
            let score = 0;
            const pwd = this.password;

            if (!pwd) {
                this.passwordStrength = 0;
                this.strengthLabel = '';
                this.strengthColor = '';
                return;
            }

            // Length checks
            if (pwd.length >= 8) score += 20;
            if (pwd.length >= 12) score += 10;

            // Character type checks
            if (/[a-z]/.test(pwd)) score += 15;
            if (/[A-Z]/.test(pwd)) score += 15;
            if (/[0-9]/.test(pwd)) score += 20;
            if (/[^a-zA-Z0-9]/.test(pwd)) score += 20;

            // Bonus for mixing types
            const types = [
                /[a-z]/.test(pwd),
                /[A-Z]/.test(pwd),
                /[0-9]/.test(pwd),
                /[^a-zA-Z0-9]/.test(pwd)
            ].filter(Boolean).length;

            if (types >= 3) score += 10;
            if (types === 4) score += 10;

            this.passwordStrength = Math.min(100, score);

            if (this.passwordStrength < 30) {
                this.strengthLabel = 'Lemah';
                this.strengthColor = 'bg-rose-500';
            } else if (this.passwordStrength < 60) {
                this.strengthLabel = 'Sedang';
                this.strengthColor = 'bg-amber-500';
            } else if (this.passwordStrength < 80) {
                this.strengthLabel = 'Kuat';
                this.strengthColor = 'bg-emerald-500';
            } else {
                this.strengthLabel = 'Sangat Kuat';
                this.strengthColor = 'bg-emerald-600';
            }
        },

        getPasswordRequirements() {
            const pwd = this.password;
            return {
                minLength: pwd.length >= 8,
                hasLowercase: /[a-z]/.test(pwd),
                hasUppercase: /[A-Z]/.test(pwd),
                hasNumber: /[0-9]/.test(pwd),
                hasSpecial: /[^a-zA-Z0-9]/.test(pwd),
            };
        },

        init() {
            @if($errors->any())
                setTimeout(() => {
                    this.triggerAlert(
                        'error',
                        @if($errors->has('name')) 'Nama Tidak Valid'
                        @elseif($errors->has('email')) 'Email Tidak Valid'
                        @elseif($errors->has('password')) 'Kata Sandi Lemah'
                        @elseif($errors->has('password_confirmation')) 'Konfirmasi Gagal'
                        @else 'Registrasi Gagal' @endif,
                        @if($errors->has('name')) '{{ addslashes($errors->first('name')) }}'
                        @elseif($errors->has('email')) '{{ addslashes($errors->first('email')) }}'
                        @elseif($errors->has('password')) '{{ addslashes($errors->first('password')) }}'
                        @elseif($errors->has('password_confirmation')) '{{ addslashes($errors->first('password_confirmation')) }}'
                        @endif
                    );
                }, 100);
            @endif
        }
    }">

        {{-- Error Alert Popup --}}
        @include('components.auth.error-popup')

        <div>
            <h1 class="text-3xl font-black tracking-tight text-slate-900">Buat akun gratis</h1>
            <p class="mt-2 text-sm text-slate-500">Isi data di bawah untuk mulai menggunakan AbodaFarm.</p>
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('register.store') }}" class="space-y-5">
            @csrf

            <div>
                <label for="name" class="mb-1.5 block text-sm font-semibold text-slate-700">Nama lengkap</label>
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M15 19.128a9 9 0 10-6 0M12 14a4 4 0 100-8 4 4 0 000 8z" />
                    </svg>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus
                        autocomplete="name" placeholder="Nama lengkap kamu"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-10 py-3.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10">
                </div>
            </div>

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
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required
                        autocomplete="email" placeholder="nama@email.com"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-10 py-3.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10">
                </div>
            </div>

            <div>
                <label for="password" class="mb-1.5 block text-sm font-semibold text-slate-700">Kata sandi</label>
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M16 11V8a4 4 0 10-8 0v3m-1 0a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2H7z" />
                    </svg>
                    <input id="password" :type="showPassword ? 'text' : 'password'" name="password" required
                        autocomplete="new-password" placeholder="Minimal 8 karakter"
                        x-model="password"
                        @input="calculateStrength()"
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

                {{-- Password Strength Indicator --}}
                <div x-show="password.length > 0" class="mt-2">
                    {{-- Progress Bar --}}
                    <div class="mb-2 h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full transition-all duration-300"
                             :class="strengthColor"
                             :style="'width: ' + passwordStrength + '%'">
                        </div>
                    </div>
                    {{-- Strength Label --}}
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-medium"
                           :class="{
                               'text-rose-600': strengthLabel === 'Lemah',
                               'text-amber-600': strengthLabel === 'Sedang',
                               'text-emerald-600': strengthLabel === 'Kuat' || strengthLabel === 'Sangat Kuat'
                           }">
                            <span x-text="strengthLabel"></span>
                        </p>
                        <p class="text-xs text-slate-400" x-text="password.length + '/8+'"></p>
                    </div>
                    {{-- Requirements Checklist --}}
                    <div class="mt-2 grid grid-cols-2 gap-1">
                        <template x-for="(req, key) in {
                            '8+ karakter': password.length >= 8,
                            'Huruf besar': /[A-Z]/.test(password),
                            'Huruf kecil': /[a-z]/.test(password),
                            'Angka': /[0-9]/.test(password),
                            'Simbol (!@#$)': /[^a-zA-Z0-9]/.test(password),
                        }" :key="key">
                            <div class="flex items-center gap-1.5 text-xs">
                                <svg x-show="req" class="size-3.5 flex-shrink-0 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                                <svg x-show="!req" class="size-3.5 flex-shrink-0 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                <span :class="req ? 'text-emerald-600 font-medium' : 'text-slate-400'" x-text="key"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div>
                <label for="password_confirmation" class="mb-1.5 block text-sm font-semibold text-slate-700">Konfirmasi
                    kata sandi</label>
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M16 11V8a4 4 0 10-8 0v3m-1 0a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2H7z" />
                    </svg>
                    <input id="password_confirmation" :type="showConfirm ? 'text' : 'password'"
                        name="password_confirmation" required autocomplete="new-password"
                        placeholder="Ulangi kata sandi"
                        x-model="confirmPassword"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-10 py-3.5 pr-12 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10">
                    <button type="button" @click="showConfirm = !showConfirm"
                        class="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                        aria-label="Toggle password visibility">
                        <svg x-show="!showConfirm" class="size-4" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M3.98 8.223A10.477 10.477 0 001.5 12c1.73 4.034 5.76 7 10.5 7 4.74 0 8.77-2.966 10.5-7a10.5 10.5 0 00-4.605-5.44" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M14.121 14.121a3 3 0 11-4.243-4.243 3 3 0 014.243 4.243z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3l18 18" />
                        </svg>
                        <svg x-show="showConfirm" class="size-4" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
                {{-- Match Indicator --}}
                <div x-show="confirmPassword.length > 0" class="mt-1.5 flex items-center gap-1.5">
                    <svg x-show="password === confirmPassword && password.length > 0"
                         class="size-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    <svg x-show="password !== confirmPassword"
                         class="size-3.5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    <span class="text-xs font-medium"
                          :class="password === confirmPassword && password.length > 0 ? 'text-emerald-600' : 'text-rose-400'"
                          x-text="password === confirmPassword && password.length > 0 ? 'Kata sandi cocok' : 'Kata sandi tidak cocok'">
                    </span>
                </div>
            </div>

            <button type="submit"
                class="inline-flex w-full items-center justify-center rounded-2xl bg-sky-600 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-sky-600/20 transition hover:bg-sky-700">
                Daftar sekarang
            </button>
        </form>

        <p class="text-center text-sm text-slate-500">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="font-semibold text-sky-700 hover:text-sky-800">Masuk di sini</a>
        </p>
    </div>
</x-layouts::auth.abodafarm>