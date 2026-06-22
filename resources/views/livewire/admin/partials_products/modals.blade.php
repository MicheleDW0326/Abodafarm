{{-- =========================================== --}}
{{-- PRODUCT MODAL --}}
{{-- =========================================== --}}

<template x-teleport="body">
    <div x-show="$wire.showProductModal" x-transition.opacity.duration.300ms class="fixed inset-0 z-[99999]">
        <div class="absolute inset-0 bg-black/50" @click="$wire.closeProductModal()" @keydown.escape.window="$wire.closeProductModal()"></div>
        <div class="absolute left-1/2 top-1/2 w-full max-w-2xl max-h-[90vh] -translate-x-1/2 -translate-y-1/2 rounded-xl bg-white shadow-xl overflow-hidden" @click.stop>
            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <h3 class="text-lg font-semibold text-slate-900">
                    @if ($editingProductId)
                        <i class="fa-solid fa-edit mr-2 text-blue-500"></i>Edit Produk
                    @else
                        <i class="fa-solid fa-plus mr-2 text-emerald-500"></i>Tambah Produk Baru
                    @endif
                </h3>
                <button @click="$wire.closeProductModal()" class="text-slate-400 transition hover:text-slate-600">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 6L6 18M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            <div class="px-6 py-4 max-h-[calc(90vh-80px)] overflow-y-auto">
                {{-- Skeleton Loading --}}
                <div wire:loading wire:target="openProductModal, saveProduct" class="space-y-4 animate-pulse">
                    <div class="flex gap-3">
                        <div class="h-24 flex-1 rounded-lg bg-slate-200"></div>
                        <div class="h-24 flex-1 rounded-lg bg-slate-200"></div>
                    </div>
                    <div class="h-10 w-full rounded bg-slate-200"></div>
                    <div class="h-20 w-full rounded bg-slate-200"></div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="h-10 rounded bg-slate-200"></div>
                        <div class="h-10 rounded bg-slate-200"></div>
                    </div>
                    <div class="h-32 w-full rounded bg-slate-200"></div>
                </div>
                <form wire:submit.prevent="saveProduct" class="space-y-5">

                {{-- Product Type Selection --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-900 mb-2">Tipe Produk</label>
                    <div class="grid grid-cols-2 gap-3">
                        <button
                            type="button"
                            wire:click="$set('productType', 'live')"
                            :class="$wire.productType === 'live' ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-slate-200 hover:border-emerald-300'"
                            class="flex items-center justify-center gap-2 rounded-lg border-2 px-4 py-3 transition-all"
                        >
                            <i class="fa-solid fa-cow"></i>
                            <span class="font-medium">Ternak Hidup</span>
                        </button>
                        <button
                            type="button"
                            wire:click="$set('productType', 'fresh')"
                            :class="$wire.productType === 'fresh' ? 'border-rose-500 bg-rose-50 text-rose-700' : 'border-slate-200 hover:border-rose-300'"
                            class="flex items-center justify-center gap-2 rounded-lg border-2 px-4 py-3 transition-all"
                        >
                            <i class="fa-solid fa-drumstick-bite"></i>
                            <span class="font-medium">Daging Segar</span>
                        </button>
                    </div>
                </div>

                {{-- Basic Info --}}
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-slate-900 mb-1">Nama Produk *</label>
                        <input type="text" wire:model.live="productName" placeholder="Contoh: Sapi Bali Premium" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition" />
                        @error('productName') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-slate-900 mb-1">Deskripsi</label>
                        <textarea rows="2" wire:model.live="productDescription" placeholder="Deskripsi produk..." class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-900 mb-1">Kategori *</label>
                        <select wire:model.live="productCategory" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition">
                            <option value="">Pilih Kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('productCategory') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-900 mb-1">Harga (Rp) *</label>
                        <input type="number" wire:model.live="productPrice" min="0" placeholder="0" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition" />
                        @error('productPrice') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Status & Featured --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-900 mb-1">Status</label>
                        <select wire:model.live="productStatus" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition">
                            <option value="available">Siap Jual</option>
                            <option value="pre_order">Pre Order</option>
                            <option value="in_care">Dalam Perawatan</option>
                            <option value="sold">Terjual</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-900 mb-1">Stok Awal</label>
                        <input type="number" wire:model.live="productStock" min="0" placeholder="0" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition" />
                    </div>
                </div>

                {{-- Live Stock Fields --}}
                <div x-show="$wire.productType === 'live'" x-transition class="space-y-4 rounded-lg bg-green-50 p-4">
                    <h4 class="font-semibold text-green-800"><i class="fa-solid fa-cow mr-2"></i>Info Ternak Hidup</h4>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-900 mb-1">Berat (kg)</label>
                            <input type="number" wire:model.live="productWeight" step="0.1" min="0" placeholder="0" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-900 mb-1">Umur</label>
                            <input type="text" wire:model.live="productAge" placeholder="Contoh: 2 Tahun" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-900 mb-1">Jenis Kelamin</label>
                            <select wire:model.live="productSex" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition">
                                <option value="">Pilih</option>
                                <option value="male">Jantan</option>
                                <option value="female">Betina</option>
                                <option value="mixed">Campuran</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-900 mb-1">Asal Daerah</label>
                            <input type="text" wire:model.live="productOrigin" placeholder="Contoh: Talaud" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-900 mb-1">Kondisi Kesehatan</label>
                        <input type="text" wire:model.live="productCondition" placeholder="Contoh: Sehat, Aktif" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition" />
                    </div>
                </div>

                {{-- Fresh Meat Fields --}}
                <div x-show="$wire.productType === 'fresh'" x-transition class="space-y-4 rounded-lg bg-rose-50 p-4">
                    <h4 class="font-semibold text-rose-800"><i class="fa-solid fa-drumstick-bite mr-2"></i>Info Daging Segar</h4>

                    <div>
                        <label class="block text-sm font-medium text-slate-900 mb-1">Berat per Unit (kg)</label>
                        <input type="number" wire:model.live="productWeightPerUnit" step="0.1" min="0" placeholder="0" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition" />
                    </div>
                </div>

                {{-- Featured & Image --}}
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <input type="checkbox" wire:model.live="productIsFeatured" id="isFeatured" class="size-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
                        <label for="isFeatured" class="text-sm font-medium text-slate-900">Tampilkan sebagai Produk Unggulan</label>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-900 mb-2">Foto Produk</label>

                        {{-- Upload Area --}}
                        <div class="relative">
                            <input type="file" wire:model.live="productImage" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" />
                            <div class="flex items-center justify-center gap-3 rounded-lg border-2 border-dashed border-slate-300 bg-slate-50 px-4 py-6 transition-all hover:border-emerald-400 hover:bg-emerald-50/50">
                                <div class="text-center">
                                    @if ($productImage)
                                        <div class="flex items-center justify-center gap-2 text-emerald-600">
                                            <svg class="size-6 animate-spin" viewBox="0 0 24 24" fill="none" wire:loading wire:target="productImage">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <span class="text-sm font-medium" wire:loading wire:target="productImage">Mengupload...</span>
                                            <span class="text-sm font-medium" wire:loading.remove wire:target="productImage">
                                                <i class="fa-solid fa-check text-emerald-500"></i> Foto dipilih
                                            </span>
                                        </div>
                                    @else
                                        <div class="text-slate-400">
                                            <i class="fa-solid fa-cloud-upload-alt text-2xl mb-1"></i>
                                            <p class="text-sm">Klik atau drag foto ke sini</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Image Preview --}}
                        @if ($productImage)
                            <div class="mt-3 relative w-32 h-32 rounded-lg overflow-hidden border-2 border-emerald-200 bg-emerald-50">
                                <img src="{{ $productImage->temporaryUrl() }}" alt="Preview" class="w-full h-full object-cover" wire:loading.remove wire:target="productImage" />
                                <div class="absolute inset-0 flex items-center justify-center bg-emerald-50/80" wire:loading wire:target="productImage">
                                    <svg class="size-8 text-emerald-600 animate-spin" viewBox="0 0 24 24" fill="none">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                            </div>
                        @elseif ($editingProductId && $productImagePath)
                            @php
                                $currentImageSrc = str_starts_with($productImagePath, 'http') ? $productImagePath : asset('storage/' . $productImagePath);
                            @endphp
                            <div class="mt-3 relative w-32 h-32 rounded-lg overflow-hidden border-2 border-slate-200">
                                <img src="{{ $currentImageSrc }}" alt="Current" class="w-full h-full object-cover" />
                                <div class="absolute bottom-0 left-0 right-0 bg-black/50 text-white text-xs text-center py-1">
                                    Foto saat ini
                                </div>
                            </div>
                        @endif

                        @error('productImage')
                            <p class="mt-2 text-red-600 text-xs">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex gap-3 pt-4 border-t border-slate-200">
                    <button type="button" @click="$wire.closeProductModal()" class="flex-1 rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition-all duration-300 hover:bg-slate-50" wire:loading.attr="disabled">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 rounded-lg bg-gradient-to-r from-emerald-600 to-emerald-700 px-4 py-2.5 text-sm font-semibold text-white transition-all duration-300 hover:scale-[1.02] hover:shadow-lg hover:shadow-emerald-500/30 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
                        wire:loading.attr="disabled"
                        wire:target="productImage, saveProduct">
                        <span wire:loading.remove wire:target="productImage">
                            <i class="fa-solid fa-save mr-2"></i>Simpan
                        </span>
                        <span wire:loading wire:target="productImage" class="flex items-center justify-center gap-2">
                            <svg class="size-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Mengupload...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</template>

