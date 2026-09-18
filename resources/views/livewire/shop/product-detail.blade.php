<div x-data="{
    showToast: false,
    toastText: '',
    toastTimer: null,
    activeImage: 0,
    showFullscreenGallery: false,

    showAddToast(name, action = 'added') {
        this.toastText = action === 'buynow'
            ? 'Melanjutkan ke checkout...'
            : `${name} ditambahkan ke keranjang`;
        this.showToast = true;
        clearTimeout(this.toastTimer);
        this.toastTimer = setTimeout(() => {
            this.showToast = false;
        }, action === 'buynow' ? 1500 : 2200);
    },

    addToCart(product, qty = 1) {
        const key = 'product_' + product.id + '_' + (product.type || 'fresh');

        // Validate stock before adding
        const currentCart = JSON.parse(localStorage.getItem('abodafarm_cart') || '[]');
        const existingItem = currentCart.find(item => item.key === key);
        const currentQty = existingItem ? existingItem.qty : 0;
        const availableStock = product.stock || 0;

        if (availableStock <= 0) {
            window.dispatchEvent(new CustomEvent('alert', {
                detail: { type: 'error', title: 'Stok Habis', message: 'Maaf, stok produk ini sudah habis.' }
            }));
            return;
        }

        if (currentQty + qty > availableStock) {
            window.dispatchEvent(new CustomEvent('alert', {
                detail: { type: 'warning', title: 'Stok Terbatas', message: `Maaf, hanya ${availableStock} unit tersedia. Anda sudah memiliki ${currentQty} di keranjang.` }
            }));
            return;
        }

        // Dispatch add-to-cart event - cart-drawer will handle
        window.dispatchEvent(new CustomEvent('add-to-cart', {
            detail: {
                id: product.id,
                name: product.name,
                price: product.current_price || product.price,
                image: product.image,
                type: product.type || 'fresh',
                key: key,
                stock: availableStock
            }
        }));

        this.showAddToast(product.name);
    },

    buyNow(product, qty = 1) {
        this.addToCart(product, qty);
        this.showAddToast(product.name, 'buynow');
        setTimeout(() => {
            window.location.href = '/checkout';
        }, 1000);
    },

    isInCart(productId) {
        const cart = JSON.parse(localStorage.getItem('abodafarm_cart') || '[]');
        return cart.some(item => item.product_id === productId);
    },

    getCartQty(productId) {
        const cart = JSON.parse(localStorage.getItem('abodafarm_cart') || '[]');
        const item = cart.find(i => i.product_id === productId);
        return item ? item.qty : 0;
    }
}" x-on:cart-item-added.window="showAddToast($event.detail.name)" class="bg-slate-50 text-slate-900">

    <!-- Topbar -->
    @include('components.topbar')

    <!-- Toast Notification -->
    <div x-show="showToast" x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed bottom-6 right-6 z-50 rounded-xl border border-emerald-200 bg-white px-5 py-3 shadow-lg shadow-emerald-100">
        <div class="flex items-center gap-3">
            <div class="flex size-8 items-center justify-center rounded-full bg-emerald-100">
                <svg class="size-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <span class="text-sm font-medium text-slate-700" x-text="toastText"></span>
        </div>
    </div>

    <!-- Fullscreen Gallery Modal -->
    <div x-show="showFullscreenGallery" x-on:keydown.escape.window="showFullscreenGallery = false"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <button @click="showFullscreenGallery = false"
                class="absolute right-4 top-4 rounded-full bg-white/10 p-2 text-white backdrop-blur-sm transition hover:bg-white/20">
            <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <img :src=\"{{ json_encode($allImages) }}[activeImage]\" class="max-h-[90vh] max-w-full rounded-lg object-contain">
        <div class="absolute bottom-4 left-1/2 flex -translate-x-1/2 gap-2">
            <template x-for="(img, index) in {{ json_encode($allImages) }}" :key="index">
                <button @click="activeImage = index"
                        :class="activeImage === index ? 'bg-white' : 'bg-white/30'"
                        class="size-2 rounded-full transition-all"></button>
            </template>
        </div>
    </div>

    <!-- Breadcrumb -->
    <div class="border-b border-slate-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
            <nav class="flex items-center gap-2 text-sm">
                <a href="{{ route('home') }}" class="text-slate-500 hover:text-emerald-600">Beranda</a>
                <i class="fa-solid fa-chevron-right text-xs text-slate-400"></i>
                <a href="{{ route('shop') }}" class="text-slate-500 hover:text-emerald-600">Produk</a>
                <i class="fa-solid fa-chevron-right text-xs text-slate-400"></i>
                <a href="{{ route('shop') }}?category={{ $product->category?->slug }}" class="text-slate-500 hover:text-emerald-600">
                    {{ $product->category?->name ?? 'Kategori' }}
                </a>
                <i class="fa-solid fa-chevron-right text-xs text-slate-400"></i>
                <span class="font-medium text-slate-900">{{ Str::limit($product->name, 30) }}</span>
            </nav>
        </div>
    </div>

    <!-- Main Content -->
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:grid lg:grid-cols-2 lg:gap-12">
        <!-- Image Gallery -->
        <div class="lg:sticky lg:top-4">
            <!-- Main Image -->
            <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="aspect-square">
                    <template x-for="(img, index) in {{ json_encode($allImages) }}" :key="index">
                        <img :src="img" alt="{{ $product->name }}"
                             class="size-full object-cover"
                             x-show="activeImage === index"
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100">
                    </template>
                </div>

                <!-- Image Navigation -->
                <button @click="activeImage = activeImage > 0 ? activeImage - 1 : {{ max(0, count($allImages ?? []) - 1) }}"
                        class="absolute left-3 top-1/2 -translate-y-1/2 rounded-full bg-white/90 p-2 shadow-md backdrop-blur-sm transition hover:bg-white">
                    <svg class="size-5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                <button @click="activeImage = activeImage < {{ max(0, count($allImages ?? []) - 1) }} ? activeImage + 1 : 0"
                        class="absolute right-3 top-1/2 -translate-y-1/2 rounded-full bg-white/90 p-2 shadow-md backdrop-blur-sm transition hover:bg-white">
                    <svg class="size-5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>

                <!-- Fullscreen Button -->
                <button @click="showFullscreenGallery = true"
                        class="absolute bottom-4 right-4 rounded-lg bg-white/90 px-3 py-2 text-sm font-medium text-slate-700 shadow-md backdrop-blur-sm transition hover:bg-white">
                    <i class="fa-solid fa-expand mr-1"></i> Perbesar
                </button>

                <!-- Badges -->
                <div class="absolute left-4 top-4 flex flex-col gap-2">
                    @if($product->has_discount)
                        <span class="rounded-lg bg-red-500 px-3 py-1.5 text-sm font-bold text-white shadow">
                            -{{ $product->discount_percentage }}% OFF
                        </span>
                    @endif
                    @if($product->is_featured)
                        <span class="rounded-lg bg-amber-400 px-3 py-1.5 text-sm font-bold text-amber-900 shadow">
                            <i class="fa-solid fa-star mr-1"></i>Unggulan
                        </span>
                    @endif
                </div>
            </div>

            <!-- Thumbnail Gallery -->
            @if(($allImages && count($allImages)) > 1)
                <div class="mt-4 flex gap-3 overflow-x-auto pb-2">
                    @foreach($allImages as $index => $image)
                        <button @click="activeImage = {{ $index }}"
                                :class="activeImage === {{ $index }} ? 'ring-2 ring-emerald-500 ring-offset-2' : 'opacity-70 hover:opacity-100'"
                                class="shrink-0 overflow-hidden rounded-lg border-2 border-slate-200 transition">
                            <x-image
                                src="{{ $image }}"
                                alt="Thumbnail {{ $index + 1 }}"
                                type="thumbnail"
                                width="80"
                                height="80"
                                class="size-20 object-cover"
                            />
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Product Info -->
        <div class="mt-8 lg:mt-0">
            <!-- Category & Title -->
            <p class="text-sm font-medium uppercase tracking-wide text-emerald-600">
                {{ $product->category?->name ?? 'Lainnya' }}
            </p>
            <h1 class="mt-2 text-2xl font-bold text-slate-900 sm:text-3xl">
                {{ $product->name }}
            </h1>

            <!-- Rating & Stock -->
            <div class="mt-4 flex flex-wrap items-center gap-4">
                <!-- Rating -->
                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-0.5">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fa-solid fa-star text-sm {{ $i <= ($product->rating ?? 0) ? 'text-amber-400' : 'text-slate-200' }}"></i>
                        @endfor
                    </div>
                    <span class="text-sm font-medium text-slate-700">{{ $product->formatted_rating }}</span>
                    <span class="text-sm text-slate-500">({{ $product->total_reviews ?? 0 }} ulasan)</span>
                </div>

                <!-- Stock Status -->
                @if($product->stock_status === 'out_of_stock')
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-medium text-slate-700">
                        <i class="fa-solid fa-times-circle mr-1 text-red-500"></i> Stok Habis
                    </span>
                @elseif($product->stock_status === 'low_stock')
                    <span class="rounded-full bg-amber-100 px-3 py-1 text-sm font-medium text-amber-700">
                        <i class="fa-solid fa-exclamation-circle mr-1"></i> Stok Terbatas ({{ $product->stock }} tersisa)
                    </span>
                @else
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-sm font-medium text-emerald-700">
                        <i class="fa-solid fa-check-circle mr-1"></i> Tersedia
                    </span>
                @endif
            </div>

            <!-- Price -->
            <div class="mt-6">
                @if($product->has_discount)
                    <div class="flex items-baseline gap-3">
                        <span class="text-4xl font-bold text-red-600">
                            {{ $product->formatted_current_price }}
                        </span>
                        <span class="text-xl text-slate-400 line-through">
                            {{ $product->formatted_price }}
                        </span>
                        @if($product->unit)
                            <span class="text-base text-slate-500">/ {{ $product->unit }}</span>
                        @endif
                    </div>
                @else
                    <div class="flex items-baseline gap-2">
                        <span class="text-4xl font-bold text-emerald-600">
                            {{ $product->formatted_current_price }}
                        </span>
                        @if($product->unit)
                            <span class="text-base text-slate-500">/ {{ $product->unit }}</span>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Short Description -->
            @if($product->description)
                <p class="mt-6 text-slate-600">
                    {{ Str::limit(strip_tags($product->description), 200) }}
                </p>
            @endif

            <!-- Divider -->
            <div class="mt-8 border-t border-slate-200"></div>

            <!-- Quantity Selector & Add to Cart -->
            <div class="mt-6">
                @if($product->stock > 0)
                    @if($product->type === 'live')
                        {{-- LIVESTOCK: Show Booking Button --}}
                        <div class="mb-4 rounded-xl bg-green-50 p-4 border border-green-200">
                            <div class="flex items-start gap-3">
                                <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-green-100">
                                    <i class="fa-solid fa-cow text-green-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-green-800">Ternak Hidup</h4>
                                    <p class="mt-1 text-sm text-green-700">Produk ternak dapat dipesan melalui sistem booking. Tim kami akan menghubungi Anda untuk konfirmasi.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons for Livestock -->
                        <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                            <a href="{{ route('pelanggan.booking') }}?product={{ $product->id }}"
                               class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-green-600 py-3.5 text-sm font-semibold text-white shadow-lg shadow-green-200 transition-all hover:bg-green-700 active:scale-[0.98]">
                                <i class="fa-solid fa-calendar-check"></i>
                                Booking Ternak
                            </a>
                            <a href="https://wa.me/6281234567890?text=Halo, saya interesado dengan {{ urlencode($product->name) }} ({{ route('shop.product', $product->slug) }})"
                               target="_blank"
                               class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-emerald-500 py-3.5 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition-all hover:bg-emerald-600 active:scale-[0.98]">
                                <i class="fa-brands fa-whatsapp"></i>
                                Hubungi via WhatsApp
                            </a>
                        </div>
                    @else
                        {{-- NORMAL PRODUCTS: Show Cart/Checkout Buttons --}}
                        <!-- Quantity -->
                        <div class="flex items-center gap-4">
                            <span class="text-sm font-medium text-slate-700">Jumlah:</span>
                            <div class="flex items-center overflow-hidden rounded-xl border border-slate-300">
                                <button wire:click="decrementQuantity"
                                        class="flex size-10 items-center justify-center text-slate-600 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
                                        @if($quantity <= 1) disabled @endif>
                                    <i class="fa-solid fa-minus text-sm"></i>
                                </button>
                                <input type="number" wire:model.live="quantity" min="1" max="{{ $product->stock }}"
                                       class="size-14 w-full border-x border-slate-300 text-center text-lg font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                       readonly>
                                <button wire:click="incrementQuantity"
                                        class="flex size-10 items-center justify-center text-slate-600 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
                                        @if($quantity >= $product->stock) disabled @endif>
                                    <i class="fa-solid fa-plus text-sm"></i>
                                </button>
                            </div>
                            @if($product->unit)
                                <span class="text-sm text-slate-500">/ {{ $product->unit }}</span>
                            @endif
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                            <button
                                x-data="{}"
                                @click="addToCart({{ json_encode([
                                    'id' => $product->id,
                                    'name' => $product->name,
                                    'price' => $product->current_price,
                                    'image' => $product->image_url,
                                    'type' => $product->type,
                                    'stock' => $product->stock,
                                    'qty' => $quantity
                                ]) }}, {{ $quantity }})"
                                class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-emerald-600 py-3.5 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition-all hover:bg-emerald-700 active:scale-[0.98]">
                                <i class="fa-solid fa-cart-plus"></i>
                                Tambah ke Keranjang
                            </button>
                            <button
                                @click="buyNow({{ json_encode([
                                    'id' => $product->id,
                                    'name' => $product->name,
                                    'price' => $product->current_price,
                                    'image' => $product->image_url,
                                    'type' => $product->type,
                                    'stock' => $product->stock
                                ]) }}, {{ $quantity }})"
                                class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-amber-500 py-3.5 text-sm font-semibold text-white shadow-lg shadow-amber-200 transition-all hover:bg-amber-600 active:scale-[0.98]">
                                <i class="fa-solid fa-bolt"></i>
                                Beli Sekarang
                            </button>
                        </div>

                        <!-- Wishlist Button -->
                        <button class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl border border-slate-300 py-3 text-sm font-medium text-slate-600 transition hover:border-red-300 hover:text-red-500">
                            <i class="fa-solid fa-heart"></i>
                            Tambah ke Wishlist
                        </button>
                    @endif
                @else
                    <!-- Out of Stock State -->
                    <x-ui.empty-state
                        icon="fa-box-open"
                        title="Maaf, stok habis"
                        description="Produk ini sedang tidak tersedia"
                    >
                        <x-slot:action>
                            <button class="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-6 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100">
                                <i class="fa-solid fa-bell"></i> Notify Me When Available
                            </button>
                        </x-slot:action>
                    </x-ui.empty-state>
                @endif
            </div>

            <!-- Shipping Info -->
            <div class="mt-6 rounded-xl border border-slate-200 bg-white p-4">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-lg bg-emerald-100">
                        <i class="fa-solid fa-truck text-emerald-600"></i>
                    </div>
                    <div>
                        <p class="font-medium text-slate-900">Pengiriman Cepat</p>
                        <p class="text-sm text-slate-500">Estimasi 1-3 hari kerja</p>
                    </div>
                </div>
            </div>

            <!-- Farmer/Toko Peternak Link -->
            @if($farmer)
                <a href="{{ $farmer['profile_url'] }}"
                   target="_blank"
                   class="mt-4 flex items-center gap-4 rounded-xl border border-amber-200 bg-amber-50 p-4 transition hover:border-amber-300 hover:bg-amber-100">
                    @if($farmer['logo_url'])
                        <img src="{{ $farmer['logo_url'] }}"
                             alt="{{ $farmer['name'] }}"
                             class="size-12 rounded-full object-cover border-2 border-white shadow">
                    @else
                        <div class="flex size-12 items-center justify-center rounded-full bg-amber-200 text-xl font-bold text-amber-700">
                            {{ strtoupper(substr($farmer['name'], 0, 1)) }}
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="font-semibold text-amber-900 truncate">{{ $farmer['name'] }}</p>
                            @if($farmer['is_verified'])
                                <span class="flex items-center gap-1 rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700">
                                    <i class="fa-solid fa-circle-check"></i>
                                    Terverifikasi
                                </span>
                            @endif
                        </div>
                        @if($farmer['rating'] > 0)
                            <div class="mt-0.5 flex items-center gap-2 text-sm">
                                <div class="flex items-center gap-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fa-solid fa-star text-xs {{ $i <= round($farmer['rating']) ? 'text-amber-400' : 'text-amber-200' }}"></i>
                                    @endfor
                                </div>
                                <span class="font-medium text-amber-700">{{ number_format($farmer['rating'], 1) }}</span>
                                <span class="text-amber-600">({{ number_format($farmer['total_reviews']) }} ulasan)</span>
                            </div>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 text-sm font-medium text-amber-700">
                        <span>Kunjungi Toko</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </div>
                </a>
            @endif
        </div>
    </main>

    <!-- Product Details Tabs -->
    <section class="mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <!-- Tabs Header -->
            <div class="flex border-b border-slate-200">
                <button wire:click="setTab('description')"
                        :class="$wire.activeTab === 'description' ? 'border-b-2 border-emerald-600 text-emerald-600' : 'text-slate-600 hover:text-slate-900'"
                        class="flex-1 px-6 py-4 text-sm font-semibold transition">
                    Deskripsi
                </button>
                <button wire:click="setTab('specifications')"
                        :class="$wire.activeTab === 'specifications' ? 'border-b-2 border-emerald-600 text-emerald-600' : 'text-slate-600 hover:text-slate-900'"
                        class="flex-1 px-6 py-4 text-sm font-semibold transition">
                    Spesifikasi
                </button>
                <button wire:click="setTab('how_to_use')"
                        :class="$wire.activeTab === 'how_to_use' ? 'border-b-2 border-emerald-600 text-emerald-600' : 'text-slate-600 hover:text-slate-900'"
                        class="flex-1 px-6 py-4 text-sm font-semibold transition">
                    Cara Pakai
                </button>
                <button wire:click="setTab('shipping')"
                        :class="$wire.activeTab === 'shipping' ? 'border-b-2 border-emerald-600 text-emerald-600' : 'text-slate-600 hover:text-slate-900'"
                        class="flex-1 px-6 py-4 text-sm font-semibold transition">
                    Pengiriman
                </button>
            </div>

            <!-- Tab Content -->
            <div class="p-6">
                <!-- Description Tab -->
                <div x-show="$wire.activeTab === 'description'">
                    @if($product->description)
                        <div class="prose max-w-none text-slate-600">
                            {!! nl2br(e($product->description)) !!}
                        </div>
                    @else
                        <p class="text-center text-slate-500">Deskripsi produk belum tersedia.</p>
                    @endif
                </div>

                <!-- Specifications Tab -->
                <div x-show="$wire.activeTab === 'specifications'">
                    @if($product->specifications)
                        <div class="prose max-w-none text-slate-600">
                            {!! nl2br(e($product->specifications)) !!}
                        </div>
                    @else
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="flex items-center justify-between rounded-lg bg-slate-50 p-3">
                                <span class="text-sm font-medium text-slate-600">Kategori</span>
                                <span class="text-sm text-slate-900">{{ $product->category?->name ?? '-' }}</span>
                            </div>
                            <div class="flex items-center justify-between rounded-lg bg-slate-50 p-3">
                                <span class="text-sm font-medium text-slate-600">Unit</span>
                                <span class="text-sm text-slate-900">{{ $product->unit ?? '-' }}</span>
                            </div>
                            <div class="flex items-center justify-between rounded-lg bg-slate-50 p-3">
                                <span class="text-sm font-medium text-slate-600">Stok</span>
                                <span class="text-sm text-slate-900">{{ $product->stock }} unit</span>
                            </div>
                            <div class="flex items-center justify-between rounded-lg bg-slate-50 p-3">
                                <span class="text-sm font-medium text-slate-600">Berat</span>
                                <span class="text-sm text-slate-900">~{{ ($product->weight ?? 1) * $product->stock }} kg</span>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- How to Use Tab -->
                <div x-show="$wire.activeTab === 'how_to_use'">
                    @if($product->how_to_use)
                        <div class="prose max-w-none text-slate-600">
                            {!! nl2br(e($product->how_to_use)) !!}
                        </div>
                    @else
                        <p class="text-center text-slate-500">Informasi cara penggunaan belum tersedia.</p>
                    @endif
                </div>

                <!-- Shipping Tab -->
                <div x-show="$wire.activeTab === 'shipping'">
                    @if($product->shipping_info)
                        <div class="prose max-w-none text-slate-600">
                            {!! nl2br(e($product->shipping_info)) !!}
                        </div>
                    @else
                        <div class="space-y-4">
                            <div class="flex items-start gap-4">
                                <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-emerald-100">
                                    <i class="fa-solid fa-truck text-emerald-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-slate-900">Pengiriman Regular</h4>
                                    <p class="mt-1 text-sm text-slate-600">Estimasi 1-3 hari kerja untuk area Sulawesi Utara</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-4">
                                <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-amber-100">
                                    <i class="fa-solid fa-fire-flame-curved text-amber-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-slate-900">Pengiriman Ekspres</h4>
                                    <p class="mt-1 text-sm text-slate-600">Same day delivery untuk area Manado dan sekitarnya</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-4">
                                <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-blue-100">
                                    <i class="fa-solid fa-box text-blue-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-slate-900">Pengemasan Aman</h4>
                                    <p class="mt-1 text-sm text-slate-600">Produk dikemas dengan standar keamanan tinggi</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <!-- Reviews Section -->
    @if($reviews && count($reviews) > 0)
        <section class="mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 p-6">
                    <h2 class="text-lg font-bold text-slate-900">Ulasan Produk</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ $product->total_reviews ?? 0 }} ulasan dari pembeli</p>
                </div>

                <div class="grid gap-8 p-6 lg:grid-cols-3">
                    <!-- Rating Summary -->
                    <div class="text-center">
                        <div class="text-5xl font-bold text-slate-900">{{ $product->formatted_rating }}</div>
                        <div class="mt-2 flex justify-center gap-0.5">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fa-solid fa-star text-lg {{ $i <= ($product->rating ?? 0) ? 'text-amber-400' : 'text-slate-200' }}"></i>
                            @endfor
                        </div>
                        <p class="mt-1 text-sm text-slate-500">{{ $product->total_reviews ?? 0 }} ulasan</p>

                        <!-- Rating Breakdown -->
                        <div class="mt-6 space-y-2">
                            @foreach($ratingBreakdown as $star => $data)
                                <div class="flex items-center gap-2 text-sm">
                                    <span class="w-6 text-slate-600">{{ $star }}</span>
                                    <i class="fa-solid fa-star text-amber-400"></i>
                                    <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-200">
                                        <div class="h-full rounded-full bg-amber-400" style="width: {{ $data['percent'] }}%"></div>
                                    </div>
                                    <span class="w-8 text-right text-slate-500">{{ $data['percent'] }}%</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Reviews List -->
                    <div class="lg:col-span-2 space-y-4">
                        @foreach($reviews as $review)
                            <div class="rounded-xl border border-slate-200 p-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="flex size-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 font-semibold">
                                            {{ strtoupper(substr($review['user_name'], 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-semibold text-slate-900">{{ $review['user_name'] }}</p>
                                            <div class="flex items-center gap-2">
                                                <div class="flex gap-0.5">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <i class="fa-solid fa-star text-xs {{ $i <= $review['rating'] ? 'text-amber-400' : 'text-slate-200' }}"></i>
                                                    @endfor
                                                </div>
                                                <span class="text-xs text-slate-500">{{ $review['created_at'] }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @if($review['title'])
                                    <h4 class="mt-3 font-semibold text-slate-900">{{ $review['title'] }}</h4>
                                @endif
                                <p class="mt-2 text-sm text-slate-600">{{ $review['content'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    <!-- Frequently Bought Together Section -->
    @if(count($frequentlyBought) > 0)
        <section class="mx-auto max-w-7xl px-4 pb-8 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-100 text-purple-600">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Sering Dibeli Bersama</h2>
                    <p class="text-sm text-slate-500">Produk yang sering dipesan bersamaan</p>
                </div>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                @foreach($frequentlyBought as $item)
                    <article class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                        <a href="{{ route('shop.product', $item['slug']) }}" class="block">
                            <div class="aspect-square overflow-hidden bg-slate-100">
                                @if($item['image'])
                                    <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}"
                                         class="size-full object-cover transition-transform duration-500 group-hover:scale-105">
                                @else
                                    <div class="flex size-full items-center justify-center">
                                        <i class="fa-solid fa-box text-3xl text-slate-300"></i>
                                    </div>
                                @endif
                            </div>
                        </a>
                        <div class="p-3">
                            <p class="text-[10px] font-medium text-emerald-600">{{ $item['category'] }}</p>
                            <a href="{{ route('shop.product', $item['slug']) }}">
                                <h3 class="mt-0.5 line-clamp-2 text-xs font-semibold text-slate-900 transition group-hover:text-emerald-600">
                                    {{ $item['name'] }}
                                </h3>
                            </a>
                            <div class="mt-2">
                                <span class="text-sm font-bold text-emerald-600">{{ $item['price'] }}</span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <!-- Recommendations Section -->
    @if(count($recommendations) > 0)
        <section class="mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                    <i class="fa-solid fa-thumbs-up"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-900">{{ $recommendationTitle }}</h2>
                    <p class="text-sm text-slate-500">Berdasarkan histori transaksi Anda</p>
                </div>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($recommendations as $related)
                    <article class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                        <a href="{{ route('shop.product', $related['slug']) }}" class="block">
                            <div class="aspect-square overflow-hidden bg-slate-100">
                                @if($related['image'])
                                    <img src="{{ $related['image'] }}" alt="{{ $related['name'] }}"
                                         class="size-full object-cover transition-transform duration-500 group-hover:scale-105">
                                @else
                                    <div class="flex size-full items-center justify-center">
                                        <i class="fa-solid fa-box text-3xl text-slate-300"></i>
                                    </div>
                                @endif

                                <!-- Category Badge -->
                                @if(isset($related['category_type']) && $related['category_type'])
                                    @if($related['category_type'] === 'feed')
                                        <span class="absolute left-2 top-2 rounded-lg bg-amber-500 px-2 py-1 text-[10px] font-bold text-white">
                                            <i class="fa-solid fa-wheat-awn mr-1"></i>Pakan
                                        </span>
                                    @elseif($related['category_type'] === 'supplement')
                                        <span class="absolute left-2 top-2 rounded-lg bg-blue-500 px-2 py-1 text-[10px] font-bold text-white">
                                            <i class="fa-solid fa-capsules mr-1"></i>Suplemen
                                        </span>
                                    @endif
                                @endif
                            </div>
                        </a>
                        <div class="p-4">
                            <p class="text-xs font-medium text-emerald-600">{{ $related['category'] }}</p>
                            <a href="{{ route('shop.product', $related['slug']) }}">
                                <h3 class="mt-1 line-clamp-2 text-sm font-semibold text-slate-900 transition group-hover:text-emerald-600">
                                    {{ $related['name'] }}
                                </h3>
                            </a>
                            <div class="mt-2 flex items-center justify-between">
                                <span class="text-lg font-bold text-emerald-600">
                                    {{ $related['price'] }}
                                </span>
                                @if($related['has_discount'])
                                    <span class="rounded bg-red-100 px-2 py-0.5 text-[10px] font-bold text-red-600">
                                        Sale
                                    </span>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

</div>
