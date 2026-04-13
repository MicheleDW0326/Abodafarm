<div class="min-h-screen bg-sky-50 py-12">
    <div class="mx-auto grid max-w-6xl gap-8 px-4 sm:px-6 lg:grid-cols-5 lg:px-8">
        <section class="lg:col-span-3">
            <a href="{{ route('home') }}" class="mb-5 inline-flex text-sm font-semibold text-sky-700 hover:text-sky-800">← Kembali ke beranda</a>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h1 class="text-2xl font-semibold text-slate-900">Checkout FreshGo</h1>
                <p class="mt-2 text-sm text-slate-600">Lengkapi data pengiriman untuk menyelesaikan pesanan.</p>

                @if ($orderPlaced)
                    <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
                        Pesanan berhasil dibuat dengan nomor <span class="font-bold">{{ $placedOrderNumber }}</span>. Tim FreshGo akan segera memproses pengiriman kamu.
                    </div>
                @endif

                @error('cart')
                    <div class="mt-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
                        {{ $message }}
                    </div>
                @enderror

                <form wire:submit="placeOrder" class="mt-6 space-y-4">
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

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Alamat Lengkap</label>
                        <textarea wire:model="address" rows="4" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" placeholder="Jalan, nomor rumah, kelurahan, kecamatan"></textarea>
                        @error('address') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Catatan (opsional)</label>
                        <textarea wire:model="notes" rows="3" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200" placeholder="Contoh: tolong potong fillet kecil"></textarea>
                        @error('notes') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" class="inline-flex rounded-xl bg-emerald-500 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-600">
                        Konfirmasi Pesanan
                    </button>
                </form>
            </div>
        </section>

        <aside class="lg:col-span-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Ringkasan Pesanan</h2>

                @if ($this->cartCount === 0)
                    <p class="mt-4 text-sm text-slate-500">Keranjang kosong.</p>
                @else
                    <div class="mt-4 space-y-3">
                        @foreach ($this->cartItems as $item)
                            <div class="flex items-center gap-3 rounded-xl border border-slate-100 p-3">
                                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="size-12 rounded-lg object-cover">
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-slate-800">{{ $item['name'] }}</p>
                                    <p class="text-xs text-slate-500">{{ $item['qty'] }} x Rp {{ number_format($item['price']) }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 border-t border-slate-200 pt-4">
                        <div class="flex items-center justify-between text-sm text-slate-600">
                            <span>Total Item</span>
                            <span>{{ $this->cartCount }}</span>
                        </div>
                        <div class="mt-2 flex items-center justify-between">
                            <span class="text-slate-800">Total Bayar</span>
                            <span class="text-xl font-bold text-sky-700">Rp {{ number_format($this->cartTotal) }}</span>
                        </div>
                    </div>
                @endif
            </div>
        </aside>
    </div>
</div>
