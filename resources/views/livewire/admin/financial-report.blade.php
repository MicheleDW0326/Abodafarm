<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Laporan</h2>
            <p class="mt-1 text-sm text-slate-500">Analisis bisnis dan performa penjualan</p>
        </div>

        {{-- Period Filter --}}
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex rounded-lg bg-slate-100 p-1">
                <button wire:click="$set('period', 'day')" class="rounded-md px-3 py-1.5 text-xs font-medium transition-all {{ $period === 'day' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-600' }}">
                    Harian
                </button>
                <button wire:click="$set('period', 'week')" class="rounded-md px-3 py-1.5 text-xs font-medium transition-all {{ $period === 'week' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-600' }}">
                    Mingguan
                </button>
                <button wire:click="$set('period', 'month')" class="rounded-md px-3 py-1.5 text-xs font-medium transition-all {{ $period === 'month' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-600' }}">
                    Bulanan
                </button>
                <button wire:click="$set('period', 'year')" class="rounded-md px-3 py-1.5 text-xs font-medium transition-all {{ $period === 'year' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-600' }}">
                    Tahunan
                </button>
            </div>

            <div class="flex items-center gap-2">
                <input type="date" wire:model.live="dateFrom" class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm" />
                <span class="text-slate-400">-</span>
                <input type="date" wire:model.live="dateTo" class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm" />
            </div>
        </div>
    </div>

    {{-- Tab Navigation --}}
    <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-4">
        <button
            wire:click="setTab('overview')"
            class="rounded-lg px-4 py-2 text-sm font-medium transition-colors {{ $activeTab === 'overview' ? 'bg-emerald-100 text-emerald-700' : 'text-slate-600 hover:bg-slate-100' }}"
        >
            <i class="fa-solid fa-chart-pie mr-2"></i>Overview
        </button>
        <button
            wire:click="setTab('orders')"
            class="rounded-lg px-4 py-2 text-sm font-medium transition-colors {{ $activeTab === 'orders' ? 'bg-emerald-100 text-emerald-700' : 'text-slate-600 hover:bg-slate-100' }}"
        >
            <i class="fa-solid fa-receipt mr-2"></i>Pesanan
        </button>
        <button
            wire:click="setTab('bookings')"
            class="rounded-lg px-4 py-2 text-sm font-medium transition-colors {{ $activeTab === 'bookings' ? 'bg-amber-100 text-amber-700' : 'text-slate-600 hover:bg-slate-100' }}"
        >
            <i class="fa-solid fa-cow mr-2"></i>Booking
        </button>
        <button
            wire:click="setTab('payments')"
            class="rounded-lg px-4 py-2 text-sm font-medium transition-colors {{ $activeTab === 'payments' ? 'bg-emerald-100 text-emerald-700' : 'text-slate-600 hover:bg-slate-100' }}"
        >
            <i class="fa-solid fa-credit-card mr-2"></i>Pembayaran
        </button>
        <button
            wire:click="setTab('revenue')"
            class="rounded-lg px-4 py-2 text-sm font-medium transition-colors {{ $activeTab === 'revenue' ? 'bg-emerald-100 text-emerald-700' : 'text-slate-600 hover:bg-slate-100' }}"
        >
            <i class="fa-solid fa-money-bill mr-2"></i>Pendapatan
        </button>
        <button
            wire:click="setTab('operational')"
            class="rounded-lg px-4 py-2 text-sm font-medium transition-colors {{ $activeTab === 'operational' ? 'bg-emerald-100 text-emerald-700' : 'text-slate-600 hover:bg-slate-100' }}"
        >
            <i class="fa-solid fa-gears mr-2"></i>Operasional
        </button>
    </div>

    {{-- ============================================================ --}}
    {{-- OVERVIEW TAB --}}
    {{-- ============================================================ --}}
    @if($activeTab === 'overview')
        {{-- Summary Cards --}}
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <div class="flex items-center justify-between">
                    <div class="rounded-lg bg-emerald-100 p-3">
                        <i class="fa-solid fa-money-bill-wave text-xl text-emerald-600"></i>
                    </div>
                    <span class="text-xs font-medium {{ $dailyComparison['revenue_change'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $dailyComparison['revenue_change'] >= 0 ? '+' : '' }}{{ number_format($dailyComparison['revenue_change'], 1) }}%
                    </span>
                </div>
                <p class="mt-4 text-2xl font-bold text-slate-900">Rp {{ number_format($overviewStats['total_revenue'], 0, ',', '.') }}</p>
                <p class="text-sm text-slate-500">Total Pendapatan</p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <div class="flex items-center justify-between">
                    <div class="rounded-lg bg-blue-100 p-3">
                        <i class="fa-solid fa-receipt text-xl text-blue-600"></i>
                    </div>
                    <span class="text-xs font-medium {{ $dailyComparison['orders_change'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $dailyComparison['orders_change'] >= 0 ? '+' : '' }}{{ number_format($dailyComparison['orders_change'], 1) }}%
                    </span>
                </div>
                <p class="mt-4 text-2xl font-bold text-slate-900">{{ number_format($overviewStats['total_orders'], 0, ',', '.') }}</p>
                <p class="text-sm text-slate-500">Total Pesanan</p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <div class="flex items-center justify-between">
                    <div class="rounded-lg bg-purple-100 p-3">
                        <i class="fa-solid fa-shopping-cart text-xl text-purple-600"></i>
                    </div>
                </div>
                <p class="mt-4 text-2xl font-bold text-slate-900">Rp {{ number_format($overviewStats['cart_revenue'], 0, ',', '.') }}</p>
                <p class="text-sm text-slate-500">Pembelian</p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <div class="flex items-center justify-between">
                    <div class="rounded-lg bg-amber-100 p-3">
                        <i class="fa-solid fa-cow text-xl text-amber-600"></i>
                    </div>
                </div>
                <p class="mt-4 text-2xl font-bold text-slate-900">Rp {{ number_format($overviewStats['inquiry_revenue'], 0, ',', '.') }}</p>
                <p class="text-sm text-slate-500">Booking Ternak</p>
            </div>
        </div>

        {{-- Inventory Overview --}}
        <div class="rounded-xl border border-slate-200 bg-white p-6">
            <h3 class="text-base font-semibold text-slate-900">Overview Inventaris</h3>
            <div class="mt-6 grid gap-4 md:grid-cols-4">
                <div class="rounded-lg border border-slate-200 bg-green-50 p-4 text-center">
                    <i class="fa-solid fa-cow text-2xl text-green-600"></i>
                    <p class="mt-2 text-2xl font-bold text-green-700">{{ $inventoryStats['live'] }}</p>
                    <p class="text-sm text-green-600">Ternak Hidup</p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-rose-50 p-4 text-center">
                    <i class="fa-solid fa-drumstick-bite text-2xl text-rose-600"></i>
                    <p class="mt-2 text-2xl font-bold text-rose-700">{{ $inventoryStats['fresh'] }}</p>
                    <p class="text-sm text-rose-600">Daging Segar</p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-emerald-50 p-4 text-center">
                    <i class="fa-solid fa-check-circle text-2xl text-emerald-600"></i>
                    <p class="mt-2 text-2xl font-bold text-emerald-700">{{ $inventoryStats['available'] }}</p>
                    <p class="text-sm text-emerald-600">Tersedia</p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-amber-50 p-4 text-center">
                    <i class="fa-solid fa-exclamation-triangle text-2xl text-amber-600"></i>
                    <p class="mt-2 text-2xl font-bold text-amber-700">{{ $inventoryStats['low_stock'] }}</p>
                    <p class="text-sm text-amber-600">Stok Rendah</p>
                </div>
            </div>
        </div>
    @endif

    {{-- ============================================================ --}}
    {{-- ORDERS TAB --}}
    {{-- ============================================================ --}}
    @if($activeTab === 'orders')
        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Orders by Payment Status --}}
            <div class="rounded-xl border border-slate-200 bg-white p-6">
                <h3 class="text-base font-semibold text-slate-900">Status Pembayaran</h3>
                <p class="mt-1 text-sm text-slate-500">Distribusi pesanan berdasarkan status pembayaran</p>

                <div class="mt-6 space-y-4">
                    @forelse($ordersReport['by_payment_status'] as $item)
                        @php
                            $totalCount = collect($ordersReport['by_payment_status'])->sum('count');
                            $percent = $totalCount > 0 ? ($item['count'] / $totalCount) * 100 : 0;
                        @endphp
                        <div>
                            <div class="mb-1 flex items-center justify-between text-sm">
                                <div class="flex items-center gap-2">
                                    <span class="size-2.5 rounded-full bg-{{ $item['color'] }}-500"></span>
                                    <span class="text-slate-700">{{ $item['label'] }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-slate-900">{{ $item['count'] }} pesanan</span>
                                    <span class="w-10 text-right text-xs text-slate-400">{{ number_format($percent, 0) }}%</span>
                                </div>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full bg-{{ $item['color'] }}-500" style="width: {{ $percent }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="py-4 text-center text-sm text-slate-400">Tidak ada data</p>
                    @endforelse
                </div>
            </div>

            {{-- Orders by Shipment Status --}}
            <div class="rounded-xl border border-slate-200 bg-white p-6">
                <h3 class="text-base font-semibold text-slate-900">Status Pengiriman</h3>
                <p class="mt-1 text-sm text-slate-500">Distribusi pesanan berdasarkan status pengiriman</p>

                <div class="mt-6 space-y-4">
                    @forelse($ordersReport['by_shipment_status'] as $item)
                        @php
                            $totalCount = collect($ordersReport['by_shipment_status'])->sum('count');
                            $percent = $totalCount > 0 ? ($item['count'] / $totalCount) * 100 : 0;
                        @endphp
                        <div>
                            <div class="mb-1 flex items-center justify-between text-sm">
                                <div class="flex items-center gap-2">
                                    <span class="size-2.5 rounded-full bg-{{ $item['color'] }}-500"></span>
                                    <span class="text-slate-700">{{ $item['label'] }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-slate-900">{{ $item['count'] }} pesanan</span>
                                    <span class="w-10 text-right text-xs text-slate-400">{{ number_format($percent, 0) }}%</span>
                                </div>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full bg-{{ $item['color'] }}-500" style="width: {{ $percent }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="py-4 text-center text-sm text-slate-400">Tidak ada data</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    {{-- ============================================================ --}}
    {{-- BOOKINGS TAB --}}
    {{-- ============================================================ --}}
    @if($activeTab === 'bookings')
        {{-- Booking Stats --}}
        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <div class="flex items-center gap-3">
                    <div class="rounded-lg bg-amber-100 p-3">
                        <i class="fa-solid fa-calendar-check text-xl text-amber-600"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-slate-900">{{ $bookingsReport['total_bookings'] }}</p>
                        <p class="text-sm text-slate-500">Total Booking</p>
                    </div>
                </div>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <div class="flex items-center gap-3">
                    <div class="rounded-lg bg-blue-100 p-3">
                        <i class="fa-solid fa-clipboard-list text-xl text-blue-600"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-slate-900">{{ $bookingsReport['survey_scheduled'] }}</p>
                        <p class="text-sm text-slate-500">Survei Dijadwalkan</p>
                    </div>
                </div>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <div class="flex items-center gap-3">
                    <div class="rounded-lg bg-violet-100 p-3">
                        <i class="fa-solid fa-truck-fast text-xl text-violet-600"></i>
                    </div>
                    <div>
                        @php
                            $readyForDelivery = collect($bookingsReport['by_status'])->where('status', 'ready_for_delivery')->first()['count'] ?? 0;
                        @endphp
                        <p class="text-2xl font-bold text-slate-900">{{ $readyForDelivery }}</p>
                        <p class="text-sm text-slate-500">Siap Dikirim</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bookings by Status --}}
        <div class="rounded-xl border border-slate-200 bg-white p-6">
            <h3 class="text-base font-semibold text-slate-900">Booking berdasarkan Status</h3>
            <p class="mt-1 text-sm text-slate-500">Distribusi booking berdasarkan status saat ini</p>

            <div class="mt-6 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                @forelse($bookingsReport['by_status'] as $item)
                    <div class="rounded-lg border border-slate-200 bg-{{ $item['color'] }}-50 p-4">
                        <div class="flex items-center justify-between">
                            <span class="size-3 rounded-full bg-{{ $item['color'] }}-500"></span>
                            <span class="text-2xl font-bold text-slate-900">{{ $item['count'] }}</span>
                        </div>
                        <p class="mt-2 text-sm font-medium text-slate-700">{{ $item['label'] }}</p>
                        <p class="text-xs text-slate-500">Rp {{ number_format($item['revenue'], 0, ',', '.') }}</p>
                    </div>
                @empty
                    <p class="col-span-4 py-4 text-center text-sm text-slate-400">Tidak ada data booking</p>
                @endforelse
            </div>
        </div>
    @endif

    {{-- ============================================================ --}}
    {{-- PAYMENTS TAB --}}
    {{-- ============================================================ --}}
    @if($activeTab === 'payments')
        {{-- Payment Summary --}}
        <div class="grid gap-4 md:grid-cols-4">
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <div class="rounded-lg bg-emerald-100 p-3 w-fit">
                    <i class="fa-solid fa-check-circle text-xl text-emerald-600"></i>
                </div>
                <p class="mt-4 text-2xl font-bold text-slate-900">{{ $paymentsReport['successful_payments'] }}</p>
                <p class="text-sm text-slate-500">Pembayaran Berhasil</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <div class="rounded-lg bg-red-100 p-3 w-fit">
                    <i class="fa-solid fa-times-circle text-xl text-red-600"></i>
                </div>
                <p class="mt-4 text-2xl font-bold text-slate-900">{{ $paymentsReport['failed_payments'] }}</p>
                <p class="text-sm text-slate-500">Pembayaran Gagal</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <div class="rounded-lg bg-blue-100 p-3 w-fit">
                    <i class="fa-solid fa-money-bill text-xl text-blue-600"></i>
                </div>
                <p class="mt-4 text-2xl font-bold text-slate-900">Rp {{ number_format($paymentsReport['total_collected'], 0, ',', '.') }}</p>
                <p class="text-sm text-slate-500">Total Terkumpul</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <div class="rounded-lg bg-slate-100 p-3 w-fit">
                    <i class="fa-solid fa-receipt text-xl text-slate-600"></i>
                </div>
                <p class="mt-4 text-2xl font-bold text-slate-900">{{ $paymentsReport['total_transactions'] }}</p>
                <p class="text-sm text-slate-500">Total Transaksi</p>
            </div>
        </div>

        {{-- Payment Methods --}}
        <div class="rounded-xl border border-slate-200 bg-white p-6">
            <h3 class="text-base font-semibold text-slate-900">Metode Pembayaran</h3>
            <p class="mt-1 text-sm text-slate-500">Distribusi transaksi berdasarkan metode pembayaran</p>

            <div class="mt-6 space-y-4">
                @forelse($paymentsReport['payment_methods'] as $method)
                    @php
                        $totalAmount = collect($paymentsReport['payment_methods'])->sum('amount');
                        $percent = $totalAmount > 0 ? ($method['amount'] / $totalAmount) * 100 : 0;
                        $icon = match($method['type']) {
                            'bank_transfer' => 'fa-university',
                            'ewallet' => 'fa-wallet',
                            'credit_card' => 'fa-credit-card',
                            'qris' => 'fa-qrcode',
                            default => 'fa-receipt',
                        };
                    @endphp
                    <div>
                        <div class="mb-1 flex items-center justify-between text-sm">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid {{ $icon }} text-slate-400"></i>
                                <span class="text-slate-700">{{ ucwords(str_replace('_', ' ', $method['type'])) }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-slate-900">{{ $method['count'] }} transaksi</span>
                                <span class="w-10 text-right text-xs text-slate-400">{{ number_format($percent, 0) }}%</span>
                            </div>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full bg-emerald-500" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="py-4 text-center text-sm text-slate-400">Tidak ada data metode pembayaran</p>
                @endforelse
            </div>
        </div>
    @endif

    {{-- ============================================================ --}}
    {{-- REVENUE TAB --}}
    {{-- ============================================================ --}}
    @if($activeTab === 'revenue')
        {{-- Revenue by Status --}}
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-xl border border-slate-200 bg-white p-6">
                <h3 class="text-base font-semibold text-slate-900">Pendapatan per Status</h3>
                <p class="mt-1 text-sm text-slate-500">Distribusi pendapatan berdasarkan status pesanan</p>

                <div class="mt-6 space-y-4">
                    @php
                        $totalRevenue = collect($revenueReport['by_status'])->sum('revenue');
                    @endphp
                    @forelse($revenueReport['by_status'] as $item)
                        @php
                            $percent = $totalRevenue > 0 ? ($item['revenue'] / $totalRevenue) * 100 : 0;
                        @endphp
                        <div>
                            <div class="mb-1 flex items-center justify-between text-sm">
                                <div class="flex items-center gap-2">
                                    <span class="size-2.5 rounded-full bg-{{ $item['color'] }}-500"></span>
                                    <span class="text-slate-700">{{ $item['label'] }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-slate-900">Rp {{ number_format($item['revenue'], 0, ',', '.') }}</span>
                                    <span class="w-10 text-right text-xs text-slate-400">{{ number_format($percent, 0) }}%</span>
                                </div>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full bg-{{ $item['color'] }}-500" style="width: {{ $percent }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="py-4 text-center text-sm text-slate-400">Tidak ada data</p>
                    @endforelse
                </div>
            </div>

            {{-- Daily Comparison --}}
            <div class="rounded-xl border border-slate-200 bg-white p-6">
                <h3 class="text-base font-semibold text-slate-900">Perbandingan Harian</h3>
                <p class="mt-1 text-sm text-slate-500">Hari ini vs kemarin</p>

                <div class="mt-6 grid grid-cols-2 gap-4">
                    <div class="rounded-lg bg-slate-50 p-4">
                        <p class="text-xs text-slate-500">Hari Ini</p>
                        <p class="mt-1 text-xl font-bold text-slate-900">Rp {{ number_format($dailyComparison['today_revenue'], 0, ',', '.') }}</p>
                        <p class="text-xs text-slate-500">{{ $dailyComparison['today_orders'] }} pesanan</p>
                    </div>
                    <div class="rounded-lg bg-slate-50 p-4">
                        <p class="text-xs text-slate-500">Kemarin</p>
                        <p class="mt-1 text-xl font-bold text-slate-900">Rp {{ number_format($dailyComparison['yesterday_revenue'], 0, ',', '.') }}</p>
                        <p class="text-xs text-slate-500">{{ $dailyComparison['yesterday_orders'] }} pesanan</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Top Products --}}
        <div class="rounded-xl border border-slate-200 bg-white overflow-hidden">
            <div class="border-b border-slate-200 px-6 py-4">
                <h3 class="text-base font-semibold text-slate-900">Produk Terlaris</h3>
                <p class="mt-1 text-sm text-slate-500">Berdasarkan total penjualan dalam periode</p>
            </div>

            <table class="w-full text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left font-semibold text-slate-900">#</th>
                        <th class="px-6 py-3 text-left font-semibold text-slate-900">Produk</th>
                        <th class="px-6 py-3 text-right font-semibold text-slate-900">Terjual</th>
                        <th class="px-6 py-3 text-right font-semibold text-slate-900">Total Penjualan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($revenueReport['top_products'] as $index => $product)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-3 text-slate-400">{{ $index + 1 }}</td>
                            <td class="px-6 py-3 font-medium text-slate-900">{{ $product->product_name }}</td>
                            <td class="px-6 py-3 text-right text-slate-600">{{ number_format($product->total_qty, 0, ',', '.') }} unit</td>
                            <td class="px-6 py-3 text-right font-semibold text-emerald-600">Rp {{ number_format($product->total_sales, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <i class="fa-solid fa-chart-bar text-4xl text-slate-300 mb-3"></i>
                                <p class="text-slate-500">Belum ada data penjualan</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    {{-- ============================================================ --}}
    {{-- OPERATIONAL TAB --}}
    {{-- ============================================================ --}}
    @if($activeTab === 'operational')
        {{-- Status Distribution --}}
        <div class="rounded-xl border border-slate-200 bg-white p-6">
            <h3 class="text-base font-semibold text-slate-900">Distribusi Status Pesanan</h3>
            <p class="mt-1 text-sm text-slate-500">Jumlah pesanan berdasarkan status saat ini</p>

            <div class="mt-6 grid gap-4 md:grid-cols-3 lg:grid-cols-5">
                @php
                    $statusColors = [
                        'pending' => 'amber',
                        'paid' => 'green',
                        'processing' => 'blue',
                        'shipped' => 'purple',
                        'delivered' => 'emerald',
                        'completed' => 'emerald',
                        'cancelled' => 'slate',
                        'approved' => 'green',
                        'rejected' => 'red',
                        'survey_scheduled' => 'blue',
                        'ready_for_delivery' => 'violet',
                    ];
                @endphp
                @forelse($operationalReport['status_distribution'] as $status => $count)
                    <div class="rounded-lg border border-slate-200 bg-{{ $statusColors[$status] ?? 'slate' }}-50 p-4 text-center">
                        <p class="text-2xl font-bold text-{{ $statusColors[$status] ?? 'slate' }}-700">{{ $count }}</p>
                        <p class="mt-1 text-xs font-medium text-{{ $statusColors[$status] ?? 'slate' }}-600">
                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                        </p>
                    </div>
                @empty
                    <p class="col-span-5 py-4 text-center text-sm text-slate-400">Tidak ada data</p>
                @endforelse
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Staff Activity --}}
            <div class="rounded-xl border border-slate-200 bg-white p-6">
                <h3 class="text-base font-semibold text-slate-900">Aktivitas Staff</h3>
                <p class="mt-1 text-sm text-slate-500">Aksi staff dalam periode ini</p>

                <div class="mt-6 space-y-3">
                    @forelse($operationalReport['staff_activity'] as $activity)
                        <div class="flex items-center justify-between rounded-lg bg-slate-50 p-3">
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                                    <i class="fa-solid fa-user text-sm"></i>
                                </div>
                                <span class="text-sm font-medium text-slate-700">{{ $activity->name }}</span>
                            </div>
                            <span class="text-sm font-bold text-slate-900">{{ $activity->action_count }} aksi</span>
                        </div>
                    @empty
                        <p class="py-4 text-center text-sm text-slate-400">Tidak ada aktivitas staff</p>
                    @endforelse
                </div>
            </div>

            {{-- Farmer Activity --}}
            <div class="rounded-xl border border-slate-200 bg-white p-6">
                <h3 class="text-base font-semibold text-slate-900">Aktivitas Peternak</h3>
                <p class="mt-1 text-sm text-slate-500">Aksi peternak dalam periode ini</p>

                <div class="mt-6 space-y-3">
                    @forelse($operationalReport['farmer_activity'] as $activity)
                        <div class="flex items-center justify-between rounded-lg bg-slate-50 p-3">
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                                    <i class="fa-solid fa-cow text-sm"></i>
                                </div>
                                <span class="text-sm font-medium text-slate-700">{{ $activity->name }}</span>
                            </div>
                            <span class="text-sm font-bold text-slate-900">{{ $activity->action_count }} aksi</span>
                        </div>
                    @empty
                        <p class="py-4 text-center text-sm text-slate-400">Tidak ada aktivitas peternak</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Total Logs --}}
        <div class="rounded-xl border border-slate-200 bg-white p-6 text-center">
            <p class="text-4xl font-bold text-slate-900">{{ $operationalReport['total_logs'] }}</p>
            <p class="mt-2 text-sm text-slate-500">Total perubahan status dalam periode ini</p>
        </div>
    @endif
</div>
