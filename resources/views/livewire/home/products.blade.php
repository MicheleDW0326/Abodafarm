<section id="produk" class="bg-white py-16" x-data="{
    activeTab: 'live',
    inquiryName: null,
    showModal: false,
    product: null,
    images: [],
    activeImageIndex: 0,

    openProductDetail(productId, productData) {
        this.product = productData;
        this.images = productData.images || [];
        if (this.images.length === 0 && productData.image) {
            this.images = [productData.image];
        }
        this.activeImageIndex = 0;
        this.showModal = true;
        document.body.style.overflow = 'hidden';
    },

    closeModal() {
        this.showModal = false;
        this.product = null;
        document.body.style.overflow = '';
    },

    nextImage() {
        if (this.images.length > 1) {
            this.activeImageIndex = (this.activeImageIndex + 1) % this.images.length;
        }
    },

    prevImage() {
        if (this.images.length > 1) {
            this.activeImageIndex = (this.activeImageIndex - 1 + this.images.length) % this.images.length;
        }
    },

    getStatusColor(status) {
        const colors = {
            'available': 'bg-emerald-100 text-emerald-700',
            'pre_order': 'bg-amber-100 text-amber-700',
            'in_care': 'bg-blue-100 text-blue-700',
            'sold': 'bg-slate-100 text-slate-700'
        };
        return colors[status] || 'bg-slate-100 text-slate-700';
    },

    formatPrice(price) {
        return 'Rp ' + Number(price).toLocaleString('id-ID');
    },

    openBooking() {
        if (this.product) {
            this.inquiryName = this.product.name;
            window.dispatchEvent(new CustomEvent('inquiry-ternak-hidup', {
                detail: { name: this.product.name }
            }));
        }
        this.closeModal();
    }
}">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-3xl font-semibold text-slate-900">Ternak Unggulan</h2>
                <p class="mt-2 text-slate-600">Bandingkan detail ternak sebelum booking, lalu lanjut ke proses pemesanan.
                </p>
            </div>

            <div class="inline-flex rounded-xl border border-emerald-100 bg-emerald-50 p-1 text-sm">
                <button @click="activeTab = 'live'"
                    :class="activeTab === 'live' ? 'bg-white text-emerald-700 shadow' : 'text-slate-600'"
                    class="rounded-lg px-3 py-1.5 font-semibold">Ternak Hidup</button>
                <button @click="activeTab = 'fresh'"
                    :class="activeTab === 'fresh' ? 'bg-white text-emerald-700 shadow' : 'text-slate-600'"
                    class="rounded-lg px-3 py-1.5 font-semibold">Daging Segar</button>
            </div>
        </div>

        {{-- Grid --}}
        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            {{-- Ternak Hidup (Inquiry) --}}
            <template x-if="activeTab === 'live'">
                <div class="contents">
                    @foreach ($productsLive as $index => $product)
                        <article
                            class="overflow-hidden rounded-3xl border border-emerald-100 bg-white shadow-md shadow-emerald-50">
                            <div class="relative">
                                <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}"
                                    class="h-56 w-full object-cover">
                                <div class="absolute left-4 top-4 flex flex-wrap gap-2">
                                    <span
                                        class="rounded-full bg-emerald-600 px-3 py-1 text-xs font-semibold text-white">Organik</span>
                                    <span
                                        class="rounded-full bg-white/95 px-3 py-1 text-xs font-semibold text-emerald-700">{{ $product['status'] }}</span>
                                </div>
                            </div>
                            <div class="space-y-4 p-5">
                                <div>
                                    <h3 class="line-clamp-1 text-lg font-semibold text-slate-900">{{ $product['name'] }}
                                    </h3>
                                    <p class="mt-1 text-sm text-slate-500">Lokasi {{ $product['location'] }} ·
                                        {{ $product['category'] }}</p>
                                </div>

                                <div class="grid grid-cols-2 gap-3 text-sm text-slate-600">
                                    <div class="rounded-2xl bg-slate-50 p-3">
                                        <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Berat</p>
                                        <p class="mt-1 font-semibold text-slate-900">{{ $product['weight'] ?? '-' }}</p>
                                    </div>
                                    <div class="rounded-2xl bg-slate-50 p-3">
                                        <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Umur</p>
                                        <p class="mt-1 font-semibold text-slate-900">{{ $product['age'] ?? '-' }}</p>
                                    </div>
                                    <div class="rounded-2xl bg-slate-50 p-3">
                                        <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Jenis Kelamin</p>
                                        <p class="mt-1 font-semibold text-slate-900">{{ $product['sex'] ?? '-' }}</p>
                                    </div>
                                    <div class="rounded-2xl bg-slate-50 p-3">
                                        <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Lokasi</p>
                                        <p class="mt-1 font-semibold text-slate-900">{{ $product['location'] ?? '-' }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between border-t border-slate-100 pt-4">
                                    <span class="text-xl font-bold text-emerald-700">Rp
                                        {{ number_format($product['price']) }}</span>
                                    <div class="flex gap-2">
                                        <button type="button"
                                            @click="openProductDetail({{ $product['id'] }}, {
                                                id: {{ $product['id'] }},
                                                name: '{{ addslashes($product['name']) }}',
                                                price: {{ $product['price'] }},
                                                image: '{{ $product['image'] }}',
                                                images: [],
                                                category_name: '{{ $product['category'] }}',
                                                weight: '{{ $product['weight'] ?? '' }}',
                                                age: '{{ $product['age'] ?? '' }}',
                                                sex_label: '{{ $product['sex'] ?? '' }}',
                                                origin: '{{ $product['location'] ?? '' }}',
                                                status: '{{ strtolower(str_replace(' ', '_', $product['status'])) }}',
                                                status_label: '{{ $product['status'] }}',
                                                description: ''
                                            })"
                                            class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">Detail</button>
                                        <button type="button"
                                            class="rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-amber-600"
                                            @click="inquiryName = '{{ addslashes($product['name']) }}'; window.dispatchEvent(new CustomEvent('inquiry-ternak-hidup', { detail: { name: '{{ addslashes($product['name']) }}' } }));">Booking</button>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </template>

            {{-- Daging Segar (Add-to-cart) --}}
            <template x-if="activeTab === 'fresh'">
                <div class="contents">
                    @foreach ($productsFresh as $index => $product)
                        <article class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-md">
                            <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}"
                                class="h-56 w-full object-cover">
                            <div class="space-y-3 p-5">
                                <h3 class="line-clamp-1 text-lg font-semibold text-slate-900">{{ $product['name'] }}
                                </h3>
                                <p class="text-sm text-slate-500">Daging segar organik dengan cold-chain</p>
                                <div class="flex items-center justify-between">
                                    <span class="text-lg font-bold text-emerald-700">Rp
                                        {{ number_format($product['price']) }}</span>
                                    <button
                                        @click="
                                            window.dispatchEvent(new CustomEvent('add-to-cart', {
                                                detail: {
                                                    id: {{ $product['id'] }},
                                                    name: '{{ addslashes($product['name']) }}',
                                                    price: {{ $product['price'] }},
                                                    image: '{{ $product['image'] }}'
                                                }
                                            }));
                                            $el.innerHTML = '<svg class=\'size-3 animate-spin inline\' viewBox=\'0 0 24 24\' fill=\'none\'><circle class=\'opacity-25\' cx=\'12\' cy=\'12\' r=\'10\' stroke=\'currentColor\' stroke-width=\'4\'></circle><path class=\'opacity-75\' fill=\'currentColor\' d=\'M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z\'></path></svg>';
                                            setTimeout(() => { $el.innerHTML = 'Ditambahkan'; setTimeout(() => { $el.innerHTML = 'Tambah'; }, 800); }, 300);
                                        "
                                        class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-700 active:scale-95">
                                        Tambah
                                    </button>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </template>
        </div>

        {{-- Inquiry Modal (frontend only) --}}
        <div x-show="inquiryName" x-transition
            class="fixed inset-0 z-60 flex items-center justify-center bg-slate-900/50 p-4"
            @click.self="inquiryName = null">
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-xl font-semibold text-slate-900">Inquiry Ternak Hidup</h3>
                        <p class="mt-1 text-sm text-slate-600">Kamu sedang mengajukan: <span
                                class="font-semibold">"<span x-text="inquiryName"></span>"</span></p>
                    </div>
                    <button type="button"
                        class="rounded-lg border border-slate-200 px-3 py-1 text-sm font-semibold text-slate-600 hover:bg-slate-50"
                        @click="inquiryName = null">Tutup</button>
                </div>

                <form class="mt-4 grid gap-3" @submit.prevent="inquiryName = null">
                    <input type="text" required placeholder="Nama"
                        class="h-11 rounded-xl border border-slate-200 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-200">
                    <input type="text" required placeholder="WhatsApp"
                        class="h-11 rounded-xl border border-slate-200 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-200">
                    <textarea rows="4" placeholder="Kebutuhan singkat (jumlah, umur, preferensi)..."
                        class="rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-200"></textarea>

                    <div class="flex gap-2 pt-1">
                        <button type="button"
                            class="flex-1 rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                            @click="inquiryName = null">Batal</button>
                        <button type="submit"
                            class="flex-1 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">Kirim
                            Inquiry</button>
                    </div>

                    <p class="text-xs text-slate-500">*Demo UI inquiry. Backend belum dihubungkan.</p>
                </form>
            </div>
        </div>
    </div>

    {{-- Product Detail Modal --}}
    <div x-show="showModal" x-on:keydown.escape.window="closeModal()"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
        <div x-show="showModal"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white shadow-2xl"
            @click.stop>

            <template x-if="product">
                <div>
                    {{-- Header --}}
                    <div class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-100 bg-white px-6 py-4">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900" x-text="product?.name || 'Detail Produk'"></h3>
                            <p class="text-sm text-slate-500" x-text="product?.category_name || ''"></p>
                        </div>
                        <button
                            @click="closeModal()"
                            class="rounded-full p-2 text-slate-400 transition-all hover:bg-slate-100 hover:text-slate-600"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Body --}}
                    <div class="p-6">
                        {{-- Image Gallery --}}
                        <div class="mb-6">
                            <div class="relative aspect-4/3 overflow-hidden rounded-2xl bg-slate-100">
                                <template x-if="images.length > 0">
                                    <img
                                        :src="images[activeImageIndex]"
                                        :alt="product?.name"
                                        class="h-full w-full object-cover"
                                    >
                                </template>
                                <template x-if="images.length === 0 && product?.image">
                                    <img
                                        :src="product.image"
                                        :alt="product?.name"
                                        class="h-full w-full object-cover"
                                    >
                                </template>
                                <template x-if="!product?.image && images.length === 0">
                                    <div class="flex h-full items-center justify-center">
                                        <svg class="h-20 w-20 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                </template>

                                {{-- Navigation Arrows --}}
                                <template x-if="images.length > 1">
                                    <div>
                                        <button
                                            @click="prevImage()"
                                            class="absolute left-3 top-1/2 -translate-y-1/2 rounded-full bg-white/90 p-2 text-slate-700 shadow-lg transition-all hover:bg-white hover:scale-110"
                                        >
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                            </svg>
                                        </button>
                                        <button
                                            @click="nextImage()"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 rounded-full bg-white/90 p-2 text-slate-700 shadow-lg transition-all hover:bg-white hover:scale-110"
                                        >
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </button>
                                        <div class="absolute bottom-3 right-3 rounded-full bg-black/60 px-3 py-1 text-xs font-medium text-white" x-text="(activeImageIndex + 1) + ' / ' + images.length"></div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Price & Status --}}
                        <div class="mb-6 flex items-center justify-between">
                            <span class="text-2xl font-bold text-emerald-700" x-text="formatPrice(product?.price || 0)"></span>
                            <template x-if="product?.status_label">
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold" :class="getStatusColor(product?.status)" x-text="product?.status_label"></span>
                            </template>
                        </div>

                        {{-- Description --}}
                        <template x-if="product?.description">
                            <div class="mb-6">
                                <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">Deskripsi</h4>
                                <p class="text-slate-600 leading-relaxed" x-text="product?.description"></p>
                            </div>
                        </template>

                        {{-- Livestock Details Grid --}}
                        <div class="mb-6 rounded-2xl bg-slate-50 p-5">
                            <h4 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Detail Ternak</h4>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500">Berat</span>
                                    <span class="font-semibold text-slate-900" x-text="product?.weight || '-'"></span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500">Umur</span>
                                    <span class="font-semibold text-slate-900" x-text="product?.age || '-'"></span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500">Jenis Kelamin</span>
                                    <span class="font-semibold text-slate-900" x-text="product?.sex_label || '-'"></span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500">Asal</span>
                                    <span class="font-semibold text-slate-900" x-text="product?.origin || '-'"></span>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex gap-3">
                            <button
                                @click="closeModal()"
                                class="flex-1 rounded-xl border border-slate-200 px-4 py-3 font-semibold text-slate-700 transition-all hover:bg-slate-50"
                            >
                                Tutup
                            </button>
                            <button
                                @click="openBooking()"
                                class="flex-1 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 px-4 py-3 font-semibold text-white shadow-lg shadow-amber-500/30 transition-all hover:from-amber-600 hover:to-amber-700 hover:shadow-amber-500/40"
                            >
                                Booking Sekarang
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</section>
