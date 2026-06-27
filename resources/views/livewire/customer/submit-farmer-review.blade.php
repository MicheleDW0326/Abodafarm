<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between rounded-2xl border border-slate-100 bg-gradient-to-r from-emerald-600 to-teal-600 p-6 shadow-lg">
        <div class="flex items-center gap-4">
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-white/20 text-xl text-white">
                <i class="fa-solid fa-cow"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-white">Beri Ulasan Peternak</h2>
                <p class="mt-0.5 text-emerald-100">Bagikan pengalaman Anda dengan peternak</p>
            </div>
        </div>
    </div>

    @if($showSuccess)
        {{-- Success State --}}
        <div class="flex flex-col items-center gap-4 rounded-2xl border border-emerald-200 bg-emerald-50 p-8 text-center">
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                <i class="fa-solid fa-check text-2xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-emerald-800">Terima Kasih!</h3>
                <p class="mt-1 text-sm text-emerald-700">Ulasan Anda untuk {{ $farmerName }} berhasil disimpan.</p>
            </div>
            <div class="flex gap-3">
                <a
                    href="{{ route('pelanggan.reviews') }}"
                    class="rounded-xl border border-emerald-300 bg-white px-4 py-2 text-sm font-medium text-emerald-700 transition hover:bg-emerald-50"
                >
                    Lihat Ulasan Saya
                </a>
                <a
                    href="{{ route('pelanggan.bookings') }}"
                    class="rounded-xl bg-emerald-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-600"
                >
                    Booking Saya
                </a>
            </div>
        </div>
    @else
        {{-- Farmer Info --}}
        <div class="flex items-center gap-4 rounded-xl border border-slate-100 bg-white p-4">
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                <i class="fa-solid fa-user text-xl"></i>
            </div>
            <div>
                <p class="font-semibold text-slate-900">{{ $farmerName }}</p>
                <p class="text-sm text-slate-500">Peternak</p>
            </div>
        </div>

        {{-- Booking Info --}}
        <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-4">
            <p class="text-xs text-emerald-600">
                <i class="fa-solid fa-circle-check mr-1"></i>
                Verified Purchase - Booking #{{ $booking?->order_number }}
            </p>
        </div>

        {{-- Review Form --}}
        <form wire:submit="submit" class="space-y-6 rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            {{-- Star Rating --}}
            <div>
                <label class="mb-3 block text-sm font-medium text-slate-700">
                    Bagaimana pengalaman Anda dengan layanan peternak ini?
                </label>
                <div class="flex gap-1" x-data="{ rating: {{ $rating }} }">
                    @for($i = 1; $i <= 5; $i++)
                        <button
                            type="button"
                            @click="rating = {{ $i }}; $wire.rating = {{ $i }}"
                            :class="rating >= {{ $i }} ? 'text-emerald-500 scale-110' : 'text-slate-300 hover:text-emerald-400'"
                            class="text-4xl transition hover:scale-110"
                        >
                            <i class="fa{{ $i <= $rating ? '-solid' : '-regular' }} fa-star"></i>
                        </button>
                    @endfor
                </div>
                <p class="mt-2 text-sm text-slate-500">
                    @if($rating == 5) Sangat Puas @elseif($rating == 4) Puas @elseif($rating == 3) Biasa Saja @elseif($rating == 2) Kurang Puas @else Tidak Puas @endif
                </p>
                @error('rating')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Title --}}
            <div>
                <label for="title" class="mb-1 block text-sm font-medium text-slate-700">
                    Judul Ulasan <span class="text-slate-400">(opsional)</span>
                </label>
                <input
                    type="text"
                    id="title"
                    wire:model="title"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                    placeholder="Ringkaskan pengalaman Anda"
                >
                @error('title')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Content --}}
            <div>
                <label for="content" class="mb-1 block text-sm font-medium text-slate-700">
                    Ulasan <span class="text-slate-400">(opsional)</span>
                </label>
                <textarea
                    id="content"
                    wire:model="content"
                    rows="4"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                    placeholder="Ceritakan pengalaman Anda dengan peternak ini..."
                ></textarea>
                @error('content')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Photos --}}
            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">
                    Foto <span class="text-slate-400">(maks 3 foto)</span>
                </label>

                @if(count($photos) > 0)
                    <div class="mb-3 flex flex-wrap gap-2">
                        @foreach($photos as $index => $photo)
                            <div class="relative">
                                <img
                                    src="{{ $photo->temporaryUrl() }}"
                                    alt="Preview"
                                    class="h-20 w-20 rounded-lg object-cover"
                                >
                                <button
                                    type="button"
                                    wire:click="removePhoto({{ $index }})"
                                    class="absolute -right-2 -top-2 flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-white shadow"
                                >
                                    <i class="fa-solid fa-times text-xs"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if(count($photos) < 3)
                    <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border-2 border-dashed border-slate-300 p-4 transition hover:border-emerald-400 hover:bg-emerald-50">
                        <i class="fa-solid fa-camera text-slate-400"></i>
                        <span class="text-sm text-slate-500">Tambah Foto</span>
                        <input
                            type="file"
                            wire:model="photos"
                            accept="image/*"
                            class="hidden"
                        >
                    </label>
                @endif

                <p class="mt-2 text-xs text-slate-400">Format: JPG, PNG, WebP. Maksimal 2MB per foto.</p>
                @error('photos.*')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Submit --}}
            <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-4">
                <a
                    href="{{ url()->previous() }}"
                    class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50"
                >
                    Batal
                </a>
                <button
                    type="submit"
                    class="rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg transition hover:shadow-xl"
                >
                    <i class="fa-solid fa-paper-plane mr-2"></i>
                    Kirim Ulasan
                </button>
            </div>
        </form>
    @endif
</div>
