<div
    x-data="{
        open: false,
        activeCategory: null,
        searchQuery: '',
        init() {
            console.log('Help Center initialized');
        },
        categories: [
            {
                id: 'book-livestock',
                icon: 'cattle',
                title: 'Cara Booking Ternak',
                description: 'Pelajari cara booking hewan ternak dengan mudah',
                content: {
                    steps: [
                        { num: 1, title: 'Pilih Ternak', desc: 'Jelajahi galeri dan pilih hewan ternak yang Anda inginkan' },
                        { num: 2, title: 'Klik Booking', desc: 'Tekan tombol Booking pada produk yang dipilih' },
                        { num: 3, title: 'Isi Form', desc: 'Lengkapi formulir booking dengan data yang valid' },
                        { num: 4, title: 'Tunggu Konfirmasi', desc: 'Tim kami akan menghubungi Anda untuk konfirmasi' },
                        { num: 5, title: 'Bayar Deposit', desc: 'Lakukan pembayaran deposit via transfer bank' },
                        { num: 6, title: 'Proses Pengiriman', desc: 'Ternak siap dikirim setelah deposit diterima' }
                    ]
                }
            },
            {
                id: 'buy-fresh-meat',
                icon: 'meat',
                title: 'Cara Beli Daging Segar',
                description: 'Guide untuk pembelian daging segar di toko',
                content: {
                    steps: [
                        { num: 1, title: 'Pilih Produk', desc: 'Buka halaman Toko dan pilih produk daging segar' },
                        { num: 2, title: 'Tambah ke Keranjang', desc: 'Klik tombol Tambah ke Keranjang' },
                        { num: 3, title: 'Checkout', desc: 'Periksa keranjang dan lanjut ke checkout' },
                        { num: 4, title: 'Bayar via Midtrans', desc: 'Pilih metode pembayaran dan selesaikan pembayaran' },
                        { num: 5, title: 'Pesanan Diproses', desc: 'Tim kami akan memproses pesanan Anda' },
                        { num: 6, title: 'Daging Diantar', desc: 'Daging segar dikirim langsung ke alamat Anda' }
                    ]
                }
            },
            {
                id: 'payment',
                icon: 'payment',
                title: 'Panduan Pembayaran',
                description: 'Cara pembayaran dan status pembayaran',
                content: {
                    intro: 'Kami menggunakan Midtrans sebagai payment gateway untuk transaksi yang aman dan mudah.',
                    statuses: [
                        { status: 'Pending', color: 'amber', desc: 'Pembayaran sedang menunggu. Silakan selesaikan pembayaran sebelum batas waktu.' },
                        { status: 'Paid', color: 'emerald', desc: 'Pembayaran berhasil. Pesanan Anda sedang diproses.' },
                        { status: 'Expired', color: 'slate', desc: 'Batas waktu pembayaran telah habis. Silakan buat pesanan baru.' },
                        { status: 'Cancelled', color: 'red', desc: 'Pembayaran dibatalkan. Silakan coba lagi atau hubungi customer service.' }
                    ],
                    methods: [
                        'Transfer Bank (BCA, Mandiri, BNI, BRI)',
                        'E-Wallet (GoPay, OVO, DANA, ShopeePay)',
                        'Kartu Kredit/Debit (Visa, Mastercard)',
                        'QRIS'
                    ]
                }
            },
            {
                id: 'delivery',
                icon: 'truck',
                title: 'Informasi Pengiriman',
                description: 'Detail pengiriman ternak dan daging segar',
                content: {
                    livestock: {
                        title: 'Pengiriman Ternak',
                        points: [
                            'Ternak dikirim setelah pembayaran deposit diterima',
                            'Pengiriman menggunakan kendaraan khusus ternak',
                            'Adajamin kesehatan hewan selama perjalanan',
                            'Waktu pengiriman 1-7 hari kerja tergantung lokasi',
                            'Biaya pengiriman dihitung berdasarkan jarak'
                        ]
                    },
                    freshMeat: {
                        title: 'Pengiriman Daging Segar',
                        points: [
                            'Daging dikemas dalam packaging styrofoam + es',
                            'Pengiriman menggunakan kendaraan berpendingin',
                            'Garansi daging sampai dalam kondisi segar',
                            'Pengiriman same-day untuk area tertentu',
                            'Tracking pengiriman real-time'
                        ]
                    }
                }
            },
            {
                id: 'faq',
                icon: 'faq',
                title: 'Pertanyaan Umum',
                description: 'Jawaban untuk pertanyaan yang sering diajukan',
                content: {
                    faqs: [
                        { q: 'Apakah saya bisa beli tanpa login?', a: 'Ya, Anda bisa brows produk tanpa login. Namun, untuk melakukan pemesanan dan checkout, Anda harus memiliki akun dan login terlebih dahulu.' },
                        { q: 'Bagaimana cara melacak pesanan saya?', a: 'Anda dapat melacak pesanan melalui menu Pesanan di dashboard Anda setelah login. Untuk booking ternak, kami akan menghubungi Anda via WhatsApp.' },
                        { q: 'Bagaimana cara membatalkan pesanan?', a: 'Hubungi customer service kami melalui WhatsApp atau email sebelum pesanan diproses. Deposit untuk booking ternak tidak dapat dikembalikan.' },
                        { q: 'Bagaimana sistem pembayaran bekerja?', a: 'Kami menggunakan Midtrans untuk payment gateway. Pilih metode pembayaran yang tersedia, selesaikan pembayaran, dan sistem akan otomatis mengkonfirmasi.' },
                        { q: 'Apakah ada garansi untuk produk?', a: 'Untuk daging segar, kami memberikan garansi jika daging tidak segar saat diterima. Untuk ternak, garansi kesehatan berlaku selama 7 hari setelah diterima.' },
                        { q: 'Metode pembayaran apa saja yang tersedia?', a: 'Kami menerima transfer bank, e-wallet (GoPay, OVO, DANA, ShopeePay), kartu kredit/debit, dan QRIS melalui Midtrans.' },
                        { q: 'Berapa lama pengiriman dilakukan?', a: 'Pengiriman daging segar 1-2 hari kerja. Pengiriman ternak 1-7 hari kerja tergantung lokasi dan kesiapan transportasi.' },
                        { q: 'Bagaimana cara menghubungi customer service?', a: 'Hubungi kami via WhatsApp di +62 812-xxxx-xxxx, email ke info@abodafarm.com, atau melalui fitur Kontak di website ini.' }
                    ]
                }
            },
            {
                id: 'contact',
                icon: 'contact',
                title: 'Hubungi Customer Service',
                description: 'Tim kami siap membantu Anda',
                content: {
                    contact: {
                        whatsapp: '+62 812-3456-7890',
                        email: 'info@abodafarm.com',
                        hours: '08.00 - 17.00 WIB',
                        response: 'Kami merespons dalam 1x24 jam kerja'
                    }
                }
            }
        ],
        filteredCategories() {
            if (!this.searchQuery) return this.categories;
            const query = this.searchQuery.toLowerCase();
            return this.categories.filter(c =>
                c.title.toLowerCase().includes(query) ||
                c.description.toLowerCase().includes(query) ||
                (c.content.faqs && c.content.faqs.some(f => f.q.toLowerCase().includes(query)))
            );
        },
        openCategory(cat) {
            this.activeCategory = cat;
        },
        backToCategories() {
            this.activeCategory = null;
        }
    }"
    x-init="
        $watch('open', value => document.body.style.overflow = value ? 'hidden' : '');
        console.log('Help Center Alpine.js initialized');
        document.addEventListener('toggle-help', () => {
            console.log('toggle-help event received');
            open = true;
        });
    "
