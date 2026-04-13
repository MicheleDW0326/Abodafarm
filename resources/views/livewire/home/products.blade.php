<section id="produk" class="bg-white py-16" x-data="{ activeCategory: 'all' }">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-3xl font-semibold">Produk Terlaris</h2>
                <p class="mt-2 text-slate-600">Tambahkan ke keranjang dan lanjutkan belanja tanpa reload halaman.</p>
            </div>

            <div class="inline-flex rounded-xl border border-sky-100 bg-sky-50 p-1 text-sm">
                <button @click="activeCategory = 'all'" :class="activeCategory === 'all' ? 'bg-white text-sky-700 shadow' : 'text-slate-600'" class="rounded-lg px-3 py-1.5 font-semibold">Semua</button>
                <button @click="activeCategory = 'poultry'" :class="activeCategory === 'poultry' ? 'bg-white text-sky-700 shadow' : 'text-slate-600'" class="rounded-lg px-3 py-1.5 font-semibold">Unggas</button>
                <button @click="activeCategory = 'seafood'" :class="activeCategory === 'seafood' ? 'bg-white text-sky-700 shadow' : 'text-slate-600'" class="rounded-lg px-3 py-1.5 font-semibold">Seafood</button>
            </div>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($products as $index => $product)
                <article
                    x-show="activeCategory === 'all' || activeCategory === '{{ $product['category'] }}'"
                    class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-md"
                >
                    <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" class="h-44 w-full object-cover">
                    <div class="space-y-3 p-4">
                        <h3 class="line-clamp-1 text-base font-semibold">{{ $product['name'] }}</h3>
                        <p class="text-sm text-slate-500">{{ $product['category'] === 'poultry' ? 'Unggas Segar' : 'Hasil Laut Segar' }}</p>
                        <div class="flex items-center justify-between">
                            <span class="text-lg font-bold text-sky-700">Rp {{ number_format($product['price']) }}</span>
                            <button wire:click="addToCart({{ $index }})" class="rounded-lg bg-emerald-500 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-600">Tambah</button>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
