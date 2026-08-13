<!-- Cart Drawer Component -->
<div x-data="{
    // Get cartOpen from window state (for cross-component sync)
    get cartOpen() { return window.__cartDrawerOpen || false; },
    set cartOpen(val) { window.__cartDrawerOpen = val; },
    cartItems: JSON.parse(localStorage.getItem('abodafarm_cart') || '[]'),
    get cartCount() { return this.cartItems.reduce((sum, item) => sum + item.qty, 0); },
    get cartTotal() {
        const total = this.cartItems.reduce((sum, item) => {
            const price = parseFloat(item.price) || 0;
            const qty = parseInt(item.qty) || 0;
            return sum + (price * qty);
        }, 0);
        return isNaN(total) ? 0 : total;
    },
    stockInfo: {},
    stockWarnings: {},
    isCheckingStock: false,
    stockLoaded: false,

    init() {
        // Listen for cart updates from parent
        window.addEventListener('cart-updated', () => {
            this.cartItems = JSON.parse(localStorage.getItem('abodafarm_cart') || '[]');
            this.checkAllStock();
        });

        // Also listen for storage events (from other tabs)
        window.addEventListener('storage', (e) => {
            if (e.key === 'abodafarm_cart') {
                this.cartItems = JSON.parse(e.newValue || '[]');
                this.checkAllStock();
            }
        });

        // Listen for stock-refresh event
        window.addEventListener('stock-refresh', () => {
            this.checkAllStock();
        });

        // Listen for toggle-cart event from topbar and other components
        window.addEventListener('toggle-cart', () => {
            this.cartOpen = !this.cartOpen;
            if (this.cartOpen) {
                this.checkAllStock();
            }
        });

        // Initial stock check
        this.checkAllStock();
    },

    async checkAllStock() {
        if (this.cartItems.length === 0) {
            this.stockLoaded = true;
            return;
        }

        this.isCheckingStock = true;

        const productIds = this.cartItems.map(i => i.product_id).filter(Boolean);
        if (productIds.length === 0) {
            this.isCheckingStock = false;
            this.stockLoaded = true;
            return;
        }

        try {
            const response = await fetch('/shop/check-stock-bulk', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                },
                body: JSON.stringify({ product_ids: productIds })
            });
            const data = await response.json();

            for (const item of this.cartItems) {
                const stock = data[item.product_id] || { stock: 0, available: false };
                this.stockInfo[item.key] = stock;
                this.stockWarnings[item.key] = {
                    available: stock.available,
                    stock: stock.stock,
                    maxExceeded: item.qty > stock.stock,
                    message: stock.stock > 0
                        ? `Stok tersisa: ${stock.stock} unit`
                        : 'Stok habis'
                };
            }
        } catch (e) {
            console.error('Error checking stock:', e);
            // Fallback - mark all as unknown
            for (const item of this.cartItems) {
                this.stockWarnings[item.key] = null;
            }
        } finally {
            this.isCheckingStock = false;
            this.stockLoaded = true;
        }
    },

    async decreaseQty(key) {
        const item = this.cartItems.find(i => i.key === key);
        if (item) {
            if (item.qty <= 1) {
                this.removeItem(key);
            } else {
                item.qty--;
                localStorage.setItem('abodafarm_cart', JSON.stringify(this.cartItems));
                window.dispatchEvent(new Event('cart-updated'));
                this.checkAllStock();
            }
        }
    },

    async increaseQty(key) {
        const item = this.cartItems.find(i => i.key === key);
        if (item && item.product_id) {
            const stockData = this.stockInfo[key];
            if (stockData && item.qty >= stockData.stock) {
                window.dispatchEvent(new CustomEvent('alert', {
                    detail: { type: 'error', message: 'Stok tidak mencukupi! Maksimal: ' + stockData.stock + ' unit' }
                }));
                return;
            }

            try {
                const response = await fetch('/shop/validate-cart/' + item.product_id + '?qty=' + (item.qty + 1));
                const validation = await response.json();

                if (!validation.valid) {
                    window.dispatchEvent(new CustomEvent('alert', {
                        detail: { type: 'error', message: validation.message }
                    }));
                    return;
                }

                item.qty++;
                localStorage.setItem('abodafarm_cart', JSON.stringify(this.cartItems));
                window.dispatchEvent(new Event('cart-updated'));
                this.checkAllStock();
            } catch (e) {
                if (stockData && item.qty >= stockData.stock) {
                    window.dispatchEvent(new CustomEvent('alert', {
                        detail: { type: 'error', message: 'Stok tidak mencukupi!' }
                    }));
                    return;
                }
                item.qty++;
                localStorage.setItem('abodafarm_cart', JSON.stringify(this.cartItems));
                window.dispatchEvent(new Event('cart-updated'));
            }
        } else if (item) {
            item.qty++;
            localStorage.setItem('abodafarm_cart', JSON.stringify(this.cartItems));
            window.dispatchEvent(new Event('cart-updated'));
        }
    },

    removeItem(key) {
        this.cartItems = this.cartItems.filter(i => i.key !== key);
        delete this.stockInfo[key];
        delete this.stockWarnings[key];
        localStorage.setItem('abodafarm_cart', JSON.stringify(this.cartItems));
        window.dispatchEvent(new Event('cart-updated'));
    },

    clearCart() {
        this.cartItems = [];
        this.stockInfo = {};
        this.stockWarnings = {};
        localStorage.setItem('abodafarm_cart', '[]');
        window.dispatchEvent(new Event('cart-updated'));
        window.dispatchEvent(new Event('clear-cart'));
    },

    getItemStockStatus(key) {
        return this.stockWarnings[key] || null;
    },

    getItemStockInfo(key) {
        return this.stockInfo[key] || null;
    }
}">
    <!-- Backdrop -->
    <div x-show="cartOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 bg-slate-900/50"
         @click.self="cartOpen = false"></div>

    <!-- Drawer -->
    <aside x-show="cartOpen"
           x-transition:enter="transition ease-out duration-300"
           x-transition:enter-start="translate-x-full"
           x-transition:enter-end="translate-x-0"
           x-transition:leave="transition ease-in duration-200"
           x-transition:leave-start="translate-x-0"
           x-transition:leave-end="translate-x-full"
           class="fixed right-0 top-0 z-50 flex h-full w-full max-w-md flex-col bg-white shadow-2xl">

        <!-- Header -->
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                    <i class="fa-solid fa-bag-shopping text-lg"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Keranjang Belanja</h2>
                    <p class="text-xs text-slate-500"><span x-text="cartCount"></span> item</p>
                </div>
            </div>
            <button @click="cartOpen = false" class="rounded-lg border border-slate-200 p-2 text-sm text-slate-500 transition hover:bg-slate-50">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto p-5">
            <!-- Empty State -->
            <template x-if="cartCount === 0">
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <div class="mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                        <i class="fa-solid fa-cart-shopping text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-700">Keranjang Kosong</h3>
                    <p class="mt-1 text-sm text-slate-500">Tambahkan produk untuk memulai belanja</p>
                    <button @click="cartOpen = false" class="mt-4 rounded-xl bg-emerald-500 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-600">
                        Mulai Belanja
                    </button>
                </div>
            </template>

            <!-- Cart Items -->
            <template x-if="cartCount > 0">
                <div class="space-y-4">
                    <!-- Loading indicator -->
                    <div x-show="isCheckingStock && !stockLoaded" class="flex items-center gap-2 rounded-lg bg-blue-50 px-4 py-2 text-sm text-blue-600">
                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Memperbarui stok...
                    </div>

                    <!-- Items -->
                    <template x-for="item in cartItems" :key="item.key">
                        <div class="rounded-xl border p-4 transition-all"
                             :class="getItemStockStatus(item.key)?.maxExceeded ? 'border-amber-300 bg-amber-50' :
                                     (getItemStockStatus(item.key)?.available === false ? 'border-red-300 bg-red-50' : 'border-slate-200')">

                            <!-- Stock Badge -->
                            <div class="mb-3 flex items-center justify-between">
                                <template x-if="!stockLoaded">
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">
                                        <i class="fa-solid fa-clock mr-1"></i>Memuat...
                                    </span>
                                </template>
                                <template x-if="stockLoaded && getItemStockStatus(item.key)">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-medium"
                                          :class="getItemStockStatus(item.key)?.available === false ? 'bg-red-500 text-white' :
                                                  (getItemStockStatus(item.key)?.maxExceeded ? 'bg-amber-500 text-white' : 'bg-emerald-100 text-emerald-700')">
                                        <template x-if="getItemStockStatus(item.key)?.available === false">
                                            <span><i class='fa-solid fa-ban mr-1'></i>Stok Habis</span>
                                        </template>
                                        <template x-if="getItemStockStatus(item.key)?.available && !getItemStockStatus(item.key)?.maxExceeded">
                                            <span><i class='fa-solid fa-check mr-1'></i>Stok: <span x-text="getItemStockStatus(item.key)?.stock || 0"></span></span>
                                        </template>
                                        <template x-if="getItemStockStatus(item.key)?.maxExceeded && getItemStockStatus(item.key)?.available !== false">
                                            <span><i class='fa-solid fa-exclamation mr-1'></i>Melebihi Stok</span>
                                        </template>
                                    </span>
                                </template>
                            </div>

                            <!-- Product Info -->
                            <div class="flex gap-3">
                                <img :src="item.image" :alt="item.name"
                                     class="h-16 w-16 flex-shrink-0 rounded-lg object-cover">

                                <div class="min-w-0 flex-1">
                                    <h3 class="truncate text-sm font-semibold text-slate-900" x-text="item.name"></h3>
                                    <p class="mt-0.5 text-sm text-slate-500">
                                        Rp <span x-text="Number(item.price || 0).toLocaleString('id-ID')"></span>
                                    </p>

                                    <!-- Quantity Controls -->
                                    <div class="mt-2 flex items-center justify-between">
                                        <div class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-1 py-1">
                                            <button @click="decreaseQty(item.key)"
                                                    :disabled="isCheckingStock && !stockLoaded"
                                                    class="flex h-7 w-7 items-center justify-center rounded-md bg-slate-100 text-xs font-bold text-slate-600 transition hover:bg-emerald-50 hover:text-emerald-600 disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-300">
                                                <i class="fa-solid fa-minus"></i>
                                            </button>
                                            <span class="min-w-8 text-center text-sm font-semibold" x-text="item.qty"></span>
                                            <button @click="increaseQty(item.key)"
                                                    :disabled="(isCheckingStock && !stockLoaded) || getItemStockStatus(item.key)?.available === false"
                                                    class="flex h-7 w-7 items-center justify-center rounded-md bg-slate-100 text-xs font-bold text-slate-600 transition hover:bg-emerald-50 hover:text-emerald-600 disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-300">
                                                <i class="fa-solid fa-plus"></i>
                                            </button>
                                        </div>

                                        <button @click="removeItem(item.key)"
                                                class="rounded-lg p-2 text-xs font-medium text-red-500 transition hover:bg-red-50">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Stock Warning -->
                            <template x-if="stockLoaded && getItemStockStatus(item.key)?.available === false">
                                <div class="mt-3 rounded-lg bg-red-100 px-3 py-2 text-xs text-red-700">
                                    <i class="fa-solid fa-ban mr-1"></i>
                                    Produk ini sudah habis. Silakan hapus sebelum checkout.
                                </div>
                            </template>
                            <template x-if="stockLoaded && getItemStockStatus(item.key)?.maxExceeded">
                                <div class="mt-3 rounded-lg bg-amber-100 px-3 py-2 text-xs text-amber-700">
                                    <i class="fa-solid fa-exclamation mr-1"></i>
                                    Melebihi stok! Maksimal: <span x-text="getItemStockStatus(item.key)?.stock || 0"></span> unit
                                </div>
                            </template>
                        </div>
                    </template>

                    <!-- Alerts -->
                    <template x-if="stockLoaded && Object.values(stockWarnings).some(w => w && !w.available)">
                        <div class="rounded-lg bg-red-50 border border-red-200 p-3">
                            <p class="text-sm text-red-700">
                                <i class="fa-solid fa-exclamation-circle mr-1"></i>
                                Beberapa item sudah habis. Silakan hapus sebelum checkout.
                            </p>
                        </div>
                    </template>
                </div>
            </template>
        </div>

        <!-- Footer -->
        <template x-if="cartCount > 0">
            <div class="border-t border-slate-200 p-5">
                <!-- Summary -->
                <div class="mb-4 space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-600">Jumlah Item</span>
                        <span class="font-semibold" x-text="cartCount"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-base font-medium text-slate-800">Total</span>
                        <span class="text-xl font-bold text-emerald-600">
                            Rp <span x-text="Number(cartTotal).toLocaleString('id-ID')"></span>
                        </span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="grid grid-cols-2 gap-3">
                    <button @click="clearCart()"
                            class="rounded-xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        <i class="fa-solid fa-trash-can mr-2"></i>Kosongkan
                    </button>
                    <a :href="(!stockLoaded || Object.values(stockWarnings).some(w => w && (!w.available || w.maxExceeded))) ? '#' : '{{ route('checkout') }}'"
                       :class="(!stockLoaded || Object.values(stockWarnings).some(w => w && (!w.available || w.maxExceeded)))
                           ? 'pointer-events-none rounded-xl bg-slate-300 px-4 py-3 text-center text-sm font-semibold text-white'
                           : 'rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-600 px-4 py-3 text-center text-sm font-semibold text-white shadow-lg shadow-emerald-500/30 transition hover:from-emerald-600 hover:to-emerald-700'"
                       @click="(!stockLoaded || Object.values(stockWarnings).some(w => w && (!w.available || w.maxExceeded))) && $event.preventDefault()">
                        <i class="fa-solid fa-arrow-right mr-2"></i>Checkout
                    </a>
                </div>
            </div>
        </template>
    </aside>
</div>
