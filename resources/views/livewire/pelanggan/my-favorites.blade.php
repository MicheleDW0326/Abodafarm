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
                <p class="mt-0.5 text-rose-100">Koleksi produk favorit Anda</p>
            </div>
        </div>
        <div class="hidden text-right sm:block">
            <p class="text-3xl font-bold text-white">{{ count($favorites) }}</p>
            <p class="text-sm text-rose-200">produk</p>
        </div>
    </div>

    {{-- Filter --}}
    @if(count($favorites) > 0)
        <div class="flex items-center gap-2">
            @foreach($typeOptions as $value => $label)
                <button
                    wire:click="$set('typeFilter', '{{ $value }}')"
                    class="rounded-full px-4 py-2 text-sm font-medium transition @if($typeFilter === $value) bg-rose-500 text-white @else border border-slate-200 bg-white text-slate-600 hover:border-rose-300 hover:text-rose-600 @endif"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>
    @endif

    {{-- Favorites Grid --}}
    @if(count($favorites) > 0)
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach($favorites as $item)
                @php $product = $item['product']; @endphp
                @if($product)
                    <article class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                        {{-- Remove Button --}}
                        <button
                            wire:click="removeFavorite({{ $product->id }})"
                            wire:confirm="Hapus dari wishlist?"
                            class="absolute right-3 top-3 z-10 flex h-8 w-8 items-center justify-center rounded-full bg-white/90 text-slate-400 shadow-sm backdrop-blur-sm transition hover:bg-white hover:text-rose-500"
                        >
                            <i class="fa-solid fa-xmark"></i>
                        </button>

                        {{-- Image --}}
                        <a href="{{ route('shop.product', $product->slug) }}" class="block">
                            <div class="relative aspect-square overflow-hidden bg-gradient-to-br from-slate-100 to-slate-200">
                                @if($product->image)
                                    <img
                                        src="{{ $product->image_url }}"
                                        alt="{{ $product->name }}"
                                        class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                    >
                                @else
                                    <div class="flex h-full w-full items-center justify-center text-slate-300">
                                        <i class="fa-solid fa-image text-4xl"></i>
                                    </div>
                                @endif

                                {{-- Type Badge --}}
                                <span class="absolute left-3 top-3 rounded-full bg-white/90 px-2.5 py-1 text-xs font-semibold text-slate-700 shadow-sm">
                                    @if($product->type === 'live')
                                        <i class="fa-solid fa-cow mr-1 text-emerald-600"></i> Ternak
                                    @else
                                        <i class="fa-solid fa-drumstick-bite mr-1 text-rose-600"></i> Daging Segar
                                    @endif
                                </span>

                                {{-- Out of Stock Overlay --}}
                                @if($product->stock <= 0)
                                    <div class="absolute inset-0 flex items-center justify-center bg-black/40">
                                        <span class="rounded-lg bg-white px-3 py-1 text-sm font-semibold text-slate-700">Stok Habis</span>
                                    </div>
                                @endif
                            </div>
                        </a>

                        {{-- Content --}}
                        <div class="p-4">
                            <a href="{{ route('shop.product', $product->slug) }}">
                                <h3 class="font-semibold text-slate-900 line-clamp-2 transition group-hover:text-emerald-600">{{ $product->name }}</h3>
                            </a>

                            @if($product->type === 'live')
                                <p class="mt-1 flex items-center gap-2 text-xs text-slate-500">
                                    @if($product->breed)
                                        <span>{{ $product->breed }}</span>
                                    @endif
                                    @if($product->sex)
                                        <span class="rounded bg-slate-100 px-1.5 py-0.5">{{ $product->sex_label }}</span>
                                    @endif
                                </p>
                            @else
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $product->weight_per_unit ? $product->weight_per_unit . ' kg/unit' : '' }}
                                </p>
                            @endif

                            {{-- Price --}}
                            <div class="mt-2">
                                <p class="text-lg font-bold text-emerald-600">
                                    {{ $product->formatted_current_price }}
                                </p>
                                @if($product->has_discount)
                                    <p class="text-xs text-slate-400 line-through">{{ $product->formatted_price }}</p>
                                @endif
                            </div>

                            {{-- Actions --}}
                            <div class="mt-4 flex gap-2">
                                @if($product->type === 'live' || $product->type === 'inquiry')
                                    <button
                                        wire:click="bookProduct({{ $product->id }})"
                                        class="flex flex-1 items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700"
                                    >
                                        <i class="fa-solid fa-calendar-check"></i>
                                        Booking
                                    </button>
                                @else
                                    <button
                                        wire:click="moveToCart({{ $product->id }})"
                                        @if($product->stock <= 0) disabled @endif
                                        class="flex flex-1 items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:bg-slate-300"
                                    >
                                        <i class="fa-solid fa-cart-plus"></i>
                                        +Keranjang
                                    </button>
                                @endif
                                <a href="{{ route('shop.product', $product->slug) }}"
                                   class="flex items-center justify-center gap-1.5 rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                            </div>
                        </div>
                    </article>
                @endif
            @endforeach
        </div>
    @else
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
            <a href="{{ route('shop') }}" class="mt-2 rounded-xl bg-gradient-to-r from-rose-500 to-pink-500 px-6 py-2.5 text-sm font-semibold text-white shadow-lg transition hover:shadow-xl">
                <i class="fa-solid fa-bag-shopping mr-2"></i>
                Mulai Belanja
            </a>
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
                    <li>- Tekan ikon hati di produk untuk menambahkan</li>
                    <li>- Move to cart untuk membeli produk</li>
                    <li>- Booking untuk ternak langsung dari wishlist</li>
                </ul>
            </div>
        </div>
    </div>
</div>
