<div class="min-h-screen bg-gradient-to-b from-sky-50 via-white to-emerald-50"
    x-data="{ activeRoute: '' }"
    x-init="activeRoute = window.location.pathname; window.addEventListener('livewire:navigated', () => activeRoute = window.location.pathname)">
    <!-- Topbar Navigation -->
    <header class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-slate-100 shadow-sm">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
                    <img src="/icons/MainLogo.png" alt="AbodaFarm Logo" class="h-9 w-auto object-contain">
                    <span class="block text-lg font-black tracking-tight text-slate-900">Aboda<span class="text-emerald-600">Farm</span></span>
                </a>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center gap-8">
                    <a href="{{ route('home') }}"
                        class="relative text-sm font-medium transition-colors"
                        :class="activeRoute === '/' || activeRoute === '/home' ? 'text-emerald-600' : 'text-slate-600 hover:text-emerald-600'">
                        Beranda
                        <span x-show="activeRoute === '/' || activeRoute === '/home'" class="absolute -bottom-1 left-0 h-0.5 w-full rounded-full bg-emerald-500"></span>
                    </a>
                    <a href="{{ route('home') }}#produk"
                        class="relative text-sm font-medium transition-colors"
                        :class="activeRoute.includes('/produk') ? 'text-emerald-600' : 'text-slate-600 hover:text-emerald-600'">
                        Produk
                        <span x-show="activeRoute.includes('/produk')" class="absolute -bottom-1 left-0 h-0.5 w-full rounded-full bg-emerald-500"></span>
                    </a>
                    <a href="{{ route('home') }}#tentang"
                        class="relative text-sm font-medium transition-colors"
                        :class="activeRoute.includes('/about') ? 'text-emerald-600' : 'text-slate-600 hover:text-emerald-600'">
                        Tentang
                        <span x-show="activeRoute.includes('/about')" class="absolute -bottom-1 left-0 h-0.5 w-full rounded-full bg-emerald-500"></span>
                    </a>
                    <a href="{{ route('home') }}#faq"
                        class="relative text-sm font-medium transition-colors"
                        :class="activeRoute.includes('/faq') ? 'text-emerald-600' : 'text-slate-600 hover:text-emerald-600'">
                        FAQ
                        <span x-show="activeRoute.includes('/faq')" class="absolute -bottom-1 left-0 h-0.5 w-full rounded-full bg-emerald-500"></span>
                    </a>
                    <a href="{{ route('contact') }}"
                        class="relative text-sm font-medium transition-colors text-emerald-600">
                        Hubungi
                        <span class="absolute -bottom-1 left-0 h-0.5 w-full rounded-full bg-emerald-500"></span>
                    </a>
                </div>

                <!-- CTA Button -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('home') }}#produk"
                        class="hidden sm:inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-emerald-500/30 transition-all hover:bg-emerald-700 hover:shadow-emerald-500/50 hover:-translate-y-0.5">
                        <i class="fa-solid fa-shopping-bag"></i>
                        Belanja Sekarang
                    </a>

                    <!-- Mobile Menu Button -->
                    <button type="button"
                        class="md:hidden grid size-10 place-content-center rounded-lg border border-slate-200 text-slate-600">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" />
                        </svg>
                    </button>
                </div>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="relative py-16 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-emerald-600 via-emerald-500 to-sky-600"></div>
        <div
            class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGRlZnM+PHBhdHRlcm4gaWQ9ImdyaWQiIHdpZHRoPSI2MCIgaGVpZ2h0PSI2MCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSI+PHBhdGggZD0iTSAxMCAwIEwgMCAwIDAgMTAgTSAxMCA2MCBMIDAgNjAgTDYgNjAiIGZpbGw9Im5vbmUiIHN0cm9rZT0id2hpdGUiIHN0cm9rZS1vcGFjaXR5PSIwLjA1IiBzdHJva2Utd2lkdGg9IjEiLz48L3BhdHRlcm4+PC9kZWZzPjxyZWN0IHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiIGZpbGw9InVybCgjZ3JpZCkiLz48L3N2Zz4=')] opacity-20">
        </div>
        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p
                class="inline-flex rounded-full border border-white/30 bg-white/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-white backdrop-blur mb-4">
                Hubungi Kami
            </p>
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-white mb-4">Ada Pertanyaan?</h1>
            <p class="text-lg md:text-xl text-white/90 max-w-2xl mx-auto">Tim kami siap membantu Anda 24/7. Jangan ragu
                untuk menghubungi kami kapan saja.</p>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="py-16 md:py-24 -mt-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-5 gap-8 lg:gap-12">
                <!-- Contact Info -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-lg shadow-slate-200/50 p-6 lg:p-8"
                        data-aos="fade-right">
                        <h2 class="text-2xl font-bold text-slate-900 mb-2">Informasi Kontak</h2>
                        <p class="text-slate-500 text-sm mb-6">Hubungi kami melalui berbagai saluran berikut</p>

                        <div class="space-y-5">
                            <!-- Address -->
                            <div class="flex items-start gap-4">
                                <div
                                    class="size-11 shrink-0 rounded-xl bg-emerald-100 flex items-center justify-center">
                                    <svg class="size-5 text-emerald-600" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                        <circle cx="12" cy="10" r="3" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-slate-900 text-sm">Alamat</h3>
                                    <p class="text-slate-500 text-sm leading-relaxed">Desa Talaud, Kecamatan
                                        ERPE<br>Kabupaten Minahasa<br>Sulawesi Utara, Indonesia</p>
                                </div>
                            </div>

                            <!-- Phone -->
                            <div class="flex items-start gap-4">
                                <div
                                    class="size-11 shrink-0 rounded-xl bg-emerald-100 flex items-center justify-center">
                                    <svg class="size-5 text-emerald-600" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2">
                                        <path
                                            d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-slate-900 text-sm">Telepon / WhatsApp</h3>
                                    <p class="text-slate-500 text-sm">+62 812-3456-7890</p>
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="flex items-start gap-4">
                                <div
                                    class="size-11 shrink-0 rounded-xl bg-emerald-100 flex items-center justify-center">
                                    <svg class="size-5 text-emerald-600" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2">
                                        <path
                                            d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                                        <polyline points="22,6 12,13 2,6" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-slate-900 text-sm">Email</h3>
                                    <a href="mailto:darindingilan@gmail.com"
                                        class="text-emerald-600 hover:text-emerald-700 text-sm font-medium">darindingilan@gmail.com</a>
                                </div>
                            </div>

                            <!-- Hours -->
                            <div class="flex items-start gap-4">
                                <div
                                    class="size-11 shrink-0 rounded-xl bg-emerald-100 flex items-center justify-center">
                                    <svg class="size-5 text-emerald-600" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10" />
                                        <polyline points="12 6 12 12 16 14" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-slate-900 text-sm">Jam Operasional</h3>
                                    <p class="text-slate-500 text-sm">Senin - Jumat: 08.00 - 17.00 WITA<br>Sabtu: 08.00
                                        - 14.00 WITA</p>
                                </div>
                            </div>
                        </div>

                        <!-- Social Media -->
                        <div class="mt-8 pt-6 border-t border-slate-100">
                            <h3 class="font-semibold text-slate-900 text-sm mb-4">Ikuti Kami</h3>
                            <div class="flex gap-3">
                                <a href="#"
                                    class="size-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-emerald-100 hover:text-emerald-600 transition-all">
                                    <svg class="size-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                                    </svg>
                                </a>
                                <a href="#"
                                    class="size-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-emerald-100 hover:text-emerald-600 transition-all">
                                    <svg class="size-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" />
                                    </svg>
                                </a>
                                <a href="https://wa.me/6282216224715" target="_blank"
                                    class="size-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-green-100 hover:text-green-600 transition-all">
                                    <svg class="size-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="lg:col-span-3">
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-lg shadow-slate-200/50 p-6 lg:p-8"
                        data-aos="fade-left">
                        @if ($submitted)
                            <!-- Success State -->
                            <div class="text-center py-8">
                                <div
                                    class="size-20 bg-gradient-to-br from-emerald-100 to-emerald-200 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg shadow-emerald-500/20">
                                    <svg class="size-10 text-emerald-600" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2.5">
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                        <path d="M22 4 12 14.01l-3-3" />
                                    </svg>
                                </div>
                                <h3 class="text-2xl font-bold text-slate-900 mb-2">Pesan Terkirim!</h3>
                                <p class="text-slate-500 mb-2 max-w-sm mx-auto">Pesan Anda telah berhasil terkirim ke
                                    email kami.</p>
                                <p class="text-sm text-emerald-600 font-medium mb-6">Kami akan segera merespons pesan
                                    Anda.</p>
                                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                                    <a href="mailto:darindingilan@gmail.com"
                                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-emerald-600 text-white font-semibold rounded-xl hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-500/30">
                                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2">
                                            <path
                                                d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                                            <polyline points="22,6 12,13 2,6" />
                                        </svg>
                                        Buka Email
                                    </a>
                                    <button wire:click="resetForm()"
                                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 border border-slate-200 text-slate-700 font-semibold rounded-xl hover:bg-slate-50 transition-all">
                                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2">
                                            <path d="M12 5v14M5 12h14" />
                                        </svg>
                                        Kirim Pesan Lain
                                    </button>
                                </div>
                            </div>
                        @else
                            <!-- Form -->
                            <div class="mb-6">
                                <h3 class="text-xl font-bold text-slate-900">Kirim Pesan</h3>
                                <p class="text-slate-500 text-sm mt-1">Isi formulir di bawah dan kami akan segera
                                    merespons</p>
                            </div>

                            <form wire:submit.prevent="sendViaMail" class="space-y-5">
                                <!-- Name -->
                                <div>
                                    <label for="name"
                                        class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Lengkap <span
                                            class="text-red-500">*</span></label>
                                    <input type="text" id="name" wire:model.live.debounce.300ms="name"
                                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all @error('name') border-red-400 bg-red-50/50 @enderror"
                                        placeholder="Masukkan nama lengkap Anda">
                                    @error('name')
                                        <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                                            <svg class="size-3" viewBox="0 0 24 24" fill="currentColor">
                                                <path
                                                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" />
                                            </svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <!-- Email & Phone -->
                                <div class="grid sm:grid-cols-2 gap-4">
                                    <div>
                                        <label for="email"
                                            class="block text-sm font-semibold text-slate-700 mb-1.5">Email <span
                                                class="text-red-500">*</span></label>
                                        <input type="email" id="email" wire:model.live.debounce.300ms="email"
                                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all @error('email') border-red-400 bg-red-50/50 @enderror"
                                            placeholder="email@contoh.com">
                                        @error('email')
                                            <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                                                <svg class="size-3" viewBox="0 0 24 24" fill="currentColor">
                                                    <path
                                                        d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" />
                                                </svg>
                                                {{ $message }}
                                            </p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="phone"
                                            class="block text-sm font-semibold text-slate-700 mb-1.5">No.
                                            Telepon</label>
                                        <input type="tel" id="phone" wire:model.live.debounce.300ms="phone"
                                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all"
                                            placeholder="08xxxxxxxxxx">
                                    </div>
                                </div>

                                <!-- Subject -->
                                <div>
                                    <label for="subject"
                                        class="block text-sm font-semibold text-slate-700 mb-1.5">Subjek <span
                                            class="text-red-500">*</span></label>
                                    <select id="subject" wire:model.live="subject"
                                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all @error('subject') border-red-400 bg-red-50/50 @enderror">
                                        <option value="">Pilih topik pesan...</option>
                                        <option value="Pertanyaan Produk">Pertanyaan Produk</option>
                                        <option value="Kemitraan Peternak">Kemitraan Peternak</option>
                                        <option value="Pertanyaan Pengiriman">Pertanyaan Pengiriman</option>
                                        <option value="Keluhan Produk">Keluhan Produk</option>
                                        <option value="Kerjasama Bisnis">Kerjasama Bisnis</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                    @error('subject')
                                        <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                                            <svg class="size-3" viewBox="0 0 24 24" fill="currentColor">
                                                <path
                                                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" />
                                            </svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <!-- Message -->
                                <div>
                                    <label for="message"
                                        class="block text-sm font-semibold text-slate-700 mb-1.5">Pesan <span
                                            class="text-red-500">*</span></label>
                                    <textarea id="message" wire:model.live.debounce.300ms="message" rows="5"
                                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all resize-none @error('message') border-red-400 bg-red-50/50 @enderror"
                                        placeholder="Tulis pesan Anda di sini..."></textarea>
                                    @error('message')
                                        <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                                            <svg class="size-3" viewBox="0 0 24 24" fill="currentColor">
                                                <path
                                                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" />
                                            </svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <!-- Submit Buttons -->
                                <div class="pt-2 space-y-3">
                                    <button type="submit"
                                        class="w-full px-6 py-4 bg-gradient-to-r from-emerald-600 to-emerald-700 text-white font-bold rounded-xl hover:from-emerald-700 hover:to-emerald-800 focus:ring-4 focus:ring-emerald-200 shadow-lg shadow-emerald-500/30 transition-all flex items-center justify-center gap-2 group">
                                        <svg class="size-5 group-hover:translate-x-0.5 transition-transform"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2">
                                            <path d="M22 2 11 13" />
                                            <path d="M22 2 15 22l-4-9-9-4 20-7z" />
                                        </svg>
                                        <span>Kirim via Email</span>
                                    </button>

                                    <div class="relative">
                                        <div class="absolute inset-0 flex items-center">
                                            <div class="w-full border-t border-slate-200"></div>
                                        </div>
                                        <div class="relative flex justify-center text-xs">
                                            <span class="px-3 bg-white text-slate-400">atau</span>
                                        </div>
                                    </div>

                                    <a href="https://wa.me/6282216224715?text=Halo%20AbodaFarm%2C%20saya%20ingin%20bertanya..."
                                        target="_blank"
                                        class="w-full px-6 py-4 bg-gradient-to-r from-green-600 to-green-700 text-white font-bold rounded-xl hover:from-green-700 hover:to-green-800 shadow-lg shadow-green-500/30 transition-all flex items-center justify-center gap-2 group">
                                        <svg class="size-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                                        </svg>
                                        <span>Hubungi via WhatsApp</span>
                                    </a>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="py-16 bg-gradient-to-b from-slate-50 to-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <p
                    class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-4 py-1 text-xs font-semibold uppercase tracking-wider text-emerald-700 mb-3">
                    Lokasi</p>
                <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mb-3">Temui Kami</h2>
                <p class="text-slate-500 max-w-xl mx-auto">Kunjungi peternakan kami di Desa Talaud, Sulawesi Utara dan
                    lihat langsung proses pertanian organik kami</p>
            </div>

            <div class="grid lg:grid-cols-3 gap-6">
                <!-- Map -->
                <div
                    class="lg:col-span-2 rounded-2xl overflow-hidden shadow-xl shadow-slate-200/50 border border-slate-200"
                    data-aos="fade-right">
                    <div class="aspect-video lg:aspect-auto lg:h-full min-h-[400px] bg-slate-100">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d127092.54565993707!2d124.82!3d1.33!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3287758564d8a1a1%3A0x4e0d4f8a5c6e0f0a!2sTalaud%2C%20Sulawesi%20Utara!5e0!3m2!1sid!2sid!4v1600000000000!5m2!1sid!2sid"
                            class="w-full h-full border-0" allowfullscreen="" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            title="Lokasi AbodaFarm di Talaud, Sulawesi Utara"></iframe>
                    </div>
                </div>

                <!-- Info Card -->
                <div class="space-y-4">
                    <div
                        class="bg-gradient-to-br from-emerald-600 to-emerald-700 rounded-2xl p-6 text-white shadow-xl shadow-emerald-500/30"
                        data-aos="fade-left" data-aos-delay="0">
                        <div class="size-12 rounded-xl bg-white/20 flex items-center justify-center mb-4">
                            <svg class="size-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                <circle cx="12" cy="10" r="3" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold mb-2">AbodaFarm</h3>
                        <p class="text-emerald-100 text-sm leading-relaxed">
                            Desa Talaud, Kecamatan ERPE<br>
                            Kabupaten Minahasa<br>
                            Sulawesi Utara, Indonesia
                        </p>
                    </div>

                    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-lg"
                        data-aos="fade-left" data-aos-delay="100">
                        <h3 class="font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <svg class="size-5 text-emerald-600" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10" />
                                <polyline points="12 6 12 12 16 14" />
                            </svg>
                            Jam Kunjungan
                        </h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-slate-500">Senin - Jumat</span>
                                <span class="font-medium text-slate-900">08.00 - 17.00 WITA</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Sabtu</span>
                                <span class="font-medium text-slate-900">08.00 - 14.00 WITA</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Minggu</span>
                                <span class="font-medium text-red-500">Tutup</span>
                            </div>
                        </div>
                    </div>

                    <a href="https://maps.google.com/?q=Talaud,SulawesiUtara,Indonesia" target="_blank"
                        class="flex items-center justify-center gap-2 w-full px-6 py-4 bg-white border-2 border-emerald-500 text-emerald-600 font-bold rounded-xl hover:bg-emerald-50 transition-all shadow-lg">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
                            <polyline points="15 3 21 3 21 9" />
                            <line x1="10" y1="14" x2="21" y2="3" />
                        </svg>
                        Buka di Google Maps
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-slate-200 bg-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid md:grid-cols-4 gap-8">
                <!-- Brand -->
                <div class="md:col-span-2">
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5 mb-4">
                        <img src="/icons/MainLogo.png" alt="AbodaFarm Logo" class="h-10 w-auto object-contain">
                        <span class="block text-xl font-black tracking-tight text-slate-900">Aboda<span class="text-emerald-600">Farm</span></span>
                    </a>
                    <p class="text-slate-500 text-sm leading-relaxed max-w-sm">
                        Menyediakan produk peternakan organik berkualitas tinggi langsung dari peternak lokal Sulawesi
                        Utara. 100% halal dan segar setiap hari.
                    </p>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="font-bold text-slate-900 mb-4">Navigasi</h4>
                    <ul class="space-y-2">
                        <li><a href="{{ route('home') }}"
                                class="text-slate-500 hover:text-emerald-600 text-sm transition-colors">Beranda</a>
                        </li>
                        <li><a href="{{ route('home') }}#produk"
                                class="text-slate-500 hover:text-emerald-600 text-sm transition-colors">Produk</a></li>
                        <li><a href="{{ route('home') }}#tentang"
                                class="text-slate-500 hover:text-emerald-600 text-sm transition-colors">Tentang
                                Kami</a></li>
                        <li><a href="{{ route('contact') }}" class="text-emerald-600 font-medium text-sm">Hubungi
                                Kami</a></li>
                    </ul>
                </div>

                <!-- Contact -->
                <div>
                    <h4 class="font-bold text-slate-900 mb-4">Kontak</h4>
                    <ul class="space-y-2 text-sm text-slate-500">
                        <li class="flex items-center gap-2">
                            <svg class="size-4 text-emerald-500" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path
                                    d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                            </svg>
                            +62 812-3456-7890
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="size-4 text-emerald-500" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                                <polyline points="22,6 12,13 2,6" />
                            </svg>
                            darindingilan@gmail.com
                        </li>
                    </ul>
                </div>
            </div>

            <div
                class="border-t border-slate-100 mt-8 pt-8 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-sm text-slate-400">© {{ now()->year }} AbodaFarm. Hak cipta dilindungi.</p>
                <div class="flex items-center gap-4">
                    <a href="#" class="text-slate-400 hover:text-emerald-600 transition-colors">
                        <svg class="size-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                        </svg>
                    </a>
                    <a href="#" class="text-slate-400 hover:text-emerald-600 transition-colors">
                        <svg class="size-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" />
                        </svg>
                    </a>
                    <a href="https://wa.me/6281234567890"
                        class="text-slate-400 hover:text-green-500 transition-colors">
                        <svg class="size-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </footer>
</div>
