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
        <article class="rounded-2xl border border-slate-100 bg-white p-5 shadow-xs transition hover:shadow-md">
            <div class="mb-4 flex items-start justify-between">
                <div class="rounded-lg bg-emerald-50 p-2.5 text-emerald-600">
                    <i class="fa-solid fa-cow text-lg"></i>
                </div>
            </div>
            <p class="text-sm text-slate-500">Total Booking Ternak</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($stats['totalBookings'], 0, ',', '.') }}</p>
            <p class="mt-2 text-xs text-slate-400">{{ $stats['pendingBookings'] }} menunggu persetujuan</p>
        </article>

        {{-- Booking Disetujui --}}
        <article class="rounded-2xl border border-slate-100 bg-white p-5 shadow-xs transition hover:shadow-md">
            <div class="mb-4 flex items-start justify-between">
                <div class="rounded-lg bg-green-50 p-2.5 text-green-600">
                    <i class="fa-solid fa-check text-lg"></i>
                </div>
                <span class="rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-600">
                    {{ $stats['approvedBookings'] }}
                </span>
            </div>
            <p class="text-sm text-slate-500">Booking Disetujui</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($stats['approvedBookings'], 0, ',', '.') }}</p>
            <p class="mt-2 text-xs text-slate-400">Siap diambil</p>
        </article>

        {{-- Pesanan Daging Aktif --}}
        <article class="rounded-2xl border border-slate-100 bg-white p-5 shadow-xs transition hover:shadow-md">
            <div class="mb-4 flex items-start justify-between">
                <div class="rounded-lg bg-blue-50 p-2.5 text-blue-600">
                    <i class="fa-solid fa-shopping-bag text-lg"></i>
                </div>
                <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-600">
                    {{ $stats['activeOrders'] }}
                </span>
            </div>
            <p class="text-sm text-slate-500">Pesanan Daging Aktif</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($stats['activeOrders'], 0, ',', '.') }}</p>
            <p class="mt-2 text-xs text-slate-400">Sedang diproses</p>
        </article>

        {{-- Notifikasi --}}
        <article class="rounded-2xl border border-slate-100 bg-white p-5 shadow-xs transition hover:shadow-md">
            <div class="mb-4 flex items-start justify-between">
                <div class="rounded-lg bg-amber-50 p-2.5 text-amber-600">
                    <i class="fa-solid fa-bell text-lg"></i>
                </div>
                @if($stats['unreadNotifications'] > 0)
                <span class="rounded-full bg-red-500 px-2.5 py-1 text-xs font-semibold text-white">
                    {{ $stats['unreadNotifications'] }} Baru
                </span>
                @endif
            </div>
            <p class="text-sm text-slate-500">Notifikasi</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $stats['unreadNotifications'] }}</p>
            <p class="mt-2 text-xs text-slate-400">Pesan yang belum dibaca</p>
        </article>
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
                <div class="flex flex-col items-center gap-3 px-6 py-12 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                        <i class="fa-solid fa-cow text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-600">Belum ada booking ternak</p>
                        <p class="mt-1 text-xs text-slate-400">Jelajahi ternak yang tersedia sekarang</p>
                    </div>
                    <a href="{{ route('home') }}" class="mt-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700">
                        Jelajahi Ternak
                    </a>
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
            <div class="flex flex-col items-center gap-3 px-6 py-12 text-center">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                    <i class="fa-solid fa-shopping-bag text-2xl"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-600">Belum ada pesanan daging</p>
                    <p class="mt-1 text-xs text-slate-400">Jelajahi produk daging segar</p>
                </div>
                <a href="{{ route('home') }}" class="mt-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700">
                    Jelajahi Produk
                </a>
            </div>
        @endforelse
    </section>
</div>
