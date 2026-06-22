<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Manajemen Ternak</h2>
            <p class="mt-1 text-sm text-slate-500">Kelola ternak Anda di sini</p>
        </div>
        <button
            type="button"
            wire:click="openModal()"
            class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700"
        >
            <i class="fa-solid fa-plus"></i>
            Tambah Ternak
        </button>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <article class="rounded-xl border border-slate-100 bg-white p-4">
            <p class="text-sm text-slate-500">Total Ternak</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $stats['total'] }}</p>
        </article>
        <article class="rounded-xl border border-slate-100 bg-white p-4">
            <p class="text-sm text-slate-500">Tersedia</p>
            <p class="mt-1 text-2xl font-bold text-emerald-600">{{ $stats['available'] }}</p>
        </article>
        <article class="rounded-xl border border-slate-100 bg-white p-4">
            <p class="text-sm text-slate-500">Dipesan</p>
            <p class="mt-1 text-2xl font-bold text-amber-600">{{ $stats['reserved'] }}</p>
        </article>
        <article class="rounded-xl border border-slate-100 bg-white p-4">
            <p class="text-sm text-slate-500">Terjual</p>
            <p class="mt-1 text-2xl font-bold text-blue-600">{{ $stats['sold'] }}</p>
        </article>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col gap-4 rounded-xl border border-slate-100 bg-white p-4 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <input
                type="text"
                wire:model.live.debounce.300ms="searchQuery"
                placeholder="Cari nama atau ras ternak..."
                class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2 pl-10 pr-4 text-sm placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
            />
            <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
        </div>

        <select
            wire:model.live="statusFilter"
            class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
        >
            <option value="all">Semua Status</option>
            <option value="available">Tersedia</option>
            <option value="reserved">Dipesan</option>
            <option value="sold">Terjual</option>
            <option value="inactive">Tidak Aktif</option>
        </select>

        <select
            wire:model.live="categoryFilter"
            class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
        >
            <option value="all">Semua Kategori</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
            @endforeach
        </select>

        <div class="flex items-center gap-1 rounded-lg bg-slate-100 p-1">
            <button
                type="button"
                wire:click="$set('viewMode', 'grid')"
                class="rounded-md px-3 py-1.5 text-sm transition {{ $viewMode === 'grid' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}"
            >
                <i class="fa-solid fa-grid-2"></i>
            </button>
            <button
                type="button"
                wire:click="$set('viewMode', 'table')"
                class="rounded-md px-3 py-1.5 text-sm transition {{ $viewMode === 'table' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}"
            >
                <i class="fa-solid fa-list"></i>
            </button>
        </div>
    </div>

    {{-- Grid View --}}
    @if($viewMode === 'grid')
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @forelse($livestocks as $livestock)
                <article class="overflow-hidden rounded-2xl border border-slate-100 bg-white transition hover:shadow-lg">
                    <div class="relative aspect-[4/3] bg-slate-100">
                        @if($livestock->image)
                            <img src="{{ Storage::url($livestock->image) }}" alt="{{ $livestock->name }}" class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full items-center justify-center">
                                <i class="fa-solid fa-cow text-6xl text-slate-300"></i>
                            </div>
                        @endif

                        @php
                            $statusClass = match($livestock->status) {
                                'available' => 'bg-emerald-500',
                                'reserved' => 'bg-amber-500',
                                'sold' => 'bg-blue-500',
                                'inactive' => 'bg-slate-400',
                                default => 'bg-slate-400',
                            };
                            $statusLabel = match($livestock->status) {
                                'available' => 'Tersedia',
                                'reserved' => 'Dipesan',
                                'sold' => 'Terjual',
                                'inactive' => 'Tidak Aktif',
                                default => $livestock->status,
                            };
                        @endphp
                        <span class="absolute right-3 top-3 rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase text-white {{ $statusClass }}">
                            {{ $statusLabel }}
                        </span>
                    </div>

                    <div class="p-4">
                        <h3 class="font-bold text-slate-900">{{ $livestock->name }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ $livestock->category?->name ?? '-' }}</p>

                        <div class="mt-3 flex flex-wrap gap-2 text-xs text-slate-500">
                            @if($livestock->breed)
                                <span class="rounded-full bg-slate-100 px-2 py-1">{{ $livestock->breed }}</span>
                            @endif
                            @if($livestock->sex)
                                <span class="rounded-full bg-slate-100 px-2 py-1">
                                    <i class="fa-solid fa-{{ $livestock->sex === 'male' ? 'mars' : ($livestock->sex === 'female' ? 'venus' : 'mercury') }}"></i>
                                    {{ $livestock->sex === 'male' ? 'Jantan' : ($livestock->sex === 'female' ? 'Betina' : 'Campuran') }}
                                </span>
                            @endif
                            @if($livestock->weight)
                                <span class="rounded-full bg-slate-100 px-2 py-1">{{ $livestock->weight }} kg</span>
                            @endif
                        </div>

                        <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3">
                            <span class="text-lg font-bold text-emerald-600">Rp {{ number_format($livestock->price, 0, ',', '.') }}</span>
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    wire:click="openModal({{ $livestock->id }})"
                                    class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                                >
                                    <i class="fa-solid fa-pen text-sm"></i>
                                </button>
                                <button
                                    type="button"
                                    wire:click="confirmDelete({{ $livestock->id }})"
                                    class="rounded-lg p-2 text-slate-400 transition hover:bg-red-50 hover:text-red-500"
                                >
                                    <i class="fa-solid fa-trash text-sm"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full py-12 text-center">
                    <i class="fa-solid fa-cow text-6xl text-slate-200"></i>
                    <p class="mt-4 text-slate-500">Belum ada ternak yang terdaftar</p>
                    <button
                        type="button"
                        wire:click="openModal()"
                        class="mt-4 inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700"
                    >
                        <i class="fa-solid fa-plus"></i>
                        Tambah Ternak Pertama
                    </button>
                </div>
            @endforelse
        </div>
    @else
        {{-- Table View --}}
        <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                            <th class="px-4 py-3 font-semibold">Ternak</th>
                            <th class="px-4 py-3 font-semibold">Ras</th>
                            <th class="px-4 py-3 font-semibold">Umur</th>
                            <th class="px-4 py-3 font-semibold">Berat</th>
                            <th class="px-4 py-3 font-semibold">Harga</th>
                            <th class="px-4 py-3 font-semibold">Status</th>
                            <th class="px-4 py-3 font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($livestocks as $livestock)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="size-12 rounded-lg bg-slate-100">
                                            @if($livestock->image)
                                                <img src="{{ Storage::url($livestock->image) }}" alt="" class="h-full w-full rounded-lg object-cover">
                                            @else
                                                <div class="flex h-full items-center justify-center">
                                                    <i class="fa-solid fa-cow text-slate-400"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="font-semibold text-slate-900">{{ $livestock->name }}</p>
                                            <p class="text-xs text-slate-500">{{ $livestock->category?->name ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-600">{{ $livestock->breed ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-slate-600">{{ $livestock->age ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-slate-600">{{ $livestock->weight ? $livestock->weight . ' kg' : '-' }}</td>
                                <td class="px-4 py-3 text-sm font-semibold text-emerald-600">Rp {{ number_format($livestock->price, 0, ',', '.') }}</td>
                                <td class="px-4 py-3">
                                    @php
                                        $statusClass = match($livestock->status) {
                                            'available' => 'bg-emerald-50 text-emerald-700',
                                            'reserved' => 'bg-amber-50 text-amber-700',
                                            'sold' => 'bg-blue-50 text-blue-700',
                                            'inactive' => 'bg-slate-100 text-slate-600',
                                            default => 'bg-slate-100 text-slate-600',
                                        };
                                        $statusLabel = match($livestock->status) {
                                            'available' => 'Tersedia',
                                            'reserved' => 'Dipesan',
                                            'sold' => 'Terjual',
                                            'inactive' => 'Tidak Aktif',
                                            default => $livestock->status,
                                        };
                                    @endphp
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <button
                                            type="button"
                                            wire:click="openModal({{ $livestock->id }})"
                                            class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                                        >
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="confirmDelete({{ $livestock->id }})"
                                            class="rounded-lg p-2 text-slate-400 transition hover:bg-red-50 hover:text-red-500"
                                        >
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center">
                                    <p class="text-slate-400">Belum ada ternak yang terdaftar</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Modal Form --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4" wire:click.self="closeModal">
            <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white shadow-xl" wire:click.stop>
                <div class="sticky top-0 flex items-center justify-between border-b border-slate-100 bg-white px-6 py-4">
                    <h3 class="text-lg font-bold text-slate-900">{{ $editingId ? 'Edit Ternak' : 'Tambah Ternak Baru' }}</h3>
                    <button type="button" wire:click="closeModal" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form wire:submit="save" class="p-6">
                    <div class="grid gap-6 md:grid-cols-2">
                        {{-- Left Column --}}
                        <div class="space-y-4">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Nama Ternak *</label>
                                <input
                                    type="text"
                                    wire:model="name"
                                    required
                                    placeholder="Contoh: Sapi Limousin A001"
                                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                                />
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Kategori *</label>
                                <select
                                    wire:model="categoryId"
                                    required
                                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                                >
                                    <option value="">Pilih Kategori</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Ras</label>
                                <input
                                    type="text"
                                    wire:model="breed"
                                    placeholder="Contoh: Limousin, Brahman, dll"
                                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                                />
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Umur</label>
                                <input
                                    type="text"
                                    wire:model="age"
                                    placeholder="Contoh: 2 Tahun"
                                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                                />
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Berat (kg)</label>
                                <input
                                    type="number"
                                    wire:model="weight"
                                    step="0.1"
                                    min="0"
                                    placeholder="Contoh: 350"
                                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                                />
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Jenis Kelamin *</label>
                                <select
                                    wire:model="sex"
                                    required
                                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                                >
                                    <option value="male">Jantan</option>
                                    <option value="female">Betina</option>
                                    <option value="mixed">Campuran</option>
                                </select>
                            </div>
                        </div>

                        {{-- Right Column --}}
                        <div class="space-y-4">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Harga *</label>
                                <input
                                    type="number"
                                    wire:model="price"
                                    required
                                    min="0"
                                    placeholder="Contoh: 15000000"
                                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                                />
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Status *</label>
                                <select
                                    wire:model="status"
                                    required
                                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                                >
                                    <option value="available">Tersedia</option>
                                    <option value="reserved">Dipesan</option>
                                    <option value="sold">Terjual</option>
                                    <option value="inactive">Tidak Aktif</option>
                                </select>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Lokasi Kandang</label>
                                <input
                                    type="text"
                                    wire:model="barnLocation"
                                    placeholder="Contoh: Kandang A1"
                                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                                />
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Asal</label>
                                <input
                                    type="text"
                                    wire:model="origin"
                                    placeholder="Contoh: Talaud"
                                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                                />
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Kondisi Kesehatan</label>
                                <input
                                    type="text"
                                    wire:model="condition"
                                    placeholder="Contoh: Sehat, Sudah Vaksin"
                                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                                />
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Deskripsi</label>
                                <textarea
                                    wire:model="description"
                                    rows="3"
                                    placeholder="Deskripsi tambahan..."
                                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Image Upload --}}
                    <div class="mt-6">
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Foto Utama</label>
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

                        @error('image')
                            <p class="mt-2 text-red-600 text-xs">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-6 flex justify-end gap-3 border-t border-slate-100 pt-6">
                        <button
                            type="button"
                            wire:click="closeModal"
                            class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700"
                        >
                            {{ $editingId ? 'Simpan Perubahan' : 'Tambah Ternak' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($pendingDeleteId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl" wire:click.stop>
                <div class="text-center">
                    <div class="mx-auto mb-4 flex size-14 items-center justify-center rounded-full bg-red-100">
                        <i class="fa-solid fa-triangle-exclamation text-2xl text-red-500"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">Hapus Ternak?</h3>
                    <p class="mt-2 text-sm text-slate-500">Tindakan ini tidak dapat dibatalkan.</p>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button
                        type="button"
                        wire:click="$set('pendingDeleteId', null)"
                        class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        wire:click="deleteConfirmed"
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700"
                    >
                        Ya, Hapus
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="fixed bottom-4 right-4 z-50 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-medium text-white shadow-lg" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)">
            <i class="fa-solid fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="fixed bottom-4 right-4 z-50 rounded-xl bg-red-600 px-4 py-3 text-sm font-medium text-white shadow-lg" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)">
            <i class="fa-solid fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
    @endif
</div>
