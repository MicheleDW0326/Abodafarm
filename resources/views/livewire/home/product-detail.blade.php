
<div x-data="{
    showModal: false,
    product: null,
    images: [],
    activeImageIndex: 0,
    open(productId) {
        // Fetch product data via fetch API
        fetch(`/api/products/${productId}`)
            .then(res => res.json())
            .then(data => {
                this.product = data;
                this.images = data.images || [];
                this.activeImageIndex = 0;
                this.showModal = true;
                document.body.style.overflow = 'hidden';
            });
    },
    close() {
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
    setActiveImage(index) {
        this.activeImageIndex = index;
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
            window.dispatchEvent(new CustomEvent('inquiry-ternak-hidup', {
                detail: { name: this.product.name }
            }));
        }
        this.close();
    }
}"
@show-product-detail.window="open($event.detail.productId)"
x-show="showModal"
x-on:keydown.escape.window="close()"
class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
:class="{ 'hidden': !showModal }"
>
    {{-- Backdrop --}}
    <div
        class="absolute inset-0 -z-10"
        @click="close()"
        x-show="showModal"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
    ></div>

    {{-- Modal Container --}}
    <div
        x-show="showModal"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95 translate-y-4"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white shadow-2xl"
        @click.stop
    >
        <template x-if="product">
            <div>
                {{-- Header --}}
                <div class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-100 bg-white px-6 py-4">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900" x-text="product?.name || 'Detail Produk'"></h3>
                        <p class="text-sm text-slate-500" x-text="product?.category_name || ''"></p>
                    </div>
                    <button
                        @click="close()"
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
                        {{-- Main Image --}}
                        <div class="relative aspect-[4/3] overflow-hidden rounded-2xl bg-slate-100">
                            <template x-if="images.length > 0">
                                <img
                                    :src="images[activeImageIndex] || product.image"
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

                                    {{-- Image Counter --}}
                                    <div class="absolute bottom-3 right-3 rounded-full bg-black/60 px-3 py-1 text-xs font-medium text-white" x-text="(activeImageIndex + 1) + ' / ' + images.length"></div>
                                </div>
                            </template>
                        </div>

                        {{-- Thumbnails --}}
                        <template x-if="images.length > 1">
                            <div class="mt-3 flex gap-2 overflow-x-auto pb-2">
                                <template x-for="(img, index) in images" :key="index">
                                    <button
                                        @click="setActiveImage(index)"
                                        :class="activeImageIndex === index ? 'border-emerald-500 ring-2 ring-emerald-500 ring-offset-2' : 'border-transparent hover:border-slate-300'"
                                        class="shrink-0 size-16 rounded-lg overflow-hidden border-2 transition-all"
                                    >
                                        <img :src="img" alt="" class="h-full w-full object-cover">
                                    </button>
                                </template>
                            </div>
                        </template>
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
                            <template x-if="product?.condition">
                                <div class="col-span-2 flex items-center justify-between border-t border-slate-200 pt-4 mt-2">
                                    <span class="text-slate-500">Kondisi Kesehatan</span>
                                    <span class="font-semibold text-emerald-600" x-text="product?.condition"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex gap-3">
                        <button
                            @click="close()"
                            class="flex-1 rounded-xl border border-slate-200 px-4 py-3 font-semibold text-slate-700 transition-all hover:bg-slate-50"
                        >
                            Tutup
                        </button>
                        <button
                            @click="openBooking()"
                            class="flex-1 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 px-4 py-3 font-semibold text-white shadow-lg shadow-amber-500/30 transition-all hover:from-amber-600 hover:to-amber-700 hover:shadow-amber-500/40 hover:scale-[1.02]"
                        >
                            Booking Sekarang
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>
