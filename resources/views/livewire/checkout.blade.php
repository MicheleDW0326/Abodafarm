<div x-data="{
    snapToken: null,
    paymentFinished: false,
    clientKey: '{{ $this->getMidtransClientKey() }}',
    checkInterval: null,
    snapOpened: false,
    cartItems: [],
    // Promo state (initialized from PHP, updated reactively)
    promoDiscount: {{ $discountAmount ?? 0 }},
    promoType: '{{ $promoType ?? '' }}',
    promoCodeApplied: '{{ $appliedPromoCode ?? '' }}',
    promoNameApplied: '{{ $promoName ?? '' }}',
    // Promo loading state
    isApplyingPromo: false,
    // Submit loading state
    isSubmitting: false,
    // Passed from PHP: hasCompletedPayment
    hasCompletedPayment: false,

    init() {
        // Load cart from localStorage first
        this.cartItems = JSON.parse(localStorage.getItem('abodafarm_cart') || '[]');

        // Set hidden input and Livewire property
        const cartDataInput = document.getElementById('cartDataInput');
        if (cartDataInput && this.cartItems.length > 0) {
            const cartDataStr = JSON.stringify(this.cartItems);
            cartDataInput.value = cartDataStr;
            this.$wire.cartData = cartDataStr;
        }

        // Sync cart to session
        this.syncCartToSession();

        // Listen for cart updates
        window.addEventListener('cart-updated', () => {
            this.cartItems = JSON.parse(localStorage.getItem('abodafarm_cart') || '[]');
            // Update hidden input and Livewire property
            const cartDataInput = document.getElementById('cartDataInput');
            const cartDataStr = JSON.stringify(this.cartItems);
            if (cartDataInput) {
                cartDataInput.value = cartDataStr;
            }
            // Also set Livewire property directly
            this.$wire.cartData = cartDataStr;
            this.syncCartToSession(true);
        });

        // Listen for refresh-payment event from Livewire
        window.addEventListener('refresh-payment', () => {
            this.$wire.refreshPaymentStatus();
        });

        // Check for snapToken using $wire
        this.checkSnapToken();
        this.checkInterval = setInterval(() => this.checkSnapToken(), 500);

        // Check URL params for payment status
        this.checkPaymentFinish();

        // Listen for promo changes from Livewire
        window.addEventListener('promo-applied', (e) => {
            this.promoDiscount = e.detail.discount || 0;
            this.promoType = e.detail.type || '';
            this.promoCodeApplied = e.detail.code || '';
            this.promoNameApplied = e.detail.name || '';
            this.cartItems = JSON.parse(localStorage.getItem('abodafarm_cart') || '[]');
        });

        window.addEventListener('promo-removed', () => {
            this.promoDiscount = 0;
            this.promoType = '';
            this.promoCodeApplied = '';
            this.promoNameApplied = '';
        });

        // Listen for Livewire submit loading state
        window.addEventListener('livewire:loading', (e) => {
            if (e.detail.component === this.$wire.__instance.id) {
                this.isSubmitting = e.detail.loading;
            }
        });
    },

    showPaymentSuccessAlert() {
        // Use SweetAlert2 if available, otherwise use native alert
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Pembayaran Berhasil!',
                html: 'Pesanan Anda sedang diproses. Anda akan diarahkan ke halaman utama.',
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false,
                didClose: () => {
                    localStorage.removeItem('abodafarm_cart');
                    window.location.href = '{{ route('pelanggan.dashboard') }}';
                }
            });
        } else {
            // Fallback for when SweetAlert2 is not loaded
            alert('Pembayaran Berhasil! Pesanan Anda sedang diproses.');
            localStorage.removeItem('abodafarm_cart');
            setTimeout(() => {
                window.location.href = '{{ route('pelanggan.dashboard') }}';
            }, 2000);
        }
    },

    checkSnapToken() {
        if (this.snapOpened) return;
        const token = this.$wire.snapToken;
        if (token && typeof token === 'string' && token !== this.snapToken) {
            console.log('Snap token detected:', token.substring(0, 20) + '...');
            this.snapToken = token;
            this.openMidtransSnap();
        }
    },

    syncCartToSession() {
        if (this.cartItems.length > 0) {
            fetch('/api/cart/sync', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                },
                body: JSON.stringify({ cart: this.cartItems })
            }).catch(err => console.error('Cart sync error:', err));
        }
    },

    get cartCount() {
        return this.cartItems.reduce((sum, item) => sum + item.qty, 0);
    },

    get cartTotal() {
        return this.cartItems.reduce((sum, item) => sum + (item.price * item.qty), 0);
    },

    get shippingCost() {
        // Use Alpine promo state for reactive updates
        return this.promoType === 'free_shipping' ? 0 : 20000;
    },

    get grandTotal() {
        const subtotal = this.cartTotal;
        const shipping = this.shippingCost;
        const discount = this.promoDiscount;
        return Math.max(0, subtotal + shipping - discount);
    },

    applyPromoWithSync() {
        // Prevent double apply
        if (this.isApplyingPromo) return;
        this.isApplyingPromo = true;

        // Get cart items directly from localStorage
        const cartItems = JSON.parse(localStorage.getItem('abodafarm_cart') || '[]');
        const cartData = JSON.stringify(cartItems);

        // Update hidden input
        const cartDataInput = document.getElementById('cartDataInput');
        if (cartDataInput) {
            cartDataInput.value = cartData;
        }

        // Update Livewire property
        this.$wire.cartData = cartData;

        // Call the promo method
        this.$wire.applyPromoCode();

        // Reset loading after delay (Livewire will handle actual completion)
        setTimeout(() => {
            this.isApplyingPromo = false;
        }, 1500);
    },

    openMidtransSnap() {
        if (!this.snapToken || this.snapOpened) return;

        console.log('Opening Midtrans Snap...');
        this.snapOpened = true;
        clearInterval(this.checkInterval);

        const component = this;

        const script = document.createElement('script');
        script.src = 'https://app.sandbox.midtrans.com/snap/snap.js';
        script.setAttribute('data-client-key', this.clientKey);
        script.onload = function() {
            console.log('Midtrans Snap JS loaded');
            component.executeSnapPay();
        };
        script.onerror = function() {
            console.error('Failed to load Midtrans');
            component.snapOpened = false;
            alert('Gagal memuat sistem pembayaran.');
        };
        document.head.appendChild(script);
    },

    executeSnapPay() {
        const component = this;

        if (!window.snap) {
            console.error('Midtrans snap object not found');
            return;
        }

        console.log('Executing snap.pay...');

        window.snap.pay(this.snapToken, {
            onSuccess: function(result) {
                console.log('Payment Success:', result);
                component.paymentFinished = true;
                component.$wire.refreshPaymentStatus();
                component.showPaymentSuccessAlert();
            },
            onPending: function(result) {
                console.log('Payment Pending:', result);
                component.paymentFinished = true;
                component.$wire.refreshPaymentStatus();
            },
            onError: function(result) {
                console.log('Payment Error:', result);
                component.snapOpened = false;
            },
            onClose: function() {
                console.log('Snap popup closed');
                component.snapOpened = false;
                component.$wire.refreshPaymentStatus();
            }
        });
    },

    checkPaymentFinish() {
        const urlParams = new URLSearchParams(window.location.search);

        // Always check if there's a current order ID
        if (this.$wire.currentOrderId) {
            // Call refreshPaymentStatus to update payment state and decrement stock
            this.$wire.refreshPaymentStatus().then(() => {
                // After refresh, check if payment is now completed
                if (this.$wire.paymentStatus?.is_completed) {
                    this.showPaymentSuccessAlert();
                }
            });
        } else if (urlParams.has('payment_status') || urlParams.has('order_id')) {
            // Fallback for legacy URL params without order ID
            this.$wire.refreshPaymentStatus();
        }
    }
}">

    {{-- Guest Login Prompt --}}
    @guest
    <div class="min-h-screen bg-sky-50 py-12">
        <div class="mx-auto max-w-md px-4">
            <div class="rounded-2xl bg-white p-8 text-center shadow-sm">
                <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-emerald-100">
                    <svg class="h-10 w-10 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-slate-900">Login Diperlukan</h2>
                <p class="mt-3 text-slate-600">Anda harus login untuk melanjutkan checkout. Silakan masuk ke akun Anda.</p>
                <div class="mt-8 flex flex-col gap-3">
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-6 py-3 font-semibold text-white transition hover:bg-emerald-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                        Masuk
                    </a>
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 px-6 py-3 font-semibold text-slate-700 transition hover:bg-slate-50">
                        Belum punya akun? Daftar
                    </a>
                </div>
                <p class="mt-6 text-sm text-slate-500">
                    Atau
                    <button type="button" onclick="window.dispatchEvent(new CustomEvent('require-login', { detail: { redirect: '/checkout' } }))" class="font-medium text-emerald-600 hover:text-emerald-700">
                        login dengan modal
                    </button>
                </p>
            </div>
        </div>
    </div>
    @endguest

    <div class="mx-auto grid max-w-6xl gap-8 px-4 sm:px-6 lg:grid-cols-5 lg:px-8">
        <section class="lg:col-span-3">
            <a href="{{ route('home') }}" class="mb-5 inline-flex text-sm font-semibold text-sky-700 hover:text-sky-800">← Kembali ke beranda</a>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h1 class="text-2xl font-semibold text-slate-900">Checkout AbodaFarm</h1>
                <p class="mt-2 text-sm text-slate-600">Lengkapi data pengiriman untuk menyelesaikan pesanan.</p>

                @if ($orderPlaced && !($this->paymentStatus && $this->paymentStatus['is_completed']))
                    <div class="mt-6 rounded-xl border border-sky-200 bg-sky-50 p-4">
                        <div class="flex items-start gap-3">
                            <svg class="mt-0.5 size-5 shrink-0 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-semibold text-sky-800">Pesanan dibuat dengan nomor <span class="font-bold">{{ $placedOrderNumber }}</span></p>
                                <p class="mt-1 text-xs text-sky-600">Lanjutkan ke pembayaran untuk menyelesaikan pesanan.</p>
                            </div>
                        </div>
                    </div>
                @endif

                @error('cart')
                    <div class="mt-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
                        {{ $message }}
                    </div>
                @enderror

                <!-- Order Form (hidden if order placed) -->
                @if (!$orderPlaced)
                    <!-- Hidden input for cart data (populated by Alpine on mount) -->
                    <input type="hidden" wire:model="cartData" id="cartDataInput">

                    <form wire:submit.prevent="submitCheckout" class="mt-6 space-y-4">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Nama Penerima</label>
                            <input wire:model="name" type="text" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" placeholder="Nama lengkap">
                            @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Nomor WhatsApp</label>
                            <input wire:model="phone" type="text" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" placeholder="08xxxxxxxxxx">
                            @error('phone') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <!-- Address Selection -->
                        @if (count($userAddresses) > 0)
                            <div>
                                <div class="mb-2 flex items-center justify-between">
                                    <label class="text-sm font-semibold text-slate-700">Pilih Alamat</label>
                                    <a href="{{ route('pelanggan.addresses') }}" class="text-xs text-emerald-600 hover:text-emerald-700">
                                        <i class="fa-solid fa-external-link-alt mr-1"></i> Kelola Alamat
                                    </a>
                                </div>
                                <div class="space-y-2">
                                    @foreach($userAddresses as $addr)
                                        <label class="flex items-start gap-3 rounded-xl border p-3 cursor-pointer transition hover:bg-slate-50 {{ $selectedAddressId == $addr['id'] ? 'border-sky-500 bg-sky-50' : 'border-slate-200' }}">
                                            <input type="radio" name="address" value="{{ $addr['id'] }}" wire:model.live="selectedAddressId" wire:change="selectAddress({{ $addr['id'] }})" class="mt-1 size-4 text-sky-500">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-sm font-semibold text-slate-800">{{ $addr['label'] }}</span>
                                                    @if($addr['is_default'])
                                                        <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">Utama</span>
                                                    @endif
                                                </div>
                                                <p class="mt-1 text-xs text-slate-500">{{ $addr['address'] }}</p>
                                                <p class="text-xs text-slate-400">{{ $addr['district'] }}, {{ $addr['city'] }}, {{ $addr['province'] }}</p>
                                                @if($addr['courier_notes'])
                                                    <p class="mt-1 text-xs text-slate-500 italic">
                                                        <i class="fa-solid fa-sticky-note mr-1 text-slate-400"></i>{{ Str::limit($addr['courier_notes'], 40) }}
                                                    </p>
                                                @endif
                                            </div>
                                        </label>
                                    @endforeach

                                    @if(!$useNewAddress)
                                        <button type="button" wire:click="showNewAddressForm" class="flex w-full items-center justify-center gap-2 rounded-xl border-2 border-dashed border-slate-300 p-3 text-sm text-slate-500 transition hover:border-sky-400 hover:text-sky-600">
                                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                            </svg>
                                            Tambah alamat baru
                                        </button>
                                    @endif
                                </div>
                            </div>

                            {{-- Service Area Warning --}}
                            @if(!$isInServiceArea && $serviceAreaMessage)
                                <div class="rounded-lg border border-amber-200 bg-amber-50 p-3">
                                    <div class="flex items-start gap-2">
                                        <i class="fa-solid fa-exclamation-triangle mt-0.5 text-amber-500"></i>
                                        <p class="text-xs text-amber-700">{{ $serviceAreaMessage }}</p>
                                    </div>
                                </div>
                            @endif
                        @endif

                        <!-- New Address Form -->
                        @if($useNewAddress || count($userAddresses) == 0)
                            <div class="space-y-3 rounded-xl border border-sky-200 bg-sky-50 p-4">
                                <h4 class="text-sm font-semibold text-sky-800">Alamat Baru</h4>

                                <div>
                                    <label class="mb-1 block text-xs font-medium text-slate-600">Label Alamat</label>
                                    <input wire:model="newAddressLabel" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none" placeholder="Contoh: Rumah, Kantor">
                                    @error('newAddressLabel') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="mb-1 block text-xs font-medium text-slate-600">Alamat Lengkap</label>
                                    <textarea wire:model="newFullAddress" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none" placeholder="Jalan, nomor rumah, RT/RW"></textarea>
                                    @error('newFullAddress') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-slate-600">Kota/Kabupaten</label>
                                        <input wire:model="newCity" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
                                        @error('newCity') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-slate-600">Kecamatan</label>
                                        <input wire:model="newDistrict" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
                                        @error('newDistrict') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-slate-600">Provinsi</label>
                                        <input wire:model="newProvince" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
                                        @error('newProvince') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-slate-600">Kode Pos</label>
                                        <input wire:model="newPostalCode" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
                                    </div>
                                </div>

                                @if(count($userAddresses) > 0)
                                    <button type="button" wire:click="useExistingAddress" class="text-xs text-sky-600 hover:text-sky-800">
                                        ← Kembali pilih alamat tersimpan
                                    </button>
                                @endif
                            </div>
                        @endif

                        <!-- Manual Address Input (shown if no saved addresses) -->
                        @if(count($userAddresses) == 0 && !$useNewAddress)
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-slate-700">Alamat Lengkap</label>
                                <textarea wire:model="address" rows="3" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" placeholder="Jalan, nomor rumah, kelurahan, kecamatan"></textarea>
                                @error('address') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>
                        @endif

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Catatan (opsional)</label>
                            <textarea wire:model="notes" rows="2" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" placeholder="Contoh: tolong potong fillet kecil"></textarea>
                            @error('notes') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <button type="submit"
                                :disabled="isSubmitting"
                                class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-600 disabled:opacity-50 disabled:cursor-not-allowed">
                            <template x-if="isSubmitting">
                                <svg class="size-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </template>
                            <span x-text="isSubmitting ? 'Memproses...' : 'Konfirmasi & Bayar Sekarang'"></span>
                        </button>
                    </form>
                @endif

                <!-- Payment Section (shown after order placed) -->
                @if ($orderPlaced)
                    <div class="mt-6 space-y-4">
                        @if ($errorMessage)
                            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
                                {{ $errorMessage }}
                            </div>
                        @endif

                        @if ($this->paymentStatus && $this->paymentStatus['is_pending'])
                            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                                <div class="flex items-start gap-3">
                                    <svg class="mt-0.5 size-5 shrink-0 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div>
                                        <p class="text-sm font-semibold text-amber-800">Menunggu Pembayaran</p>
                                        <p class="mt-1 text-xs text-amber-600">
                                            @if ($this->paymentStatus['payment_type'])
                                                via {{ strtoupper($this->paymentStatus['payment_type']) }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($this->paymentStatus && $this->paymentStatus['is_failed'])
                            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4">
                                <div class="flex items-start gap-3">
                                    <svg class="mt-0.5 size-5 shrink-0 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    <div>
                                        <p class="text-sm font-semibold text-rose-800">Pembayaran Gagal/Expired</p>
                                        <p class="mt-1 text-xs text-rose-600">Silakan coba lagi.</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Payment Completed Banner -->
                        @if ($this->paymentStatus && $this->paymentStatus['is_completed'])
                            <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-6 text-center">
                                <div class="mx-auto mb-3 flex size-16 items-center justify-center rounded-full bg-emerald-100">
                                    <svg class="size-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <h2 class="text-xl font-bold text-emerald-800">Pembayaran Berhasil!</h2>
                                <p class="mt-2 text-emerald-700">Terima kasih! Pesanan Anda sedang diproses.</p>
                                <div class="mt-4 inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-sm text-slate-600">
                                    <span>ID Transaksi:</span>
                                    <span class="font-mono font-semibold">{{ $this->paymentStatus['transaction_id'] ?? 'N/A' }}</span>
                                </div>
                                <div class="mt-4">
                                    <a href="{{ route('pelanggan.dashboard') }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-600">
                                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                        </svg>
                                        Kembali ke Dashboard
                                    </a>
                                </div>
                            </div>
                        @else
                            <!-- Action Buttons -->
                            <div class="flex flex-wrap gap-3">
                                <button
                                    wire:click="openPaymentModal"
                                    @if($isLoadingPayment) disabled @endif
                                    :disabled="$wire.isLoadingPayment"
                                    x-on:click="$wire.isLoadingPayment = true"
                                    class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-600 disabled:cursor-not-allowed disabled:bg-emerald-300">
                                    <template x-if="$wire.isLoadingPayment">
                                        <svg class="size-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </template>
                                    <template x-if="$wire.isLoadingPayment">
                                        <span>Memuat...</span>
                                    </template>
                                    <template x-if="!$wire.isLoadingPayment">
                                        <span class="flex items-center gap-2">
                                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                            </svg>
                                            Bayar Sekarang
                                        </span>
                                    </template>
                                </button>

                                <button
                                    wire:click="refreshPaymentStatus"
                                    :disabled="$wire.isLoadingPayment"
                                    class="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50">
                                    <template x-if="$wire.isLoadingPayment">
                                        <svg class="size-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </template>
                                    <template x-if="!$wire.isLoadingPayment">
                                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                    </template>
                                    <span x-text="$wire.isLoadingPayment ? 'Memuat...' : 'Cek Status'"></span>
                                </button>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </section>

        <aside class="lg:col-span-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Ringkasan Pesanan</h2>

                @if ($placedOrderNumber && $currentOrderId)
                    <?php $order = \App\Models\Order::with('items')->find($currentOrderId); ?>
                    @if ($order && $order->items->count() > 0)
                        <div class="mt-4 space-y-3">
                            @foreach ($order->items as $item)
                                <div class="flex items-center gap-3 rounded-xl border border-slate-100 p-3">
                                    @if ($item->product_image)
                                        <img src="{{ $item->product_image }}" alt="{{ $item->product_name }}" class="size-12 rounded-lg object-cover">
                                    @endif
                                    <div class="flex-1">
                                        <p class="text-sm font-semibold text-slate-800">{{ $item->product_name }}</p>
                                        <p class="text-xs text-slate-500">{{ $item->quantity }} x Rp {{ number_format($item->unit_price) }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4 border-t border-slate-200 pt-4">
                            <div class="flex items-center justify-between text-sm text-slate-600">
                                <span>Subtotal</span>
                                <span>Rp {{ number_format($subtotal ?? 0) }}</span>
                            </div>
                            @if(($discountAmount ?? 0) > 0)
                                <div class="mt-1 flex items-center justify-between text-sm text-emerald-600">
                                    <span>Diskon ({{ $appliedPromoCode }})</span>
                                    <span>-Rp {{ number_format($discountAmount) }}</span>
                                </div>
                            @endif
                            <div class="mt-1 flex items-center justify-between text-sm text-slate-600">
                                <span>Ongkos Kirim</span>
                                <span>Rp {{ number_format($shippingCost ?? 0) }}</span>
                            </div>
                            <div class="mt-3 flex items-center justify-between border-t border-slate-200 pt-3">
                                <span class="font-semibold text-slate-800">Total Bayar</span>
                                <span class="text-xl font-bold text-sky-700">Rp {{ number_format($order->total_amount) }}</span>
                            </div>
                        </div>
                    @endif
                @else
                    <!-- Show cart from localStorage via Alpine -->
                    <div x-show="cartCount > 0">
                        <div class="mt-4 space-y-3">
                            <template x-for="item in cartItems" :key="item.key">
                                <div class="flex items-center gap-3 rounded-xl border border-slate-100 p-3">
                                    <img :src="item.image" :alt="item.name" class="size-12 rounded-lg object-cover">
                                    <div class="flex-1">
                                        <p class="text-sm font-semibold text-slate-800" x-text="item.name"></p>
                                        <p class="text-xs text-slate-500">
                                            <span x-text="item.qty"></span> x Rp <span x-text="Number(item.price).toLocaleString('id-ID')"></span>
                                        </p>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="mt-4 border-t border-slate-200 pt-4">
                            <div class="flex items-center justify-between text-sm text-slate-600">
                                <span>Total Item</span>
                                <span x-text="cartCount"></span>
                            </div>

                            <!-- Promo Code Section -->
                            @if(!$appliedPromoCode)
                                <div class="mt-4">
                                    <label class="mb-1.5 block text-xs font-medium text-slate-600">Kode Promo</label>
                                    <div class="flex gap-2">
                                        <input type="text"
                                               wire:model.live="promoCode"
                                               placeholder="Masukkan kode promo"
                                               :disabled="isApplyingPromo"
                                               class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 disabled:bg-slate-100 disabled:cursor-not-allowed">
                                        <button type="button"
                                                @click="applyPromoWithSync()"
                                                :disabled="isApplyingPromo"
                                                class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:bg-emerald-400">
                                            <template x-if="isApplyingPromo">
                                                <svg class="size-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                            </template>
                                            <template x-if="isApplyingPromo">
                                                <span>Memuat...</span>
                                            </template>
                                            <template x-if="!isApplyingPromo">
                                                <span>Gunakan</span>
                                            </template>
                                        </button>
                                    </div>
                                    @if($promoError)
                                        <div class="mt-2 flex items-center gap-2 rounded-lg bg-red-50 border border-red-200 px-3 py-2">
                                            <i class="fa-solid fa-circle-xmark text-red-500 text-sm"></i>
                                            <p class="text-xs text-red-600">{{ $promoError }}</p>
                                        </div>
                                    @endif
                                    @if($promoSuccess)
                                        <div class="mt-2 flex items-center gap-2 rounded-lg bg-emerald-50 border border-emerald-200 px-3 py-2">
                                            <i class="fa-solid fa-circle-check text-emerald-500 text-sm"></i>
                                            <p class="text-xs text-emerald-600">{{ $promoSuccess }}</p>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="mt-4 rounded-lg bg-emerald-50 border border-emerald-200 p-3">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <i class="fa-solid fa-circle-check text-emerald-600"></i>
                                            <div>
                                                <span class="text-sm font-semibold text-emerald-700" x-text="promoNameApplied || promoCodeApplied"></span>
                                                <span class="block text-xs text-emerald-500" x-text="promoCodeApplied"></span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">
                                                <i class="fa-solid fa-check mr-1"></i>Aktif
                                            </span>
                                            <button type="button" @click="$wire.removePromoCode()" class="text-emerald-400 hover:text-emerald-600 transition">
                                                <i class="fa-solid fa-xmark text-sm"></i>
                                            </button>
                                        </div>
                                    </div>
                                    @if($promoSuccess)
                                        <p class="mt-1 text-xs text-emerald-600">{{ $promoSuccess }}</p>
                                    @endif
                                </div>
                            @endif

                            <!-- Subtotal (Alpine reactive - always shows cart total) -->
                            <div class="mt-3 flex items-center justify-between text-sm text-slate-600">
                                <span>Subtotal</span>
                                <span>Rp <span x-text="cartTotal.toLocaleString('id-ID')"></span></span>
                            </div>

                            <!-- Promo Discount (Alpine reactive) -->
                            @if($appliedPromoCode && $discountAmount && $discountAmount > 0)
                                <div class="mt-2 flex items-center justify-between text-sm text-emerald-600">
                                    <span class="flex items-center gap-1">
                                        <i class="fa-solid fa-percent text-xs"></i>
                                        Promo ({{ $appliedPromoCode }})
                                    </span>
                                    <span class="font-medium">- Rp <span x-text="discountAmount.toLocaleString('id-ID')"></span></span>
                                </div>
                            @endif

                            <!-- Free Shipping Badge -->
                            @if($appliedPromoCode && $promoType === 'free_shipping')
                                <div class="mt-2 flex items-center justify-between text-sm">
                                    <span class="flex items-center gap-1">
                                        <i class="fa-solid fa-truck text-xs text-emerald-600"></i>
                                        <span class="text-emerald-600">Gratis Ongkir</span>
                                    </span>
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">
                                        <i class="fa-solid fa-check mr-1"></i>AKTIF
                                    </span>
                                </div>
                            @endif

                            <!-- Shipping (Alpine reactive) -->
                            <div class="mt-2 flex items-center justify-between text-sm text-slate-600">
                                <span class="flex items-center gap-1">
                                    <i class="fa-solid fa-truck text-xs"></i>
                                    Ongkir
                                </span>
                                <span :class="shippingCost === 0 ? 'text-emerald-600 font-medium' : ''">
                                    <span x-text="shippingCost === 0 ? 'GRATIS' : 'Rp ' + shippingCost.toLocaleString('id-ID')"></span>
                                    <span x-show="shippingCost > 0" class="text-xs text-slate-400">(perkiraan)</span>
                                </span>
                            </div>

                            <!-- Total Savings (Alpine reactive) -->
                            <template x-if="promoDiscount > 0 || promoType === 'free_shipping'">
                                <div class="mt-2 flex items-center justify-between text-sm text-emerald-600">
                                    <span class="flex items-center gap-1">
                                        <i class="fa-solid fa-piggy-bank text-xs"></i>
                                        Total Hemat
                                    </span>
                                    <span class="font-semibold">- Rp <span x-text="(promoDiscount + (promoType === 'free_shipping' ? 20000 : 0)).toLocaleString('id-ID')"></span></span>
                                </div>
                            </template>

                            <!-- Grand Total (Alpine reactive) -->
                            <div class="mt-3 flex items-center justify-between pt-2 border-t border-slate-200">
                                <span class="text-slate-800 font-semibold">Total Bayar</span>
                                <span class="text-xl font-bold text-sky-700">
                                    Rp <span x-text="grandTotal.toLocaleString('id-ID')"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div x-show="cartCount === 0">
                        <p class="mt-4 text-sm text-slate-500">Keranjang kosong.</p>
                    </div>
                @endif
            </div>

            <!-- Order Info Card -->
            @if ($placedOrderNumber)
                <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="text-sm font-semibold text-slate-700">Informasi Pesanan</h3>
                    <div class="mt-3 space-y-2">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-500">No. Pesanan</span>
                            <span class="font-mono font-semibold text-slate-800">{{ $placedOrderNumber }}</span>
                        </div>
                        @if ($order ?? null)
                            @if ($order->customer_name)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-slate-500">Penerima</span>
                                    <span class="text-slate-800">{{ $order->customer_name }}</span>
                                </div>
                            @endif
                            @if ($order->customer_phone)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-slate-500">WhatsApp</span>
                                    <span class="text-slate-800">{{ $order->customer_phone }}</span>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            @endif
        </aside>
    </div>
</div>
