<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Kelola Promo</h1>
            <p class="mt-1 text-sm text-slate-500">Buat dan kelola kode promo untuk pelanggan</p>
        </div>
        <button wire:click="openModal()"
                class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
            <i class="fa-solid fa-plus"></i>
            Tambah Promo
        </button>
    </div>

    <!-- Search -->
    <div class="flex items-center gap-4">
        <div class="relative flex-1 max-w-md">
            <input type="text"
                   wire:model.live.debounce.300ms="search"
                   placeholder="Cari kode atau judul promo..."
                   class="w-full rounded-lg border border-slate-300 pl-10 pr-4 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
            <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
        </div>
    </div>

    <!-- Flash Message -->
    @if(session('success'))
        <div class="rounded-lg bg-emerald-50 border border-emerald-200 p-4 text-sm text-emerald-700">
            <i class="fa-solid fa-check-circle mr-2"></i>
            {{ session('success') }}
        </div>
    @endif

    <!-- Promotions Table -->
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Kode</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Info</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Diskon</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Syarat</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($promotions as $promo)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center rounded-lg bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-800">
                                {{ $promo->code }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-medium text-slate-900">{{ $promo->title }}</p>
                            @if($promo->expires_at)
                                <p class="text-xs text-slate-500">
                                    Berakhir: {{ $promo->expires_at->format('d M Y') }}
                                </p>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-semibold text-emerald-600">
                                @if($promo->type === 'percentage')
                                    {{ $promo->discount_value }}%
                                    @if($promo->max_discount_amount)
                                        <span class="text-xs font-normal text-slate-500">(Max Rp {{ number_format($promo->max_discount_amount) }})</span>
                                    @endif
                                @else
                                    Rp {{ number_format($promo->discount_value) }}
                                @endif
                            </p>
                        </td>
                        <td class="px-6 py-4">
                            @if($promo->min_purchase_amount > 0)
                                <p class="text-xs text-slate-600">Min: Rp {{ number_format($promo->min_purchase_amount) }}</p>
                            @endif
                            <p class="text-xs text-slate-500">
                                Penggunaan: {{ $promo->usage_count }}
                                @if($promo->usage_limit)
                                    / {{ $promo->usage_limit }}
                                @else
                                    / ∞
                                @endif
                            </p>
                        </td>
                        <td class="px-6 py-4">
                            @if($promo->is_active)
                                @if($promo->isValid())
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">
                                        Berakhir
                                    </span>
                                @endif
                            @else
                                <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-1 text-xs font-medium text-red-700">
                                    Nonaktif
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button wire:click="toggleActive({{ $promo->id }})"
                                        class="p-1.5 text-slate-400 hover:text-slate-600 transition"
                                        title="{{ $promo->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                    @if($promo->is_active)
                                        <i class="fa-solid fa-eye-slash"></i>
                                    @else
                                        <i class="fa-solid fa-eye"></i>
                                    @endif
                                </button>
                                <button wire:click="openModal({{ $promo->id }})"
                                        class="p-1.5 text-slate-400 hover:text-emerald-600 transition">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <button wire:click="delete({{ $promo->id }})"
                                        wire:confirm="Yakin hapus promo ini?"
                                        class="p-1.5 text-slate-400 hover:text-red-600 transition">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fa-solid fa-ticket text-4xl text-slate-300 mb-3"></i>
                                <p class="text-sm text-slate-500">Belum ada promo</p>
                                <button wire:click="openModal()"
                                        class="mt-3 text-sm font-medium text-emerald-600 hover:text-emerald-700">
                                    + Tambah Promo
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($promotions->hasPages())
            <div class="border-t border-slate-200 px-6 py-4">
                {{ $promotions->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Form -->
@if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" wire:ignore>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50 transition-opacity"
                 wire:click="closeModal"></div>

            <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-slate-900">
                        {{ $isEditing ? 'Edit Promo' : 'Tambah Promo Baru' }}
                    </h3>
                    <button wire:click="closeModal"
                            class="p-1 text-slate-400 hover:text-slate-600 transition">
                        <i class="fa-solid fa-times text-lg"></i>
                    </button>
                </div>

                <form wire:submit="save" class="space-y-4">
                    <!-- Code -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Kode Promo</label>
                        <div class="flex gap-2">
                            <input type="text"
                                   wire:model="code"
                                   placeholder="Contoh: PROMO2024"
                                   class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm uppercase focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 @error('code') border-red-500 @enderror">
                            <button type="button"
                                    wire:click="generateCode"
                                    class="rounded-lg bg-slate-100 px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-200">
                                <i class="fa-solid fa-rotate"></i>
                            </button>
                        </div>
                        @error('code')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Title -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Judul Promo</label>
                        <input type="text"
                               wire:model="title"
                               placeholder="Contoh: Diskon 20% Semua Produk"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 @error('title') border-red-500 @enderror">
                        @error('title')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Deskripsi (opsional)</label>
                        <textarea wire:model="description"
                                  rows="2"
                                  placeholder="Deskripsi singkat promo..."
                                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"></textarea>
                    </div>

                    <!-- Type & Discount -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Tipe Diskon</label>
                            <select wire:model="type"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                <option value="percentage">Persentase (%)</option>
                                <option value="fixed">Fixed (Rp)</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">
                                Nilai Diskon
                                @if($type === 'percentage')
                                    (%)
                                @else
                                    (Rp)
                                @endif
                            </label>
                            <input type="number"
                                   wire:model="discountValue"
                                   min="0"
                                   step="any"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 @error('discountValue') border-red-500 @enderror">
                            @error('discountValue')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Max Discount (for percentage) -->
                    @if($type === 'percentage')
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Maksimum Diskon (Rp)</label>
                            <input type="number"
                                   wire:model="maxDiscountAmount"
                                   min="0"
                                   placeholder="Opsional"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        </div>
                    @endif

                    <!-- Min Purchase -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Minimum Pembelian (Rp)</label>
                        <input type="number"
                               wire:model="minPurchaseAmount"
                               min="0"
                               placeholder="0 = tanpa minimum"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>

                    <!-- Usage Limits -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Batas Penggunaan</label>
                            <input type="number"
                                   wire:model="usageLimit"
                                   min="1"
                                   placeholder="∞"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Per User</label>
                            <input type="number"
                                   wire:model="perUserLimit"
                                   min="1"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        </div>
                    </div>

                    <!-- Dates -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Tanggal Mulai</label>
                            <input type="datetime-local"
                                   wire:model="startsAt"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Tanggal Berakhir</label>
                            <input type="datetime-local"
                                   wire:model="expiresAt"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        </div>
                    </div>

                    <!-- Active Toggle -->
                    <div class="flex items-center gap-3">
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input type="checkbox"
                                   wire:model="isActive"
                                   class="peer sr-only">
                            <div class="peer h-6 w-11 rounded-full bg-slate-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-emerald-600 peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                        <span class="text-sm text-slate-700">Promo Aktif</span>
                    </div>

                    <!-- Submit -->
                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
                        <button type="button"
                                wire:click="closeModal"
                                class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                            Batal
                        </button>
                        <button type="submit"
                                class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                            {{ $isEditing ? 'Simpan Perubahan' : 'Buat Promo' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
