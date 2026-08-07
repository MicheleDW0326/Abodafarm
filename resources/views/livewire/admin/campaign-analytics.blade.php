<div class="min-h-screen bg-gray-50">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Analisis Kampanye</h1>
            <p class="mt-1 text-sm text-gray-500">Pantau performa kampanye marketing dan voucher</p>
        </div>

        <!-- Overall Stats -->
        <div class="mb-8 grid gap-4 md:grid-cols-2 lg:grid-cols-5">
            <div class="rounded-xl bg-white p-5 shadow-sm border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100">
                        <i class="fa-solid fa-ticket text-xl text-blue-600"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-gray-900">{{ number_format($overallStats['total_vouchers']) }}</div>
                        <div class="text-xs text-gray-500">Total Voucher</div>
                    </div>
                </div>
            </div>
            <div class="rounded-xl bg-white p-5 shadow-sm border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100">
                        <i class="fa-solid fa-circle-check text-xl text-emerald-600"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-gray-900">{{ number_format($overallStats['active_vouchers']) }}</div>
                        <div class="text-xs text-gray-500">Aktif</div>
                    </div>
                </div>
            </div>
            <div class="rounded-xl bg-white p-5 shadow-sm border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-100">
                        <i class="fa-solid fa-receipt text-xl text-purple-600"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-gray-900">{{ number_format($overallStats['used_vouchers']) }}</div>
                        <div class="text-xs text-gray-500">Terpakai</div>
                    </div>
                </div>
            </div>
            <div class="rounded-xl bg-white p-5 shadow-sm border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100">
                        <i class="fa-solid fa-chart-line text-xl text-amber-600"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-gray-900">{{ $overallStats['conversion_rate'] }}%</div>
                        <div class="text-xs text-gray-500">Conversion</div>
                    </div>
                </div>
            </div>
            <div class="rounded-xl bg-white p-5 shadow-sm border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-100">
                        <i class="fa-solid fa-tags text-xl text-red-600"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-gray-900">Rp {{ number_format($overallStats['total_discount_given'], 0, ',', '.') }}</div>
                        <div class="text-xs text-gray-500">Total Diskon</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Campaign Performance -->
            <div class="lg:col-span-2">
                <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-100">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">Performa Kampanye</h2>

                    @if(empty($campaignStats))
                        <div class="py-8 text-center text-gray-500">
                            <i class="fa-solid fa-chart-bar text-4xl mb-3 text-gray-300"></i>
                            <p>Belum ada data kampanye</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-200">
                                        <th class="py-3 text-left font-medium text-gray-600">Kampanye</th>
                                        <th class="py-3 text-center font-medium text-gray-600">Tipe</th>
                                        <th class="py-3 text-center font-medium text-gray-600">Status</th>
                                        <th class="py-3 text-center font-medium text-gray-600">Generate</th>
                                        <th class="py-3 text-center font-medium text-gray-600">Dipakai</th>
                                        <th class="py-3 text-center font-medium text-gray-600">Conv.</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($campaignStats as $campaign)
                                        <tr class="hover:bg-gray-50 cursor-pointer" wire:click="selectCampaign({{ $campaign['id'] }})">
                                            <td class="py-3 font-medium text-gray-900">{{ $campaign['name'] }}</td>
                                            <td class="py-3 text-center text-gray-500">{{ $campaign['type'] }}</td>
                                            <td class="py-3 text-center">
                                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                                    {{ $campaign['status'] === 'Aktif' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-700' }}">
                                                    {{ $campaign['status'] }}
                                                </span>
                                            </td>
                                            <td class="py-3 text-center text-gray-600">{{ number_format($campaign['total_generated']) }}</td>
                                            <td class="py-3 text-center font-medium text-emerald-600">{{ number_format($campaign['total_used']) }}</td>
                                            <td class="py-3 text-center font-medium text-blue-600">{{ $campaign['conversion_rate'] }}%</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <!-- Voucher Usage by Type -->
                <div class="mt-6 rounded-xl bg-white p-6 shadow-sm border border-gray-100">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">Penggunaan per Tipe Diskon</h2>

                    @if(empty($voucherUsageByType))
                        <div class="py-4 text-center text-gray-500 text-sm">Belum ada data penggunaan</div>
                    @else
                        <div class="space-y-3">
                            @foreach($voucherUsageByType as $usage)
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="font-medium text-gray-700">{{ $usage['type'] }}</span>
                                    </div>
                                    <div class="text-right">
                                        <span class="font-semibold text-gray-900">{{ number_format($usage['count']) }}x</span>
                                        <span class="ml-2 text-sm text-gray-500">Rp {{ number_format($usage['total_discount'], 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Top Campaigns -->
                <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-100">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">Kampanye Teratas</h2>

                    @if(empty($topCampaigns))
                        <div class="py-4 text-center text-gray-500 text-sm">Belum ada kampanye</div>
                    @else
                        <div class="space-y-3">
                            @foreach($topCampaigns as $index => $campaign)
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-sm font-bold text-gray-600">
                                            {{ $index + 1 }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900 text-sm">{{ $campaign['name'] }}</p>
                                            <p class="text-xs text-gray-500">{{ $campaign['used'] }}x digunakan</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm font-semibold text-emerald-600">{{ $campaign['conversion'] }}%</div>
                                        <div class="text-xs text-gray-400">Rp {{ number_format($campaign['discount'], 0, ',', '.') }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Recent Usage -->
                <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-100">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">Penggunaan Terbaru</h2>

                    @if(empty($recentVoucherUsages))
                        <div class="py-4 text-center text-gray-500 text-sm">Belum ada penggunaan</div>
                    @else
                        <div class="space-y-3">
                            @foreach($recentVoucherUsages as $usage)
                                <div class="flex items-start justify-between border-b border-gray-100 pb-2 last:border-0 last:pb-0">
                                    <div>
                                        <p class="font-mono text-sm font-medium text-emerald-600">{{ $usage['code'] }}</p>
                                        <p class="text-xs text-gray-500">{{ $usage['user'] }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-semibold text-gray-900">-Rp {{ number_format($usage['discount'], 0, ',', '.') }}</p>
                                        <p class="text-xs text-gray-400">{{ $usage['used_at'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Campaign Detail Modal -->
    @if($selectedCampaign)
        <div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ show: true }" x-show="show"
             x-on:keydown.escape.window="show = false; $wire.clearSelection()">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/50 transition-opacity" x-on:click="show = false; $wire.clearSelection()"></div>

                <div class="relative w-full max-w-2xl rounded-2xl bg-white p-6 shadow-xl"
                     x-show="show"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-gray-900">Detail Kampanye</h2>
                        <button wire:click="clearSelection" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>

                    @if($selectedCampaignAnalytics)
                        <div class="space-y-6">
                            <div class="rounded-lg bg-gray-50 p-4">
                                <h3 class="font-semibold text-gray-900">{{ $selectedCampaign->name }}</h3>
                                <p class="mt-1 text-sm text-gray-500">{{ $selectedCampaign->description }}</p>
                                <div class="mt-2 flex gap-2">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                        {{ $selectedCampaign->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-700' }}">
                                        {{ $selectedCampaign->status_label }}
                                    </span>
                                    <span class="text-xs text-gray-400">{{ $selectedCampaign->type_label }}</span>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="rounded-lg border border-gray-200 p-4 text-center">
                                    <div class="text-2xl font-bold text-gray-900">{{ number_format($selectedCampaignAnalytics['total_generated']) }}</div>
                                    <div class="text-xs text-gray-500">Di Generate</div>
                                </div>
                                <div class="rounded-lg border border-gray-200 p-4 text-center">
                                    <div class="text-2xl font-bold text-emerald-600">{{ number_format($selectedCampaignAnalytics['total_used']) }}</div>
                                    <div class="text-xs text-gray-500">Dipakai</div>
                                </div>
                                <div class="rounded-lg border border-gray-200 p-4 text-center">
                                    <div class="text-2xl font-bold text-blue-600">{{ $selectedCampaignAnalytics['conversion_rate'] }}%</div>
                                    <div class="text-xs text-gray-500">Conversion</div>
                                </div>
                                <div class="rounded-lg border border-gray-200 p-4 text-center">
                                    <div class="text-2xl font-bold text-red-600">Rp {{ number_format($selectedCampaignAnalytics['total_discount_given'], 0, ',', '.') }}</div>
                                    <div class="text-xs text-gray-500">Diskon Diberikan</div>
                                </div>
                            </div>

                            @if($selectedCampaignAnalytics['budget_limit'])
                                <div>
                                    <div class="mb-2 flex items-center justify-between text-sm">
                                        <span class="text-gray-500">Budget</span>
                                        <span class="font-medium">Rp {{ number_format($selectedCampaignAnalytics['budget_used'], 0, ',', '.') }} / Rp {{ number_format($selectedCampaignAnalytics['budget_limit'], 0, ',', '.') }}</span>
                                    </div>
                                    <div class="h-2 w-full rounded-full bg-gray-200">
                                        <div class="h-2 rounded-full bg-emerald-500"
                                             style="width: {{ min(100, ($selectedCampaignAnalytics['budget_used'] / $selectedCampaignAnalytics['budget_limit']) * 100) }}%"></div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <div class="mt-6 flex justify-end">
                        <button wire:click="clearSelection"
                                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
