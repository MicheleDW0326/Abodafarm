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
                        class="relative text-sm font-medium transition-colors text-emerald-600">
                        Tentang
                        <span class="absolute -bottom-1 left-0 h-0.5 w-full rounded-full bg-emerald-500"></span>
                    </a>
                    <a href="{{ route('home') }}#faq"
                        class="relative text-sm font-medium transition-colors"
                        :class="activeRoute.includes('/faq') ? 'text-emerald-600' : 'text-slate-600 hover:text-emerald-600'">
                        FAQ
                        <span x-show="activeRoute.includes('/faq')" class="absolute -bottom-1 left-0 h-0.5 w-full rounded-full bg-emerald-500"></span>
                    </a>
                    <a href="{{ route('contact') }}"
                        class="relative text-sm font-medium transition-colors"
                        :class="activeRoute.includes('/contact') ? 'text-emerald-600' : 'text-slate-600 hover:text-emerald-600'">
                        Hubungi
                        <span x-show="activeRoute.includes('/contact')" class="absolute -bottom-1 left-0 h-0.5 w-full rounded-full bg-emerald-500"></span>
                    </a>
                </div>

                <!-- CTA Button -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('home') }}#produk" class="hidden sm:inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-emerald-500/30 transition-all hover:bg-emerald-700 hover:shadow-emerald-500/50 hover:-translate-y-0.5">
                        <i class="fa-solid fa-shopping-bag"></i>
                        Belanja Sekarang
                    </a>

                    <!-- Mobile Menu Button -->
                    <button type="button" class="md:hidden grid size-10 place-content-center rounded-lg border border-slate-200 text-slate-600">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="relative py-20 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-emerald-600 via-emerald-500 to-sky-600"></div>
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGRlZnM+PHBhdHRlcm4gaWQ9ImdyaWQiIHdpZHRoPSI2MCIgaGVpZ2h0PSI2MCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSI+PHBhdGggZD0iTSAxMCAwIEwgMCAwIDAgMTAgTSAxMCA2MCBMIDAgNjAgTDYgNjAiIGZpbGw9Im5vbmUiIHN0cm9rZT0id2hpdGUiIHN0cm9rZS1vcGFjaXR5PSIwLjA1IiBzdHJva2Utd2lkdGg9IjEiLz48L3BhdHRlcm4+PC9kZWZzPjxyZWN0IHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiIGZpbGw9InVybCgjZ3JpZCkiLz48L3N2Zz4=')] opacity-20"></div>
        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p class="inline-flex rounded-full border border-white/30 bg-white/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-white backdrop-blur mb-4">
                Tentang Kami
            </p>
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-white mb-4">Cerita AbodaFarm</h1>
            <p class="text-lg md:text-xl text-white/90 max-w-2xl mx-auto">Membangun pertanian berkelanjutan untuk masa depan yang lebih baik melalui inovasi dan kemitraan dengan peternak lokal</p>
        </div>
    </section>

    <!-- Story Section -->
    <section class="py-16 md:py-24 -mt-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div class="bg-white rounded-2xl border border-slate-100 shadow-lg shadow-slate-200/50 p-8">
                    <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-emerald-700 mb-4">Cerita Kami</span>
                    <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mb-6">Dari Peternakan Kecil Menjadi Innovations Farming</h2>
                    <div class="space-y-4 text-slate-600 leading-relaxed">
                        <p>
                            AbodaFarm didirikan pada tahun 2018 dengan visi sederhana: menghadirkan produk peternakan organik berkualitas tinggi yang dihasilkan secara berkelanjutan dan ramah lingkungan.
                        </p>
                        <p>
                            Bermula dari peternakan sapi tradisional di desa Talaud, Sulawesi Utara, kami berkembang menjadi sebuah ekosistem pertanian modern yang mengutamakan kesejahteraan hewan, kualitas produk, dan keberlanjutan lingkungan.
                        </p>
                        <p>
                            Saat ini, kami bermitra dengan lebih dari 50 peternak lokal di seluruh Sulawesi Utara, semua berkomitmen pada prinsip pertanian organik, berkelanjutan, dan etis.
                        </p>
                    </div>
                </div>
                <div class="relative">
                    <div class="aspect-square rounded-2xl overflow-hidden shadow-2xl shadow-slate-200/50 border border-slate-200">
                        <div class="w-full h-full bg-gradient-to-br from-emerald-400 to-green-600 flex items-center justify-center">
                            <div class="text-center text-white">
                                <svg class="w-24 h-24 mx-auto mb-4 opacity-80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                                </svg>
                                <p class="text-sm font-medium opacity-70">AbodaFarm - Talaud</p>
                            </div>
                        </div>
                    </div>
                    <div class="absolute -bottom-6 -right-6 bg-white rounded-xl shadow-xl shadow-emerald-500/20 border border-emerald-100 p-6 max-w-[200px]">
                        <div class="text-4xl font-black text-emerald-600 mb-1">50+</div>
                        <div class="text-sm text-slate-600 font-medium">Peternak Mitra</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission & Vision -->
    <section class="py-16 md:py-24 bg-gradient-to-b from-slate-50 to-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <p class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-emerald-700 mb-4">Misi & Visi</p>
                <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mb-3">Apa yang Kami Percaya</h2>
                <p class="text-slate-500 max-w-xl mx-auto">Prinsip-prinsip yang menjadi fondasi bisnis kami dalam membangun pertanian organik yang berkelanjutan</p>
            </div>

            <div class="grid md:grid-cols-2 gap-8">
                <!-- Visi -->
                <div class="bg-white rounded-2xl p-8 shadow-lg shadow-slate-200/50 border border-slate-100 hover:shadow-xl transition-all"
                    data-aos="fade-right">
                    <div class="size-14 bg-gradient-to-br from-emerald-100 to-emerald-200 rounded-xl flex items-center justify-center mb-6">
                        <svg class="size-7 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                            <path d="M2 12h20"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-4">Visi Kami</h3>
                    <p class="text-slate-600 leading-relaxed">
                        Menjadi pioneer pertanian organik di Indonesia, menciptakan ekosistem pertanian berkelanjutan yang menghubungkan peternak lokal dengan konsumen yang peduli akan kualitas dan keberlanjutan.
                    </p>
                </div>

                <!-- Misi -->
                <div class="bg-white rounded-2xl p-8 shadow-lg shadow-slate-200/50 border border-slate-100 hover:shadow-xl transition-all"
                    data-aos="fade-left">
                    <div class="size-14 bg-gradient-to-br from-emerald-100 to-emerald-200 rounded-xl flex items-center justify-center mb-6">
                        <svg class="size-7 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 20V10"/>
                            <path d="M18 20V4"/>
                            <path d="M6 20v-4"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-4">Misi Kami</h3>
                    <ul class="space-y-3 text-slate-600">
                        <li class="flex items-start gap-3">
                            <svg class="size-5 text-emerald-500 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 6 9 17l-5-5"/>
                            </svg>
                            <span>Menghasilkan produk peternakan premium 100% organik</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="size-5 text-emerald-500 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 6 9 17l-5-5"/>
                            </svg>
                            <span>Mendukung ekonomi lokal peternak rakyat</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="size-5 text-emerald-500 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 6 9 17l-5-5"/>
                            </svg>
                            <span>Praktik pertanian ramah lingkungan</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="size-5 text-emerald-500 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 6 9 17l-5-5"/>
                            </svg>
                            <span>Edukasi masyarakat tentang pentingnya organik</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Values -->
    <section class="py-16 md:py-24">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <p class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-emerald-700 mb-4">Nilai Kami</p>
                <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mb-3">Prinsip yang Menggerakkan Kami</h2>
                <p class="text-slate-500 max-w-xl mx-auto">Empat nilai utama yang menjadi panduan dalam setiap keputusan dan tindakan kami</p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="text-center p-6 rounded-2xl bg-white shadow-lg shadow-slate-200/50 border border-slate-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group"
                    data-aos="fade-up" data-aos-delay="0">
                    <div class="size-16 bg-gradient-to-br from-emerald-100 to-emerald-200 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                        <svg class="size-8 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Kualitas</h3>
                    <p class="text-sm text-slate-600">Standar tertinggi untuk setiap produk yang kami hasilkan</p>
                </div>

                <div class="text-center p-6 rounded-2xl bg-white shadow-lg shadow-slate-200/50 border border-slate-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group"
                    data-aos="fade-up" data-aos-delay="100">
                    <div class="size-16 bg-gradient-to-br from-emerald-100 to-emerald-200 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                        <svg class="size-8 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Keberlanjutan</h3>
                    <p class="text-sm text-slate-600">Praktik pertanian yang menjaga lingkungan untuk generasi depan</p>
                </div>

                <div class="text-center p-6 rounded-2xl bg-white shadow-lg shadow-slate-200/50 border border-slate-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group"
                    data-aos="fade-up" data-aos-delay="200">
                    <div class="size-16 bg-gradient-to-br from-emerald-100 to-emerald-200 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                        <svg class="size-8 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Kemitraan</h3>
                    <p class="text-sm text-slate-600">Kolaborasi yang adil dengan peternak lokal</p>
                </div>

                <div class="text-center p-6 rounded-2xl bg-white shadow-lg shadow-slate-200/50 border border-slate-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group"
                    data-aos="fade-up" data-aos-delay="300">
                    <div class="size-16 bg-gradient-to-br from-emerald-100 to-emerald-200 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                        <svg class="size-8 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                            <path d="M22 4 12 14.01l-3-3"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Transparansi</h3>
                    <p class="text-sm text-slate-600">Keterbukaan dari ladang hingga meja makan</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="py-16 md:py-24 bg-gradient-to-br from-slate-900 to-slate-800 text-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <p class="inline-flex rounded-full border border-emerald-500/30 bg-emerald-500/20 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-emerald-400 mb-4">Tim Kami</p>
                <h2 class="text-3xl md:text-4xl font-bold mb-3">Orang-Orang di Balik AbodaFarm</h2>
                <p class="text-slate-400 max-w-xl mx-auto">Tim profesional yang berdedikasi untuk menghadirkan produk peternakan organik berkualitas</p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="text-center group"
                    data-aos="fade-up" data-aos-delay="0">
                    <div class="size-32 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-full mx-auto mb-4 flex items-center justify-center text-4xl font-bold text-white shadow-lg shadow-emerald-500/30 group-hover:scale-105 transition-transform">
                        RK
                    </div>
                    <h3 class="text-xl font-bold mb-1">Robert Katu</h3>
                    <p class="text-emerald-400 mb-3 font-medium">Founder & CEO</p>
                    <p class="text-slate-400 text-sm max-w-xs mx-auto">Visoner di balik AbodaFarm dengan pengalaman 15+ tahun di industri pertanian organik</p>
                </div>

                <div class="text-center group"
                    data-aos="fade-up" data-aos-delay="100">
                    <div class="size-32 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-full mx-auto mb-4 flex items-center justify-center text-4xl font-bold text-white shadow-lg shadow-emerald-500/30 group-hover:scale-105 transition-transform">
                        SM
                    </div>
                    <h3 class="text-xl font-bold mb-1">Sarah Mokoagow</h3>
                    <p class="text-emerald-400 mb-3 font-medium">Head of Operations</p>
                    <p class="text-slate-400 text-sm max-w-xs mx-auto">Memastikan setiap operasi berjalan lancar dan efisien dari peternakan hingga pengiriman</p>
                </div>

                <div class="text-center group"
                    data-aos="fade-up" data-aos-delay="200">
                    <div class="size-32 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-full mx-auto mb-4 flex items-center justify-center text-4xl font-bold text-white shadow-lg shadow-emerald-500/30 group-hover:scale-105 transition-transform">
                        DL
                    </div>
                    <h3 class="text-xl font-bold mb-1">David Lumenta</h3>
                    <p class="text-emerald-400 mb-3 font-medium">Chief Veterinary</p>
                    <p class="text-slate-400 text-sm max-w-xs mx-auto">Ahli kesehatan hewan yang memastikan kesejahteraan dan kesehatan ternak kami</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-16 bg-white border-y border-slate-100">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 text-center">
                <div>
                    <div class="text-4xl md:text-5xl font-black text-emerald-600 mb-2">50+</div>
                    <p class="text-slate-500 text-sm font-medium">Peternak Mitra</p>
                </div>
                <div>
                    <div class="text-4xl md:text-5xl font-black text-emerald-600 mb-2">2018</div>
                    <p class="text-slate-500 text-sm font-medium">Tahun Berdiri</p>
                </div>
                <div>
                    <div class="text-4xl md:text-5xl font-black text-emerald-600 mb-2">100%</div>
                    <p class="text-slate-500 text-sm font-medium">Produk Organik</p>
                </div>
                <div>
                    <div class="text-4xl md:text-5xl font-black text-emerald-600 mb-2">5K+</div>
                    <p class="text-slate-500 text-sm font-medium">Pelanggan Puas</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-16 md:py-24 bg-gradient-to-r from-emerald-600 to-emerald-700">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">Siap Berkolaborasi dengan Kami?</h2>
            <p class="text-lg text-white/90 mb-8 max-w-2xl mx-auto">Hubungi kami untuk kemitraan atau kunjungi peternakan kami untuk melihat langsung bagaimana kami beroperasi.</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('contact') }}" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-white text-emerald-700 font-bold rounded-xl hover:bg-slate-100 transition-all shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/50">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    </svg>
                    Hubungi Kami
                </a>
                <a href="{{ route('home') }}#produk" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-transparent border-2 border-white text-white font-bold rounded-xl hover:bg-white/10 transition-all">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                    Lihat Produk
                </a>
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
                        Menyediakan produk peternakan organik berkualitas tinggi langsung dari peternak lokal Sulawesi Utara. 100% halal dan segar setiap hari.
                    </p>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="font-bold text-slate-900 mb-4">Navigasi</h4>
                    <ul class="space-y-2">
                        <li><a href="{{ route('home') }}" class="text-slate-500 hover:text-emerald-600 text-sm transition-colors">Beranda</a></li>
                        <li><a href="{{ route('home') }}#produk" class="text-slate-500 hover:text-emerald-600 text-sm transition-colors">Produk</a></li>
                        <li><a href="{{ route('home') }}#tentang" class="text-emerald-600 font-medium text-sm">Tentang Kami</a></li>
                        <li><a href="{{ route('contact') }}" class="text-slate-500 hover:text-emerald-600 text-sm transition-colors">Hubungi Kami</a></li>
                    </ul>
                </div>

                <!-- Contact -->
                <div>
                    <h4 class="font-bold text-slate-900 mb-4">Kontak</h4>
                    <ul class="space-y-2 text-sm text-slate-500">
                        <li class="flex items-center gap-2">
                            <svg class="size-4 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                            +62 812-3456-7890
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="size-4 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                            darindingilan@gmail.com
                        </li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-slate-100 mt-8 pt-8 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-sm text-slate-400">© {{ now()->year }} AbodaFarm. Hak cipta dilindungi.</p>
                <div class="flex items-center gap-4">
                    <a href="#" class="text-slate-400 hover:text-emerald-600 transition-colors">
                        <svg class="size-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                    <a href="#" class="text-slate-400 hover:text-emerald-600 transition-colors">
                        <svg class="size-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z"/></svg>
                    </a>
                    <a href="https://wa.me/6281234567890" class="text-slate-400 hover:text-green-500 transition-colors">
                        <svg class="size-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </footer>
</div>
