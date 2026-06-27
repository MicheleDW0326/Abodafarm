<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Verifikasi Peternak</h2>
            <p class="mt-1 text-sm text-slate-500">Kelola status verifikasi peternak</p>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <div class="rounded-xl border border-slate-100 bg-white p-4">
            <div class="flex items-center gap-3">
                <div class="rounded-lg bg-slate-100 p-2.5">
                    <i class="fa-solid fa-users text-lg text-slate-600"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-900">{{ $stats['total'] }}</p>
                    <p class="text-xs text-slate-500">Total Peternak</p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-emerald-100 bg-white p-4">
            <div class="flex items-center gap-3">
                <div class="rounded-lg bg-emerald-100 p-2.5">
                    <i class="fa-solid fa-circle-check text-lg text-emerald-600"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-emerald-600">{{ $stats['verified'] }}</p>
                    <p class="text-xs text-slate-500">Terverifikasi</p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-slate-100 bg-white p-4">
            <div class="flex items-center gap-3">
                <div class="rounded-lg bg-slate-100 p-2.5">
                    <i class="fa-solid fa-clock text-lg text-slate-400"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-400">{{ $stats['unverified'] }}</p>
                    <p class="text-xs text-slate-500">Belum Verifikasi</p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-blue-100 bg-white p-4">
            <div class="flex items-center gap-3">
                <div class="rounded-lg bg-blue-100 p-2.5">
                    <i class="fa-solid fa-check-double text-lg text-blue-600"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-blue-600">{{ $stats['complete'] }}</p>
                    <p class="text-xs text-slate-500">Profil Lengkap</p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-amber-100 bg-white p-4">
            <div class="flex items-center gap-3">
                <div class="rounded-lg bg-amber-100 p-2.5">
                    <i class="fa-solid fa-exclamation-circle text-lg text-amber-600"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-amber-600">{{ $stats['incomplete'] }}</p>
                    <p class="text-xs text-slate-500">Profil Kurang</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[200px]">
            <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" wire:model.live.debounce.300ms="searchQuery"
                placeholder="Cari peternak..."
                class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-10 pr-4 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
        </div>

        <select wire:model.live="verificationFilter"
            class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
            <option value="all">Semua Status</option>
            <option value="verified">Terverifikasi</option>
            <option value="unverified">Belum Verifikasi</option>
        </select>

        <select wire:model.live="statusFilter"
            class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
            <option value="all">Semua Profil</option>
            <option value="complete">Lengkap (70%+)</option>
            <option value="incomplete">Kurang</option>
        </select>
    </div>

    {{-- Table --}}
    <div class="rounded-xl border border-slate-200 bg-white overflow-hidden">
        <table class="w-full">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Peternak</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Profil</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Produk</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Booking</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Rating</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($farmers as $farmer)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-emerald-100 overflow-hidden flex-shrink-0">
                                    @if($farmer->image)
                                        <img src="{{ Storage::url($farmer->image) }}" alt="{{ $farmer->farm_name }}"
                                            class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <i class="fa-solid fa-farm text-emerald-600"></i>
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <p class="font-medium text-slate-900">{{ $farmer->farm_name }}</p>
                                        @if($farmer->is_verified)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-1.5 py-0.5 text-[10px] font-semibold text-blue-700">
                                                <i class="fa-solid fa-circle-check"></i>
                                                Verified
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-slate-500">{{ $farmer->user?->name ?? '-' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-16 h-1.5 bg-slate-200 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full {{ $farmer->profile_completion >= 70 ? 'bg-emerald-500' : 'bg-amber-500' }}"
                                        style="width: {{ $farmer->profile_completion }}%"></div>
                                </div>
                                <span class="text-xs font-medium {{ $farmer->profile_completion >= 70 ? 'text-emerald-600' : 'text-amber-600' }}">
                                    {{ $farmer->profile_completion }}%
                                </span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm text-slate-700">
                                {{ $farmer->stats['active_products'] ?? 0 }}
                                <span class="text-slate-400">/ {{ $farmer->stats['total_products'] ?? 0 }}</span>
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm text-slate-700">
                                {{ $farmer->stats['completed_bookings'] ?? 0 }}
                                <span class="text-slate-400">/ {{ $farmer->stats['total_bookings'] ?? 0 }}</span>
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if($farmer->rating > 0)
                                <div class="flex items-center gap-1">
                                    <i class="fa-solid fa-star text-amber-400 text-xs"></i>
                                    <span class="text-sm font-medium text-slate-700">{{ number_format($farmer->rating, 1) }}</span>
                                    <span class="text-xs text-slate-400">({{ $farmer->total_reviews }})</span>
                                </div>
                            @else
                                <span class="text-xs text-slate-400">Belum ada rating</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button type="button" wire:click="toggleVerification({{ $farmer->id }})"
                                class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium transition-colors {{ $farmer->is_verified ? 'bg-red-50 text-red-600 hover:bg-red-100' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100' }}">
                                @if($farmer->is_verified)
                                    <i class="fa-solid fa-xmark-circle"></i>
                                    Cabut Verifikasi
                                @else
                                    <i class="fa-solid fa-check-circle"></i>
                                    Verifikasi
                                @endif
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center">
                            <div class="text-slate-400">
                                <i class="fa-solid fa-users text-4xl mb-2"></i>
                                <p>Belum ada peternak</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div>
        {{ $farmers->links() }}
    </div>
</div>
