<!-- Cart Drawer Component - Simplified -->
<div x-data="{
    cartOpen: false,
    cartItems: [],
    stockInfo: {},
    isCheckingStock: false,
    _processingKeys: new Set(), // Guard against duplicate processing

    init() {
        // Load cart from localStorage
        this.loadCart();

        // Initialize window state
        if (window.__cartDrawerOpen === undefined) {
            window.__cartDrawerOpen = false;
        }
        this.cartOpen = window.__cartDrawerOpen;

        // Listen for toggle-cart event
        window.addEventListener('toggle-cart', () => {
            this.cartOpen = !this.cartOpen;
            window.__cartDrawerOpen = this.cartOpen;
            if (this.cartOpen) {
                this.checkAllStock();
            }
        });

        // Listen for add-to-cart event
        window.addEventListener('add-to-cart', (e) => {
            this.handleAddToCart(e.detail);
        });

        // Listen for cart-updated event
        window.addEventListener('cart-updated', () => {
            this.loadCart();
        });

        // Listen for storage changes (from other tabs)
        window.addEventListener('storage', (e) => {
            if (e.key === 'abodafarm_cart') {
                this.loadCart();
            }
        });
    },

    loadCart() {
        this.cartItems = JSON.parse(localStorage.getItem('abodafarm_cart') || '[]');
        localStorage.setItem('abodafarm_cart_last_activity', Date.now().toString());
    },

    get cartCount() {
        return this.cartItems.reduce((sum, item) => sum + item.qty, 0);
    },

    get cartTotal() {
        return this.cartItems.reduce((sum, item) => {
            return sum + (parseFloat(item.price) || 0) * (parseInt(item.qty) || 0);
        }, 0);
    },

    async handleAddToCart(product) {
        const key = product.key || 'product_' + product.id + '_' + (product.type || 'fresh');

        // GUARD: Prevent duplicate processing for same product
        // This prevents race conditions when user clicks rapidly or when multiple events fire
        if (this._processingKeys.has(key)) {
            console.log('[Cart] Already processing:', key);
            return;
        }
        this._processingKeys.add(key);
        console.log('[Cart] Processing add to cart for:', key, '| Current items in cart:', this.cartItems.length);

        // Re-fetch existing item after we set the processing flag
        // (in case of async race, the item might have been added by another call)
        let existing = this.cartItems.find(item => item.key === key);
        let currentQty = existing ? existing.qty : 0;

        // Check stock from server
        try {
            const response = await fetch(`/shop/check-stock/${product.id}`);
            const data = await response.json();
            const availableStock = data.stock || 0;

            // Re-check existing after async fetch
            existing = this.cartItems.find(item => item.key === key);
            currentQty = existing ? existing.qty : 0;

            if (!data.available) {
                window.dispatchEvent(new CustomEvent('alert', {
                    detail: { type: 'error', title: 'Stok Habis', message: 'Maaf, stok produk ini sudah habis.' }
                }));
                this._processingKeys.delete(key);
                return;
            }

            if (currentQty + 1 > availableStock) {
                window.dispatchEvent(new CustomEvent('alert', {
                    detail: { type: 'warning', title: 'Stok Terbatas', message: `Maaf, hanya ${availableStock} unit tersedia. Anda sudah memiliki ${currentQty} di keranjang.` }
                }));
                this._processingKeys.delete(key);
                return;
            }

            // Add to cart
            if (existing) {
                existing.qty++;
                console.log('[Cart] Updated existing item qty:', existing.qty);
            } else {
                this.cartItems.push({
                    key: key,
                    product_id: product.id,
                    name: product.name,
                    price: parseFloat(product.price) || 0,
                    image: product.image,
                    type: product.type || 'fresh',
                    qty: 1,
                    added_at: new Date().toISOString()
                });
                console.log('[Cart] Added new item to cart');
            }

            this.saveCart();
            console.log('[Cart] Cart saved. Total items:', this.cartItems.length, '| Cart count:', this.cartCount, '| Total:', this.cartTotal);
            window.dispatchEvent(new CustomEvent('cart-item-added', {
                detail: { name: product.name, key: key }
            }));
            window.dispatchEvent(new Event('cart-updated'));

        } catch (error) {
            console.error('[Cart] Stock check failed:', error);
            // Proceed without stock check if API fails
            // Re-check existing after error
            existing = this.cartItems.find(item => item.key === key);

            if (existing) {
                existing.qty++;
            } else {
                this.cartItems.push({
                    key: key,
                    product_id: product.id,
                    name: product.name,
                    price: parseFloat(product.price) || 0,
                    image: product.image,
                    type: product.type || 'fresh',
                    qty: 1,
                    added_at: new Date().toISOString()
                });
            }
            this.saveCart();
            console.log('[Cart] Fallback: Cart saved. Total items:', this.cartItems.length);
            window.dispatchEvent(new CustomEvent('cart-item-added', {
                detail: { name: product.name, key: key }
            }));
            window.dispatchEvent(new Event('cart-updated'));
        } finally {
            // Cleanup processing flag after a short delay to ensure all events are processed
            setTimeout(() => {
                this._processingKeys.delete(key);
            }, 300);
        }
    },

    saveCart() {
        localStorage.setItem('abodafarm_cart', JSON.stringify(this.cartItems));
        localStorage.setItem('abodafarm_cart_last_activity', Date.now().toString());
    },

    decreaseQty(key) {
        const item = this.cartItems.find(i => i.key === key);
        if (item) {
            if (item.qty <= 1) {
                this.removeItem(key);
            } else {
                item.qty--;
                this.saveCart();
                window.dispatchEvent(new Event('cart-updated'));
            }
        }
    },

    async increaseQty(key) {
        const item = this.cartItems.find(i => i.key === key);
        if (item && item.product_id) {
            // Check stock before incrementing
            try {
                const response = await fetch(`/shop/check-stock/${item.product_id}`);
                const data = await response.json();

                if (item.qty >= data.stock) {
                    window.dispatchEvent(new CustomEvent('alert', {
                        detail: { type: 'warning', title: 'Stok Terbatas', message: `Maaf, hanya ${data.stock} unit tersedia.` }
                    }));
                    return;
                }

                item.qty++;
                this.saveCart();
                window.dispatchEvent(new Event('cart-updated'));
            } catch (error) {
                console.error('Stock check failed:', error);
                // Allow increment if API fails
                item.qty++;
                this.saveCart();
                window.dispatchEvent(new Event('cart-updated'));
            }
        } else if (item) {
            item.qty++;
            this.saveCart();
            window.dispatchEvent(new Event('cart-updated'));
        }
    },

    removeItem(key) {
        this.cartItems = this.cartItems.filter(i => i.key !== key);
        this.saveCart();
        window.dispatchEvent(new Event('cart-updated'));
    },

    clearCart() {
        this.cartItems = [];
        this.saveCart();
        window.dispatchEvent(new Event('cart-updated'));
        window.dispatchEvent(new Event('clear-cart'));
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
            }
        } catch (e) {
            console.error('Stock check error:', e);
        } finally {
            this.isCheckingStock = false;
        }
    },

    getStockStatus(key) {
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
         class="fixed inset-0 z-50 bg-black/50"
         @click="cartOpen = false; window.__cartDrawerOpen = false"></div>

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
            <button @click="cartOpen = false; window.__cartDrawerOpen = false" class="rounded-lg border border-slate-200 p-2 text-sm text-slate-500 transition hover:bg-slate-50">
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
                    <button @click="cartOpen = false; window.__cartDrawerOpen = false" class="mt-4 rounded-xl bg-emerald-500 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-600">
                        Mulai Belanja
                    </button>
                </div>
            </template>

            <!-- Cart Items -->
            <template x-if="cartCount > 0">
                <div class="space-y-4">
                    <template x-for="item in cartItems" :key="item.key">
                        <div class="rounded-xl border border-slate-200 p-4">
                            <div class="flex gap-3">
                                <img :src="item.image" :alt="item.name" class="h-16 w-16 flex-shrink-0 rounded-lg object-cover">
                                <div class="min-w-0 flex-1">
                                    <h3 class="truncate text-sm font-semibold text-slate-900" x-text="item.name"></h3>
                                    <p class="mt-0.5 text-sm text-slate-500">Rp <span x-text="Number(item.price || 0).toLocaleString('id-ID')"></span></p>

                                    <!-- Quantity Controls -->
                                    <div class="mt-2 flex items-center justify-between">
                                        <div class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-1 py-1">
                                            <button @click="decreaseQty(item.key)" class="flex h-7 w-7 items-center justify-center rounded-md bg-slate-100 text-xs font-bold text-slate-600 transition hover:bg-emerald-50 hover:text-emerald-600">
                                                <i class="fa-solid fa-minus"></i>
                                            </button>
                                            <span class="min-w-8 text-center text-sm font-semibold" x-text="item.qty"></span>
                                            <button @click="increaseQty(item.key)" class="flex h-7 w-7 items-center justify-center rounded-md bg-slate-100 text-xs font-bold text-slate-600 transition hover:bg-emerald-50 hover:text-emerald-600">
                                                <i class="fa-solid fa-plus"></i>
                                            </button>
                                        </div>
                                        <button @click="removeItem(item.key)" class="rounded-lg p-2 text-xs font-medium text-red-500 transition hover:bg-red-50">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
        </div>

        <!-- Footer -->
        <template x-if="cartCount > 0">
            <div class="border-t border-slate-200 p-5">
                <div class="mb-4 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-base font-medium text-slate-800">Total</span>
                        <span class="text-xl font-bold text-emerald-600">Rp <span x-text="Number(cartTotal).toLocaleString('id-ID')"></span></span>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <button @click="clearCart()" class="rounded-xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        <i class="fa-solid fa-trash-can mr-2"></i>Kosongkan
                    </button>
                    <button @click="
                        @auth
                            window.location.href = '/checkout';
                        @else
                            window.dispatchEvent(new CustomEvent('require-login', { detail: { redirect: '/checkout' } }));
                        @endauth
                    " class="w-full rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-600 px-4 py-3 text-center text-sm font-semibold text-white shadow-lg shadow-emerald-500/30 transition hover:from-emerald-600 hover:to-emerald-700">
                        <i class="fa-solid fa-arrow-right mr-2"></i>Checkout
                    </button>
                </div>
            </div>
        </template>
    </aside>
</div>
