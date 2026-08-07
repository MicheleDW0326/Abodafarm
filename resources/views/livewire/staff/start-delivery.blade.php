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
                                <i class="fa-solid fa-truck-fast mr-2 text-violet-600"></i>
                                Mulai Pengiriman
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

                    {{-- Customer Info Preview --}}
                    <div class="border-b border-slate-200 bg-slate-50 px-6 py-3">
                        <div class="flex items-center gap-3">
                            <div class="flex size-10 items-center justify-center rounded-full bg-violet-100 text-violet-600">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <div>
                                <p class="font-medium text-slate-900">{{ $order->customer_name }}</p>
                                <p class="text-sm text-slate-500">{{ $order->customer_phone }}</p>
                            </div>
                        </div>
                        @if($order->customer_address)
                            <div class="mt-2 flex items-start gap-2">
                                <i class="fa-solid fa-map-marker-alt mt-1 text-slate-400"></i>
                                <p class="text-sm text-slate-600">{{ $order->customer_address }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Form --}}
                    <form wire:submit="startDelivery" class="p-6 space-y-5">
                        {{-- Estimated Delivery Time (Required) --}}
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">
                                Estimasi Waktu Sampai <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="datetime-local"
                                wire:model="estimatedDeliveryTime"
                                min="{{ now()->addMinutes(30)->format('Y-m-d\TH:i') }}"
                                class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm transition-colors focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20
                                       {{ $errors->has('estimatedDeliveryTime') ? 'border-red-500' : '' }}"
                            />
                            @error('estimatedDeliveryTime')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-slate-500">
                                <i class="fa-solid fa-info-circle mr-1"></i>
                                Tanggal dan waktu estimasi kedatangan
                            </p>
                        </div>

                        {{-- Courier Name (Optional) --}}
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">
                                Nama Kurir
                            </label>
                            <input
                                type="text"
                                wire:model="courierName"
                                placeholder="Contoh: Budi Santoso"
                                maxlength="100"
                                class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm transition-colors focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20
                                       {{ $errors->has('courierName') ? 'border-red-500' : '' }}"
                            />
                            @error('courierName')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Vehicle Info (Optional) --}}
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">
                                Informasi Kendaraan
                            </label>
                            <input
                                type="text"
                                wire:model="vehicleInfo"
                                placeholder="Contoh: Motor ABC 1234 XY, Warna Merah"
                                maxlength="255"
                                class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm transition-colors focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20
                                       {{ $errors->has('vehicleInfo') ? 'border-red-500' : '' }}"
                            />
                            @error('vehicleInfo')
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
                                placeholder="Contoh: Hubungi pelanggan sebelum tiba. Patokan: depan swalayan."
                                maxlength="500"
                                class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm transition-colors focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20 resize-none
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

                        {{-- Info Box --}}
                        <div class="rounded-lg bg-violet-50 border border-violet-100 p-4">
                            <div class="flex gap-3">
                                <div class="flex-shrink-0">
                                    <i class="fa-solid fa-circle-info text-violet-500"></i>
                                </div>
                                <div class="text-sm text-violet-700">
                                    <p class="font-medium">Informasi Penting</p>
                                    <ul class="mt-1 space-y-1 text-violet-600">
                                        <li>• Pastikan estimasi waktu pengiriman realistis</li>
                                        <li>• Catatan akan terlihat oleh pelanggan</li>
                                        <li>• Status pesanan akan berubah menjadi "Dikirim"</li>
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
                                class="rounded-lg bg-violet-600 px-5 py-2.5 text-sm font-medium text-white transition-colors hover:bg-violet-700 flex items-center gap-2"
                            >
                                <i class="fa-solid fa-truck"></i>
                                Mulai Kirim
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    @endif
</div>
