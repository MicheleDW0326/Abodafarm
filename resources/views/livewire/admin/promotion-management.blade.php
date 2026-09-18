<div>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Kelola Promo</h1>
                <p class="mt-1 text-sm text-slate-500">Buat dan kelola kode promo untuk pelanggan</p>
            </div>
            <x-ui.button.primary wire:click="openModal()" class="cursor-pointer ">
                <i class="fa-solid fa-plus mr-2"></i> Tambah Promo
            </x-ui.button.primary>
        </div>

        <!-- Search -->
        <div class="flex items-center gap-4">
            <x-ui.search.input wire:model.live.debounce.300ms="search" placeholder="Cari kode atau judul promo..."
                class="max-w-md" />
        </div>

        <!-- Promotions Table -->
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                            Kode</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                            Info</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                            Diskon</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                            Syarat</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                            Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-600">
                            Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($promotions as $promo)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center rounded-lg bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-800">
                                    {{ $promo->code }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-medium text-slate-900">{{ $promo->title }}</p>
                                @if ($promo->expires_at)
                                    <p class="text-xs text-slate-500">
                                        Berakhir: {{ $promo->expires_at->format('d M Y') }}
                                    </p>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-semibold text-emerald-600">
                                    @if ($promo->type === 'percentage')
                                        {{ $promo->discount_value }}%
                                        @if ($promo->max_discount_amount)
                                            <span class="text-xs font-normal text-slate-500">(Max Rp
                                                {{ number_format($promo->max_discount_amount) }})</span>
                                        @endif
                                    @else
                                        Rp {{ number_format($promo->discount_value) }}
                                    @endif
                                </p>
                            </td>
                            <td class="px-6 py-4">
                                @if ($promo->min_purchase_amount > 0)
                                    <p class="text-xs text-slate-600">Min: Rp
                                        {{ number_format($promo->min_purchase_amount) }}</p>
                                @endif
                                <p class="text-xs text-slate-500">
                                    Penggunaan: {{ $promo->usage_count }}
                                    @if ($promo->usage_limit)
                                        / {{ $promo->usage_limit }}
                                    @else
                                        / ∞
                                    @endif
                                </p>
                            </td>
                            <td class="px-6 py-4">
                                @if ($promo->is_active)
                                    @if ($promo->isValid())
                                        <span
                                            class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700">
                                            Aktif
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">
                                            Berakhir
                                        </span>
                                    @endif
                                @else
                                    <span
                                        class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-1 text-xs font-medium text-red-700">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="toggleActive({{ $promo->id }})"
                                        class="p-1.5 text-slate-400 hover:text-slate-600 transition"
                                        title="{{ $promo->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        @if ($promo->is_active)
                                            <i class="fa-solid fa-eye-slash"></i>
                                        @else
                                            <i class="fa-solid fa-eye"></i>
                                        @endif
                                    </button>
                                    <button wire:click="openModal({{ $promo->id }})"
                                        class="p-1.5 text-slate-400 hover:text-emerald-600 transition">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <button wire:click="confirmDelete({{ $promo->id }})"
                                        class="p-1.5 text-slate-400 hover:text-red-600 transition">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12">
                                <x-ui.empty-state icon="fa-ticket" title="Belum ada promo"
                                    description="Buat promo pertama Anda untuk menarik pelanggan">
                                    <x-slot:action>
                                        <x-ui.btn.primary wire:click="openModal()">
                                            <i class="fa-solid fa-plus mr-1"></i> Tambah Promo
                                        </x-ui.btn.primary>
                                    </x-slot:action>
                                </x-ui.empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if ($promotions->hasPages())
                <div class="border-t border-slate-200 px-6 py-4">
                    {{ $promotions->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal Form -->
    <div x-data="{
        show: @entangle('showModal'),
        init() {
            this.$watch('show', value => {
                document.body.style.overflow = value ? 'hidden' : '';
            });
        }
    }" role="dialog" aria-modal="true" aria-labelledby="promo-modal-title"
        :aria-hidden="!show" x-on:keydown.escape.window="show = false; $wire.closeModal()">

        <!-- Backdrop -->
        <div x-show="show" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 bg-black/50" x-on:click="show = false; $wire.closeModal()">
        </div>

        <!-- Modal Container -->
        <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100" x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">

            <!-- Modal Dialog -->
            <div class="w-full max-w-lg rounded-2xl bg-white shadow-2xl" @click.stop>

                <!-- Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                    <div>
                        <h3 id="promo-modal-title" class="text-lg font-bold text-slate-900">
                            {{ $isEditing ? 'Edit Promo' : 'Tambah Promo Baru' }}
                        </h3>
                        <p class="text-sm text-slate-500">
                            {{ $isEditing ? 'Perbarui detail promo' : 'Buat kode promo baru' }}
                        </p>
                    </div>
                    <button wire:click="closeModal"
                        class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                        aria-label="Tutup modal">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <!-- Body (scrollable) -->
                <div class="max-h-[70vh] overflow-y-auto p-6">
                    <form wire:submit.prevent="save" class="space-y-4">
                        <!-- Code -->
                        <div>
                            <x-ui.form.input label="Kode Promo" wire:model.live="code" placeholder="Contoh: PROMO2024"
                                :error="$errors->first('code')" />
                            <button type="button" wire:click="generateCode"
                                class="mt-2 text-xs text-emerald-600 hover:text-emerald-700 font-medium">
                                <i class="fa-solid fa-rotate mr-1"></i> Generate Otomatis
                            </button>
                        </div>

                        <!-- Title -->
                        <x-ui.form.input label="Judul Promo" wire:model.live="title"
                            placeholder="Contoh: Diskon 20% Semua Produk" :error="$errors->first('title')" required />

                        <!-- Description -->
                        <x-ui.form.textarea label="Deskripsi (opsional)" wire:model.live="description"
                            placeholder="Deskripsi singkat promo..." rows="2" />

                        <!-- Type & Discount -->
                        <div class="grid grid-cols-2 gap-4">
                            <x-ui.form.select label="Tipe Diskon" wire:model.live="type" :options="[
                                ['value' => 'percentage', 'label' => 'Persentase (%)'],
                                ['value' => 'fixed', 'label' => 'Fixed (Rp)'],
                            ]" />

                            <x-ui.form.input :label="$type === 'percentage' ? 'Nilai Diskon (%)' : 'Nilai Diskon (Rp)'" type="number" wire:model.live="discountValue"
                                min="0" step="any" :error="$errors->first('discountValue')" required />
                        </div>

                        <!-- Max Discount (for percentage) -->
                        @if ($type === 'percentage')
                            <x-ui.form.input label="Maksimum Diskon (Rp)" type="number"
                                wire:model.live="maxDiscountAmount" min="0" placeholder="Opsional"
                                hint="Batas maksimal diskon dalam Rupiah" />
                        @endif

                        <!-- Min Purchase -->
                        <x-ui.form.input label="Minimum Pembelian (Rp)" type="number"
                            wire:model.live="minPurchaseAmount" min="0" placeholder="0 = tanpa minimum"
                            hint="Minimum total belanja untuk menggunakan promo ini" />

                        <!-- Usage Limits -->
                        <div class="grid grid-cols-2 gap-4">
                            <x-ui.form.input label="Batas Penggunaan" type="number" wire:model.live="usageLimit"
                                min="1" placeholder="∞" hint="Jumlah maksimal penggunaan promo" />

                            <x-ui.form.input label="Per User" type="number" wire:model.live="perUserLimit"
                                min="1" placeholder="1" />
                        </div>

                        <!-- Dates -->
                        <div class="grid grid-cols-2 gap-4">
                            <x-ui.form.input label="Tanggal Mulai" type="datetime-local"
                                wire:model.live="startsAt" />

                            <x-ui.form.input label="Tanggal Berakhir" type="datetime-local"
                                wire:model.live="expiresAt" />
                        </div>

                        <!-- Active Toggle -->
                        <x-ui.form.toggle label="Promo Aktif" wire:model.live="isActive" />
                    </form>
                </div>

                <!-- Footer -->
                <div class="flex justify-end gap-3 px-6 py-4 border-t border-slate-200 bg-slate-50">
                    <x-ui.btn.cancel wire:click="closeModal">
                        Batal
                    </x-ui.btn.cancel>
                    <x-ui.btn.primary wire:click="save">
                        {{ $isEditing ? 'Simpan Perubahan' : 'Buat Promo' }}
                    </x-ui.btn.primary>
                </div>
            </div>
        </div>
    </div>
