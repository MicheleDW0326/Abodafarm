<div x-show="cartOpen" x-transition.opacity class="fixed inset-0 z-50 bg-slate-900/50" @click.self="cartOpen = false">
    <aside class="ml-auto h-full w-full max-w-md overflow-y-auto bg-white p-5 shadow-2xl">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-xl font-semibold">Keranjang Belanja</h2>
            <button @click="cartOpen = false" class="rounded-lg border border-slate-200 px-2 py-1 text-sm">Tutup</button>
        </div>

        <div x-data="{
            cartItems: JSON.parse(localStorage.getItem('abodafarm_cart') || '[]'),
            get cartCount() { return this.cartItems.reduce((sum, item) => sum + item.qty, 0); },
            get cartTotal() { return this.cartItems.reduce((sum, item) => sum + (item.price * item.qty), 0); },
            stockInfo: {},
            stockWarnings: {},
            isCheckingStock: false,

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

                // Check stock for all items on load
                this.checkAllStock();
            },

            async checkAllStock() {
                if (this.cartItems.length === 0) return;
                this.isCheckingStock = true;

                const productIds = this.cartItems.map(i => i.product_id).filter(Boolean);
                if (productIds.length === 0) {
                    this.isCheckingStock = false;
                    return;
                }

                try {
                    // Bulk stock check endpoint
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
                    // Fallback - check each individually
                    for (const item of this.cartItems) {
                        if (item.product_id) {
                            try {
                                const response = await fetch('/shop/check-stock/' + item.product_id);
                                const data = await response.json();
                                this.stockInfo[item.key] = data;
                                this.stockWarnings[item.key] = {
                                    available: data.available,
                                    stock: data.stock,
                                    maxExceeded: item.qty > data.stock,
                                    message: data.message
                                };
                            } catch (e) {
                                console.error('Error checking stock for item:', item.product_id);
                            }
                        }
                    }
                } finally {
                    this.isCheckingStock = false;
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

                    // Validate with server
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
                        // Fallback - allow increase with client-side check
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
                    // For items without product_id, just increase
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
            <template x-if="cartCount === 0">
                <p class="rounded-xl border border-dashed border-slate-300 p-4 text-sm text-slate-500">Keranjang
                    kamu
                    masih kosong. Tambahkan produk dari section Produk.</p>
            </template>

            <template x-if="cartCount > 0">
                <div>
                    <!-- Loading indicator -->
                    <div x-show="isCheckingStock" class="mb-3 flex items-center gap-2 text-xs text-slate-500">
                        <svg class="h-3 w-3 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Memperbarui stok...
                    </div>

                    <div class="space-y-3">
                        <template x-for="item in cartItems" :key="item.key">
                            <article class="rounded-xl border p-3 transition-all"
                                     :class="getItemStockStatus(item.key)?.maxExceeded ? 'border-amber-400 bg-amber-50' :
                                            (getItemStockStatus(item.key)?.available === false ? 'border-red-300 bg-red-50' : 'border-slate-200')">
                                <!-- Stock Info Badge -->
                                <div class="mb-2 flex items-center justify-between">
                                    <div class="flex items-center gap-1.5 rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                         :class="getItemStockStatus(item.key)?.available === false ? 'bg-red-500 text-white' :
                                                 (getItemStockStatus(item.key)?.maxExceeded ? 'bg-amber-500 text-white' : 'bg-emerald-100 text-emerald-700')">
                                        <template x-if="getItemStockStatus(item.key)?.available === false">
                                            <span><i class='fa-solid fa-ban mr-1'></i>Stok Habis</span>
                                        </template>
                                        <template x-if="getItemStockStatus(item.key)?.available && !getItemStockStatus(item.key)?.maxExceeded">
                                            <span><i class='fa-solid fa-box mr-1'></i>Stok: <span x-text="getItemStockStatus(item.key)?.stock || 0"></span></span>
                                        </template>
                                        <template x-if="getItemStockStatus(item.key)?.maxExceeded && getItemStockStatus(item.key)?.available !== false">
                                            <span><i class='fa-solid fa-triangle-exclamation mr-1'></i>Melebihi Stok</span>
                                        </template>
                                    </div>
                                </div>

                                <!-- Stock Warning Message -->
                                <template x-if="getItemStockStatus(item.key)?.maxExceeded && getItemStockStatus(item.key)?.available">
                                    <div class="mb-2 rounded-lg bg-amber-100 px-3 py-2 text-xs text-amber-800">
                                        <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                                        <span x-text="'Melebihi stok! Maksimal: ' + (getItemStockStatus(item.key)?.stock || 0) + ' unit'"></span>
                                    </div>
                                </template>

                                <template x-if="!getItemStockStatus(item.key)?.available">
                                    <div class="mb-2 rounded-lg bg-red-100 px-3 py-2 text-xs text-red-800">
                                        <i class="fa-solid fa-ban mr-1"></i>
                                        <span>Produk ini sudah habis. Silakan hapus sebelum checkout.</span>
                                    </div>
                                </template>

                                <div class="flex gap-3">
                                    <img :src="item.image" :alt="item.name"
                                        class="size-16 rounded-lg object-cover">
                                    <div class="flex-1">
                                        <h3 class="text-sm font-semibold" x-text="item.name"></h3>
                                        <p class="mt-0.5 text-xs text-slate-500">
                                            Rp <span x-text="Number(item.price).toLocaleString('id-ID')"></span> / item
                                        </p>
                                        <div class="mt-2 flex items-center justify-between">
                                            <div class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-2 py-1">
                                                <button @click="decreaseQty(item.key)"
                                                    class="flex h-6 w-6 items-center justify-center rounded-md bg-slate-100 text-sm font-bold text-slate-600 transition hover:bg-emerald-50 hover:text-emerald-600">
                                                    <i class="fa-solid fa-minus text-xs"></i>
                                                </button>
                                                <span class="min-w-6 text-center text-sm font-semibold" x-text="item.qty"></span>
                                                <button @click="increaseQty(item.key)"
                                                    :disabled="getItemStockStatus(item.key)?.available === false"
                                                    :class="getItemStockStatus(item.key)?.available === false ? 'bg-slate-200 text-slate-400 cursor-not-allowed' : 'bg-slate-100 text-slate-600 hover:bg-emerald-50 hover:text-emerald-600'"
                                                    class="flex h-6 w-6 items-center justify-center rounded-md text-sm font-bold transition">
                                                    <i class="fa-solid fa-plus text-xs"></i>
                                                </button>
                                            </div>
                                            <button @click="removeItem(item.key)"
                                                class="text-xs font-semibold text-rose-600 hover:text-rose-700">
                                                <i class="fa-solid fa-trash-can mr-1"></i>Hapus
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        </template>
                    </div>

                    <!-- Out of Stock Alert -->
                    <template x-if="Object.values(stockWarnings).some(w => !w.available)">
                        <div class="mt-4 rounded-lg bg-red-50 border border-red-200 p-3">
                            <p class="text-sm text-red-700">
                                <i class="fa-solid fa-exclamation-circle mr-1"></i>
                                Beberapa item di keranjang sudah habis. Silakan hapus atau ubah jumlah sebelum checkout.
                            </p>
                        </div>
                    </template>

                    <!-- Stock Exceeded Alert -->
                    <template x-if="Object.values(stockWarnings).some(w => w.maxExceeded && w.available)">
                        <div class="mt-4 rounded-lg bg-amber-50 border border-amber-200 p-3">
                            <p class="text-sm text-amber-700">
                                <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                                Jumlah beberapa item melebihi stok yang tersedia. Silakan ubah jumlah sebelum checkout.
                            </p>
                        </div>
                    </template>

                    <div class="mt-5 rounded-xl bg-sky-50 p-4">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-600">Jumlah Item</span>
                            <span class="font-semibold" x-text="cartCount"></span>
                        </div>
                        <div class="mt-2 flex items-center justify-between">
                            <span class="text-slate-700">Total</span>
                            <span class="text-xl font-bold text-sky-700">Rp <span x-text="Number(cartTotal).toLocaleString('id-ID')"></span></span>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-2">
                            <button @click="clearCart()"
                                class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Kosongkan</button>
                            <a href="{{ route('checkout') }}"
                                :class="Object.values(stockWarnings).some(w => !w.available || w.maxExceeded)
                                    ? 'rounded-lg bg-slate-400 px-3 py-2 text-center text-sm font-semibold text-white cursor-not-allowed pointer-events-none'
                                    : 'rounded-lg bg-emerald-500 px-3 py-2 text-center text-sm font-semibold text-white hover:bg-emerald-600'"
                                :href="Object.values(stockWarnings).some(w => !w.available || w.maxExceeded) ? '#' : '{{ route('checkout') }}'">
                                Checkout
                            </a>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </aside>
</div>
