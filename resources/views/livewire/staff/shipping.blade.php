<div class="space-y-6" x-data="{
    activeTab: 'active',
    showDeliveryModal: false,
    selectedDelivery: null,
    alert: null,

    showAlert(type, message) {
        this.alert = { type, message };
        setTimeout(() => this.alert = null, 4000);
    }
}" @alert.window="showAlert($event.detail.type, $event.detail.message)">

    {{-- Alert --}}
    <div x-show="alert" x-transition
         :class="alert?.type === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : (alert?.type === 'error' ? 'bg-red-50 border-red-200 text-red-800' : 'bg-blue-50 border-blue-200 text-blue-800')"
         class="rounded-xl border p-4">
        <p class="font-medium" x-text="alert?.message"></p>
    </div>

    {{-- Page Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Pengiriman</h1>
            <p class="mt-1 text-sm text-slate-500">Kelola dan lacak pengiriman pesanan</p>
        </div>
        <button @click="activeTab = 'active'" class="inline-flex items-center gap-2 rounded-xl bg-purple-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-purple-700">
            <i class="fa-solid fa-sync"></i>
            Refresh
        </button>
    </div>

    {{-- Stats Cards --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-100 text-purple-600">
                    <i class="fa-solid fa-truck-fast text-xl"></i>
                </div>
                <div>
                    <div class="text-2xl font-bold text-slate-900">{{ $stats['active'] ?? 0 }}</div>
                    <div class="text-xs text-slate-500">Aktif</div>
                </div>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                    <i class="fa-solid fa-user-check text-xl"></i>
                </div>
                <div>
                    <div class="text-2xl font-bold text-slate-900">{{ $stats['assigned'] ?? 0 }}</div>
                    <div class="text-xs text-slate-500">Ditugaskan</div>
                </div>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                    <i class="fa-solid fa-check-circle text-xl"></i>
                </div>
                <div>
                    <div class="text-2xl font-bold text-slate-900">{{ $stats['completed_today'] ?? 0 }}</div>
                    <div class="text-xs text-slate-500">Selesai Hari Ini</div>
                </div>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-100 text-red-600">
                    <i class="fa-solid fa-triangle-exclamation text-xl"></i>
                </div>
                <div>
                    <div class="text-2xl font-bold text-slate-900">{{ $stats['failed_today'] ?? 0 }}</div>
                    <div class="text-xs text-slate-500">Gagal Hari Ini</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex gap-2 border-b border-slate-200">
        <button @click="activeTab = 'active'"
                :class="activeTab === 'active' ? 'border-purple-600 text-purple-600' : 'border-transparent text-slate-500 hover:text-slate-700'"
                class="border-b-2 px-4 py-3 text-sm font-medium transition-colors">
            <i class="fa-solid fa-clock mr-1"></i> Aktif ({{ $activeDeliveries->count() }})
        </button>
        <button @click="activeTab = 'assigned'"
                :class="activeTab === 'assigned' ? 'border-purple-600 text-purple-600' : 'border-transparent text-slate-500 hover:text-slate-700'"
                class="border-b-2 px-4 py-3 text-sm font-medium transition-colors">
            <i class="fa-solid fa-user-check mr-1"></i> Ditugaskan ({{ $assignedDeliveries->count() }})
        </button>
        <button @click="activeTab = 'out'"
                :class="activeTab === 'out' ? 'border-purple-600 text-purple-600' : 'border-transparent text-slate-500 hover:text-slate-700'"
                class="border-b-2 px-4 py-3 text-sm font-medium transition-colors">
            <i class="fa-solid fa-truck-moving mr-1"></i> Dalam Pengiriman ({{ $outForDelivery->count() }})
        </button>
        <button @click="activeTab = 'completed'"
                :class="activeTab === 'completed' ? 'border-purple-600 text-purple-600' : 'border-transparent text-slate-500 hover:text-slate-700'"
                class="border-b-2 px-4 py-3 text-sm font-medium transition-colors">
            <i class="fa-solid fa-check-double mr-1"></i> Selesai ({{ $completedToday->count() }})
        </button>
    </div>

    {{-- Active Deliveries --}}
    <div x-show="activeTab === 'active'" class="space-y-4">
        @forelse($activeDeliveries as $delivery)
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100">
                            <i class="fa-solid fa-truck-fast text-xl text-slate-600"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-semibold text-slate-900">{{ $delivery->delivery_code }}</h3>
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $delivery->status_badge_class }}">
                                    {{ $delivery->status_label }}
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ $delivery->order->customer_name ?? 'Pelanggan' }} •
                                {{ $delivery->order->customer_phone ?? '-' }}
                            </p>
                            <p class="text-xs text-slate-400">
                                {{ $delivery->order->customer_address ?? 'Alamat tidak tersedia' }}
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-slate-900">Rp {{ number_format($delivery->order->total_amount, 0, ',', '.') }}</p>
                        <p class="text-xs text-slate-400">{{ $delivery->order->items->count() }} item</p>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                    @if($delivery->status === 'assigned')
                        <button wire:click="acceptDelivery({{ $delivery->id }})"
                                class="inline-flex items-center gap-2 rounded-xl bg-purple-600 px-4 py-2 text-sm font-semibold text-white hover:bg-purple-700">
                            <i class="fa-solid fa-check"></i> Terima Pesanan
                        </button>
                    @endif
                    @if($delivery->status === 'assigned' || $delivery->status === 'out_for_delivery')
                        <button wire:click="selectDelivery({{ $delivery->id }})"
                                @click="showDeliveryModal = true"
                                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                            <i class="fa-solid fa-eye"></i> Detail
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-12 text-center">
                <i class="fa-solid fa-truck text-5xl text-slate-300 mb-4"></i>
                <p class="text-slate-500">Tidak ada pengiriman aktif</p>
            </div>
        @endforelse
    </div>

    {{-- Assigned Deliveries --}}
    <div x-show="activeTab === 'assigned'" class="space-y-4">
        @forelse($assignedDeliveries as $delivery)
            <div class="rounded-2xl border border-amber-200 bg-amber-50/50 p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-semibold text-slate-900">{{ $delivery->delivery_code }}</h3>
                            <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700">Menunggu Konfirmasi</span>
                        </div>
                        <p class="mt-1 text-sm text-slate-600">
                            {{ $delivery->order->customer_name ?? 'Pelanggan' }}
                        </p>
                    </div>
                    <button wire:click="selectDelivery({{ $delivery->id }})"
                            @click="showDeliveryModal = true"
                            class="rounded-xl bg-purple-600 px-4 py-2 text-sm font-semibold text-white hover:bg-purple-700">
                        <i class="fa-solid fa-check mr-1"></i> Konfirmasi
                    </button>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-12 text-center">
                <p class="text-slate-500">Tidak ada pesanan menunggu konfirmasi</p>
        </div>
        @endforelse
    </div>

    {{-- Out for Delivery --}}
    <div x-show="activeTab === 'out'" class="space-y-4">
        @forelse($outForDelivery as $delivery)
            <div class="rounded-2xl border border-purple-200 bg-purple-50/50 p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-semibold text-slate-900">{{ $delivery->delivery_code }}</h3>
                            <span class="rounded-full bg-purple-100 px-2 py-0.5 text-xs font-medium text-purple-700">
                                <i class="fa-solid fa-truck-moving mr-1"></i> Dalam Pengiriman
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-slate-600">
                            {{ $delivery->order->customer_address ?? 'Alamat tidak tersedia' }}
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <button wire:click="selectDelivery({{ $delivery->id }})"
                                @click="showDeliveryModal = true"
                                class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                            <i class="fa-solid fa-check mr-1"></i> Selesaikan
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-12 text-center">
            <p class="text-slate-500">Tidak ada pesanan dalam pengiriman</p>
        </div>
        @endforelse
    </div>

    {{-- Completed Today --}}
    <div x-show="activeTab === 'completed'" class="space-y-4">
        @forelse($completedToday as $delivery)
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50/50 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-semibold text-slate-900">{{ $delivery->delivery_code }}</h3>
                            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">
                                <i class="fa-solid fa-check-circle mr-1"></i> Selesai
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ $delivery->order->customer_name ?? 'Pelanggan' }} •
                            {{ $delivery->delivered_at ? $delivery->delivered_at->format('H:i') : '-' }}
                        </p>
                    </div>
                    <p class="text-sm font-semibold text-slate-500">
                        {{ $delivery->receiver_name ? 'Penerima: ' . $delivery->receiver_name : '-' }}
                    </p>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-12 text-center">
                <p class="text-slate-500">Belum ada pesanan selesai hari ini</p>
            </div>
        @endforelse
    </div>

    {{-- Delivery Modal --}}
    @if($selectedDelivery)
        <div x-show="showDeliveryModal" x-on:keydown.escape.window="showDeliveryModal = false"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-2xl rounded-2xl bg-white shadow-2xl"
                 @click.stop>
                {{-- Header --}}
                <div class="flex items-center justify-between border-b border-slate-100 p-5">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">{{ $selectedDelivery->delivery_code }}</h3>
                        <p class="text-sm text-slate-500">{{ $selectedDelivery->order->customer_name }}</p>
                    </div>
                    <button @click="showDeliveryModal = false" class="rounded-full p-2 hover:bg-slate-100">
                        <i class="fa-solid fa-times text-slate-400"></i>
                    </button>
                </div>

                {{-- Content --}}
                <div class="max-h-[60vh] overflow-y-auto p-5">
                    {{-- Order Info --}}
                    <div class="mb-6 rounded-xl bg-slate-50 p-4">
                        <h4 class="mb-3 font-semibold text-slate-700">Informasi Pengiriman</h4>
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <p class="text-slate-500">Alamat</p>
                                <p class="font-medium">{{ $selectedDelivery->order->customer_address ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-slate-500">Telepon</p>
                                <p class="font-medium">{{ $selectedDelivery->order->customer_phone ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-slate-500">Total</p>
                                <p class="font-bold text-emerald-600">Rp {{ number_format($selectedDelivery->order->total_amount, 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <p class="text-slate-500">Items</p>
                                <p class="font-medium">{{ $selectedDelivery->order->items->count() }} item</p>
                            </div>
                        </div>
                    </div>

                    {{-- Timeline --}}
                    <div class="mb-6">
                        <h4 class="mb-3 font-semibold text-slate-700">Timeline</h4>
                        <div class="space-y-3">
                            @foreach($selectedDelivery->timeline as $entry)
                                <div class="flex items-start gap-3">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100">
                                        <i class="fa-solid {{ $entry->status_icon }} text-sm text-slate-500"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-slate-900">{{ $entry->status_label }}</p>
                                        @if($entry->notes)
                                            <p class="text-sm text-slate-500">{{ $entry->notes }}</p>
                                        @endif
                                        <p class="text-xs text-slate-400">{{ $entry->formatted_timestamp }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="space-y-4">
                        @if($selectedDelivery->status === 'assigned')
                            <button wire:click="acceptDelivery"
                                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-purple-600 py-3 font-semibold text-white hover:bg-purple-700">
                                <i class="fa-solid fa-check"></i> Terima Pesanan
                            </button>
                        @endif

                        @if($selectedDelivery->status === 'assigned' || $selectedDelivery->status === 'out_for_delivery')
                            <button wire:click="startDelivery"
                                    class="flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 py-3 font-semibold text-slate-700 hover:bg-slate-50">
                                <i class="fa-solid fa-truck-moving"></i> Mulai Pengiriman
                            </button>
                        @endif

                        @if($selectedDelivery->status === 'out_for_delivery')
                            <div class="rounded-xl border border-slate-200 p-4">
                                <h4 class="mb-3 font-semibold text-slate-700">Selesaikan Pengiriman</h4>
                                <form wire:submit.prevent="completeDelivery">
                                    <div class="space-y-3">
                                        <div>
                                            <label class="mb-1 block text-sm font-medium text-slate-600">Nama Penerima</label>
                                            <input type="text" wire:model="receiverName" required
                                                   class="w-full rounded-xl border border-slate-200 px-4 py-2 focus:border-purple-500 focus:ring-purple-500">
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-sm font-medium text-slate-600">Catatan</label>
                                            <textarea wire:model="deliveryNotes" rows="2"
                                                      class="w-full rounded-xl border border-slate-200 px-4 py-2 focus:border-purple-500 focus:ring-purple-500"></textarea>
                                        </div>
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="mb-1 block text-sm font-medium text-slate-600">Foto Bukti</label>
                                                <input type="file" wire:model="proofPhoto" accept="image/*"
                                                       class="w-full text-sm">
                                            </div>
                                            <div>
                                                <label class="mb-1 block text-sm font-medium text-slate-600">Tanda Tangan</label>
                                                <input type="file" wire:model="customerSignature" accept="image/*"
                                                       class="w-full text-sm">
                                            </div>
                                        </div>
                                        <button type="button" wire:click="updateLocation"
                                                class="text-sm text-purple-600 hover:text-purple-700">
                                            <i class="fa-solid fa-location-dot mr-1"></i> Update Lokasi GPS
                                        </button>
                                        <button type="submit"
                                                class="flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 py-3 font-semibold text-white hover:bg-emerald-700">
                                            <i class="fa-solid fa-check-circle"></i> Konfirmasi Selesai
                                        </button>
                                    </div>
                                </form>

                                <div class="mt-4 border-t border-slate-200 pt-4">
                                    <button wire:click="markDeliveryFailed"
                                            class="flex w-full items-center justify-center gap-2 rounded-xl border border-red-200 py-3 font-semibold text-red-600 hover:bg-red-50">
                                        <i class="fa-solid fa-triangle-exclamation"></i> Tandai Gagal
                                    </button>
                                    @if(empty($failureReason))
                                        <div class="mt-3">
                                            <input type="text" wire:model="failureReason" placeholder="Alasan kegagalan..."
                                                   class="w-full rounded-xl border border-slate-200 px-4 py-2 text-sm">
                                            <button wire:click="markDeliveryFailed"
                                                    @if(empty($failureReason)) disabled @endif
                                                    class="mt-2 w-full rounded-xl bg-red-600 py-2 text-sm font-semibold text-white disabled:opacity-50">
                                                Konfirmasi Gagal
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
