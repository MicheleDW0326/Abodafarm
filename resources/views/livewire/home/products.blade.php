<section id="produk" class="bg-white py-16" x-data="{
    activeTab: 'live',
    inquiryName: null,
    showModal: false,
    product: null,
    images: [],
    activeImageIndex: 0,
    showLoginPrompt: false,
    pendingBookingProduct: null,

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

    openBookingFromCard(productData) {
        // Check auth via Livewire
        @this.openBookingModal(productData);
    },

    openBooking() {
        if (this.product) {
            @this.openBookingModal({
                id: this.product.id,
                name: this.product.name,
                price: this.product.price,
                image: this.product.image || '',
                category: this.product.category_name || '',
                weight: this.product.weight || '',
                age: this.product.age || '',
                sex: this.product.sex_label || '',
                location: this.product.origin || '',
            });
        }
        this.closeModal();
    },

    closeLoginPrompt() {
        this.showLoginPrompt = false;
        this.pendingBookingProduct = null;
        document.body.style.overflow = '';
    },

    goToLogin() {
        window.location.href = '/login';
    }
}"
@open-login-prompt.window="showLoginPrompt = true; pendingBookingProduct = $event.detail.product; document.body.style.overflow = 'hidden';"
@close-login-prompt.window="closeLoginPrompt()">
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
                    @foreach ($filteredLiveProducts as $index => $product)
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
                                            wire:click="openBookingModal({{ Js::encode([
                                                'id' => $product['id'],
                                                'name' => $product['name'],
                                                'price' => $product['price'],
                                                'image' => $product['image'],
                                                'category' => $product['category'],
                                                'weight' => $product['weight'] ?? '',
                                                'age' => $product['age'] ?? '',
                                                'sex' => $product['sex'] ?? '',
                                                'location' => $product['location'] ?? '',
                                            ]) }})"
                                            class="rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-amber-600">Booking</button>
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

        {{-- Booking Modal (Livewire) --}}
        @if($showBookingModal && $bookingProduct)
            <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-900/60 backdrop-blur-sm p-0 sm:p-4"
                 x-data="{
                     modalOpen: true,
                     init() {
                         document.body.style.overflow = 'hidden';
                         this.$nextTick(() => this.modalOpen = true);
                     },
                     closeModal() {
                         this.modalOpen = false;
                         setTimeout(() => {
                             document.body.style.overflow = '';
                             @this.closeBookingModal();
                         }, 200);
                     }
                 }"
                 x-show="modalOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="closeModal()"
                 x-on:keydown.escape.window="closeModal()">
                {{-- Modal Container --}}
                <div class="relative flex w-full flex-col bg-white shadow-2xl sm:max-w-lg sm:rounded-2xl rounded-t-2xl max-h-[92vh] sm:max-h-[85vh] overflow-hidden"
                     @click.stop
                     x-show="modalOpen"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:translate-y-0 sm:scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95">

                    {{-- Header (Sticky) --}}
                    <div class="flex items-center justify-between border-b border-slate-100 bg-white px-5 py-4 sm:px-6 sm:py-5">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100">
                                <svg class="h-5 w-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-900">Booking Ternak</h3>
                                <p class="text-xs text-slate-500">Reservasi mudah tanpa deposit</p>
                            </div>
                        </div>
                        <button type="button" @click="closeModal()"
                                class="rounded-full p-2 text-slate-400 transition-all hover:bg-slate-100 hover:text-slate-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Scrollable Content --}}
                    <div class="flex-1 overflow-y-auto overscroll-contain p-5 sm:p-6">
                        <form wire:submit.prevent="submitBooking" class="space-y-5">
                            {{-- Livestock Summary Card --}}
                            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                                {{-- Image --}}
                                <div class="relative aspect-[16/9] sm:aspect-[3/2] bg-slate-100">
                                    @if($bookingProduct['image'])
                                        <img src="{{ $bookingProduct['image'] }}"
                                             alt="{{ $bookingProduct['name'] }}"
                                             class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full items-center justify-center">
                                            <svg class="h-16 w-16 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    @endif
                                    {{-- Status Badge --}}
                                    <div class="absolute right-3 top-3">
                                        <span class="inline-flex items-center rounded-full bg-emerald-600 px-3 py-1 text-xs font-semibold text-white shadow-lg">
                                            Tersedia
                                        </span>
                                    </div>
                                </div>

                                {{-- Info --}}
                                <div class="p-4">
                                    <h4 class="font-bold text-slate-900 text-lg leading-tight">{{ $bookingProduct['name'] }}</h4>

                                    <div class="mt-2 flex items-baseline gap-2">
                                        <span class="text-2xl font-bold text-emerald-600">
                                            Rp {{ number_format($bookingProduct['price']) }}
                                        </span>
                                    </div>

                                    {{-- Specs Grid --}}
                                    <div class="mt-3 grid grid-cols-3 gap-2">
                                        @if($bookingProduct['weight'])
                                            <div class="rounded-xl bg-slate-50 p-2.5 text-center">
                                                <p class="text-[10px] uppercase tracking-wider text-slate-400">Berat</p>
                                                <p class="mt-0.5 text-sm font-semibold text-slate-800">{{ $bookingProduct['weight'] }}</p>
                                            </div>
                                        @endif
                                        @if($bookingProduct['age'])
                                            <div class="rounded-xl bg-slate-50 p-2.5 text-center">
                                                <p class="text-[10px] uppercase tracking-wider text-slate-400">Umur</p>
                                                <p class="mt-0.5 text-sm font-semibold text-slate-800">{{ $bookingProduct['age'] }}</p>
                                            </div>
                                        @endif
                                        @if($bookingProduct['location'])
                                            <div class="rounded-xl bg-slate-50 p-2.5 text-center">
                                                <p class="text-[10px] uppercase tracking-wider text-slate-400">Lokasi</p>
                                                <p class="mt-0.5 text-sm font-semibold text-slate-800 truncate">{{ $bookingProduct['location'] }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Contact Form --}}
                            <div class="space-y-4">
                                <div>
                                    <label class="mb-1.5 flex items-center gap-1.5 text-sm font-medium text-slate-700">
                                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        Nama Lengkap
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text"
                                           wire:model="bookingName"
                                           required
                                           placeholder="Masukkan nama Anda"
                                           class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm transition placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                                    @error('bookingName')
                                        <p class="mt-1.5 flex items-center gap-1 text-xs text-red-500">
                                            <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="mb-1.5 flex items-center gap-1.5 text-sm font-medium text-slate-700">
                                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                        WhatsApp
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input type="tel"
                                           wire:model="bookingPhone"
                                           required
                                           placeholder="08xxxxxxxxxx"
                                           class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm transition placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                                    @error('bookingPhone')
                                        <p class="mt-1.5 flex items-center gap-1 text-xs text-red-500">
                                            <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                {{-- Address Selection --}}
                                <div>
                                    <div class="mb-2 flex items-center justify-between">
                                        <label class="flex items-center gap-1.5 text-sm font-medium text-slate-700">
                                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            Alamat Pengiriman
                                            <span class="text-red-500">*</span>
                                        </label>
                                        @if(count($userAddresses) > 0 && !$showNewAddressForm)
                                            <button type="button"
                                                    wire:click="showAddNewAddress()"
                                                    class="text-xs font-medium text-emerald-600 hover:text-emerald-700">
                                                + Tambah Baru
                                            </button>
                                        @endif
                                    </div>

                                    {{-- Existing Addresses --}}
                                    @if(count($userAddresses) > 0 && !$showNewAddressForm)
                                        <div class="space-y-2">
                                            @foreach($userAddresses as $address)
                                                <label class="flex items-start gap-3 rounded-xl border p-3 cursor-pointer transition-all
                                                    {{ $selectedAddressId == $address['id'] ? 'border-emerald-500 bg-emerald-50' : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50' }}">
                                                    <input type="radio"
                                                           name="selectedAddress"
                                                           value="{{ $address['id'] }}"
                                                           wire:model.live="selectedAddressId"
                                                           class="mt-1 h-4 w-4 text-emerald-600 border-slate-300 focus:ring-emerald-500">
                                                    <div class="flex-1">
                                                        <div class="flex items-center gap-2">
                                                            <span class="text-sm font-semibold text-slate-900">{{ $address['label'] }}</span>
                                                            @if($address['is_default'])
                                                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">Utama</span>
                                                            @endif
                                                        </div>
                                                        <p class="mt-0.5 text-xs text-slate-600">{{ $address['address'] }}</p>
                                                        <p class="text-xs text-slate-500">{{ $address['district'] }}, {{ $address['city'] }}, {{ $address['province'] }}</p>
                                                    </div>
                                                </label>
                                            @endforeach

                                            @if(!$showNewAddressForm)
                                                <button type="button"
                                                        wire:click="showAddNewAddress()"
                                                        class="flex w-full items-center justify-center gap-2 rounded-xl border-2 border-dashed border-slate-300 p-3 text-sm font-medium text-slate-500 transition-colors hover:border-emerald-400 hover:text-emerald-600">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                                    </svg>
                                                    Tambah Alamat Baru
                                                </button>
                                            @endif
                                        </div>
                                    @endif

                                    {{-- New Address Form --}}
                                    @if($showNewAddressForm || count($userAddresses) === 0)
                                        <div class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-4 space-y-4">
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm font-semibold text-emerald-800">Alamat Baru</span>
                                                @if(count($userAddresses) > 0)
                                                    <button type="button"
                                                            wire:click="$set('showNewAddressForm', false)"
                                                            class="text-xs text-slate-500 hover:text-slate-700">
                                                        Batal
                                                    </button>
                                                @endif
                                            </div>

                                            <div>
                                                <label class="mb-1 block text-xs font-medium text-slate-600">Label Alamat</label>
                                                <input type="text"
                                                       wire:model="newAddressLabel"
                                                       placeholder="Contoh: Rumah, Kantor"
                                                       class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm transition focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                                @error('newAddressLabel')
                                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <div>
                                                <label class="mb-1 block text-xs font-medium text-slate-600">Alamat Lengkap</label>
                                                <textarea wire:model="newAddress"
                                                          rows="2"
                                                          placeholder="Nama jalan, nomor rumah, RT/RW..."
                                                          class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm transition focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 resize-none"></textarea>
                                                @error('newAddress')
                                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <div class="grid grid-cols-2 gap-3">
                                                <div>
                                                    <label class="mb-1 block text-xs font-medium text-slate-600">Provinsi</label>
                                                    <input type="text"
                                                           wire:model="newProvince"
                                                           placeholder="Provinsi"
                                                           class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm transition focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                                    @error('newProvince')
                                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                                <div>
                                                    <label class="mb-1 block text-xs font-medium text-slate-600">Kota/Kabupaten</label>
                                                    <input type="text"
                                                           wire:model="newCity"
                                                           placeholder="Kota/Kabupaten"
                                                           class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm transition focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                                    @error('newCity')
                                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-2 gap-3">
                                                <div>
                                                    <label class="mb-1 block text-xs font-medium text-slate-600">Kecamatan</label>
                                                    <input type="text"
                                                           wire:model="newDistrict"
                                                           placeholder="Kecamatan"
                                                           class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm transition focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                                    @error('newDistrict')
                                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                                <div>
                                                    <label class="mb-1 block text-xs font-medium text-slate-600">Kode Pos</label>
                                                    <input type="text"
                                                           wire:model="newPostalCode"
                                                           placeholder="Kode Pos"
                                                           class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm transition focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                                </div>
                                            </div>

                                            {{-- Google Maps Place Picker --}}
                                            <div>
                                                <label class="mb-1 block text-xs font-medium text-slate-600">Lokasi di Peta (Opsional)</label>
                                                <div class="rounded-lg border border-slate-200 bg-white overflow-hidden">
                                                    <div x-data="{
                                                        mapInstance: null,
                                                        marker: null,
                                                        searchMarker: null,
                                                        init() {
                                                            // Wait for Google Maps to be ready
                                                            this.waitForGoogleMaps();
                                                        },
                                                        waitForGoogleMaps() {
                                                            if (typeof google !== 'undefined' && google.maps) {
                                                                this.initMap();
                                                            } else {
                                                                setTimeout(() => this.waitForGoogleMaps(), 100);
                                                            }
                                                        },
                                                        initMap() {
                                                            const defaultCenter = { lat: -2.5489, lng: 118.0149 };
                                                            const mapContainer = this.$refs.mapContainer;

                                                            this.mapInstance = new google.maps.Map(mapContainer, {
                                                                center: defaultCenter,
                                                                zoom: 5,
                                                                mapTypeId: 'roadmap',
                                                            });

                                                            // Search autocomplete
                                                            const searchInput = this.$refs.searchInput;
                                                            const autocomplete = new google.maps.places.Autocomplete(searchInput);

                                                            autocomplete.addListener('place_changed', () => {
                                                                const place = autocomplete.getPlace();
                                                                if (place.geometry) {
                                                                    this.mapInstance.setCenter(place.geometry.location);
                                                                    this.mapInstance.setZoom(15);
                                                                    this.setMarker(place.geometry.location);
                                                                }
                                                            });

                                                            // Initial draggable marker
                                                            this.marker = new google.maps.Marker({
                                                                position: defaultCenter,
                                                                map: this.mapInstance,
                                                                draggable: true,
                                                                animation: google.maps.Animation.DROP,
                                                            });

                                                            this.marker.addListener('dragend', () => {
                                                                const position = this.marker.getPosition();
                                                                this.updateLocation(position);
                                                            });
                                                        },
                                                        setMarker(location) {
                                                            if (this.marker) {
                                                                this.marker.setPosition(location);
                                                            } else {
                                                                this.marker = new google.maps.Marker({
                                                                    position: location,
                                                                    map: this.mapInstance,
                                                                    draggable: true,
                                                                });
                                                                this.marker.addListener('dragend', () => {
                                                                    this.updateLocation(this.marker.getPosition());
                                                                });
                                                            }
                                                            this.updateLocation(location);
                                                        },
                                                        updateLocation(location) {
                                                            // Update Livewire properties
                                                            @this.set('newLatitude', location.lat());
                                                            @this.set('newLongitude', location.lng());

                                                            // Get place details
                                                            const geocoder = new google.maps.Geocoder();
                                                            geocoder.geocode({ location: location }, (results, status) => {
                                                                if (status === 'OK' && results[0]) {
                                                                    @this.set('newGooglePlaceId', results[0].place_id);

                                                                    // Auto-fill address fields if empty
                                                                    const addr = results[0].address_components;
                                                                    if (addr) {
                                                                        // Province
                                                                        const prov = addr.find(c => c.types.includes('administrative_area_level_1'));
                                                                        if (prov && !this.$wire.newProvince) {
                                                                            @this.set('newProvince', prov.long_name);
                                                                        }
                                                                        // City
                                                                        const city = addr.find(c => c.types.includes('administrative_area_level_2'));
                                                                        if (city && !this.$wire.newCity) {
                                                                            @this.set('newCity', city.long_name);
                                                                        }
                                                                        // District
                                                                        const district = addr.find(c => c.types.includes('sublocality_level_1'));
                                                                        if (district && !this.$wire.newDistrict) {
                                                                            @this.set('newDistrict', district.long_name);
                                                                        }
                                                                    }
                                                                }
                                                            });
                                                        }
                                                    }">
                                                        <input type="text"
                                                               x-ref="searchInput"
                                                               placeholder="Cari lokasi..."
                                                               class="w-full border-b border-slate-200 px-3 py-2 text-sm focus:outline-none">
                                                        <div x-ref="mapContainer" class="h-40 w-full bg-slate-100">
                                                            <div class="flex h-full items-center justify-center text-slate-400">
                                                                <svg class="h-8 w-8 animate-spin" fill="none" viewBox="0 0 24 24">
                                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                                </svg>
                                                            </div>
                                                        </div>
                                                        <p class="bg-amber-50 px-3 py-1.5 text-[10px] text-amber-700">
                                                            <svg class="inline h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                            </svg>
                                                            Geser marker untuk menentukan lokasi tepat
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>

                                            @if(count($userAddresses) > 0)
                                                <button type="button"
                                                        wire:click="saveNewAddress()"
                                                        class="flex w-full items-center justify-center gap-2 rounded-lg bg-emerald-600 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                    Simpan Alamat
                                                </button>
                                            @endif
                                        </div>
                                    @endif

                                    @error('selectedAddressId')
                                        <p class="mt-1.5 flex items-center gap-1 text-xs text-red-500">
                                            <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                {{-- Notes --}}
                                <div>
                                    <label class="mb-1.5 flex items-center gap-1.5 text-sm font-medium text-slate-700">
                                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                        </svg>
                                        Catatan
                                        <span class="text-slate-400 font-normal">(opsional)</span>
                                    </label>
                                    <textarea wire:model="bookingNotes"
                                              rows="2"
                                              placeholder="Contoh: untuk kurban, ingin lihat dulu, dll"
                                              class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm transition placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 resize-none"></textarea>
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- Footer Action (Sticky) --}}
                    <div class="flex-shrink-0 border-t border-slate-100 bg-white px-5 py-4 sm:px-6 sm:py-5">
                        <div class="flex gap-3">
                            <button type="button"
                                    @click="closeModal()"
                                    class="flex-1 rounded-xl border border-slate-200 py-3 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-50 active:bg-slate-100">
                                Batal
                            </button>
                            <button type="button"
                                    wire:click="submitBooking"
                                    wire:loading.attr="disabled"
                                    wire:target="submitBooking"
                                    class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 py-3 text-sm font-bold text-white shadow-lg shadow-amber-500/30 transition-all hover:from-amber-600 hover:to-amber-700 hover:shadow-amber-500/40 active:scale-[0.98] disabled:opacity-60 disabled:cursor-not-allowed">
                                <span wire:loading.remove wire:target="submitBooking">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9m0-5h2m-2 0h2m8-9v9a2 2 0 01-2 2H9a2 2 0 01-2-2V7a2 2 0 012-2h6m0 0h2a2 2 0 012 2v9m0-5V7a2 2 0 00-2-2h-2"/>
                                    </svg>
                                    Ajukan Booking
                                </span>
                                <span wire:loading wire:target="submitBooking" class="flex items-center gap-2">
                                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Mengirim...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
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
                                Ajukan Booking
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- Login Prompt Modal --}}
    <div x-show="showLoginPrompt"
         x-on:keydown.escape.window="closeLoginPrompt()"
         class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="w-full max-w-sm overflow-hidden rounded-2xl bg-white shadow-2xl"
             x-show="showLoginPrompt"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            {{-- Icon --}}
            <div class="flex justify-center pt-8">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-amber-100">
                    <svg class="h-8 w-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
            </div>

            {{-- Content --}}
            <div class="px-6 pb-2 pt-4 text-center">
                <h3 class="text-lg font-bold text-slate-900">Login Diperlukan</h3>
                <p class="mt-2 text-sm text-slate-600">Silakan masuk atau daftar terlebih dahulu untuk melakukan booking ternak.</p>
            </div>

            {{-- Actions --}}
            <div class="flex gap-3 px-6 pb-6 pt-4">
                <button type="button"
                        @click="closeLoginPrompt()"
                        class="flex-1 rounded-xl border border-slate-200 py-3 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-50">
                    Batal
                </button>
                <button type="button"
                        @click="goToLogin()"
                        class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-600 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-500/30 transition-all hover:from-emerald-600 hover:to-emerald-700 active:scale-[0.98]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    Masuk
                </button>
            </div>
        </div>
    </div>

    {{-- Success Modal --}}
    @if($showSuccessModal)
        <div x-data="{ show: true }"
             x-show="show"
             x-on:keydown.escape.window="show = false; @this.closeSuccessModal()"
             class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl"
                 x-show="show"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">
                {{-- Success Animation --}}
                <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 p-8 text-center">
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-white/20">
                        <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <h3 class="mt-4 text-xl font-bold text-white">Booking Berhasil!</h3>
                    <p class="mt-2 text-emerald-100">Permintaan booking Anda telah diajukan</p>
                </div>

                {{-- Content --}}
                <div class="p-6">
                    {{-- Booking Details --}}
                    <div class="rounded-xl bg-slate-50 p-4">
                        <div class="mb-3 flex items-center justify-between">
                            <span class="text-sm text-slate-500">Kode Booking</span>
                            <span class="font-mono font-bold text-emerald-600">{{ $lastBookingNumber }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-500">Ternak</span>
                            <span class="font-medium text-slate-900">{{ $lastBookingProduct }}</span>
                        </div>
                    </div>

                    {{-- Info --}}
                    <div class="mt-4 flex items-start gap-3 rounded-xl bg-amber-50 p-4">
                        <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <div class="text-sm text-amber-700">
                            <p class="font-medium">Langkah Selanjutnya:</p>
                            <ul class="mt-1 space-y-1 text-amber-600">
                                <li>1. Peternak akan meninjau booking Anda</li>
                                <li>2. Anda akan menerima notifikasi via WhatsApp</li>
                                <li>3. Hubungi peternak untuk detail pembayaran</li>
                            </ul>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="mt-6 flex gap-3">
                        <a href="{{ route('pelanggan.bookings') }}"
                           class="flex flex-1 items-center justify-center gap-2 rounded-xl border border-slate-200 py-3 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-50">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            Lihat Booking Saya
                        </a>
                        <button type="button"
                                wire:click="closeSuccessModal()"
                                @click="show = false"
                                class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-600 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-500/30 transition-all hover:from-emerald-600 hover:to-emerald-700">
                            Selesai
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</section>
