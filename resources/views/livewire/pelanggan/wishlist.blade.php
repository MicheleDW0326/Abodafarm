<div class="space-y-6" x-data="{
    cartItems: JSON.parse(localStorage.getItem('abodafarm_cart') || '[]'),

    async addToCart(productId, name, price, image, type, maxStock) {
        const key = 'product_' + productId + '_' + type;
        const existing = this.cartItems.find(item => item.key === key);

        if (existing) {
            if (existing.qty < maxStock) {
                existing.qty++;
            }
        } else {
            this.cartItems.push({
                key: key,
                product_id: productId,
                name: name,
                price: price,
                image: image,
                type: type,
                qty: 1,
                max_stock: maxStock
            });
        }

        localStorage.setItem('abodafarm_cart', JSON.stringify(this.cartItems));
        window.dispatchEvent(new Event('cart-updated'));
    }
}" x-on:add-to-cart-from-wishlist.window="addToCart(
    $event.detail.product_id,
    $event.detail.name,
    $event.detail.price,
    $event.detail.image,
    $event.detail.type,
    $event.detail.max_stock
)">
    {{-- Header --}}
    <div class="flex items-center justify-between rounded-2xl border border-rose-100 bg-gradient-to-r from-rose-500 to-pink-500 p-6 shadow-lg">
        <div class="flex items-center gap-4">
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-white/20 text-xl text-white">
                <i class="fa-solid fa-heart"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-white">Wishlist</h2>
                <p class="mt-0.5 text-rose-100">Produk yang Anda simpan</p>
            </div>
        </div>
        <div class="hidden text-right sm:block">
            <p class="text-3xl font-bold text-white">{{ count($items) }}</p>
            <p class="text-sm text-rose-200">produk</p>
        </div>
    </div>

    {{-- Empty State --}}
    @if(count($items) === 0)
        <div class="flex flex-col items-center gap-4 rounded-2xl border border-dashed border-slate-300 bg-slate-50 py-16 text-center">
            <div class="relative">
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                    <i class="fa-solid fa-heart text-3xl"></i>
                </div>
                <div class="absolute -right-2 -top-2 flex h-8 w-8 items-center justify-center rounded-full bg-rose-100 text-rose-500">
                    <i class="fa-solid fa-plus text-sm"></i>
                </div>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-slate-700">Wishlist kosong</h3>
                <p class="mt-1 text-sm text-slate-500">Simpan produk favorit Anda untuk nanti.</p>
            </div>
            <a
                href="{{ route('shop') }}"
                class="rounded-xl bg-gradient-to-r from-rose-500 to-pink-500 px-6 py-2.5 text-sm font-semibold text-white shadow-lg transition hover:shadow-xl"
            >
                <i class="fa-solid fa-bag-shopping mr-2"></i>
                Mulai Belanja
            </a>
        </div>
    @else
        {{-- Wishlist Grid --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach($items as $item)
                <article class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-all duration-300 hover:shadow-lg">
                    {{-- Remove Button --}}
                    <button
                        wire:click="removeFromWishlist({{ $item['product_id'] }})"
                        wire:confirm="Hapus produk ini dari wishlist?"
                        class="absolute right-3 top-3 z-10 flex h-8 w-8 items-center justify-center rounded-full bg-white/90 text-slate-400 shadow-sm backdrop-blur-sm transition-all hover:bg-white hover:text-rose-500"
                    >
                        <i class="fa-solid fa-xmark"></i>
                    </button>

                    {{-- Image --}}
                    <a href="{{ route('shop.product', $item['slug']) }}" class="block">
                        <div class="relative aspect-square overflow-hidden bg-gradient-to-br from-slate-100 to-slate-200">
                            @if($item['image'])
                                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}"
                                     class="size-full object-cover transition-transform duration-500 group-hover:scale-105">
                            @else
                                <div class="flex size-full items-center justify-center">
                                    <i class="fa-solid fa-image text-4xl text-slate-300"></i>
                                </div>
                            @endif

                            {{-- Discount Badge --}}
                            @if($item['has_discount'])
                                <span class="absolute left-3 top-3 rounded-lg bg-red-500 px-2 py-1 text-xs font-bold text-white">
                                    SALE
                                </span>
                            @endif

                            {{-- Out of Stock Overlay --}}
                            @if(!$item['in_stock'])
                                <div class="absolute inset-0 flex items-center justify-center bg-black/40">
                                    <span class="rounded-lg bg-white px-3 py-1 text-sm font-semibold text-slate-700">Stok Habis</span>
                                </div>
                            @endif
                        </div>
                    </a>

                    {{-- Info --}}
                    <div class="p-4">
                        {{-- Category --}}
                        @if($item['category'])
                            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600">
                                {{ $item['category'] }}
                            </p>
                        @endif

                        {{-- Name --}}
                        <a href="{{ route('shop.product', $item['slug']) }}">
                            <h3 class="mt-1.5 line-clamp-2 text-sm font-bold text-slate-900 transition group-hover:text-emerald-600">
                                {{ $item['name'] }}
                            </h3>
                        </a>

                        {{-- Price --}}
                        <div class="mt-2">
                            @if($item['has_discount'])
                                <span class="text-lg font-bold text-red-600">{{ $item['price'] }}</span>
                                <span class="ml-1 text-sm text-slate-400 line-through">{{ $item['original_price'] }}</span>
                            @else
                                <span class="text-lg font-bold text-emerald-600">{{ $item['price'] }}</span>
                            @endif
                        </div>

                        {{-- Stock Status --}}
                        @if($item['in_stock'])
                            <p class="mt-1 text-xs text-emerald-600">
                                <i class="fa-solid fa-circle-check mr-1"></i>
                                Tersedia
                            </p>
                        @else
                            <p class="mt-1 text-xs text-red-500">
                                <i class="fa-solid fa-circle-xmark mr-1"></i>
                                Stok habis
                            </p>
                        @endif

                        {{-- Actions --}}
                        <div class="mt-4 flex gap-2">
                            @if($item['type'] === 'live' || $item['type'] === 'inquiry')
                                {{-- Booking button for livestock --}}
                                <button
                                    wire:click="bookProduct({{ $item['product_id'] }})"
                                    @if(!$item['in_stock']) disabled @endif
                                    class="flex flex-1 items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:bg-slate-300"
                                >
                                    <i class="fa-solid fa-calendar-check"></i>
                                    Booking
                                </button>
                            @else
                                {{-- Add to cart for regular products --}}
                                <button
                                    wire:click="moveToCart({{ $item['product_id'] }})"
                                    @if(!$item['in_stock']) disabled @endif
                                    class="flex flex-1 items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:bg-slate-300"
                                >
                                    <i class="fa-solid fa-cart-plus"></i>
                                    +Keranjang
                                </button>
                            @endif
                            <a href="{{ route('shop.product', $item['slug']) }}"
                               class="flex items-center justify-center gap-1.5 rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif

    {{-- Info Card --}}
    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                <i class="fa-solid fa-circle-info"></i>
            </div>
            <div class="text-sm text-slate-600">
                <p class="font-medium text-slate-900">Tips Wishlist</p>
                <ul class="mt-1 space-y-1 text-xs">
                    <li>- Produk di wishlist tersimpan otomatis di akun Anda</li>
                    <li>- Stok bisa berubah sewaktu-waktu, jadi cepat checkout ya!</li>
                    <li>- Tekan hati untuk menambahkan atau menghapus produk</li>
                </ul>
            </div>
        </div>
    </div>
</div>
