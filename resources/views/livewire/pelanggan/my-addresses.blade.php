<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between rounded-2xl border border-slate-100 bg-gradient-to-r from-emerald-600 to-emerald-700 p-6 shadow-lg">
        <div class="flex items-center gap-4">
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-white/20 text-xl text-white">
                <i class="fa-solid fa-map-marker-alt"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-white">Alamat Pengiriman</h2>
                <p class="mt-0.5 text-emerald-100">Kelola alamat pengiriman Anda</p>
            </div>
        </div>
        <button
            wire:click="openAddModal"
            class="flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-50"
        >
            <i class="fa-solid fa-plus"></i>
            Tambah Alamat
        </button>
    </div>

    {{-- Addresses Grid --}}
    @if(count($addresses) > 0)
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($addresses as $address)
                <div class="group relative rounded-2xl border border-slate-100 bg-white p-5 shadow-xs transition hover:shadow-md @if($address['is_default']) ring-2 ring-emerald-500 @endif">
                    {{-- Default Badge --}}
                    @if($address['is_default'])
                        <span class="absolute -top-2 right-4 rounded-full bg-emerald-500 px-3 py-1 text-xs font-semibold text-white shadow-sm">
                            <i class="fa-solid fa-star mr-1"></i> Utama
                        </span>
                    @endif

                    {{-- Label --}}
                    <div class="mb-3 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                                <i class="fa-solid fa-{{ $address['label'] === 'Rumah' ? 'home' : ($address['label'] === 'Kantor' ? 'building' : 'map-pin') }}"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-slate-900">{{ $address['label'] }}</p>
                                <p class="text-xs text-slate-500">{{ $address['recipient_name'] }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Address Details --}}
                    <div class="space-y-2">
                        <p class="text-sm text-slate-600">
                            <i class="fa-solid fa-user mr-2 text-slate-400"></i>{{ $address['recipient_name'] }}
                        </p>
                        <p class="text-sm text-slate-600">
                            <i class="fa-solid fa-phone mr-2 text-slate-400"></i>{{ $address['phone'] }}
                        </p>
                        <p class="text-sm text-slate-600">
                            <i class="fa-solid fa-map-pin mr-2 text-slate-400"></i>
                            {{ $address['address'] }}, {{ $address['district'] }}, {{ $address['city'] }}, {{ $address['province'] }}
                            @if($address['postal_code'])
                                {{ $address['postal_code'] }}
                            @endif
                        </p>
                    </div>

                    {{-- Actions --}}
                    <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4">
                        <div class="flex gap-1">
                            <button
                                wire:click="openEditModal({{ $address['id'] }})"
                                class="rounded-lg px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-slate-100"
                            >
                                <i class="fa-solid fa-edit mr-1"></i> Edit
                            </button>
                            @if(!$address['is_default'])
                                <button
                                    wire:click="setAsDefault({{ $address['id'] }})"
                                    class="rounded-lg px-3 py-1.5 text-xs font-medium text-emerald-600 transition hover:bg-emerald-50"
                                >
                                    <i class="fa-solid fa-star mr-1"></i> Jadikan Utama
                                </button>
                            @endif
                        </div>
                        <button
                            wire:click="delete({{ $address['id'] }})"
                            wire:confirm="Yakin ingin menghapus alamat ini?"
                            class="rounded-lg px-3 py-1.5 text-xs font-medium text-red-500 transition hover:bg-red-50"
                        >
                            <i class="fa-solid fa-trash mr-1"></i>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="flex flex-col items-center gap-4 rounded-2xl border border-dashed border-slate-300 bg-slate-50 py-16 text-center">
            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                <i class="fa-solid fa-map-marker-alt text-3xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-slate-700">Belum ada alamat</h3>
                <p class="mt-1 text-sm text-slate-500">Tambahkan alamat untuk kemudahan pengiriman</p>
            </div>
            <button
                wire:click="openAddModal"
                class="mt-2 rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700"
            >
                <i class="fa-solid fa-plus mr-2"></i>Tambah Alamat
            </button>
        </div>
    @endif

    {{-- Add/Edit Modal --}}
    @if($showModal)
        <div
            x-data="{ open: @entangle('showModal') }"
            x-show="$wire.showModal"
            x-on:keydown.escape.window="$wire.closeModal()"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4"
        >
            <div
                x-show="$wire.showModal"
                x-transition
                class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-white shadow-xl"
            >
                {{-- Modal Header --}}
                <div class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-100 bg-white px-6 py-4">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">{{ $isEditing ? 'Edit Alamat' : 'Tambah Alamat Baru' }}</h3>
                        <p class="text-sm text-slate-500">{{ $isEditing ? 'Perbarui informasi alamat' : 'Isi informasi alamat baru' }}</p>
                    </div>
                    <button
                        wire:click="closeModal"
                        class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                    >
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>

                {{-- Modal Content --}}
                <form wire:submit="save" class="space-y-4 p-6">
                    {{-- Label --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Label Alamat</label>
                        <div class="mt-2 flex gap-2">
                            @foreach(['Rumah', 'Kantor', 'Lainnya'] as $labelOption)
                                <button
                                    type="button"
                                    wire:click="$set('label', '{{ $labelOption }}')"
                                    class="flex-1 rounded-lg border px-3 py-2 text-sm font-medium transition @if($label === $labelOption) border-emerald-500 bg-emerald-50 text-emerald-700 @else border-slate-200 text-slate-600 hover:border-slate-300 @endif"
                                >
                                    <i class="fa-solid fa-{{ $labelOption === 'Rumah' ? 'home' : ($labelOption === 'Kantor' ? 'building' : 'map-pin') }} mr-1"></i>
                                    {{ $labelOption }}
                                </button>
                            @endforeach
                        </div>
                        @error('label')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Recipient Name --}}
                    <div>
                        <label for="recipient_name" class="block text-sm font-medium text-slate-700">Nama Penerima</label>
                        <input
                            type="text"
                            id="recipient_name"
                            wire:model="recipient_name"
                            class="mt-1 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                            placeholder="Nama lengkap penerima"
                        >
                        @error('recipient_name')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Phone --}}
                    <div>
                        <label for="phone" class="block text-sm font-medium text-slate-700">Nomor Telepon</label>
                        <input
                            type="tel"
                            id="phone"
                            wire:model="phone"
                            class="mt-1 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                            placeholder="08xxxxxxxxxx"
                        >
                        @error('phone')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Address --}}
                    <div>
                        <label for="address" class="block text-sm font-medium text-slate-700">Alamat Lengkap</label>
                        <textarea
                            id="address"
                            wire:model="address"
                            rows="3"
                            class="mt-1 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                            placeholder="Jl. Nama Jalan, No. Rumah, RT/RW, Kel."
                        ></textarea>
                        @error('address')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Location --}}
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="province" class="block text-sm font-medium text-slate-700">Provinsi</label>
                            <input
                                type="text"
                                id="province"
                                wire:model="province"
                                class="mt-1 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                                placeholder="Contoh: Sulawesi Utara"
                            >
                            @error('province')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="city" class="block text-sm font-medium text-slate-700">Kabupaten/Kota</label>
                            <input
                                type="text"
                                id="city"
                                wire:model="city"
                                class="mt-1 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                                placeholder="Contoh: Manado"
                            >
                            @error('city')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="district" class="block text-sm font-medium text-slate-700">Kecamatan</label>
                            <input
                                type="text"
                                id="district"
                                wire:model="district"
                                class="mt-1 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                                placeholder="Contoh: Tuminting"
                            >
                            @error('district')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="postal_code" class="block text-sm font-medium text-slate-700">Kode Pos</label>
                            <input
                                type="text"
                                id="postal_code"
                                wire:model="postal_code"
                                class="mt-1 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                                placeholder="Contoh: 95359"
                            >
                            @error('postal_code')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Set as Default --}}
                    <div class="flex items-center gap-3 rounded-xl border border-slate-100 p-4">
                        <input
                            type="checkbox"
                            id="is_default"
                            wire:model="is_default"
                            class="h-5 w-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                        >
                        <label for="is_default" class="text-sm font-medium text-slate-700">
                            Jadikan sebagai alamat utama
                        </label>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-4">
                        <button
                            type="button"
                            wire:click="closeModal"
                            class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700"
                        >
                            <i class="fa-solid fa-save mr-2"></i>
                            {{ $isEditing ? 'Simpan Perubahan' : 'Tambah Alamat' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
