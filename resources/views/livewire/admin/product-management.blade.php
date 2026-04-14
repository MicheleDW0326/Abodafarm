<div wire:ignore.self x-data="{
        activeTab: @entangle('activeTab'),
        showProductModal: @entangle('showProductModal'),
        showStockModal: @entangle('showStockModal')
    }" class="space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <p class="mt-1 text-sm text-slate-500">Pantau dan atur semua produkmu di sini</p>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="flex gap-2 border-b border-slate-200 bg-white rounded-lg p-4">
        <button
            @click="activeTab = 'products'"
            :class="activeTab === 'products' ? 'border-b-2 border-[#1A7FA8] text-[#1A7FA8] font-semibold' : 'text-slate-600 hover:text-slate-900'"
            class="pb-3 px-4 text-sm font-medium transition-all duration-300"
        >
            📦 Daftar Produk
        </button>
        <button
            @click="activeTab = 'stock'"
            :class="activeTab === 'stock' ? 'border-b-2 border-[#1A7FA8] text-[#1A7FA8] font-semibold' : 'text-slate-600 hover:text-slate-900'"
            class="pb-3 px-4 text-sm font-medium transition-all duration-300"
        >
            📊 Manajemen Stok
        </button>
        <button
            @click="activeTab = 'categories'"
            :class="activeTab === 'categories' ? 'border-b-2 border-[#1A7FA8] text-[#1A7FA8] font-semibold' : 'text-slate-600 hover:text-slate-900'"
            class="pb-3 px-4 text-sm font-medium transition-all duration-300"
        >
            🏷️ Kategori
        </button>
    </div>

    <!-- Tab: Products -->
    <div x-show="activeTab === 'products'" x-transition class="space-y-4">
        <!-- Search and Add Button -->
        <div class="flex items-center gap-3">
            <div class="flex-1 relative">
                <input
                    type="text"
                    placeholder="Cari produk..."
                    wire:model.live="searchQuery"
                    class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2 pl-10 pr-4 text-sm text-slate-700 placeholder:text-slate-400 focus:border-[#1A7FA8] focus:outline-none"
                />
                <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="7" /><path d="m20 20-3.5-3.5" stroke-linecap="round" />
                </svg>
            </div>
            <button
                wire:click="openProductModal()"
                class="flex items-center gap-2 rounded-lg bg-gradient-to-r from-[#1A7FA8] to-[#0f5a7f] px-4 py-2.5 text-sm font-semibold text-white transition-all duration-300 hover:scale-105 hover:shadow-lg hover:shadow-[#1A7FA8]/30"
            >
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 5v14M5 12h14" stroke-linecap="round" />
                </svg>
                Tambah Produk
            </button>
        </div>

        <!-- Products Table -->
        <div class="rounded-lg border border-slate-200 bg-white overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50">
                            <th class="px-6 py-3 text-left font-semibold text-slate-900">Produk</th>
                            <th class="px-6 py-3 text-left font-semibold text-slate-900">Kategori</th>
                            <th class="px-6 py-3 text-right font-semibold text-slate-900">Harga</th>
                            <th class="px-6 py-3 text-right font-semibold text-slate-900">Stok</th>
                            <th class="px-6 py-3 text-center font-semibold text-slate-900">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($products as $product)
                            <tr class="hover:bg-slate-50 transition-colors duration-200">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        @if ($product->image)
                                            @php
                                                $imageSrc = str_starts_with($product->image, 'http')
                                                    ? $product->image
                                                    : asset('storage/' . $product->image);
                                            @endphp
                                            <img src="{{ $imageSrc }}" alt="{{ $product->name }}" class="size-10 rounded object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'" />
                                            <div style="display: none;" class="size-10 rounded bg-slate-200 flex items-center justify-center">
                                                <svg class="size-6 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <rect x="3" y="3" width="18" height="18" rx="2" /><circle cx="8.5" cy="8.5" r="1.5" /><path d="m21 15l-5-5L5 21" />
                                                </svg>
                                            </div>
                                        @else
                                            <div class="size-10 rounded bg-slate-200 flex items-center justify-center">
                                                <svg class="size-6 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <rect x="3" y="3" width="18" height="18" rx="2" /><circle cx="8.5" cy="8.5" r="1.5" /><path d="m21 15l-5-5L5 21" />
                                                </svg>
                                            </div>
                                        @endif
                                        <div>
                                            <p class="font-medium text-slate-900">{{ $product->name }}</p>
                                            <p class="text-xs text-slate-500">{{ Str::limit($product->description, 50) }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center rounded-full bg-[#7DD4F0]/10 px-3 py-1 text-xs font-medium text-[#0f5a7f]">
                                        {{ $product->category->name ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right font-semibold text-slate-900">
                                    Rp {{ number_format($product->price, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="inline-flex items-center rounded-full {{ $product->stock > 50 ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }} px-3 py-1 text-xs font-medium">
                                        {{ $product->stock }} unit
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button
                                            wire:click="openProductModal({{ $product->id }})"
                                            class="rounded-lg p-2 text-blue-600 transition-all duration-300 hover:bg-blue-100"
                                        >
                                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" stroke-linecap="round" stroke-linejoin="round" /><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </button>
                                        <button
                                            wire:click="openStockModal({{ $product->id }})"
                                            class="rounded-lg p-2 text-purple-600 transition-all duration-300 hover:bg-purple-100"
                                        >
                                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M12 2v20M2 12h20" stroke-linecap="round" />
                                            </svg>
                                        </button>
                                        <button
                                            wire:click="confirmDeleteProduct({{ $product->id }})"
                                            class="rounded-lg p-2 text-red-600 transition-all duration-300 hover:bg-red-100"
                                        >
                                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6h16zM10 11v6M14 11v6" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center">
                                    <p class="text-slate-500">Belum ada produk. Mulai dengan tambah produk baru.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab: Stock Management -->
    <div x-show="activeTab === 'stock'" x-transition class="space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-slate-900">Riwayat Masuk Stok</h3>
            <button
                disabled title="Pilih produk dari tabel dulu"
                class="flex items-center gap-2 rounded-lg bg-slate-400 px-4 py-2.5 text-sm font-semibold text-white cursor-not-allowed opacity-50"
            >
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 5v14M5 12h14" stroke-linecap="round" />
                </svg>
                Pilih Produk dari Tabel
            </button>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50">
                            <th class="px-6 py-3 text-left font-semibold text-slate-900">Produk</th>
                            <th class="px-6 py-3 text-right font-semibold text-slate-900">Jumlah</th>
                            <th class="px-6 py-3 text-left font-semibold text-slate-900">Supplier</th>
                            <th class="px-6 py-3 text-left font-semibold text-slate-900">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($stocks as $stock)
                            <tr class="hover:bg-slate-50 transition-colors duration-200">
                                <td class="px-6 py-4 font-medium text-slate-900">
                                    {{ $stock->product->name ?? 'Produk Tidak Ditemukan' }}
                                </td>
                                <td class="px-6 py-4 text-right font-semibold text-slate-900">
                                    +{{ $stock->quantity }} unit
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ $stock->supplier }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $stock->date->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center">
                                    <p class="text-slate-500">Belum ada riwayat masuk stok</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab: Categories -->
    <div x-show="activeTab === 'categories'" x-transition class="space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-slate-900">Daftar Kategori</h3>
        <button
            disabled title="Fitur kategori akan ditambahkan nanti"
            class="flex items-center gap-2 rounded-lg bg-slate-400 px-4 py-2.5 text-sm font-semibold text-white cursor-not-allowed opacity-50"
        >
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 5v14M5 12h14" stroke-linecap="round" />
                </svg>
                Tambah Kategori
            </button>
        </div>

        <div class="grid gap-4 grid-cols-1 md:grid-cols-2 lg:grid-cols-4">
            @foreach ($categories as $category)
                <div class="group rounded-lg border-2 border-slate-200 bg-white p-4 transition-all duration-300 hover:border-[#1A7FA8] hover:shadow-lg hover:shadow-[#1A7FA8]/10">
                    <div class="flex items-start justify-between">
                        <div>
                            <h4 class="font-semibold text-slate-900">{{ $category->name }}</h4>
                            <p class="mt-1 text-xs text-slate-500">{{ Str::limit($category->description ?? '', 60) }}</p>
                        </div>
                        <button
                            wire:click="$dispatch('deleteCategoryConfirm', [{{ $category->id }}])"
                            class="rounded p-1 text-slate-400 transition-all duration-300 hover:bg-red-100 hover:text-red-600"
                        >
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6z" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Modals -->
    @include('livewire.admin.partials_products.modals')

</div>


