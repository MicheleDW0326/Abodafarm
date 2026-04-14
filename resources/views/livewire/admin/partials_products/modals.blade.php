<!-- Product Modal -->
<div x-show="showProductModal" x-transition.opacity.duration.300ms class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="$wire.closeProductModal()" @keydown.escape.window="$wire.closeProductModal()">
	<div class="w-full max-w-lg rounded-lg bg-white shadow-xl" @click.stop>
		<!-- Modal Header -->
		<div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
			<h3 class="text-lg font-semibold text-slate-900">
				@if ($editingProductId)
					Edit Produk
				@else
					Tambah Produk
				@endif
			</h3>
			<button @click="$wire.closeProductModal()" class="text-slate-400 transition hover:text-slate-600">
				<svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<path d="M18 6L6 18M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" />
				</svg>
			</button>
		</div>

		<!-- Modal Body -->
		<div class="px-6 py-4 max-h-96 overflow-y-auto">
			<form wire:submit.prevent="saveProduct" class="space-y-4">
				<div>
					<label class="block text-sm font-medium text-slate-900 mb-1">Nama Produk</label>
					<input type="text" wire:model="productName" placeholder="Masukkan nama produk" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-[#1A7FA8] focus:ring-1 focus:ring-[#1A7FA8] outline-none transition" />
					@error('productName') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
				</div>

				<div>
					<label class="block text-sm font-medium text-slate-900 mb-1">Foto Produk</label>
					<input type="file" wire:model="productImage" accept="image/*" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm text-slate-900 file:mr-2 file:rounded file:border-0 file:bg-[#1A7FA8] file:text-white file:cursor-pointer" />
					@if ($productImage)
						<div class="mt-2 relative w-24 h-24 rounded-lg overflow-hidden border-2 border-slate-200">
							<img src="{{ $productImage->temporaryUrl() }}" alt="Preview" class="w-full h-full object-cover" />
						</div>
					@elseif ($editingProductId && $productImagePath)
						@php
							$currentImageSrc = str_starts_with($productImagePath, 'http')
								? $productImagePath
								: asset('storage/' . $productImagePath);
						@endphp
						<div class="mt-2 relative w-24 h-24 rounded-lg overflow-hidden border-2 border-slate-200">
							<img src="{{ $currentImageSrc }}" alt="Current" class="w-full h-full object-cover" />
						</div>
					@endif
					@error('productImage') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
				</div>

				<div>
					<label class="block text-sm font-medium text-slate-900 mb-1">Deskripsi</label>
					<textarea rows="3" wire:model="productDescription" placeholder="Masukkan deskripsi produk" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-[#1A7FA8] focus:ring-1 focus:ring-[#1A7FA8] outline-none transition"></textarea>
					@error('productDescription') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
				</div>

				<div class="grid grid-cols-2 gap-4">
					<div>
						<label class="block text-sm font-medium text-slate-900 mb-1">Kategori</label>
						<select wire:model="productCategory" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm text-slate-900 focus:border-[#1A7FA8] focus:ring-1 focus:ring-[#1A7FA8] outline-none transition">
							<option value="">Pilih Kategori</option>
							@foreach ($categories as $category)
								<option value="{{ $category->id }}">{{ $category->name }}</option>
							@endforeach
						</select>
						@error('productCategory') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
					</div>
					<div>
						<label class="block text-sm font-medium text-slate-900 mb-1">Harga</label>
						<input type="number" wire:model="productPrice" placeholder="0" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-[#1A7FA8] focus:ring-1 focus:ring-[#1A7FA8] outline-none transition" />
						@error('productPrice') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
					</div>
				</div>

				<div class="flex gap-3 pt-4">
					<button type="button" @click="$wire.closeProductModal()" class="flex-1 rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-900 transition-all duration-300 hover:bg-slate-50">
						Batal
					</button>
					<button type="submit" class="flex-1 rounded-lg bg-gradient-to-r from-[#1A7FA8] to-[#0f5a7f] px-4 py-2.5 text-sm font-semibold text-white transition-all duration-300 hover:scale-105 hover:shadow-lg hover:shadow-[#1A7FA8]/30">
						Simpan
					</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Stock Modal -->
<div x-show="showStockModal" x-transition.opacity.duration.300ms class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="$wire.closeStockModal()" @keydown.escape.window="$wire.closeStockModal()">
	<div class="w-full max-w-lg rounded-lg bg-white shadow-xl" @click.stop>
		<div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
			<h3 class="text-lg font-semibold text-slate-900">Input Stok Masuk</h3>
			<button @click="$wire.closeStockModal()" class="text-slate-400 transition hover:text-slate-600">
				<svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<path d="M18 6L6 18M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" />
				</svg>
			</button>
		</div>

		<div class="px-6 py-4 max-h-96 overflow-y-auto">
			<form wire:submit.prevent="saveStock" class="space-y-4">
				@if ($editingStockProductId && $editingStockProduct)
					<div>
						<label class="block text-sm font-medium text-slate-900 mb-1">Produk</label>
						<input type="text" value="{{ $editingStockProduct->name }}" disabled class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm text-slate-600 bg-slate-50" />
					</div>

					<div class="grid grid-cols-2 gap-4">
						<div>
							<label class="block text-sm font-medium text-slate-900 mb-1">Jumlah Masuk</label>
							<input type="number" wire:model="stockQuantity" placeholder="0" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-[#1A7FA8] focus:ring-1 focus:ring-[#1A7FA8] outline-none transition" />
							@error('stockQuantity') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
						</div>
						<div>
							<label class="block text-sm font-medium text-slate-900 mb-1">Tanggal</label>
							<input type="date" wire:model="stockDate" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm text-slate-900 focus:border-[#1A7FA8] focus:ring-1 focus:ring-[#1A7FA8] outline-none transition" />
							@error('stockDate') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
						</div>
					</div>

					<div>
						<label class="block text-sm font-medium text-slate-900 mb-1">Supplier</label>
						<input type="text" wire:model="stockSupplier" placeholder="Nama supplier" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-[#1A7FA8] focus:ring-1 focus:ring-[#1A7FA8] outline-none transition" />
						@error('stockSupplier') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
					</div>

					<div class="flex gap-3 pt-4">
						<button type="button" @click="$wire.closeStockModal()" class="flex-1 rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-900 transition-all duration-300 hover:bg-slate-50">
							Batal
						</button>
						<button type="submit" class="flex-1 rounded-lg bg-gradient-to-r from-[#1A7FA8] to-[#0f5a7f] px-4 py-2.5 text-sm font-semibold text-white transition-all duration-300 hover:scale-105 hover:shadow-lg hover:shadow-[#1A7FA8]/30">
							Simpan
						</button>
					</div>
				@else
					<p class="text-slate-500 py-4 text-center">Silakan pilih produk terlebih dahulu</p>
				@endif
			</form>
		</div>
	</div>
</div>
