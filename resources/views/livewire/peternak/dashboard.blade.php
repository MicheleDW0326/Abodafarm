<div class="space-y-6">
    {{-- Welcome Header --}}
    <div class="rounded-2xl border border-slate-100 bg-gradient-to-r from-emerald-600 to-emerald-700 p-6 shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white">Selamat Datang, {{ auth()->user()->name }}!</h2>
                <p class="mt-1 text-emerald-100">Berikut ringkasan performa peternakan Anda.</p>
            </div>
            <div class="hidden md:block">
                <span class="grid size-16 place-content-center rounded-full bg-white/20 text-2xl font-bold text-white">
                    {{ auth()->user()->initials() }}
                </span>
            </div>
        </div>
    </div>

    {{-- Main Stats Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        {{-- Total Revenue --}}
        <article class="rounded-2xl border border-slate-100 bg-white p-5 shadow-xs">
            <div class="mb-3 flex items-start justify-between">
                <div class="rounded-lg bg-purple-50 p-2.5 text-purple-600">
                    <i class="fa-solid fa-coins text-lg"></i>
                </div>
                @if($stats['revenueChange'] != 0)
                    <span class="rounded-full px-2 py-0.5 text-xs font-bold {{ $stats['revenueChange'] >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-500' }}">
                        {{ $stats['revenueChange'] >= 0 ? '+' : '' }}{{ $stats['revenueChange'] }}%
                    </span>
                @endif
            </div>
            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Total Pendapatan</p>
            <p class="mt-1 text-xl font-bold text-slate-900">{{ $formatRupiah($stats['totalRevenue']) }}</p>
            <p class="mt-1 text-xs text-slate-500">Bulan ini: {{ $formatRupiah($stats['thisMonthRevenue']) }}</p>
        </article>

        {{-- Bookings --}}
        <article class="rounded-2xl border border-slate-100 bg-white p-5 shadow-xs">
            <div class="mb-3 flex items-start justify-between">
                <div class="rounded-lg bg-blue-50 p-2.5 text-blue-600">
                    <i class="fa-solid fa-calendar-check text-lg"></i>
                </div>
                <span class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-bold text-blue-600">
                    {{ $stats['activeBookings'] }} Aktif
                </span>
            </div>
            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Booking</p>
            <p class="mt-1 text-xl font-bold text-slate-900">{{ number_format($stats['totalBookings']) }}</p>
            <p class="mt-1 text-xs text-slate-500">Total booking masuk</p>
        </article>

        {{-- Completed Orders --}}
        <article class="rounded-2xl border border-slate-100 bg-white p-5 shadow-xs">
            <div class="mb-3 flex items-start justify-between">
                <div class="rounded-lg bg-emerald-50 p-2.5 text-emerald-600">
                    <i class="fa-solid fa-check-double text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Selesai</p>
            <p class="mt-1 text-xl font-bold text-slate-900">{{ number_format($stats['completedOrders']) }}</p>
            <p class="mt-1 text-xs text-slate-500">Pesanan selesai</p>
        </article>

        {{-- Reviews --}}
        <article class="rounded-2xl border border-slate-100 bg-white p-5 shadow-xs">
            <div class="mb-3 flex items-start justify-between">
                <div class="rounded-lg bg-amber-50 p-2.5 text-amber-600">
                    <i class="fa-solid fa-star text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Ulasan</p>
            <p class="mt-1 text-xl font-bold text-slate-900">{{ number_format($stats['totalReviews']) }}</p>
            <p class="mt-1 text-xs text-slate-500">Ulasan dari pelanggan</p>
        </article>

        {{-- Average Rating --}}
        <article class="rounded-2xl border border-slate-100 bg-white p-5 shadow-xs">
            <div class="mb-3 flex items-start justify-between">
                <div class="rounded-lg bg-rose-50 p-2.5 text-rose-600">
                    <i class="fa-solid fa-heart text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Rating Rata-rata</p>
            <div class="mt-1 flex items-center gap-2">
                <p class="text-xl font-bold text-slate-900">{{ $stats['avgRating'] }}</p>
                <div class="flex items-center gap-0.5">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="fa-solid fa-star text-xs {{ $i <= round($stats['avgRating']) ? 'text-amber-400' : 'text-slate-200' }}"></i>
                    @endfor
                </div>
            </div>
            <p class="mt-1 text-xs text-slate-500">Berdasarkan {{ $stats['totalReviews'] }} ulasan</p>
        </article>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Monthly Revenue Chart --}}
        <section class="rounded-2xl border border-slate-100 bg-white p-6 lg:col-span-2">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Pendapatan Bulanan {{ $chartYear }}</h3>
                    <p class="mt-0.5 text-xs text-slate-400">Grafik performa penjualan Anda</p>
                </div>
                <div class="flex items-center gap-4 text-xs">
                    <span class="flex items-center gap-1.5">
                        <span class="size-2 rounded-full bg-emerald-500"></span> Pendapatan
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="size-2 rounded-full bg-blue-400"></span> Pesanan
                    </span>
                </div>
            </div>

            @php
                $maxRevenue = max(1, ...$monthlyRevenue);
                $maxOrders = max(1, ...$monthlyOrders);
                $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                $monthIndex = (int) now()->format('n') - 5;
            @endphp

            <div class="flex h-48 items-end gap-1">
                @foreach ($monthlyRevenue as $index => $value)
                    <div class="group relative flex flex-1 flex-col items-center gap-1">
                        <div class="flex h-40 w-full items-end justify-center">
                            {{-- Revenue Bar --}}
                            <div class="w-4/5 rounded-t-md bg-emerald-500 transition-all group-hover:bg-emerald-600"
                                 style="height: {{ max(4, ($value / $maxRevenue) * 100) }}%">
                            </div>
                        </div>
                        @php $monthLabel = isset($months[$monthIndex + $index]) ? $months[$monthIndex + $index] : chr(65 + $index); @endphp
                        <span class="text-[10px] text-slate-400">{{ $monthLabel }}</span>

                        {{-- Tooltip --}}
                        <div class="absolute -top-12 left-1/2 -translate-x-1/2 rounded-md bg-slate-900 px-2 py-1 text-[10px] text-white opacity-0 transition group-hover:opacity-100 pointer-events-none z-10 whitespace-nowrap">
                            <div class="font-semibold">{{ $formatRupiah($value) }}</div>
                            <div class="text-slate-300">{{ $monthlyOrders[$index] }} pesanan</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Order Status Distribution --}}
        <section class="rounded-2xl border border-slate-100 bg-white p-6">
            <div class="mb-5">
                <h3 class="text-base font-bold text-slate-900">Distribusi Status</h3>
                <p class="mt-0.5 text-xs text-slate-400">{{ number_format($totalStatusOrders, 0, ',', '.') }} total pesanan</p>
            </div>

            <div class="space-y-3">
                @foreach($orderStatusDistribution as $status)
                    @php $percent = $totalStatusOrders > 0 ? round(($status['count'] / $totalStatusOrders) * 100) : 0; @endphp
                    <div>
                        <div class="mb-1 flex items-center justify-between text-xs">
                            <span class="text-slate-600">{{ $status['label'] }}</span>
                            <span class="font-semibold text-slate-900">{{ $status['count'] }}</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full {{ $status['color'] }} rounded-full transition-all"
                                 style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    {{-- Top Products & Latest Reviews Row --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Top Products --}}
        <section class="rounded-2xl border border-slate-100 bg-white p-6">
            <div class="mb-5 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="rounded-xl bg-amber-50 p-2.5 text-amber-600">
                        <i class="fa-solid fa-trophy text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Produk Terlaris</h3>
                        <p class="text-xs text-slate-400">Berdasarkan total penjualan</p>
                    </div>
                </div>
            </div>

            @if(count($topProducts) > 0)
                <div class="space-y-3">
                    @foreach($topProducts as $index => $product)
                        <div class="flex items-center gap-3 rounded-xl bg-slate-50 p-3">
                            <div class="flex size-10 items-center justify-center rounded-full bg-amber-100 text-amber-600 font-bold">
                                #{{ $index + 1 }}
                            </div>
                            @if($product['image'])
                                <img src="{{ Str::startsWith($product['image'], 'http') ? $product['image'] : asset('storage/' . $product['image']) }}"
                                     alt="{{ $product['name'] }}"
                                     class="size-10 rounded-lg object-cover bg-slate-200">
                            @else
                                <div class="flex size-10 items-center justify-center rounded-lg bg-slate-200 text-slate-400">
                                    <i class="fa-solid fa-image"></i>
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-slate-900 truncate">{{ $product['name'] }}</p>
                                <p class="text-xs text-slate-500">{{ $product['total_qty'] }} terjual</p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-emerald-600">{{ $formatRupiah($product['total_sales']) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <i class="fa-solid fa-box-open text-4xl text-slate-200 mb-3"></i>
                    <p class="text-sm text-slate-400">Belum ada data penjualan</p>
                </div>
            @endif
        </section>

        {{-- Latest Reviews --}}
        <section class="rounded-2xl border border-slate-100 bg-white p-6">
            <div class="mb-5 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="rounded-xl bg-rose-50 p-2.5 text-rose-600">
                        <i class="fa-solid fa-comment-dots text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Ulasan Terbaru</h3>
                        <p class="text-xs text-slate-400">Dari pelanggan Anda</p>
                    </div>
                </div>
            </div>

            @if(count($latestReviews) > 0)
                <div class="space-y-4">
                    @foreach($latestReviews as $review)
                        <div class="flex gap-3">
                            @if($review['reviewer_avatar'])
                                <img src="{{ $review['reviewer_avatar'] }}"
                                     alt="{{ $review['reviewer_name'] }}"
                                     class="size-10 rounded-full object-cover bg-slate-200">
                            @else
                                <div class="flex size-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 font-bold">
                                    {{ strtoupper(substr($review['reviewer_name'], 0, 1)) }}
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <p class="font-semibold text-slate-900">{{ $review['reviewer_name'] }}</p>
                                    <div class="flex items-center gap-0.5">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fa-solid fa-star text-[10px] {{ $i <= $review['rating'] ? 'text-amber-400' : 'text-slate-200' }}"></i>
                                        @endfor
                                    </div>
                                </div>
                                <p class="mt-0.5 text-xs text-slate-400">{{ $review['product_name'] ?? 'Produk' }} · {{ $review['created_at'] }}</p>
                                <p class="mt-1 text-sm text-slate-600 line-clamp-2">{{ $review['content'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <i class="fa-solid fa-comment-dots text-4xl text-slate-200 mb-3"></i>
                    <p class="text-sm text-slate-400">Belum ada ulasan</p>
                </div>
            @endif
        </section>
    </div>

    {{-- Quick Actions --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
        <a href="{{ route('peternak.livestock') }}" class="flex items-center gap-4 rounded-2xl border border-slate-100 bg-white p-5 shadow-xs transition hover:shadow-md hover:border-emerald-200">
            <div class="rounded-xl bg-emerald-50 p-3 text-emerald-600">
                <i class="fa-solid fa-cow text-xl"></i>
            </div>
            <div>
                <p class="font-semibold text-slate-900">Kelola Ternak</p>
                <p class="text-xs text-slate-500">{{ $stats['totalProducts'] }} ternak</p>
            </div>
        </a>

        <a href="{{ route('peternak.livestock.add') }}" class="flex items-center gap-4 rounded-2xl border border-slate-100 bg-white p-5 shadow-xs transition hover:shadow-md hover:border-emerald-200">
            <div class="rounded-xl bg-emerald-100 p-3 text-emerald-700">
                <i class="fa-solid fa-plus text-xl"></i>
            </div>
            <div>
                <p class="font-semibold text-slate-900">Tambah Ternak</p>
                <p class="text-xs text-slate-500">Daftarkan baru</p>
            </div>
        </a>

        <a href="{{ route('peternak.bookings') }}" class="flex items-center gap-4 rounded-2xl border border-slate-100 bg-white p-5 shadow-xs transition hover:shadow-md hover:border-blue-200">
            <div class="rounded-xl bg-blue-50 p-3 text-blue-600">
                <i class="fa-solid fa-calendar-check text-xl"></i>
            </div>
            <div>
                <p class="font-semibold text-slate-900">Booking</p>
                <p class="text-xs text-slate-500">{{ $stats['activeBookings'] }} aktif</p>
            </div>
        </a>

        <a href="{{ route('peternak.reviews') }}" class="flex items-center gap-4 rounded-2xl border border-slate-100 bg-white p-5 shadow-xs transition hover:shadow-md hover:border-amber-200">
            <div class="rounded-xl bg-amber-50 p-3 text-amber-600">
                <i class="fa-solid fa-star text-xl"></i>
            </div>
            <div>
                <p class="font-semibold text-slate-900">Ulasan</p>
                <p class="text-xs text-slate-500">{{ $stats['totalReviews'] }} ulasan</p>
            </div>
        </a>

        <a href="{{ route('farmer.public-profile', ['username' => auth()->user()?->name ?? auth()->user()?->id]) }}" class="flex items-center gap-4 rounded-2xl border border-slate-100 bg-white p-5 shadow-xs transition hover:shadow-md hover:border-purple-200">
            <div class="rounded-xl bg-purple-50 p-3 text-purple-600">
                <i class="fa-solid fa-farm text-xl"></i>
            </div>
            <div>
                <p class="font-semibold text-slate-900">Profil</p>
                <p class="text-xs text-slate-500">Update info</p>
            </div>
        </a>
    </div>
</div>
