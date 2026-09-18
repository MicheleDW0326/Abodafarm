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
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
        {{-- Total Revenue --}}
        <x-ui.card.stat
            icon="fa-coins"
            label="Total Pendapatan"
            :value="$formatRupiah($stats['totalRevenue'])"
            :change="($stats['revenueChange'] != 0) ? (($stats['revenueChange'] >= 0 ? '+' : '') . $stats['revenueChange'] . '%') : null"
            :trend="($stats['revenueChange'] >= 0) ? 'up' : 'down'"
            color="purple"
            sub="Bulan ini: {{ $formatRupiah($stats['thisMonthRevenue']) }}"
        />

        {{-- Bookings --}}
        <x-ui.card.stat
            icon="fa-calendar-check"
            label="Booking"
            :value="number_format($stats['totalBookings'])"
            :badge="$stats['activeBookings'] . ' Aktif'"
            color="blue"
            sub="Total booking masuk"
        />

        {{-- Completed Orders --}}
        <x-ui.card.stat
            icon="fa-check-double"
            label="Selesai"
            :value="number_format($stats['completedOrders'])"
            color="emerald"
            sub="Pesanan selesai"
        />

        {{-- Reviews --}}
        <x-ui.card.stat
            icon="fa-star"
            label="Ulasan"
            :value="number_format($stats['totalReviews'])"
            color="amber"
            sub="Ulasan dari pelanggan"
        />

        {{-- Average Rating --}}
        <x-ui.card.stat
            icon="fa-heart"
            label="Rating Rata-rata"
            :value="$stats['avgRating'] . '/5'"
            color="rose"
            :showStars="true"
            :starsCount="round($stats['avgRating'])"
            sub="Berdasarkan {{ $stats['totalReviews'] }} ulasan"
        />
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
                <x-ui.empty-state
                    icon="fa-box-open"
                    title="Belum ada data penjualan"
                    description="Produk terlaris akan muncul setelah ada penjualan"
                />
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
                <x-ui.empty-state
                    icon="fa-comment-dots"
                    title="Belum ada ulasan"
                    description="Ulasan dari pelanggan akan muncul di sini"
                />
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