>
    <!-- Backdrop -->
    <div x-show="open" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        @click="open = false" @keydown.escape.window="open = false"
        class="fixed inset-0 z-[60] bg-slate-900/60 backdrop-blur-sm"></div>

    <!-- Modal -->
    <div x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-4"
        :style="open ? 'display: flex; align-items: center; justify-content: center;' : 'display: none;'"
        class="fixed inset-0 z-[70] overflow-y-auto"
        @click.stop>

        <!-- Modal Content -->
        <div class="relative w-full my-8 mx-auto rounded-2xl bg-white shadow-2xl max-w-2xl overflow-hidden">

        <!-- Header -->
        <div class="sticky top-0 z-10 border-b border-slate-100 bg-gradient-to-r from-emerald-600 to-emerald-500 px-6 py-5">
            <!-- Debug: remove this after testing -->
            <div x-show="open" class="mb-2 rounded bg-yellow-300 px-2 py-1 text-xs text-yellow-800">Debug: Modal is open!</div>
            <div class="flex items-center justify-between">
                <div x-show="!activeCategory" class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/20">
                        <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                            <line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-white">Help Center</h2>
                        <p class="text-sm text-emerald-100">How can we help you today?</p>
                    </div>
                </div>
                <div x-show="activeCategory" class="flex items-center gap-3">
                    <button @click="backToCategories()"
                        class="flex h-10 w-10 items-center justify-center rounded-full bg-white/20 text-white transition hover:bg-white/30">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <h2 class="text-xl font-bold text-white" x-text="activeCategory?.title"></h2>
                </div>
                <button @click="open = false"
                    class="flex h-10 w-10 items-center justify-center rounded-full bg-white/20 text-white transition hover:bg-white/30">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>

            <!-- Search (only on main view) -->
            <div x-show="!activeCategory" class="mt-4">
                <div class="relative">
                    <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="M21 21l-4.35-4.35"/>
                    </svg>
                    <input type="text" x-model="searchQuery"
                        placeholder="Cari bantuan..."
                        class="w-full rounded-xl border-0 bg-white/20 py-3 pl-12 pr-4 text-white placeholder-emerald-200 shadow-inner backdrop-blur-sm focus:bg-white/30 focus:outline-none focus:ring-2 focus:ring-white/50">
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="h-[calc(100%-140px)] overflow-y-auto p-6">
            <!-- Categories Grid -->
            <div x-show="!activeCategory" class="grid gap-4 sm:grid-cols-2">
                <template x-for="cat in filteredCategories()" :key="cat.id">
                    <button @click="openCategory(cat)"
                        class="group relative overflow-hidden rounded-xl border border-slate-200 bg-white p-5 text-left transition-all hover:border-emerald-200 hover:shadow-lg hover:shadow-emerald-100">
                        <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 transition-colors group-hover:bg-emerald-100">
                            <!-- Cattle Icon -->
                            <template x-if="cat.icon === 'cattle'">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z"/>
                                </svg>
                            </template>
                            <!-- Meat Icon -->
                            <template x-if="cat.icon === 'meat'">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.87c1.355 0 2.697.055 4.024.165C17.155 8.51 18 9.473 18 10.608v2.513m-3-4.87v-1.5m-6 1.5v-1.5m12 9.75l-1.5.75a3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-3 0L3 16.5m15-3.38a48.474 48.474 0 00-6-.37c-2.032 0-4.034.125-6 .37m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.17c0 .62-.504 1.124-1.125 1.124H4.125A1.125 1.125 0 013 20.625v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 016 13.12M12.265 3.11a.375.375 0 11-.53 0L12 2.845l.265.265zm-3 0a.375.375 0 11-.53 0L9 2.845l.265.265zm6 0a.375.375 0 11-.53 0L15 2.845l.265.265z"/>
                                </svg>
                            </template>
                            <!-- Payment Icon -->
                            <template x-if="cat.icon === 'payment'">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/>
                                </svg>
                            </template>
                            <!-- Truck Icon -->
                            <template x-if="cat.icon === 'truck'">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>
                                </svg>
                            </template>
                            <!-- FAQ Icon -->
                            <template x-if="cat.icon === 'faq'">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z"/>
                                </svg>
                            </template>
                            <!-- Contact Icon -->
                            <template x-if="cat.icon === 'contact'">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.355 0-2.697-.056-4.024-.166-1.201-.209-2.206-1.076-2.206-2.164V9.504c1.172-1.025 3.071-1.025 4.242 0l2.261 2.26c1.07-1.005 2.528-1.553 4.008-1.553.825 0 1.622.247 2.312.553M6.75 6.25h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 17.25h.75"/>
                                </svg>
                            </template>
                        </div>
                        <h3 class="mb-1 font-semibold text-slate-800" x-text="cat.title"></h3>
                        <p class="text-sm text-slate-500" x-text="cat.description"></p>
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 opacity-0 transition group-hover:opacity-100">
                            <svg class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                            </svg>
                        </div>
                    </button>
                </template>
            </div>

            <!-- Category Detail -->
            <div x-show="activeCategory" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-x-4"
                x-transition:enter-end="opacity-100 translate-x-0">

                <!-- How to Book Livestock / Buy Fresh Meat -->
                <template x-if="activeCategory?.id === 'book-livestock' || activeCategory?.id === 'buy-fresh-meat'">
                    <div class="space-y-4">
                        <template x-for="step in activeCategory?.content?.steps" :key="step.num">
                            <div class="flex gap-4">
                                <div class="flex flex-shrink-0 flex-col items-center">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 font-bold" x-text="step.num"></div>
                                    <div class="h-full w-0.5 bg-emerald-100 last:hidden"></div>
                                </div>
                                <div class="mb-6 flex-1 pb-4">
                                    <h4 class="font-semibold text-slate-800" x-text="step.title"></h4>
                                    <p class="mt-1 text-sm text-slate-600" x-text="step.desc"></p>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <!-- Payment Guide -->
                <template x-if="activeCategory?.id === 'payment'">
                    <div class="space-y-6">
                        <p class="text-slate-600" x-text="activeCategory?.content?.intro"></p>
                        <div class="space-y-4">
                            <h4 class="font-semibold text-slate-800">Status Pembayaran</h4>
                            <template x-for="s in activeCategory?.content?.statuses" :key="s.status">
                                <div class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full"
                                        :class="{
                                            'bg-amber-100 text-amber-600': s.color === 'amber',
                                            'bg-emerald-100 text-emerald-600': s.color === 'emerald',
                                            'bg-slate-100 text-slate-600': s.color === 'slate',
                                            'bg-red-100 text-red-600': s.color === 'red'
                                        }">
                                        <svg x-show="s.status === 'Pending'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <svg x-show="s.status === 'Paid'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <svg x-show="s.status === 'Expired'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <svg x-show="s.status === 'Cancelled'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </div>
                                    <div>
                                        <span class="font-medium text-slate-800" x-text="s.status"></span>
                                        <p class="mt-1 text-sm text-slate-600" x-text="s.desc"></p>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <div>
                            <h4 class="mb-3 font-semibold text-slate-800">Metode Pembayaran</h4>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="m in activeCategory?.content?.methods" :key="m">
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-sm text-slate-600" x-text="m"></span>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Delivery Information -->
                <template x-if="activeCategory?.id === 'delivery'">
                    <div class="space-y-6">
                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-5">
                            <h4 class="flex items-center gap-2 font-semibold text-amber-800">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z"/></svg>
                                <span x-text="activeCategory?.content?.livestock?.title"></span>
                            </h4>
                            <ul class="mt-3 space-y-2">
                                <template x-for="p in activeCategory?.content?.livestock?.points" :key="p">
                                    <li class="flex items-start gap-2 text-sm text-amber-700">
                                        <svg class="mt-0.5 h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span x-text="p"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                        <div class="rounded-xl border border-blue-200 bg-blue-50 p-5">
                            <h4 class="flex items-center gap-2 font-semibold text-blue-800">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.87c1.355 0 2.697.055 4.024.165C17.155 8.51 18 9.473 18 10.608v2.513m-3-4.87v-1.5m-6 1.5v-1.5m12 9.75l-1.5.75a3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-3 0L3 16.5m15-3.38a48.474 48.474 0 00-6-.37c-2.032 0-4.034.125-6 .37m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.17c0 .62-.504 1.124-1.125 1.124H4.125A1.125 1.125 0 013 20.625v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 016 13.12M12.265 3.11a.375.375 0 11-.53 0L12 2.845l.265.265zm-3 0a.375.375 0 11-.53 0L9 2.845l.265.265zm6 0a.375.375 0 11-.53 0L15 2.845l.265.265z"/></svg>
                                <span x-text="activeCategory?.content?.freshMeat?.title"></span>
                            </h4>
                            <ul class="mt-3 space-y-2">
                                <template x-for="p in activeCategory?.content?.freshMeat?.points" :key="p">
                                    <li class="flex items-start gap-2 text-sm text-blue-700">
                                        <svg class="mt-0.5 h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span x-text="p"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>
                </template>

                <!-- FAQ Accordion -->
                <template x-if="activeCategory?.id === 'faq'">
                    <div x-data="{ openFaq: null }" class="space-y-3">
                        <template x-for="(faq, index) in activeCategory?.content?.faqs" :key="index">
                            <div class="overflow-hidden rounded-xl border border-slate-200">
                                <button @click="openFaq === index ? openFaq = null : openFaq = index"
                                    class="flex w-full items-center justify-between bg-white p-4 text-left transition hover:bg-slate-50">
                                    <span class="font-medium text-slate-800" x-text="faq.q"></span>
                                    <svg class="h-5 w-5 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': openFaq === index }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                                <div x-show="openFaq === index"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 max-h-0"
                                    x-transition:enter-end="opacity-100 max-h-96"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 max-h-96"
                                    x-transition:leave-end="opacity-0 max-h-0"
                                    class="overflow-hidden border-t border-slate-100 bg-slate-50 p-4">
                                    <p class="text-sm text-slate-600" x-text="faq.a"></p>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <!-- Contact -->
                <template x-if="activeCategory?.id === 'contact'">
                    <div class="space-y-6">
                        <p class="text-slate-600">Tim customer service kami siap membantu Anda. Hubungi kami melalui:</p>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <a href="https://wa.me/6281234567890" target="_blank"
                                class="flex items-center gap-4 rounded-xl border border-emerald-200 bg-emerald-50 p-4 transition hover:bg-emerald-100">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-500 text-white">
                                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                </div>
                                <div>
                                    <p class="font-medium text-emerald-800">WhatsApp</p>
                                    <p class="text-sm text-emerald-600" x-text="activeCategory?.content?.contact?.whatsapp"></p>
                                </div>
                            </a>
                            <a href="mailto:info@abodafarm.com"
                                class="flex items-center gap-4 rounded-xl border border-blue-200 bg-blue-50 p-4 transition hover:bg-blue-100">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-500 text-white">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                                </div>
                                <div>
                                    <p class="font-medium text-blue-800">Email</p>
                                    <p class="text-sm text-blue-600" x-text="activeCategory?.content?.contact?.email"></p>
                                </div>
                            </a>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-slate-500">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Jam operasional: <span x-text="activeCategory?.content?.contact?.hours"></span></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Footer - Still need help -->
        <div class="sticky bottom-0 border-t border-slate-100 bg-gradient-to-r from-slate-50 to-slate-100 p-4">
            <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                <p class="text-sm text-slate-600">Still need help?</p>
                <div class="flex items-center gap-3">
                    <a href="https://wa.me/6281234567890" target="_blank"
                        class="flex items-center gap-2 rounded-full bg-green-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-green-600">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        WhatsApp
                    </a>
                    <a href="mailto:info@abodafarm.com"
                        class="flex items-center gap-2 rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                        Email
                    </a>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>
