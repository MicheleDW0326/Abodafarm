{{--
    Login Modal for Guest Checkout
    Usage:
    1. Include in layout: <x-auth.login-modal />
    2. Dispatch event from JS: window.dispatchEvent(new CustomEvent('require-login'))
    3. Dispatch with redirect: window.dispatchEvent(new CustomEvent('require-login', { detail: { redirect: '/checkout' } }))
--}}

<div x-data="{
    showModal: false,
    redirectTo: '/checkout',
    activeTab: 'login',

    init() {
        window.addEventListener('require-login', (e) => {
            this.redirectTo = e.detail?.redirect || '/checkout';
            this.showModal = true;
            this.activeTab = 'login';
        });

        // Listen for login success
        window.addEventListener('login-success', () => {
            this.showModal = false;
        });
    }
}"
     x-show="showModal"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @keydown.escape.window="showModal = false"
     class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/70 backdrop-blur-sm p-4">

    {{-- Modal Card --}}
    <div x-show="showModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-4"
         @click.stop
         class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl">

        {{-- Header --}}
        <div class="relative bg-gradient-to-br from-emerald-600 to-emerald-700 px-6 py-5">
            <button type="button"
                    @click="showModal = false"
                    class="absolute right-3 top-3 rounded-full p-1.5 text-white/80 transition-all hover:bg-white/20 hover:text-white">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-white/20">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-white">Masuk untuk Melanjutkan</h3>
                    <p class="text-sm text-emerald-100">Silakan masuk ke akun Anda untuk checkout</p>
                </div>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="flex border-b border-slate-200">
            <button type="button"
                    @click="activeTab = 'login'"
                    class="flex-1 px-6 py-3 text-sm font-medium transition-all"
                    :class="activeTab === 'login' ? 'border-b-2 border-emerald-500 text-emerald-600' : 'text-slate-500 hover:text-slate-700'">
                Masuk
            </button>
            <button type="button"
                    @click="activeTab = 'register'"
                    class="flex-1 px-6 py-3 text-sm font-medium transition-all"
                    :class="activeTab === 'register' ? 'border-b-2 border-emerald-500 text-emerald-600' : 'text-slate-500 hover:text-slate-700'">
                Daftar
            </button>
        </div>

        {{-- Login Form --}}
        <div x-show="activeTab === 'login'" class="p-6">
            <form action="{{ route('login') }}" method="POST" x-ref="loginForm">
                @csrf
                <input type="hidden" name="redirect_to" x-model="redirectTo">

                <div class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                        <input type="email"
                               name="email"
                               required
                               autofocus
                               class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                               placeholder="nama@email.com">
                        @error('email')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Kata Sandi</label>
                        <input type="password"
                               name="password"
                               required
                               class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                               placeholder="••••••••">
                        @error('password')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                            class="w-full rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-700 py-3 text-sm font-semibold text-white transition hover:from-emerald-700 hover:to-emerald-800">
                        Masuk
                    </button>
                </div>
            </form>

            <p class="mt-4 text-center text-sm text-slate-500">
                Lupa kata sandi?
                <a href="{{ route('password.request') }}" class="font-medium text-emerald-600 hover:text-emerald-700">Reset di sini</a>
            </p>
        </div>

        {{-- Register Form --}}
        <div x-show="activeTab === 'register'" x-cloak class="p-6">
            <form action="{{ route('register') }}" method="POST" x-ref="registerForm">
                @csrf
                <input type="hidden" name="redirect_to" x-model="redirectTo">

                <div class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Nama Lengkap</label>
                        <input type="text"
                               name="name"
                               required
                               class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                               placeholder="Nama Anda">
                        @error('name')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                        <input type="email"
                               name="email"
                               required
                               class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                               placeholder="nama@email.com">
                        @error('email')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Kata Sandi</label>
                        <input type="password"
                               name="password"
                               required
                               minlength="8"
                               class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                               placeholder="Minimal 8 karakter">
                        @error('password')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Konfirmasi Kata Sandi</label>
                        <input type="password"
                               name="password_confirmation"
                               required
                               class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                               placeholder="Ulangi kata sandi">
                    </div>

                    <button type="submit"
                            class="w-full rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-700 py-3 text-sm font-semibold text-white transition hover:from-emerald-700 hover:to-emerald-800">
                        Daftar Sekarang
                    </button>
                </div>
            </form>
        </div>

        {{-- Footer --}}
        <div class="border-t border-slate-100 bg-slate-50 px-6 py-4">
            <p class="text-center text-xs text-slate-500">
                Dengan masuk, Anda menyetujui
                <a href="#" class="text-emerald-600 hover:underline">Syarat & Ketentuan</a>
                dan
                <a href="#" class="text-emerald-600 hover:underline">Kebijakan Privasi</a>
                kami.
            </p>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>
