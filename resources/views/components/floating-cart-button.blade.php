<!-- Floating Cart Button - Appears when items are in cart -->
<div x-data="{
    cartItems: JSON.parse(localStorage.getItem('abodafarm_cart') || '[]'),
    showPreview: false,
    lastAddedItem: null,
    previewTimeout: null,
    justAdded: false,
    pulseAnimation: false,

    init() {
        // Listen for cart updates
        window.addEventListener('cart-updated', () => {
            this.cartItems = JSON.parse(localStorage.getItem('abodafarm_cart') || '[]');
            this.updateLastAdded();
        });

        // Listen for new item added (for special animation)
        window.addEventListener('cart-item-added', (e) => {
            this.lastAddedItem = {
                name: e.detail.name,
                image: e.detail.image,
                key: e.detail.key
            };
            this.triggerPulseAnimation();
        });

        // Listen for storage changes (other tabs)
        window.addEventListener('storage', (e) => {
            if (e.key === 'abodafarm_cart') {
                this.cartItems = JSON.parse(e.newValue || '[]');
            }
        });
    },

    updateLastAdded() {
        if (this.cartItems.length > 0) {
            // Get the most recently added item (check by added_at timestamp)
            const items = [...this.cartItems].sort((a, b) => {
                const timeA = new Date(a.added_at || 0).getTime();
                const timeB = new Date(b.added_at || 0).getTime();
                return timeB - timeA;
            });
            this.lastAddedItem = items[0];
        }
    },

    triggerPulseAnimation() {
        this.pulseAnimation = true;
        setTimeout(() => {
            this.pulseAnimation = false;
        }, 1000);
    },

    get cartCount() {
        return this.cartItems.reduce((sum, item) => sum + item.qty, 0);
    },

    get cartTotal() {
        const total = this.cartItems.reduce((sum, item) => {
            return sum + ((parseFloat(item.price) || 0) * (parseInt(item.qty) || 0));
        }, 0);
        return isNaN(total) ? 0 : total;
    },

    openCartDrawer() {
        window.dispatchEvent(new CustomEvent('toggle-cart'));
        this.showPreview = false;
    },

    openCheckout() {
        window.location.href = '{{ route('checkout') }}';
    }
}"
x-init="updateLastAdded()"
:class="cartCount > 0 ? 'pointer-events-auto' : 'pointer-events-none'"
class="fixed bottom-6 right-6 z-50 flex flex-col items-end gap-3">

    <!-- Preview Card (shows on hover) -->
    <div x-show="showPreview && cartCount > 0"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         @mouseenter="showPreview = true"
         @mouseleave="showPreview = false"
         class="w-80 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">

        <!-- Preview Header -->
        <div class="flex items-center justify-between border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-teal-50 px-4 py-3">
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-500 text-white">
                    <i class="fa-solid fa-cart-shopping text-sm"></i>
                </div>
                <div>
                    <p class="text-sm font-bold text-slate-800">Keranjang Belanja</p>
                    <p class="text-xs text-slate-500"><span x-text="cartCount"></span> item</p>
                </div>
            </div>
            <button @click="showPreview = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <!-- Last Added Item -->
        <div class="max-h-48 overflow-y-auto p-3" x-show="lastAddedItem">
            <p class="mb-2 text-xs font-medium text-slate-500">Baru ditambahkan:</p>
            <div class="flex items-center gap-3 rounded-xl bg-emerald-50 p-2">
                <img :src="lastAddedItem?.image" :alt="lastAddedItem?.name"
                     class="h-12 w-12 rounded-lg object-cover">
                <div class="flex-1 min-w-0">
                    <p class="truncate text-sm font-semibold text-slate-800" x-text="lastAddedItem?.name"></p>
                    <p class="text-xs text-slate-500">
                        <span x-text="lastAddedItem?.qty"></span>x · Rp <span x-text="Number(lastAddedItem?.price || 0).toLocaleString('id-ID')"></span>
                    </p>
                </div>
            </div>
        </div>

        <!-- All Items Summary (if more than 1) -->
        <div class="border-t border-slate-100 px-4 py-3" x-show="cartCount > 1">
            <div class="flex items-center justify-between text-sm">
                <span class="text-slate-600">Total (<span x-text="cartCount"></span> item)</span>
                <span class="font-bold text-emerald-600">Rp <span x-text="Number(cartTotal).toLocaleString('id-ID')"></span></span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-2 border-t border-slate-100 bg-slate-50 p-3">
            <button @click="openCartDrawer()"
                    class="flex-1 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-semibold text-slate-700 transition-all hover:bg-slate-50 hover:border-slate-300">
                <i class="fa-solid fa-eye mr-1.5"></i>Lihat Keranjang
            </button>
            <button @click="openCheckout()"
                    class="flex-1 rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-600 px-3 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-500/30 transition-all hover:from-emerald-600 hover:to-emerald-700 hover:shadow-xl">
                <i class="fa-solid fa-arrow-right mr-1.5"></i>Checkout
            </button>
        </div>
    </div>

    <!-- Floating Cart Button -->
    <div class="relative">
        <!-- Pulse animation when item just added -->
        <div x-show="cartCount > 0"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-50"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-50"
             :class="pulseAnimation ? 'scale-110' : ''"
             @mouseenter="showPreview = true"
             @mouseleave="showPreview = false"
             class="group relative transition-transform duration-300">
            <!-- Main Button -->
            <button @click="openCartDrawer()"
                    class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-600 text-white shadow-xl shadow-emerald-500/40 transition-all duration-300 hover:from-emerald-600 hover:to-emerald-700 hover:shadow-2xl hover:shadow-emerald-500/50 hover:-translate-y-1 active:scale-95">

                <!-- Cart Icon -->
                <div class="relative">
                    <i class="fa-solid fa-bag-shopping text-xl transition-transform group-hover:scale-110"></i>

                    <!-- Item Count Badge -->
                    <span x-show="cartCount > 0"
                          x-text="cartCount"
                          class="absolute -right-2 -top-2 flex h-5 min-w-5 items-center justify-center rounded-full bg-amber-500 text-[10px] font-bold text-white shadow-lg ring-2 ring-white">
                    </span>
                </div>
            </button>

            <!-- Tooltip -->
            <div class="absolute bottom-full right-0 mb-3 hidden flex-col items-center group-hover:flex">
                <div class="mb-1.5 whitespace-nowrap rounded-lg bg-slate-800 px-3 py-1.5 text-xs font-medium text-white shadow-lg">
                    Lihat Keranjang
                </div>
                <!-- Arrow -->
                <div class="h-2 w-2 rotate-45 bg-slate-800"></div>
            </div>
        </div>

        <!-- Mobile-optimized version (bottom center for mobile) -->
        <button @click="openCartDrawer()"
                x-show="cartCount > 0"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="fixed bottom-24 left-1/2 z-50 flex -translate-x-1/2 items-center gap-3 rounded-full bg-gradient-to-r from-emerald-500 to-emerald-600 px-5 py-3 text-sm font-bold text-white shadow-xl shadow-emerald-500/40 transition-all hover:from-emerald-600 hover:to-emerald-700 hover:shadow-2xl lg:hidden">
            <i class="fa-solid fa-bag-shopping"></i>
            <span>Lihat Keranjang</span>
            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-white text-xs font-bold text-emerald-600" x-text="cartCount"></span>
        </button>
    </div>

    <!-- Notification Dot (for new items) -->
    <div x-show="cartCount > 0"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-0"
         x-transition:enter-end="opacity-100 scale-100"
         class="absolute -right-1 -top-1 hidden lg:block">
        <span class="relative flex h-3 w-3">
            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-amber-400 opacity-75"></span>
            <span class="relative inline-flex h-3 w-3 rounded-full bg-amber-500"></span>
        </span>
    </div>
</div>
