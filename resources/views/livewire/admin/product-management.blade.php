<div wire:ignore.self x-data="{
        activeTab: @entangle('activeTab'),
        showProductModal: @entangle('showProductModal'),
        showCategoryModal: @entangle('showCategoryModal'),
        showInventoryModal: @entangle('showInventoryModal'),
        typeFilter: @entangle('typeFilter'),
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
            :class="activeTab === 'products' ? 'border-b-2 border-emerald-600 text-emerald-600 font-semibold' : 'text-slate-600 hover:text-slate-900'"
            class="pb-3 px-4 text-sm font-medium transition-all duration-300"
        >
            <i class="fa-solid fa-box mr-2"></i>Daftar Produk
        </button>
        <button
            @click="activeTab = 'inventory'"
            :class="activeTab === 'inventory' ? 'border-b-2 border-emerald-600 text-emerald-600 font-semibold' : 'text-slate-600 hover:text-slate-900'"
            class="pb-3 px-4 text-sm font-medium transition-all duration-300"
        >
            <i class="fa-solid fa-warehouse mr-2"></i>Inventaris
        </button>
        <button
            @click="activeTab = 'categories'"
            :class="activeTab === 'categories' ? 'border-b-2 border-emerald-600 text-emerald-600 font-semibold' : 'text-slate-600 hover:text-slate-900'"
            class="pb-3 px-4 text-sm font-medium transition-all duration-300"
        >
            <i class="fa-solid fa-tags mr-2"></i>Kategori
        </button>
    </div>

    <!-- Tab: Products -->
    <div x-show="activeTab === 'products'" x-transition class="space-y-4">
        <!-- Search, Filter and Add Button -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
            <div class="relative flex-1 w-full">
                <input
                    type="text"
                    placeholder="Cari produk..."
                    wire:model.live.debounce.300ms="searchQuery"
                    class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2 pl-10 pr-4 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                />
                <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="7" /><path d="m20 20-3.5-3.5" stroke-linecap="round" />
                </svg>
            </div>

            <!-- Type Filter -->
            <div class="flex gap-2">
                <button
                    @click="typeFilter = 'all'"
                    :class="typeFilter === 'all' ? 'bg-emerald-100 text-emerald-700 border-emerald-300' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
                    class="px-3 py-2 text-xs font-medium rounded-lg border transition-all"
                >
                    Semua ({{ $liveProductsCount + $freshProductsCount + $supplyProductsCount }})
                </button>
                <button
                    @click="typeFilter = 'live'"
                    :class="typeFilter === 'live' ? 'bg-green-100 text-green-700 border-green-300' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
                    class="px-3 py-2 text-xs font-medium rounded-lg border transition-all"
                >
                    <i class="fa-solid fa-cow mr-1"></i>Ternak ({{ $liveProductsCount }})
                </button>
                <button
                    @click="typeFilter = 'fresh'"
                    :class="typeFilter === 'fresh' ? 'bg-rose-100 text-rose-700 border-rose-300' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
                    class="px-3 py-2 text-xs font-medium rounded-lg border transition-all"
                >
                    <i class="fa-solid fa-drumstick-bite mr-1"></i>Daging ({{ $freshProductsCount }})
                </button>
                <button
                    @click="typeFilter = 'supply'"
                    :class="typeFilter === 'supply' ? 'bg-amber-100 text-amber-700 border-amber-300' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
                    class="px-3 py-2 text-xs font-medium rounded-lg border transition-all"
                >
                    <i class="fa-solid fa-box-open mr-1"></i>Barang ({{ $supplyProductsCount }})
                </button>
            </div>

            <button
                wire:click="openProductModal()"
                class="flex items-center gap-2 rounded-lg bg-gradient-to-r from-emerald-600 to-emerald-700 px-4 py-2.5 text-sm font-semibold text-white transition-all duration-300 hover:scale-105 hover:shadow-lg hover:shadow-emerald-500/30"
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
                            <th class="px-4 py-3 text-left font-semibold text-slate-900">Produk</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-900">Tipe</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-900">Kategori</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-900">Info</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-900">Harga</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-900">Status</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-900">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($products as $product)
                            <tr class="hover:bg-slate-50 transition-colors duration-200">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        @if ($product->image)
                                            @php
                                                $imageSrc = str_starts_with($product->image, 'http')
                                                    ? $product->image
                                                    : asset('storage/' . $product->image);
                                            @endphp
                                            <img src="{{ $imageSrc }}" alt="{{ $product->name }}" class="size-12 rounded-lg object-cover" onerror="this.style.display='none'" />
                                        @endif
                                        <div>
                                            <p class="font-medium text-slate-900">{{ $product->name }}</p>
                                            <p class="text-xs text-slate-500">{{ $product->category->name ?? '-' }}</p>
                                            @if($product->is_featured)
                                                <span class="inline-flex items-center rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-medium text-amber-700">
                                                    <i class="fa-solid fa-star mr-1"></i>Featured
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    @if($product->type === 'live')
                                        <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700">
                                            <i class="fa-solid fa-cow mr-1"></i>Ternak Hidup
                                        </span>
                                    @elseif($product->type === 'fresh')
                                        <span class="inline-flex items-center rounded-full bg-rose-100 px-2.5 py-1 text-xs font-medium text-rose-700">
                                            <i class="fa-solid fa-drumstick-bite mr-1"></i>Daging Segar
                                        </span>
                                    @elseif($product->type === 'supply')
                                        <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-700">
                                            <i class="fa-solid fa-box-open mr-1"></i>Pakan & Perlengkapan
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">
                                        {{ $product->category->name ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    @if($product->type === 'live')
                                        <div class="text-xs text-slate-600 space-y-0.5">
                                            @if($product->weight)
                                                <div><i class="fa-solid fa-weight-scale mr-1"></i>{{ $product->weight }} kg</div>
                                            @endif
                                            @if($product->age)
                                                <div><i class="fa-solid fa-calendar mr-1"></i>{{ $product->age }}</div>
                                            @endif
                                            @if($product->sex)
                                                <div><i class="fa-solid fa-venus-mars mr-1"></i>{{ $product->sex_label }}</div>
                                            @endif
                                            @if($product->origin)
                                                <div><i class="fa-solid fa-location-dot mr-1"></i>{{ Str::limit($product->origin, 15) }}</div>
                                            @endif
                                        </div>
                                    @else
                                        <div class="text-xs text-slate-600">
                                            @if($product->stock > 0)
                                                <span class="inline-flex items-center rounded bg-green-100 px-2 py-0.5 text-green-700">
                                                    <i class="fa-solid fa-check mr-1"></i>{{ $product->stock }} unit
                                                </span>
                                            @else
                                                <span class="inline-flex items-center rounded bg-red-100 px-2 py-0.5 text-red-700">
                                                    <i class="fa-solid fa-times mr-1"></i>Stok habis
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-slate-900">
                                    Rp {{ number_format($product->price, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($product->status === 'available')
                                        <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700">Siap Jual</span>
                                    @elseif($product->status === 'pre_order')
                                        <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-700">Pre Order</span>
                                    @elseif($product->status === 'in_care')
                                        <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-1 text-xs font-medium text-blue-700">Dalam Perawatan</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">Terjual</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button
                                            wire:click="openProductModal({{ $product->id }})"
                                            class="rounded-lg p-2 text-blue-600 transition-all duration-300 hover:bg-blue-100"
                                            title="Edit"
                                        >
                                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" stroke-linecap="round" stroke-linejoin="round" />
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </button>
                                        @if($product->type === 'live')
                                            <button
                                                wire:click="openInventoryModal({{ $product->id }})"
                                                class="rounded-lg p-2 text-purple-600 transition-all duration-300 hover:bg-purple-100"
                                                title="Catat Perawatan"
                                            >
                                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2" stroke-linecap="round" stroke-linejoin="round" />
                                                    <rect x="9" y="3" width="6" height="4" rx="2" />
                                                    <path d="M9 14l2 2 4-4" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                            </button>
                                        @else
                                            <button
                                                wire:click="openInventoryModal({{ $product->id }})"
                                                class="rounded-lg p-2 text-purple-600 transition-all duration-300 hover:bg-purple-100"
                                                title="Tambah Stok"
                                            >
                                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M12 2v20M2 12h20" stroke-linecap="round" />
                                                </svg>
                                            </button>
                                        @endif
                                        <button
                                            wire:click="confirmDeleteProduct({{ $product->id }})"
                                            class="rounded-lg p-2 text-red-600 transition-all duration-300 hover:bg-red-100"
                                            title="Hapus"
                                        >
                                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6h16z" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center text-slate-400">
                                        <i class="fa-solid fa-box-open text-4xl mb-3"></i>
                                        <p class="text-slate-500">Belum ada produk</p>
                                        <button
                                            wire:click="openProductModal()"
                                            class="mt-3 text-sm text-emerald-600 hover:text-emerald-700 font-medium"
                                        >
                                            + Tambah produk baru
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab: Inventory -->
    <div x-show="activeTab === 'inventory'" x-transition class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Riwayat Inventaris</h3>
                <p class="text-sm text-slate-500">Catatan stock dan perawatan ternak</p>
            </div>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50">
                            <th class="px-4 py-3 text-left font-semibold text-slate-900">Produk</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-900">Tipe</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-900">Jumlah</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-900">Keterangan</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-900">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($inventoryLogs as $log)
                            <tr class="hover:bg-slate-50 transition-colors duration-200">
                                <td class="px-4 py-3">
                                    <span class="font-medium text-slate-900">{{ $log->product->name ?? 'Produk Tidak Ditemukan' }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    @if($log->type === 'restock')
                                        <span class="inline-flex items-center rounded bg-green-100 px-2 py-1 text-xs font-medium text-green-700">
                                            <i class="fa-solid fa-plus mr-1"></i>Restock
                                        </span>
                                    @elseif($log->type === 'sale')
                                        <span class="inline-flex items-center rounded bg-blue-100 px-2 py-1 text-xs font-medium text-blue-700">
                                            <i class="fa-solid fa-shopping-cart mr-1"></i>Penjualan
                                        </span>
                                    @elseif($log->type === 'death')
                                        <span class="inline-flex items-center rounded bg-red-100 px-2 py-1 text-xs font-medium text-red-700">
                                            <i class="fa-solid fa-skull mr-1"></i>Kematian
                                        </span>
                                    @elseif($log->type === 'transfer')
                                        <span class="inline-flex items-center rounded bg-amber-100 px-2 py-1 text-xs font-medium text-amber-700">
                                            <i class="fa-solid fa-truck mr-1"></i>Transfer
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded bg-slate-100 px-2 py-1 text-xs font-medium text-slate-600">
                                            {{ $log->type_label }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right font-semibold">
                                    @if(in_array($log->type, ['restock']))
                                        <span class="text-green-600">+{{ $log->quantity }}</span>
                                    @elseif(in_array($log->type, ['sale', 'death', 'transfer']))
                                        <span class="text-red-600">-{{ $log->quantity }}</span>
                                    @else
                                        <span class="text-slate-600">{{ $log->quantity }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    @if($log->health_status)
                                        <div class="text-xs"><i class="fa-solid fa-heart mr-1"></i>{{ $log->health_status }}</div>
                                    @endif
                                    @if($log->vaccination_records)
                                        <div class="text-xs"><i class="fa-solid fa-syringe mr-1"></i>{{ $log->vaccination_records }}</div>
                                    @endif
                                    @if($log->notes)
                                        <div class="text-xs text-slate-500">{{ Str::limit($log->notes, 30) }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-slate-500 text-xs">
                                    {{ $log->date->format('d M Y') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                    <i class="fa-solid fa-clipboard-list text-4xl mb-3"></i>
                                    <p>Belum ada riwayat inventaris</p>
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
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Kategori</h3>
                <p class="text-sm text-slate-500">Kelola kategori produk di halaman utama</p>
            </div>
            <button
                wire:click="openCategoryModal()"
                class="flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition-all duration-300 hover:bg-emerald-700"
            >
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 5v14M5 12h14" stroke-linecap="round" />
                </svg>
                Tambah
            </button>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white overflow-hidden">
            <table class="w-full">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600">Kategori</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600">Slug</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-slate-600">Produk</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-slate-600">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-slate-600">Urutan</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($categories as $cat)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    @if($cat->image_url)
                                        <img src="{{ $cat->image_url }}" alt="{{ $cat->name }}" class="size-10 rounded-lg object-cover" />
                                    @else
                                        <div class="flex size-10 items-center justify-center rounded-lg bg-emerald-100 text-lg">
                                            {{ $cat->icon ?: '📦' }}
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-medium text-slate-900">{{ $cat->name }}</p>
                                        <p class="text-xs text-slate-500 line-clamp-1">{{ Str::limit($cat->description, 50) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <code class="rounded bg-slate-100 px-2 py-1 text-xs text-slate-600">{{ $cat->slug }}</code>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-sm font-medium text-slate-600">
                                    {{ $cat->products()->count() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <button wire:click="toggleCategoryActive({{ $cat->id }})" class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium transition {{ $cat->is_active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                                    @if($cat->is_active)
                                        <i class="fa-solid fa-check-circle"></i> Aktif
                                    @else
                                        <i class="fa-solid fa-times-circle"></i> Nonaktif
                                    @endif
                                </button>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-sm text-slate-600">{{ $cat->sort_order }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-1">
                                    <button wire:click="openCategoryModal({{ $cat->id }})" class="rounded p-2 text-slate-400 hover:bg-blue-100 hover:text-blue-600">
                                        <i class="fa-solid fa-edit"></i>
                                    </button>
                                    <button wire:click="confirmDeleteCategory({{ $cat->id }})" class="rounded p-2 text-slate-400 hover:bg-red-100 hover:text-red-600">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center">
                                <div class="text-slate-400">
                                    <i class="fa-solid fa-layer-group text-4xl mb-3"></i>
                                    <p>Belum ada kategori</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modals -->
    @include('livewire.admin.partials_products.modals')

</div>
