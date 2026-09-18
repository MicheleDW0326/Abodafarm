<div class="space-y-6">
    {{-- Welcome Header --}}
    <div class="rounded-2xl border border-slate-100 bg-gradient-to-r from-emerald-600 to-emerald-700 p-6 shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white">Selamat Datang, {{ auth()->user()->name }}!</h2>
                <p class="mt-1 text-emerald-100">Kelola akun dan pesanan Anda dengan mudah.</p>
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
        {{-- Total Booking --}}
        <x-ui.card.stat
            icon="fa-cow"
            label="Total Booking Ternak"
            :value="number_format($stats['totalBookings'])"
            color="emerald"
            sub="{{ $stats['pendingBookings'] }} menunggu persetujuan"
        />

        {{-- Booking Disetujui --}}
        <x-ui.card.stat
            icon="fa-check"
            label="Booking Disetujui"
            :value="number_format($stats['approvedBookings'])"
            color="green"
            badge="{{ $stats['approvedBookings'] }} Siap diambil"
        />

        {{-- Pesanan Daging Aktif --}}
        <x-ui.card.stat
            icon="fa-shopping-bag"
            label="Pesanan Daging Aktif"
            :value="number_format($stats['activeOrders'])"
            color="blue"
            badge="{{ $stats['activeOrders'] }} Sedang diproses"
        />

        {{-- Notifikasi --}}
        <x-ui.card.stat
            icon="fa-bell"
            label="Notifikasi"
            :value="$stats['unreadNotifications']"
            color="amber"
            :badge="($stats['unreadNotifications'] > 0) ? $stats['unreadNotifications'] . ' Baru' : null"
        />
    </div>

    {{-- Recent Activity & Quick Actions --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Recent Bookings --}}
        <section class="rounded-2xl border border-slate-100 bg-white lg:col-span-2">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Booking Ternak Terbaru</h3>
                    <p class="mt-0.5 text-xs text-slate-400">Riwayat booking Anda</p>
                </div>
                <a href="{{ route('pelanggan.bookings') }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-700">
                    Lihat Semua →
                </a>
            </div>

            @forelse($recentBookings as $booking)
                <div class="flex items-center justify-between border-b border-slate-50 px-6 py-4 last:border-0 last:pb-0 transition hover:bg-slate-50/50">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                            <i class="fa-solid fa-cow text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ $booking->order_number }}</p>
                            <p class="text-xs text-slate-500">{{ $booking->created_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $booking->status_badge_class }}">
                        {{ $booking->booking_status_label }}
                    </span>
                </div>
            @empty
                <div class="px-6 py-8">
                    <x-ui.empty-state
                        icon="fa-cow"
                        title="Belum ada booking ternak"
                        description="Jelajahi ternak yang tersedia sekarang"
                    >
                        <x-slot:action>
                            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700">
                                <i class="fa-solid fa-search"></i> Jelajahi Ternak
                            </a>
                        </x-slot:action>
                    </x-ui.empty-state>
                </div>
            @endforelse
        </section>

        {{-- Quick Actions --}}
        <section class="space-y-4">
            <div class="rounded-2xl border border-slate-100 bg-white p-6">
                <h3 class="text-base font-bold text-slate-900">Aksi Cepat</h3>
                <p class="mb-4 mt-0.5 text-xs text-slate-400">Menu yang sering diakses</p>

                <div class="space-y-3">
                    <a href="{{ route('pelanggan.bookings') }}" class="flex items-center gap-3 rounded-xl bg-emerald-50 p-3 transition hover:bg-emerald-100">
                        <div class="rounded-lg bg-emerald-500 p-2 text-white">
                            <i class="fa-solid fa-cow text-sm"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-900">Booking Ternak</p>
                            <p class="text-xs text-slate-500">Lihat & kelola booking</p>
                        </div>
                    </a>

                    <a href="{{ route('pelanggan.orders') }}" class="flex items-center gap-3 rounded-xl bg-blue-50 p-3 transition hover:bg-blue-100">
                        <div class="rounded-lg bg-blue-500 p-2 text-white">
                            <i class="fa-solid fa-shopping-bag text-sm"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-900">Pesanan Daging</p>
                            <p class="text-xs text-slate-500">{{ $stats['activeOrders'] }} pesanan aktif</p>
                        </div>
                    </a>

                    <a href="{{ route('pelanggan.profile') }}" class="flex items-center gap-3 rounded-xl bg-purple-50 p-3 transition hover:bg-purple-100">
                        <div class="rounded-lg bg-purple-500 p-2 text-white">
                            <i class="fa-solid fa-user text-sm"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-900">Profil Saya</p>
                            <p class="text-xs text-slate-500">Update informasi akun</p>
                        </div>
                    </a>

                    <a href="{{ route('pelanggan.addresses') }}" class="flex items-center gap-3 rounded-xl bg-amber-50 p-3 transition hover:bg-amber-100">
                        <div class="rounded-lg bg-amber-500 p-2 text-white">
                            <i class="fa-solid fa-map-marker-alt text-sm"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-900">Alamat Pengiriman</p>
                            <p class="text-xs text-slate-500">Kelola alamat</p>
                        </div>
                    </a>
                </div>
            </div>

            {{-- Unread Notifications --}}
            @if($stats['unreadNotifications'] > 0)
                <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4">
                    <div class="flex items-center gap-3">
                        <div class="rounded-full bg-amber-500 p-2 text-white">
                            <i class="fa-solid fa-bell text-sm"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ $stats['unreadNotifications'] }} Notifikasi Baru</p>
                            <a href="{{ route('pelanggan.notifications') }}" class="text-xs text-amber-600 hover:underline">Lihat Sekarang →</a>
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
                <h3 class="text-sm font-bold text-slate-900">Pesanan Daging Terbaru</h3>
                <p class="mt-0.5 text-xs text-slate-400">Riwayat pesanan daging segar</p>
            </div>
            <a href="{{ route('pelanggan.orders') }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-700">
                Lihat Semua →
            </a>
        </div>

        @forelse($recentOrders as $order)
            <div class="flex items-center justify-between border-b border-slate-50 px-6 py-4 last:border-0 last:pb-0 transition hover:bg-slate-50/50">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-rose-50 text-rose-600">
                        <i class="fa-solid fa-drumstick-bite text-lg"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-900">{{ $order->order_number }}</p>
                        <p class="text-xs text-slate-500">{{ $order->created_at->format('d M Y, H:i') }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm font-bold text-slate-900">{{ $formatRupiah($order->total_amount) }}</p>
                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $order->status_badge_class }}">
                        {{ $order->booking_status_label }}
                    </span>
                </div>
            </div>
        @empty
            <div class="px-6 py-8">
                <x-ui.empty-state
                    icon="fa-shopping-bag"
                    title="Belum ada pesanan daging"
                    description="Jelajahi produk daging segar"
                >
                    <x-slot:action>
                        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700">
                            <i class="fa-solid fa-search"></i> Jelajahi Produk
                        </a>
                    </x-slot:action>
                </x-ui.empty-state>
            </div>
        @endforelse
    </section>
</div>