{{-- =========================================== --}}
{{-- CATEGORY MODAL --}}
{{-- =========================================== --}}

<template x-teleport="body">
    <div x-show="$wire.showCategoryModal" x-transition.opacity.duration.300ms class="fixed inset-0 z-[99999]">
        <div class="absolute inset-0 bg-black/50" @click="$wire.closeCategoryModal()" @keydown.escape.window="$wire.closeCategoryModal()"></div>
        <div class="absolute left-1/2 top-1/2 w-full max-w-md -translate-x-1/2 -translate-y-1/2 rounded-xl bg-white shadow-xl overflow-hidden" @click.stop>
        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
            <h3 class="text-lg font-semibold text-slate-900">
                @if ($editingCategoryId)
                    <i class="fa-solid fa-edit mr-2 text-blue-500"></i>Edit Kategori
                @else
                    <i class="fa-solid fa-plus mr-2 text-emerald-500"></i>Tambah Kategori
                @endif
            </h3>
            <button @click="$wire.closeCategoryModal()" class="text-slate-400 transition hover:text-slate-600">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 6L6 18M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>
        </div>

        {{-- Body --}}
        <div class="px-6 py-4">
            <form wire:submit.prevent="saveCategory" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-900 mb-1">Nama Kategori *</label>
                    <input type="text" wire:model.live="categoryName" placeholder="Contoh: Sapi" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition" />
                    @error('categoryName') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-900 mb-1">Slug</label>
                    <input type="text" wire:model.live="categorySlug" placeholder="auto-generate dari nama" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition" />
                    <p class="mt-1 text-xs text-slate-400">Biarkan kosong untuk auto-generate</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-900 mb-1">Icon (FontAwesome class)</label>
                    <input type="text" wire:model.live="categoryIcon" placeholder="Contoh: fa-solid fa-cow" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-900 mb-1">Deskripsi</label>
                    <textarea rows="2" wire:model.live="categoryDescription" placeholder="Deskripsi kategori..." class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition"></textarea>
                </div>

                <div class="flex gap-3 pt-4 border-t border-slate-200">
                    <button type="button" @click="$wire.closeCategoryModal()" class="flex-1 rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition-all duration-300 hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 rounded-lg bg-gradient-to-r from-emerald-600 to-emerald-700 px-4 py-2.5 text-sm font-semibold text-white transition-all duration-300 hover:scale-[1.02] hover:shadow-lg hover:shadow-emerald-500/30">
                        <i class="fa-solid fa-save mr-2"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</template>

