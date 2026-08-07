<div class="min-h-screen bg-gray-50 py-8">
    <div class="mx-auto max-w-4xl px-4">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Voucher Saya</h1>
            <p class="mt-1 text-sm text-gray-500">Kelola dan gunakan voucher belanja Anda</p>
        </div>

        <!-- Stats -->
        <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div class="rounded-xl bg-white p-4 shadow-sm border border-gray-100">
                <div class="text-2xl font-bold text-emerald-600">{{ $availableCount }}</div>
                <div class="text-xs text-gray-500">Tersedia</div>
            </div>
            <div class="rounded-xl bg-white p-4 shadow-sm border border-gray-100">
                <div class="text-2xl font-bold text-amber-600">{{ $expiringCount }}</div>
                <div class="text-xs text-gray-500">Segera Berakhir</div>
            </div>
            <div class="rounded-xl bg-white p-4 shadow-sm border border-gray-100">
                <div class="text-2xl font-bold text-blue-600">{{ $usedCount }}</div>
                <div class="text-xs text-gray-500">Terpakai</div>
            </div>
            <div class="rounded-xl bg-white p-4 shadow-sm border border-gray-100">
                <div class="text-2xl font-bold text-gray-400">{{ $expiredCount }}</div>
                <div class="text-xs text-gray-500">Kedaluwarsa</div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="mb-6 flex gap-2 overflow-x-auto">
            <button wire:click="switchTab('available')"
                    class="px-4 py-2 text-sm font-medium rounded-lg transition whitespace-nowrap
                           {{ $activeTab === 'available' ? 'bg-emerald-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200' }}">
                Tersedia
                @if($availableCount > 0)
                    <span class="ml-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs text-emerald-700">{{ $availableCount }}</span>
                @endif
            </button>
            <button wire:click="switchTab('expiring')"
                    class="px-4 py-2 text-sm font-medium rounded-lg transition whitespace-nowrap
                           {{ $activeTab === 'expiring' ? 'bg-amber-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200' }}">
                Segera Berakhir
                @if($expiringCount > 0)
                    <span class="ml-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs text-amber-700">{{ $expiringCount }}</span>
                @endif
            </button>
            <button wire:click="switchTab('used')"
                    class="px-4 py-2 text-sm font-medium rounded-lg transition whitespace-nowrap
                           {{ $activeTab === 'used' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200' }}">
                Terpakai
            </button>
            <button wire:click="switchTab('expired')"
                    class="px-4 py-2 text-sm font-medium rounded-lg transition whitespace-nowrap
                           {{ $activeTab === 'expired' ? 'bg-gray-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200' }}">
                Kedaluwarsa
            </button>
        </div>

        <!-- Voucher List -->
        <div class="space-y-4">
            @forelse($vouchers as $voucher)
                <div wire:click="selectVoucher({{ $voucher->id }})"
                     class="cursor-pointer rounded-xl bg-white p-4 shadow-sm border transition hover:shadow-md
                            {{ $voucher->status === 'active' ? 'border-emerald-200' : 'border-gray-200 opacity-60' }}">
                    <div class="flex items-center gap-4">
                        <!-- Discount Badge -->
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-xl
                                    {{ $voucher->discount_type === 'percentage' ? 'bg-emerald-100' : '' }}
                                    {{ $voucher->discount_type === 'fixed' ? 'bg-blue-100' : '' }}
                                    {{ $voucher->discount_type === 'free_shipping' ? 'bg-purple-100' : '' }}
                                    {{ $voucher->discount_type === 'cashback' ? 'bg-amber-100' : '' }}">
                            <span class="text-lg font-bold text-gray-800">
                                @if($voucher->discount_type === 'percentage')
                                    {{ $voucher->discount_value }}%
                                @elseif($voucher->discount_type === 'free_shipping')
                                    <i class="fa-solid fa-truck text-xl"></i>
                                @elseif($voucher->discount_type === 'cashback')
                                    <i class="fa-solid fa-coins text-xl"></i>
                                @else
                                    Rp
                                @endif
                            </span>
                        </div>

                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <h3 class="font-semibold text-gray-900 truncate">{{ $voucher->name }}</h3>
                                @if($voucher->days_until_expiry !== null && $voucher->days_until_expiry <= 3 && $voucher->days_until_expiry > 0)
                                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700">
                                        {{ $voucher->days_until_expiry }} hari lagi
                                    </span>
                                @endif
                            </div>
                            <p class="mt-0.5 text-sm text-gray-500 truncate">{{ $voucher->description ?? 'Tidak ada deskripsi' }}</p>
                            <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-400">
                                @if($voucher->minimum_order)
                                    <span>Min. Rp {{ number_format($voucher->minimum_order, 0, ',', '.') }}</span>
                                @endif
                                @if($voucher->maximum_discount)
                                    <span>Max. Rp {{ number_format($voucher->maximum_discount, 0, ',', '.') }}</span>
                                @endif
                                <span>Berlaku hingga {{ $voucher->valid_until->format('d M Y') }}</span>
                            </div>
                        </div>

                        <!-- Arrow -->
                        <div class="shrink-0">
                            <i class="fa-solid fa-chevron-right text-gray-400"></i>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-xl bg-white p-8 text-center border border-gray-200">
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100">
                        <i class="fa-solid fa-ticket text-2xl text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Tidak ada voucher</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        @if($activeTab === 'available')
                            Anda belum memiliki voucher yang tersedia.
                        @elseif($activeTab === 'used')
                            Belum ada voucher yang digunakan.
                        @else
                            Tidak ada voucher yang kedaluwarsa.
                        @endif
                    </p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Voucher Detail Modal -->
    @if($selectedVoucher)
        <div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ show: true }" x-show="show" x-on:keydown.escape.window="show = false; $wire.closeDetail()">
            <div class="flex min-h-screen items-center justify-center p-4">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/50 transition-opacity" x-on:click="show = false; $wire.closeDetail()"></div>

                <!-- Modal -->
                <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl"
                     x-show="show"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100">
                    <!-- Close Button -->
                    <button wire:click="closeDetail"
                            class="absolute right-4 top-4 rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>

                    <!-- Voucher Content -->
                    <div class="text-center">
                        <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-2xl
                                    {{ $selectedVoucher->discount_type === 'percentage' ? 'bg-emerald-100' : '' }}
                                    {{ $selectedVoucher->discount_type === 'fixed' ? 'bg-blue-100' : '' }}
                                    {{ $selectedVoucher->discount_type === 'free_shipping' ? 'bg-purple-100' : '' }}
                                    {{ $selectedVoucher->discount_type === 'cashback' ? 'bg-amber-100' : '' }}">
                            <span class="text-2xl font-bold text-gray-800">
                                @if($selectedVoucher->discount_type === 'percentage')
                                    {{ $selectedVoucher->discount_value }}%
                                @elseif($selectedVoucher->discount_type === 'free_shipping')
                                    <i class="fa-solid fa-truck text-3xl"></i>
                                @elseif($selectedVoucher->discount_type === 'cashback')
                                    <i class="fa-solid fa-coins text-3xl"></i>
                                @else
                                    Rp
                                @endif
                            </span>
                        </div>

                        <h2 class="text-xl font-bold text-gray-900">{{ $selectedVoucher->name }}</h2>
                        <p class="mt-1 text-sm text-gray-500">{{ $selectedVoucher->description ?? '' }}</p>

                        <!-- Code -->
                        <div class="mt-4 rounded-lg bg-gray-50 p-3">
                            <div class="text-xs text-gray-400 mb-1">Kode Voucher</div>
                            <div class="font-mono font-bold text-lg text-emerald-600">{{ $selectedVoucher->voucher_code }}</div>
                        </div>

                        <!-- Details -->
                        <div class="mt-4 space-y-2 text-left">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Tipe Diskon</span>
                                <span class="font-medium text-gray-900">{{ $selectedVoucher->formatted_discount }}</span>
                            </div>
                            @if($selectedVoucher->minimum_order)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Minimum Belanja</span>
                                    <span class="font-medium text-gray-900">Rp {{ number_format($selectedVoucher->minimum_order, 0, ',', '.') }}</span>
                                </div>
                            @endif
                            @if($selectedVoucher->maximum_discount)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Maksimum Diskon</span>
                                    <span class="font-medium text-gray-900">Rp {{ number_format($selectedVoucher->maximum_discount, 0, ',', '.') }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Berlaku</span>
                                <span class="font-medium text-gray-900">
                                    {{ $selectedVoucher->valid_from->format('d M') }} - {{ $selectedVoucher->valid_until->format('d M Y') }}
                                </span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Sisa Penggunaan</span>
                                <span class="font-medium text-gray-900">{{ $selectedVoucher->usage_limit - $selectedVoucher->used_count }}x</span>
                            </div>
                        </div>

                        <!-- Apply Button -->
                        @if($selectedVoucher->status === 'active' && $selectedVoucher->canUse())
                            <a href="{{ route('checkout') }}"
                               class="mt-6 block w-full rounded-xl bg-emerald-600 py-3 text-center font-semibold text-white transition hover:bg-emerald-700">
                                Gunakan Sekarang
                            </a>
                        @elseif($selectedVoucher->status === 'used')
                            <div class="mt-6 rounded-xl bg-gray-100 py-3 text-center font-semibold text-gray-500">
                                Sudah Terpakai
                            </div>
                        @else
                            <div class="mt-6 rounded-xl bg-gray-100 py-3 text-center font-semibold text-gray-500">
                                Tidak Berlaku
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
