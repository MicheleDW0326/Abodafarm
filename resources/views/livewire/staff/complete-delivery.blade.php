<div>
    @if($showModal && $order)
        <template x-teleport="body">
            <div x-show="$wire.showModal" x-transition.opacity.duration="300ms" class="fixed inset-0 z-[99999]">
                <div class="absolute inset-0 bg-black/50" @click="$wire.closeModal()" @keydown.escape.window="$wire.closeModal()"></div>

                <div class="absolute left-1/2 top-1/2 w-full max-w-lg -translate-x-1/2 -translate-y-1/2 rounded-xl bg-white shadow-xl"
                     @click.stop>
                    {{-- Header --}}
                    <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">
                                <i class="fa-solid fa-clipboard-check mr-2 text-emerald-600"></i>
                                Selesaikan Pengiriman
                            </h3>
                            <p class="mt-1 text-sm text-slate-500">
                                Pesanan: <span class="font-medium text-slate-700">{{ $order->order_number }}</span>
                            </p>
                        </div>
                        <button
                            @click="$wire.closeModal()"
                            class="rounded-lg p-2 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600"
                        >
                            <i class="fa-solid fa-times text-lg"></i>
                        </button>
                    </div>

                    {{-- Order Info Preview --}}
                    <div class="border-b border-slate-200 bg-slate-50 px-6 py-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex size-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                                    <i class="fa-solid fa-user"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-slate-900">{{ $order->customer_name }}</p>
                                    <p class="text-sm text-slate-500">{{ $order->customer_phone }}</p>
                                </div>
                            </div>
                            @if($order->estimated_delivery_time)
                                <div class="text-right">
                                    <p class="text-xs text-slate-500">Estimasi</p>
                                    <p class="text-sm font-medium text-slate-700">
                                        {{ \Carbon\Carbon::parse($order->estimated_delivery_time)->format('d M, H:i') }}
                                    </p>
                                </div>
                            @endif
                        </div>
                        @if($order->customer_address)
                            <div class="mt-2 flex items-start gap-2">
                                <i class="fa-solid fa-map-marker-alt mt-1 text-slate-400"></i>
                                <p class="text-sm text-slate-600">{{ $order->customer_address }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Form --}}
                    <form wire:submit="completeDelivery" class="p-6 space-y-5">
                        {{-- Receiver Name (Required) --}}
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">
                                Nama Penerima <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                wire:model="receiverName"
                                placeholder="Contoh: John Doe"
                                maxlength="100"
                                class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm transition-colors focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20
                                       {{ $errors->has('receiverName') ? 'border-red-500' : '' }}"
                            />
                            @error('receiverName')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-slate-500">
                                <i class="fa-solid fa-info-circle mr-1"></i>
                                Nama orang yang menerima paket
                            </p>
                        </div>

                        {{-- Delivery Photo (Required) --}}
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">
                                Foto Bukti Pengiriman <span class="text-red-500">*</span>
                            </label>

                            <div
                                x-data="{
                                    preview: null,
                                    handleFileChange(event) {
                                        const file = event.target.files[0];
                                        if (file) {
                                            this.preview = URL.createObjectURL(file);
                                            @this.set('deliveryPhoto', file);
                                        }
                                    }
                                }"
                                class="relative"
                            >
                                {{-- Preview Area --}}
                                <div
                                    x-show="preview"
                                    class="relative mb-3 overflow-hidden rounded-xl border-2 border-emerald-200 bg-emerald-50"
                                >
                                    <img
                                        x-bind:src="preview"
                                        alt="Preview"
                                        class="max-h-48 w-full object-contain"
                                    />
                                    <button
                                        type="button"
                                        x-on:click="preview = null; $wire.set('deliveryPhoto', null); document.getElementById('photoInput').value = '';"
                                        class="absolute right-2 top-2 rounded-full bg-red-500 p-1.5 text-white shadow-lg transition-colors hover:bg-red-600"
                                    >
                                        <i class="fa-solid fa-times text-xs"></i>
                                    </button>
                                </div>

                                {{-- Upload Button --}}
                                <label
                                    x-show="!preview"
                                    class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 p-6 cursor-pointer transition-colors hover:border-emerald-400 hover:bg-emerald-50"
                                >
                                    <div class="rounded-full bg-slate-100 p-3 mb-3">
                                        <i class="fa-solid fa-camera text-2xl text-slate-400"></i>
                                    </div>
                                    <p class="text-sm font-medium text-slate-700">Klik untuk upload foto</p>
                                    <p class="text-xs text-slate-500 mt-1">JPG, PNG, WEBP (max 3MB)</p>
                                    <input
                                        type="file"
                                        id="photoInput"
                                        accept="image/jpeg,image/png,image/jpg,image/webp"
                                        class="hidden"
                                        x-on:change="handleFileChange($event)"
                                    />
                                </label>
                            </div>

                            @error('deliveryPhoto')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Delivery Notes (Optional) --}}
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">
                                Catatan Pengiriman
                            </label>
                            <textarea
                                wire:model="deliveryNotes"
                                rows="3"
                                placeholder="Contoh: Paket diserahkan ke security. Kondisi kemasan baik."
                                maxlength="500"
                                class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm transition-colors focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 resize-none
                                       {{ $errors->has('deliveryNotes') ? 'border-red-500' : '' }}"
                            ></textarea>
                            @error('deliveryNotes')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                            <div class="mt-1 flex justify-end">
                                <span class="text-xs text-slate-400">
                                    <span x-text="$wire.deliveryNotes?.length || 0">0</span>/500
                                </span>
                            </div>
                        </div>

                        {{-- GPS Location (Optional) --}}
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-sm font-medium text-slate-700">
                                    Lokasi GPS
                                </label>
                                <button
                                    type="button"
                                    wire:click="getCurrentLocation()"
                                    class="inline-flex items-center gap-1 text-xs font-medium text-emerald-600 transition-colors hover:text-emerald-700"
                                >
                                    <i class="fa-solid fa-location-crosshairs"></i>
                                    Dapatkan Lokasi
                                </button>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <input
                                        type="number"
                                        wire:model="latitude"
                                        step="0.00000001"
                                        placeholder="Latitude"
                                        class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm transition-colors focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20
                                               {{ $errors->has('latitude') ? 'border-red-500' : '' }}"
                                    />
                                    @error('latitude')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <input
                                        type="number"
                                        wire:model="longitude"
                                        step="0.00000001"
                                        placeholder="Longitude"
                                        class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm transition-colors focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20
                                               {{ $errors->has('longitude') ? 'border-red-500' : '' }}"
                                    />
                                    @error('longitude')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-slate-500">
                                <i class="fa-solid fa-info-circle mr-1"></i>
                                Koordinat lokasi penerimaan paket (opsional)
                            </p>
                        </div>

                        {{-- Info Box --}}
                        <div class="rounded-lg bg-emerald-50 border border-emerald-100 p-4">
                            <div class="flex gap-3">
                                <div class="flex-shrink-0">
                                    <i class="fa-solid fa-circle-info text-emerald-500"></i>
                                </div>
                                <div class="text-sm text-emerald-700">
                                    <p class="font-medium">Informasi Penting</p>
                                    <ul class="mt-1 space-y-1 text-emerald-600">
                                        <li>• Foto bukti pengiriman wajib diperlukan</li>
                                        <li>• Pastikan foto jelas menunjukkan paket diterima</li>
                                        <li>• Status pesanan akan berubah menjadi "Diterima"</li>
                                        <li>• Pelanggan akan menerima notifikasi</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        {{-- Footer --}}
                        <div class="flex items-center justify-end gap-3 pt-2">
                            <button
                                type="button"
                                wire:click="closeModal()"
                                class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-100"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-medium text-white transition-colors hover:bg-emerald-700 flex items-center gap-2"
                            >
                                <i class="fa-solid fa-check"></i>
                                Selesaikan Pengiriman
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    @endif
</div>