{{-- =========================================== --}}
{{-- INVENTORY MODAL --}}
{{-- =========================================== --}}

<template x-teleport="body">
    <div x-show="$wire.showInventoryModal" x-transition.opacity.duration.300ms class="fixed inset-0 z-[99999]">
        <div class="absolute inset-0 bg-black/50" @click="$wire.closeInventoryModal()" @keydown.escape.window="$wire.closeInventoryModal()"></div>
        <div class="absolute left-1/2 top-1/2 w-full max-w-lg max-h-[90vh] -translate-x-1/2 -translate-y-1/2 rounded-xl bg-white shadow-xl overflow-hidden" @click.stop>
        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
            <h3 class="text-lg font-semibold text-slate-900">
                <i class="fa-solid fa-clipboard-list mr-2 text-purple-500"></i>Catat Inventaris
            </h3>
            <button @click="$wire.closeInventoryModal()" class="text-slate-400 transition hover:text-slate-600">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 6L6 18M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>
        </div>

        {{-- Body --}}
        <div class="px-6 py-4 max-h-[70vh] overflow-y-auto">
            <form wire:submit.prevent="saveInventory" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-900 mb-1">Tipe Transaksi</label>
                    <select wire:model.live="inventoryType" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition">
                        <option value="restock">Restock / Masuk</option>
                        <option value="sale">Penjualan / Keluar</option>
                        <option value="death">Kematian</option>
                        <option value="transfer">Transfer</option>
                        <option value="adjustment">Penyesuaian</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-900 mb-1">Jumlah *</label>
                        <input type="number" wire:model.live="inventoryQuantity" min="1" placeholder="0" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition" />
                        @error('inventoryQuantity') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-900 mb-1">Tanggal *</label>
                        <input type="date" wire:model.live="inventoryDate" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition" />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-900 mb-1">Status Kesehatan</label>
                    <input type="text" wire:model.live="inventoryHealthStatus" placeholder="Contoh: Sehat, Demam" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-900 mb-1">Riwayat Vaksinasi</label>
                    <input type="text" wire:model.live="inventoryVaccinationRecords" placeholder="Contoh: Anthrax 2024, SE 2024" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-900 mb-1">Catatan</label>
                    <textarea rows="2" wire:model.live="inventoryNotes" placeholder="Catatan tambahan..." class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition"></textarea>
                </div>

                <div class="flex gap-3 pt-4 border-t border-slate-200">
                    <button type="button" @click="$wire.closeInventoryModal()" class="flex-1 rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition-all duration-300 hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 rounded-lg bg-gradient-to-r from-purple-600 to-purple-700 px-4 py-2.5 text-sm font-semibold text-white transition-all duration-300 hover:scale-[1.02] hover:shadow-lg hover:shadow-purple-500/30">
                        <i class="fa-solid fa-save mr-2"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</template>
