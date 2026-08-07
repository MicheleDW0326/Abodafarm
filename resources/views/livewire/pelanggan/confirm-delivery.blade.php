<div>
    @if($showModal && $order)
        <template x-teleport="body">
            <div x-show="$wire.showModal" x-transition.opacity.duration="300ms" class="fixed inset-0 z-[99999]">
                <div class="absolute inset-0 bg-black/50" @click="$wire.closeModal()" @keydown.escape.window="$wire.closeModal()"></div>

                <div class="absolute left-1/2 top-1/2 w-full max-w-md -translate-x-1/2 -translate-y-1/2 rounded-2xl bg-white shadow-2xl"
                     @click.stop>
                    {{-- Success Animation Header --}}
                    <div class="relative overflow-hidden rounded-t-2xl bg-gradient-to-r from-emerald-500 to-emerald-600 px-6 py-8 text-center">
                        <div class="absolute inset-0 bg-white/10"></div>
                        <div class="relative">
                            <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-white/20">
                                <i class="fa-solid fa-check-circle text-5xl text-white animate-bounce"></i>
                            </div>
                            <h3 class="text-xl font-bold text-white">Pesanan Diterima!</h3>
                            <p class="mt-1 text-emerald-100">Konfirmasi bahwa pesanan Anda telah sampai</p>
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="p-6">
                        {{-- Order Info --}}
                        <div class="mb-6 rounded-xl bg-slate-50 p-4">
                            <div class="flex items-center gap-3">
                                <div class="flex size-12 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                                    <i class="fa-solid fa-box text-xl"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="font-semibold text-slate-900">{{ $order->order_number }}</p>
                                    <p class="text-sm text-slate-500">{{ $order->total_items }} item</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-bold text-emerald-600">
                                        Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Delivery Info --}}
                        @if($order->delivery_completed_at || $order->receiver_name)
                            <div class="mb-6 rounded-xl bg-amber-50 border border-amber-100 p-4">
                                <h4 class="mb-2 flex items-center gap-2 text-sm font-semibold text-amber-800">
                                    <i class="fa-solid fa-clipboard-check text-amber-600"></i>
                                    Info Pengiriman
                                </h4>
                                <div class="space-y-2 text-sm">
                                    @if($order->receiver_name)
                                        <div class="flex justify-between">
                                            <span class="text-amber-700">Diterima Oleh:</span>
                                            <span class="font-medium text-amber-900">{{ $order->receiver_name }}</span>
                                        </div>
                                    @endif
                                    @if($order->delivery_completed_at)
                                        <div class="flex justify-between">
                                            <span class="text-amber-700">Waktu:</span>
                                            <span class="font-medium text-amber-900">
                                                {{ \Carbon\Carbon::parse($order->delivery_completed_at)->format('d M Y, H:i') }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- Confirmation Message --}}
                        <div class="mb-6 text-center">
                            <p class="text-slate-600">
                                Apakah Anda yakin pesanan <strong class="text-slate-900">{{ $order->order_number }}</strong> telah diterima dengan baik?
                            </p>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex items-center gap-3">
                            <button
                                type="button"
                                wire:click="closeModal()"
                                class="flex-1 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-700 transition-all hover:bg-slate-50"
                            >
                                <i class="fa-solid fa-times mr-2"></i>
                                Batal
                            </button>
                            <button
                                type="button"
                                wire:click="confirmDelivery()"
                                class="flex-1 rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition-all hover:from-emerald-600 hover:to-emerald-700 hover:shadow-xl"
                            >
                                <i class="fa-solid fa-check mr-2"></i>
                                Ya, Konfirmasi
                            </button>
                        </div>

                        {{-- Note --}}
                        <p class="mt-4 text-center text-xs text-slate-400">
                            Dengan mengkonfirmasi, pesanan Anda akan ditandai sebagai selesai.
                        </p>
                    </div>
                </div>
            </div>
        </template>
    @endif
</div>
