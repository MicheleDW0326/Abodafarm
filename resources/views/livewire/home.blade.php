<div
    x-data="{
        mobileOpen: false,
        cartOpen: false,
        showToast: false,
        toastText: '',
        toastTimer: null,
        showAddToast(name) {
            this.toastText = `${name} ditambahkan ke keranjang`
            this.showToast = true
            clearTimeout(this.toastTimer)
            this.toastTimer = setTimeout(() => {
                this.showToast = false
            }, 2200)
        }
    }"
    x-on:cart-item-added.window="showAddToast($event.detail.name)"
    class="bg-sky-50 text-slate-900"
>
    <header class="sticky top-0 z-40 border-b border-sky-100/80 bg-white/85 backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
            <a href="#" class="flex items-center gap-3">
                <span class="grid size-10 place-content-center rounded-xl bg-linear-to-br from-teal-500 to-sky-500 text-lg font-bold text-white">FG</span>
                <span class="text-xl font-semibold tracking-tight">FreshGo</span>
            </a>

            <nav class="hidden items-center gap-8 text-sm font-medium md:flex">
                <a href="#kategori" class="transition hover:text-sky-600">Kategori</a>
                <a href="#produk" class="transition hover:text-sky-600">Produk</a>
                <a href="#keunggulan" class="transition hover:text-sky-600">Keunggulan</a>
                <a href="#faq" class="transition hover:text-sky-600">FAQ</a>
            </nav>

            <div class="hidden items-center gap-3 md:flex">
                @guest
                    <a href="{{ route('login') }}" class="rounded-xl border border-sky-200 px-4 py-2 text-sm font-medium text-sky-700 transition hover:bg-sky-50">Masuk</a>
                @endguest
                <button @click="cartOpen = true" class="relative rounded-xl border border-sky-200 bg-white px-4 py-2 text-sm font-semibold text-sky-700 transition hover:bg-sky-50">
                    Keranjang
                    @if ($this->cartCount > 0)
                        <span class="absolute -right-2 -top-2 grid size-5 place-content-center rounded-full bg-emerald-500 text-[11px] font-bold text-white">
                            {{ $this->cartCount }}
                        </span>
                    @endif
                </button>
                <a href="#produk" class="rounded-xl bg-sky-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-sky-300/60 transition hover:bg-sky-700">Belanja</a>
            </div>

            <button @click="mobileOpen = !mobileOpen" class="rounded-lg border border-sky-200 p-2 md:hidden" aria-label="Toggle menu">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>

        <div x-show="mobileOpen" x-transition class="border-t border-sky-100 bg-white px-4 py-4 md:hidden">
            <div class="flex flex-col gap-3 text-sm font-medium">
                <a href="#kategori" @click="mobileOpen = false">Kategori</a>
                <a href="#produk" @click="mobileOpen = false">Produk</a>
                <a href="#keunggulan" @click="mobileOpen = false">Keunggulan</a>
                <a href="#faq" @click="mobileOpen = false">FAQ</a>
                <button @click="mobileOpen = false; cartOpen = true" class="mt-2 rounded-lg border border-sky-200 px-3 py-2 text-left">Keranjang ({{ $this->cartCount }})</button>
            </div>
        </div>
    </header>

    <div x-show="cartOpen" x-transition.opacity class="fixed inset-0 z-50 bg-slate-900/50" @click.self="cartOpen = false">
        <aside class="ml-auto h-full w-full max-w-md overflow-y-auto bg-white p-5 shadow-2xl">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-xl font-semibold">Keranjang Belanja</h2>
                <button @click="cartOpen = false" class="rounded-lg border border-slate-200 px-2 py-1 text-sm">Tutup</button>
            </div>

            @if ($this->cartCount === 0)
                <p class="rounded-xl border border-dashed border-slate-300 p-4 text-sm text-slate-500">Keranjang kamu masih kosong. Tambahkan produk dari section Produk Terlaris.</p>
            @else
                <div class="space-y-3">
                    @foreach ($this->cartItems as $item)
                        <article class="rounded-xl border border-slate-200 p-3">
                            <div class="flex gap-3">
                                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="size-16 rounded-lg object-cover">
                                <div class="flex-1">
                                    <h3 class="text-sm font-semibold">{{ $item['name'] }}</h3>
                                    <p class="mt-0.5 text-xs text-slate-500">Rp {{ number_format($item['price']) }} / item</p>
                                    <div class="mt-2 flex items-center justify-between">
                                        <div class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-2 py-1">
                                            <button wire:click="decreaseQty('{{ $item['key'] }}')" class="text-sm font-bold text-slate-600">-</button>
                                            <span class="min-w-4 text-center text-sm font-semibold">{{ $item['qty'] }}</span>
                                            <button wire:click="increaseQty('{{ $item['key'] }}')" class="text-sm font-bold text-slate-600">+</button>
                                        </div>
                                        <button wire:click="removeItem('{{ $item['key'] }}')" class="text-xs font-semibold text-rose-600">Hapus</button>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-5 rounded-xl bg-sky-50 p-4">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-600">Jumlah Item</span>
                        <span class="font-semibold">{{ $this->cartCount }}</span>
                    </div>
                    <div class="mt-2 flex items-center justify-between">
                        <span class="text-slate-700">Total</span>
                        <span class="text-xl font-bold text-sky-700">Rp {{ number_format($this->cartTotal) }}</span>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <button wire:click="clearCart" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700">Kosongkan</button>
                        <a href="{{ route('checkout') }}" class="rounded-lg bg-emerald-500 px-3 py-2 text-center text-sm font-semibold text-white">Checkout</a>
                    </div>
                </div>
            @endif
        </aside>
    </div>

    <div
        x-show="showToast"
        x-transition
        class="fixed bottom-5 right-5 z-60 rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow-xl"
    >
        <span x-text="toastText"></span>
    </div>

    <livewire:home.hero :hero-slides="$heroSlides" />
    <livewire:home.categories :categories="$categories" />
    <livewire:home.products :products="$products" />
    <livewire:home.highlights :highlights="$highlights" />
    <livewire:home.delivery :delivery-steps="$deliverySteps" />
    <livewire:home.testimonials :testimonials="$testimonials" />
    <livewire:home.faq :faqs="$faqs" />
    <livewire:home.cta />
    <livewire:home.footer />
</div>
