<x-layouts::auth.abodafarm :title="__('Verifikasi Email')" eyebrow="Aktivasi akun" headline="Satu langkah lagi, verifikasi emailmu."
    description="Klik tautan verifikasi yang kami kirim ke email kamu. Setelah itu akun AbodaFarm langsung aktif."
    image="https://images.unsplash.com/photo-1544943910-4c1dc44aab44?auto=format&fit=crop&w=1200&q=80">
    <div class="space-y-8" x-data="{ cooldown: {{ session('status') == 'verification-link-sent' ? 30 : 0 }}, timer: null, init() { if (this.cooldown > 0) { this.timer = setInterval(() => { this.cooldown--; if (this.cooldown <= 0) clearInterval(this.timer); }, 1000) } } }">
        <div>
            <h1 class="text-3xl font-black tracking-tight text-slate-900">Verifikasi email kamu</h1>
            <p class="mt-2 text-sm leading-6 text-slate-500">Kami sudah mengirim email verifikasi. Buka inbox kamu lalu
                klik tautan verifikasi untuk melanjutkan.</p>
        </div>

        <div class="rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-700">
            Jika email belum masuk, cek folder spam atau kirim ulang dari tombol di bawah.
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                Link verifikasi baru sudah dikirim ke email kamu.
            </div>
        @endif

        <div class="space-y-4">
            <form method="POST" action="{{ route('verification.send') }}" class="space-y-3">
                @csrf
                <button type="submit" :disabled="cooldown > 0"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-sky-600 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-sky-600/20 transition hover:bg-sky-700 disabled:cursor-not-allowed disabled:opacity-60">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M3 8.25A2.25 2.25 0 015.25 6h13.5A2.25 2.25 0 0121 8.25v7.5A2.25 2.25 0 0118.75 18H5.25A2.25 2.25 0 013 15.75v-7.5z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M3.75 7.5l7.05 5.04c.73.52 1.72.52 2.45 0l7.05-5.04" />
                    </svg>
                    <span x-show="cooldown === 0">Kirim Ulang Email Verifikasi</span>
                    <span x-show="cooldown > 0" style="display: none;">Kirim ulang dalam <span
                            x-text="cooldown"></span>d</span>
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="inline-flex w-full items-center justify-center rounded-2xl border border-slate-300 bg-white px-5 py-3.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                    data-test="logout-button">
                    Keluar
                </button>
            </form>
        </div>

        <p class="text-center text-xs leading-5 text-slate-400">
            Butuh bantuan? Hubungi dukungan AbodaFarm jika kamu tidak menerima email verifikasi.
        </p>
    </div>
</x-layouts::auth.abodafarm>
