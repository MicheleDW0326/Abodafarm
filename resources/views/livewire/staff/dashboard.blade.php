<div class="space-y-6">
    {{-- ============================================================ --}}
    {{-- WELCOME HEADER --}}
    {{-- ============================================================ --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">
                Selamat Datang, {{ auth()->user()?->name }}! 👋
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Berikut ringkasan aktivitas operasional hari ini, {{ now()->format('d M Y') }}
            </p>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 px-4 py-2 text-white shadow-lg shadow-emerald-500/20">
                <i class="fa-solid fa-calendar-day text-lg"></i>
                <span class="text-sm font-semibold">{{ now()->format('l, d F Y') }}</span>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- TODAY'S OVERVIEW CARDS --}}
    {{-- ============================================================ --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($analyticsCards as $card)
            <article class="group relative overflow-hidden rounded-2xl border border-slate-100 bg-white p-5 shadow-xs transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5">
                {{-- Gradient accent bar --}}
                <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r {{ $card['gradient'] }}"></div>

                <div class="flex items-start justify-between">
                    <div class="rounded-xl {{ $card['bg'] }} p-3 {{ $card['text'] }}">
                        <i class="{{ $card['icon'] }} text-lg"></i>
                    </div>
                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold bg-slate-100 text-slate-500">
                        Hari ini
                    </span>
                </div>
                <div class="mt-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ $card['label'] }}</p>
                    <p class="mt-1 text-3xl font-bold text-slate-900">{{ $card['value'] }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $card['sub_value'] }}</p>
                </div>
            </article>
        @endforeach
    </div>

    {{-- ============================================================ --}}
    {{-- ACTIVITY OVERVIEW SECTION --}}
    {{-- ============================================================ --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Weekly Activity Chart Placeholder --}}
        <section class="rounded-2xl border border-slate-100 bg-white p-6 shadow-xs">
            <div class="mb-6 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="rounded-xl bg-blue-50 p-2.5 text-blue-600">
                        <i class="fa-solid fa-chart-line text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Aktivitas Mingguan</h3>
                        <p class="mt-0.5 text-xs text-slate-400">Pesanan per hari dalam seminggu</p>
                    </div>
                </div>
            </div>

            <div class="flex h-48 items-end gap-2">
                @foreach ($weeklyOrders as $index => $value)
                    <div class="group relative flex flex-1 flex-col items-center gap-1">
                        <div class="flex h-40 w-full items-end justify-center">
                            <div class="w-full max-w-10 rounded-t-md bg-gradient-to-t from-blue-500 to-blue-400 transition-all duration-300 group-hover:from-blue-600 group-hover:to-blue-500"
                                 style="height: {{ max(4, ($value / $maxWeekly) * 100) }}%">
                            </div>
                        </div>
                        <span class="text-[10px] text-slate-400 font-medium">{{ $weekDays[$index] }}</span>

                        {{-- Tooltip --}}
                        <div class="absolute -top-10 left-1/2 -translate-x-1/2 rounded-md bg-slate-900 px-2 py-1.5 text-[10px] text-white opacity-0 transition-all duration-200 group-hover:opacity-100 pointer-events-none z-10 whitespace-nowrap shadow-lg">
                            <div class="font-semibold">{{ $value }} pesanan</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Quick Actions --}}
        <section class="rounded-2xl border border-slate-100 bg-white p-6 shadow-xs">
            <div class="mb-6 flex items-center gap-3">
                <div class="rounded-xl bg-emerald-50 p-2.5 text-emerald-600">
                    <i class="fa-solid fa-bolt text-lg"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Aksi Cepat</h3>
                    <p class="mt-0.5 text-xs text-slate-400">Shortcut untuk tugas harian</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('staff.orders.list') }}"
                   class="flex flex-col items-center gap-2 rounded-xl border border-slate-100 bg-gradient-to-br from-slate-50 to-white p-4 text-center transition-all duration-200 hover:border-blue-200 hover:bg-blue-50 hover:shadow-md">
                    <div class="rounded-full bg-blue-100 p-3 text-blue-600">
                        <i class="fa-solid fa-list-check text-xl"></i>
                    </div>
                    <span class="text-sm font-semibold text-slate-700">Lihat Pesanan</span>
                    <span class="text-xs text-slate-400">Daftar pesanan aktif</span>
                </a>

                <a href="{{ route('staff.shipping') }}"
                   class="flex flex-col items-center gap-2 rounded-xl border border-slate-100 bg-gradient-to-br from-slate-50 to-white p-4 text-center transition-all duration-200 hover:border-violet-200 hover:bg-violet-50 hover:shadow-md">
                    <div class="rounded-full bg-violet-100 p-3 text-violet-600">
                        <i class="fa-solid fa-truck text-xl"></i>
                    </div>
                    <span class="text-sm font-semibold text-slate-700">Pengiriman</span>
                    <span class="text-xs text-slate-400">Kelola pengiriman</span>
                </a>

                <a href="{{ route('staff.history') }}"
                   class="flex flex-col items-center gap-2 rounded-xl border border-slate-100 bg-gradient-to-br from-slate-50 to-white p-4 text-center transition-all duration-200 hover:border-amber-200 hover:bg-amber-50 hover:shadow-md">
                    <div class="rounded-full bg-amber-100 p-3 text-amber-600">
                        <i class="fa-solid fa-clock-rotate-left text-xl"></i>
                    </div>
                    <span class="text-sm font-semibold text-slate-700">Riwayat</span>
                    <span class="text-xs text-slate-400">Riwayat pesanan</span>
                </a>

                <a href="{{ route('staff.profile') }}"
                   class="flex flex-col items-center gap-2 rounded-xl border border-slate-100 bg-gradient-to-br from-slate-50 to-white p-4 text-center transition-all duration-200 hover:border-emerald-200 hover:bg-emerald-50 hover:shadow-md">
                    <div class="rounded-full bg-emerald-100 p-3 text-emerald-600">
                        <i class="fa-solid fa-user-circle text-xl"></i>
                    </div>
                    <span class="text-sm font-semibold text-slate-700">Profil</span>
                    <span class="text-xs text-slate-400">Pengaturan akun</span>
                </a>
            </div>
        </section>
    </div>

    {{-- ============================================================ --}}
    {{-- RECENT ASSIGNED ORDERS TABLE --}}
    {{-- ============================================================ --}}
    <section class="rounded-2xl border border-slate-100 bg-white shadow-xs">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <div class="flex items-center gap-3">
                <div class="rounded-xl bg-slate-100 p-2 text-slate-600">
                    <i class="fa-solid fa-inbox text-lg"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Pesanan Terbaru</h3>
                    <p class="mt-0.5 text-xs text-slate-400">Pesanan yang baru masuk hari ini</p>
                </div>
            </div>
            <a href="{{ route('staff.operations') }}"
               class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 transition-all hover:bg-emerald-100">
                Lihat Semua
                <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/50">
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Invoice
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Customer
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Waktu
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($recentOrders as $order)
                        <tr class="transition-colors hover:bg-slate-50/50">
                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="text-sm font-semibold text-slate-900">{{ $order->order_number }}</span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="flex size-8 items-center justify-center rounded-full bg-slate-100 text-xs font-semibold text-slate-600">
                                        {{ strtoupper(substr($order->customer_name ?? 'G', 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-slate-900">{{ $order->customer_name ?? 'Guest' }}</p>
                                        <p class="text-xs text-slate-400">{{ $order->customer_phone ?? '-' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                @php
                                    $statusClass = match($order->status) {
                                        'pending' => 'bg-amber-50 text-amber-700',
                                        'paid', 'approved' => 'bg-green-50 text-green-700',
                                        'processing' => 'bg-blue-50 text-blue-700',
                                        'ready_to_ship' => 'bg-violet-50 text-violet-700',
                                        'shipped' => 'bg-purple-50 text-purple-700',
                                        'delivered', 'completed' => 'bg-emerald-50 text-emerald-700',
                                        'cancelled', 'rejected' => 'bg-red-50 text-red-700',
                                        default => 'bg-slate-50 text-slate-600'
                                    };
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">
                                    {{ $order->status_label ?? ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <p class="text-sm text-slate-600">
                                    {{ \Carbon\Carbon::parse($order->created_at)->format('d M Y') }}
                                </p>
                                <p class="text-xs text-slate-400">
                                    {{ \Carbon\Carbon::parse($order->created_at)->format('H:i') }}
                                </p>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right">
                                <button type="button"
                                        class="inline-flex items-center gap-1 rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-700 transition-all hover:bg-slate-200">
                                    <i class="fa-solid fa-eye text-[10px]"></i>
                                    Detail
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12">
                                <div class="flex flex-col items-center justify-center text-center">
                                    <div class="rounded-full bg-slate-100 p-4 text-slate-300 mb-3">
                                        <i class="fa-solid fa-inbox text-3xl"></i>
                                    </div>
                                    <p class="text-sm font-medium text-slate-500">Belum ada pesanan</p>
                                    <p class="mt-1 text-xs text-slate-400">Pesanan baru akan muncul di sini</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
