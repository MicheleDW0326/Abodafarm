<x-layouts::auth.abodafarm :title="__('Lupa kata sandi')" eyebrow="Akses akun dengan aman"
    headline="Tenang, kami bantu reset kata sandimu."
    description="Masukkan email terdaftar dan kami akan kirim link reset agar kamu bisa login kembali dengan cepat."
    image="https://images.unsplash.com/photo-1517260739337-6799d26f4b9a?auto=format&fit=crop&w=1200&q=80">
    <div class="space-y-8">
        <a href="{{ route('login') }}"
            class="inline-flex items-center gap-1.5 text-sm text-slate-400 transition hover:text-slate-600">
            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m15 18-6-6 6-6" />
            </svg>
            Kembali ke login
        </a>

        <div>
            <h1 class="text-3xl font-black tracking-tight text-slate-900">Lupa kata sandi?</h1>
            <p class="mt-2 text-sm leading-6 text-slate-500">Masukkan email akun AbodaFarm kamu, lalu cek inbox untuk
                link reset kata sandi.</p>
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
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
                @error('email')
                    <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                @enderror
                <p class="mt-1.5 text-xs text-slate-400">Kami akan kirim link reset ke email ini.</p>
            </div>

            <button type="submit"
                class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-sky-600 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-sky-600/20 transition hover:bg-sky-700"
                data-test="email-password-reset-link-button">
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M3 8.25A2.25 2.25 0 015.25 6h13.5A2.25 2.25 0 0121 8.25v7.5A2.25 2.25 0 0118.75 18H5.25A2.25 2.25 0 013 15.75v-7.5z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M3.75 7.5l7.05 5.04c.73.52 1.72.52 2.45 0l7.05-5.04" />
                </svg>
                Kirim Link Reset
            </button>
        </form>

        <p class="text-center text-sm text-slate-500">
            Ingat kata sandi?
            <a href="{{ route('login') }}" class="font-semibold text-sky-700 hover:text-sky-800">Masuk sekarang</a>
        </p>
    </div>
</x-layouts::auth.abodafarm>
