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
            stockWarnings: {},

            init() {
                // Listen for cart updates from parent
                window.addEventListener('cart-updated', () => {
                    this.cartItems = JSON.parse(localStorage.getItem('abodafarm_cart') || '[]');
                });

                // Also listen for storage events (from other tabs)
                window.addEventListener('storage', (e) => {
                    if (e.key === 'abodafarm_cart') {
                        this.cartItems = JSON.parse(e.newValue || '[]');
                    }
                });

                // Check stock for all items on load
                this.checkAllStock();
            },

            async checkAllStock() {
                for (const item of this.cartItems) {
                    if (item.product_id) {
                        try {
                            const response = await fetch('/shop/check-stock/' + item.product_id);
                            const data = await response.json();
                            this.stockWarnings[item.key] = {
                                available: data.available,
                                stock: data.stock,
                                maxExceeded: item.qty > data.stock
                            };
                        } catch (e) {
                            console.error('Error checking stock:', e);
                        }
                    }
                }
            },

            async decreaseQty(key) {
                const item = this.cartItems.find(i => i.key === key);
                if (item) {
                    if (item.qty <= 1) {
                        this.removeItem(key);
                    } else {
                        item.qty--;
                        delete this.stockWarnings[key];
                        localStorage.setItem('abodafarm_cart', JSON.stringify(this.cartItems));
                        window.dispatchEvent(new Event('cart-updated'));
                    }
                }
            },

            async increaseQty(key) {
                const item = this.cartItems.find(i => i.key === key);
                if (item && item.product_id) {
                    // Check stock before increasing
                    try {
                        const response = await fetch('/shop/validate-cart/' + item.product_id + '?qty=' + (item.qty + 1));
                        const validation = await response.json();

                        if (!validation.valid) {
                            this.stockWarnings[key] = {
                                available: validation.currentStock > 0,
                                stock: validation.currentStock,
                                maxExceeded: true,
                                message: validation.message
                            };
                            // Show alert
                            alert(validation.message);
                            return;
                        }

                        item.qty++;
                        delete this.stockWarnings[key];
                        localStorage.setItem('abodafarm_cart', JSON.stringify(this.cartItems));
                        window.dispatchEvent(new Event('cart-updated'));
                    } catch (e) {
                        // Fallback - allow increase
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
                delete this.stockWarnings[key];
                localStorage.setItem('abodafarm_cart', JSON.stringify(this.cartItems));
                window.dispatchEvent(new Event('cart-updated'));
            },

            clearCart() {
                this.cartItems = [];
                this.stockWarnings = {};
                localStorage.setItem('abodafarm_cart', '[]');
                window.dispatchEvent(new Event('cart-updated'));
                window.dispatchEvent(new Event('clear-cart'));
            },

            getItemStockStatus(key) {
                return this.stockWarnings[key] || null;
            }
        }">
            <template x-if="cartCount === 0">
                <p class="rounded-xl border border-dashed border-slate-300 p-4 text-sm text-slate-500">Keranjang
                    kamu
                    masih kosong. Tambahkan produk dari section Produk.</p>
            </template>

            <template x-if="cartCount > 0">
                <div>
                    <div class="space-y-3">
                        <template x-for="item in cartItems" :key="item.key">
                            <article class="rounded-xl border border-slate-200 p-3"
                                     :class="getItemStockStatus(item.key)?.maxExceeded ? 'border-amber-400 bg-amber-50' : ''">
                                <!-- Stock Warning -->
                                <template x-if="getItemStockStatus(item.key)">
                                    <div class="mb-2 rounded-lg bg-amber-100 px-3 py-2 text-xs text-amber-800">
                                        <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                                        <span x-text="getItemStockStatus(item.key).message || 'Stok tidak mencukupi'"></span>
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
                                            <div class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-2 py-1">
                                                <button @click="decreaseQty(item.key)"
                                                    class="text-sm font-bold text-slate-600">-</button>
                                                <span class="min-w-4 text-center text-sm font-semibold" x-text="item.qty"></span>
                                                <button @click="increaseQty(item.key)"
                                                    :disabled="getItemStockStatus(item.key)?.maxExceeded"
                                                    :class="getItemStockStatus(item.key)?.maxExceeded ? 'text-slate-300 cursor-not-allowed' : 'text-slate-600 hover:text-emerald-600'">+</button>
                                            </div>
                                            <button @click="removeItem(item.key)"
                                                class="text-xs font-semibold text-rose-600">Hapus</button>
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
                                class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700">Kosongkan</button>
                            <a href="{{ route('checkout') }}"
                                :class="Object.values(stockWarnings).some(w => !w.available)
                                    ? 'rounded-lg bg-slate-400 px-3 py-2 text-center text-sm font-semibold text-white cursor-not-allowed pointer-events-none'
                                    : 'rounded-lg bg-emerald-500 px-3 py-2 text-center text-sm font-semibold text-white hover:bg-emerald-600'"
                                :href="Object.values(stockWarnings).some(w => !w.available) ? '#' : '{{ route('checkout') }}'">
                                Checkout
                            </a>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </aside>
</div>
