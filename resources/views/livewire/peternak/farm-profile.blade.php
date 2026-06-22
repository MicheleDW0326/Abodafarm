<div class="space-y-6">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Profil Peternakan</h2>
        <p class="mt-1 text-sm text-slate-500">Kelola informasi peternakan Anda</p>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-slate-100 bg-white p-6">
            <h3 class="mb-4 text-lg font-semibold text-slate-900">Informasi Peternakan</h3>

            <form wire:submit="save" class="space-y-4">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Nama Peternakan *</label>
                    <input type="text" wire:model="farmName" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Deskripsi</label>
                    <textarea wire:model="description" rows="3" placeholder="Deskripsikan peternakan Anda..." class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"></textarea>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Nomor HP</label>
                    <input type="text" wire:model="phone" placeholder="08xxxxxxxxxx" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Desa</label>
                        <input type="text" wire:model="village" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Kecamatan</label>
                        <input type="text" wire:model="district" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Kabupaten</label>
                        <input type="text" wire:model="regency" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Provinsi</label>
                        <input type="text" wire:model="province" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Alamat Lengkap</label>
                    <textarea wire:model="address" rows="2" placeholder="Alamat lengkap..." class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"></textarea>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Foto Peternakan</label>
                    <div class="relative">
                        <input type="file" wire:model.live="image" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" />
                        <div class="flex items-center justify-center gap-3 rounded-lg border-2 border-dashed border-slate-300 bg-slate-50 px-4 py-6 transition-all hover:border-emerald-400 hover:bg-emerald-50/50">
                            <div class="text-center">
                                @if ($image)
                                    <div class="flex items-center justify-center gap-2 text-emerald-600">
                                        <svg class="size-6 animate-spin" viewBox="0 0 24 24" fill="none" wire:loading wire:target="image">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span class="text-sm font-medium" wire:loading wire:target="image">Mengupload...</span>
                                        <span class="text-sm font-medium" wire:loading.remove wire:target="image">
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
                    @if ($image)
                        <div class="mt-3 relative w-32 h-32 rounded-lg overflow-hidden border-2 border-emerald-200 bg-emerald-50">
                            <img src="{{ $image->temporaryUrl() }}" alt="Preview" class="w-full h-full object-cover" wire:loading.remove wire:target="image" />
                            <div class="absolute inset-0 flex items-center justify-center bg-emerald-50/80" wire:loading wire:target="image">
                                <svg class="size-8 text-emerald-600 animate-spin" viewBox="0 0 24 24" fill="none">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </div>
                    @elseif ($existingImage)
                        <div class="mt-3 relative w-32 h-32 rounded-lg overflow-hidden border-2 border-slate-200 bg-slate-50">
                            <img src="{{ Storage::url($existingImage) }}" alt="Current" class="w-full h-full object-cover" />
                            <button
                                type="button"
                                wire:click="$set('existingImage', null)"
                                class="absolute right-1 top-1 rounded-full bg-red-500 p-1 text-white hover:bg-red-600"
                            >
                                <i class="fa-solid fa-xmark text-xs"></i>
                            </button>
                        </div>
                    @endif
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                        Simpan Profil
                    </button>
                </div>
            </form>
        </div>

        {{-- Preview Card --}}
        <div class="rounded-2xl border border-slate-100 bg-white p-6">
            <h3 class="mb-4 text-lg font-semibold text-slate-900">Preview Profil</h3>

            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                <div class="flex items-start gap-4">
                    <div class="size-20 rounded-lg bg-slate-200">
                        @if($image)
                            <img src="{{ $image->temporaryUrl() }}" alt="" class="h-full w-full rounded-lg object-cover" wire:loading.remove wire:target="image">
                        @elseif($existingImage)
                            <img src="{{ Storage::url($existingImage) }}" alt="" class="h-full w-full rounded-lg object-cover">
                        @else
                            <div class="flex h-full items-center justify-center">
                                <i class="fa-solid fa-farm text-2xl text-slate-400"></i>
                            </div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <h4 class="font-bold text-slate-900">{{ $farmName ?: 'Nama Peternakan' }}</h4>
                        @if($regency || $province)
                            <p class="mt-1 text-sm text-slate-500">
                                <i class="fa-solid fa-location-dot mr-1"></i>
                                {{ implode(', ', array_filter([$village, $district, $regency, $province])) }}
                            </p>
                        @endif
                        @if($phone)
                            <p class="mt-1 text-sm text-slate-500">
                                <i class="fa-solid fa-phone mr-1"></i>
                                {{ $phone }}
                            </p>
                        @endif
                    </div>
                </div>

                @if($description)
                    <p class="mt-4 text-sm text-slate-600">{{ $description }}</p>
                @endif
            </div>

            <div class="mt-4 rounded-lg border border-blue-100 bg-blue-50 p-4">
                <p class="text-xs text-blue-700">
                    <i class="fa-solid fa-circle-info mr-1"></i>
                    Profil ini akan ditampilkan di halaman detail ternak untuk membangun kepercayaan pelanggan.
                </p>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="fixed bottom-4 right-4 z-50 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-medium text-white shadow-lg">
            <i class="fa-solid fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif
</div>
