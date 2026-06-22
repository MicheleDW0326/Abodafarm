<div class="space-y-6">
    {{-- Welcome Header --}}
    <div class="rounded-2xl border border-slate-100 bg-gradient-to-r from-emerald-600 to-emerald-700 p-6 shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white">Selamat Datang, {{ auth()->user()->name }}!</h2>
                <p class="mt-1 text-emerald-100">Berikut ringkasan aktivitas peternakan Anda hari ini.</p>
            </div>
            <div class="hidden md:block">
                <span class="grid size-16 place-content-center rounded-full bg-white/20 text-2xl font-bold text-white">
                    {{ auth()->user()->initials() }}
                </span>
            </div>
        </div>
    </div>

    {{-- Main Stats Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Total Ternak --}}
        <article class="rounded-2xl border border-slate-100 bg-white p-5 shadow-xs transition hover:shadow-md">
            <div class="mb-4 flex items-start justify-between">
                <div class="rounded-lg bg-emerald-50 p-2.5 text-emerald-600">
                    <i class="fa-solid fa-cow text-lg"></i>
                </div>
                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-600">
                    Aktif
                </span>
            </div>
            <p class="text-sm text-slate-500">Total Ternak</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($stats['totalLivestock'], 0, ',', '.') }}</p>
            <p class="mt-2 text-xs text-slate-400">{{ $stats['activeLivestock'] }} sedang dijual</p>
        </article>

        {{-- Ternak Terjual --}}
        <article class="rounded-2xl border border-slate-100 bg-white p-5 shadow-xs transition hover:shadow-md">
            <div class="mb-4 flex items-start justify-between">
                <div class="rounded-lg bg-amber-50 p-2.5 text-amber-600">
                    <i class="fa-solid fa-hand-holding-dollar text-lg"></i>
                </div>
            </div>
            <p class="text-sm text-slate-500">Ternak Terjual</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($stats['soldLivestock'], 0, ',', '.') }}</p>
            <p class="mt-2 text-xs text-slate-400">Total {{ $stats['soldLivestock'] }} ekor</p>
        </article>

        {{-- Pesanan Aktif --}}
        <article class="rounded-2xl border border-slate-100 bg-white p-5 shadow-xs transition hover:shadow-md">
            <div class="mb-4 flex items-start justify-between">
                <div class="rounded-lg bg-blue-50 p-2.5 text-blue-600">
                    <i class="fa-solid fa-shopping-cart text-lg"></i>
                </div>
                <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-600">
                    {{ $stats['activeOrders'] }} Pesanan
                </span>
            </div>
            <p class="text-sm text-slate-500">Pesanan Aktif</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($stats['activeOrders'], 0, ',', '.') }}</p>
            <p class="mt-2 text-xs text-slate-400">Menunggu diproses</p>
        </article>

        {{-- Total Pendapatan --}}
        <article class="rounded-2xl border border-slate-100 bg-white p-5 shadow-xs transition hover:shadow-md">
            <div class="mb-4 flex items-start justify-between">
                <div class="rounded-lg bg-purple-50 p-2.5 text-purple-600">
                    <i class="fa-solid fa-wallet text-lg"></i>
                </div>
            </div>
            <p class="text-sm text-slate-500">Total Pendapatan</p>
            <p class="mt-1 text-xl font-bold text-slate-900">{{ $formatRupiah($stats['totalRevenue']) }}</p>
            <p class="mt-2 text-xs text-slate-400">Dari penjualan ternak</p>
        </article>
    </div>

    {{-- Quick Actions & Alerts Row --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Monthly Revenue Chart --}}
        <section class="rounded-2xl border border-slate-100 bg-white p-6 lg:col-span-2">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Pendapatan 6 Bulan Terakhir</h3>
                    <p class="mt-0.5 text-xs text-slate-400">Grafik performa penjualan Anda</p>
                </div>
                <a href="{{ route('peternak.sales') }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-700">
                    Lihat Detail →
                </a>
            </div>

            @php
                $maxRevenue = max(1, ...$monthlyRevenue['data']);
            @endphp

            <div class="flex h-40 items-end gap-3">
                @foreach ($monthlyRevenue['data'] as $index => $value)
                    @php
                        $height = max(8, (int) round(($value / $maxRevenue) * 100));
                    @endphp
                    <div class="group flex flex-1 flex-col items-center gap-2">
                        <div class="relative flex h-32 w-full items-end justify-center">
                            <div class="w-full rounded-t-lg bg-emerald-500/20 transition group-hover:bg-emerald-500/40" style="height: {{ $height }}%">
                                <div class="absolute -top-8 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-md bg-slate-900 px-2 py-1 text-[10px] text-white opacity-0 transition group-hover:opacity-100">
                                    {{ $formatRupiah($value) }}
                                </div>
                            </div>
                        </div>
                        <span class="text-xs text-slate-400">{{ $monthlyRevenue['labels'][$index] }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Quick Actions --}}
        <section class="space-y-4">
            <div class="rounded-2xl border border-slate-100 bg-white p-6">
                <h3 class="text-base font-bold text-slate-900">Aksi Cepat</h3>
                <p class="mb-4 mt-0.5 text-xs text-slate-400">Menu yang sering diakses</p>

                <div class="space-y-3">
                    <a href="{{ route('peternak.livestock') }}" class="flex items-center gap-3 rounded-xl bg-emerald-50 p-3 transition hover:bg-emerald-100">
                        <div class="rounded-lg bg-emerald-500 p-2 text-white">
                            <i class="fa-solid fa-plus text-sm"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-900">Tambah Ternak</p>
                            <p class="text-xs text-slate-500">Daftarkan ternak baru</p>
                        </div>
                    </a>

                    <a href="{{ route('peternak.orders') }}" class="flex items-center gap-3 rounded-xl bg-blue-50 p-3 transition hover:bg-blue-100">
                        <div class="rounded-lg bg-blue-500 p-2 text-white">
                            <i class="fa-solid fa-eye text-sm"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-900">Lihat Pesanan</p>
                            <p class="text-xs text-slate-500">{{ $stats['activeOrders'] }} pesanan aktif</p>
                        </div>
                    </a>

                    <a href="{{ route('peternak.profile') }}" class="flex items-center gap-3 rounded-xl bg-purple-50 p-3 transition hover:bg-purple-100">
                        <div class="rounded-lg bg-purple-500 p-2 text-white">
                            <i class="fa-solid fa-farm text-sm"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-900">Profil Peternakan</p>
                            <p class="text-xs text-slate-500">Update informasi</p>
                        </div>
                    </a>
                </div>
            </div>

            {{-- Notifications Preview --}}
            @if($stats['unreadNotifications'] > 0)
                <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4">
                    <div class="flex items-center gap-3">
                        <div class="rounded-full bg-amber-500 p-2 text-white">
                            <i class="fa-solid fa-bell text-sm"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ $stats['unreadNotifications'] }} Notifikasi Baru</p>
                            <a href="{{ route('peternak.notifications') }}" class="text-xs text-amber-600 hover:underline">Lihat Sekarang →</a>
                        </div>
                    </div>
                </div>
            @endif
        </section>
    </div>

    {{-- Recent Orders --}}
    <section class="overflow-hidden rounded-2xl border border-slate-100 bg-white">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Pesanan Terbaru</h3>
                <p class="mt-0.5 text-xs text-slate-400">Pesanan untuk ternak Anda</p>
            </div>
            <a href="{{ route('peternak.orders') }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-700">
                Lihat Semua →
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                        <th class="px-4 py-3 font-semibold">Kode</th>
                        <th class="px-4 py-3 font-semibold">Tanggal</th>
                        <th class="px-4 py-3 font-semibold">Pembeli</th>
                        <th class="px-4 py-3 font-semibold">Total</th>
                        <th class="px-4 py-3 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($recentOrders as $order)
                        <tr class="transition hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <span class="text-sm font-semibold text-slate-900">{{ $order->order_number }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm text-slate-600">{{ $order->placed_at?->format('d M Y') ?? '-' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm text-slate-900">{{ $order->customer_name }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm font-semibold text-slate-900">{{ $formatRupiah($order->total_amount) }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $statusClass = match($order->status) {
                                        'pending' => 'bg-amber-50 text-amber-700',
                                        'processing' => 'bg-blue-50 text-blue-700',
                                        'shipped' => 'bg-purple-50 text-purple-700',
                                        'delivered' => 'bg-emerald-50 text-emerald-700',
                                        'cancelled' => 'bg-red-50 text-red-700',
                                        default => 'bg-slate-50 text-slate-700',
                                    };
                                    $statusLabel = match($order->status) {
                                        'pending' => 'Menunggu',
                                        'processing' => 'Diproses',
                                        'shipped' => 'Dikirim',
                                        'delivered' => 'Selesai',
                                        'cancelled' => 'Batal',
                                        default => $order->status,
                                    };
                                @endphp
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <i class="fa-solid fa-inbox text-4xl text-slate-300"></i>
                                    <p class="text-sm text-slate-400">Belum ada pesanan</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
