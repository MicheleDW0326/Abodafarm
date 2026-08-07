<div class="min-h-screen bg-gray-50">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Kelola Kampanye</h1>
                <p class="mt-1 text-sm text-gray-500">Kelola kampanye marketing dan distribusi voucher</p>
            </div>
            <button wire:click="openCreateModal"
                    class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                <i class="fa-solid fa-plus"></i>
                Buat Kampanye
            </button>
        </div>

        <!-- Filters -->
        <div class="mb-6 flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" wire:model.live="searchQuery"
                       placeholder="Cari kampanye..."
                       class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
            </div>
            <select wire:model.live="statusFilter" class="rounded-lg border border-gray-300 px-4 py-2 text-sm">
                <option value="all">Semua Status</option>
                @foreach($statuses as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            <select wire:model.live="typeFilter" class="rounded-lg border border-gray-300 px-4 py-2 text-sm">
                <option value="all">Semua Tipe</option>
                @foreach($campaignTypes as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <!-- Campaigns Grid -->
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            @forelse($campaigns as $campaign)
                <div class="rounded-xl bg-white p-5 shadow-sm border border-gray-100 hover:shadow-md transition">
                    <!-- Header -->
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                {{ $campaign->status === 'active' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                {{ $campaign->status === 'draft' ? 'bg-gray-100 text-gray-700' : '' }}
                                {{ $campaign->status === 'scheduled' ? 'bg-blue-100 text-blue-700' : '' }}
                                {{ $campaign->status === 'paused' ? 'bg-amber-100 text-amber-700' : '' }}
                                {{ $campaign->status === 'ended' ? 'bg-gray-100 text-gray-500' : '' }}">
                                {{ $campaign->status_label }}
                            </span>
                            <h3 class="mt-2 font-semibold text-gray-900">{{ $campaign->name }}</h3>
                        </div>
                        <span class="text-xs text-gray-400">{{ $campaign->type_label }}</span>
                    </div>

                    <!-- Description -->
                    <p class="text-sm text-gray-500 line-clamp-2">{{ $campaign->description ?? 'Tidak ada deskripsi' }}</p>

                    <!-- Stats -->
                    <div class="mt-4 grid grid-cols-3 gap-2 text-center">
                        <div class="rounded-lg bg-gray-50 p-2">
                            <div class="text-lg font-bold text-gray-900">{{ $campaign->total_vouchers_claimed }}</div>
                            <div class="text-xs text-gray-500">Diklaim</div>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-2">
                            <div class="text-lg font-bold text-emerald-600">{{ $campaign->total_vouchers_used }}</div>
                            <div class="text-xs text-gray-500">Dipakai</div>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-2">
                            <div class="text-lg font-bold text-blue-600">{{ $campaign->conversion_rate }}%</div>
                            <div class="text-xs text-gray-500">Conv.</div>
                        </div>
                    </div>

                    <!-- Date -->
                    <div class="mt-4 flex items-center gap-2 text-xs text-gray-400">
                        <i class="fa-regular fa-calendar"></i>
                        {{ $campaign->start_date->format('d M') }} - {{ $campaign->end_date->format('d M Y') }}
                    </div>

                    <!-- Actions -->
                    <div class="mt-4 flex gap-2">
                        <button wire:click="viewDetail({{ $campaign->id }})"
                                class="flex-1 rounded-lg border border-gray-200 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50">
                            Detail
                        </button>
                        <button wire:click="openEditModal({{ $campaign->id }})"
                                class="flex-1 rounded-lg border border-gray-200 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50">
                            Edit
                        </button>
                        @if($campaign->auto_generate_voucher)
                            <button wire:click="generateVouchers({{ $campaign->id }})"
                                    class="flex-1 rounded-lg bg-emerald-50 py-1.5 text-xs font-medium text-emerald-600 hover:bg-emerald-100">
                                Generate
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-xl bg-white p-8 text-center border border-gray-200">
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100">
                        <i class="fa-solid fa-bullhorn text-2xl text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Belum ada kampanye</h3>
                    <p class="mt-1 text-sm text-gray-500">Mulai kampanye marketing pertama Anda</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $campaigns->links() }}
        </div>
    </div>

    <!-- Create/Edit Modal -->
    @if($showCreateModal || $showEditModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ show: true }" x-show="show"
             x-on:keydown.escape.window="show = false; $wire.closeModal()">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/50 transition-opacity" x-on:click="show = false; $wire.closeModal()"></div>

                <div class="relative w-full max-w-2xl rounded-2xl bg-white p-6 shadow-xl"
                     x-show="show"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100">

                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-gray-900">
                            {{ $showEditModal ? 'Edit Kampanye' : 'Buat Kampanye Baru' }}
                        </h2>
                        <button wire:click="closeModal" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>

                    <form wire:submit="{{ $showEditModal ? 'updateCampaign' : 'saveCampaign' }}" class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Nama Kampanye</label>
                                <input type="text" wire:model="name" required
                                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Tipe Kampanye</label>
                                <select wire:model="campaign_type" required
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                                    @foreach($campaignTypes as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Deskripsi</label>
                            <textarea wire:model="description" rows="2"
                                      class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"></textarea>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                                <input type="datetime-local" wire:model="start_date" required
                                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Tanggal Berakhir</label>
                                <input type="datetime-local" wire:model="end_date" required
                                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Status</label>
                                <select wire:model="status"
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                                    @foreach($statuses as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Prioritas</label>
                                <input type="number" wire:model="priority" min="0"
                                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                            </div>
                        </div>

                        <!-- Voucher Settings -->
                        <div class="rounded-lg bg-gray-50 p-4">
                            <div class="flex items-center gap-3 mb-4">
                                <input type="checkbox" wire:model="auto_generate_voucher" id="auto_generate"
                                       class="size-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                <label for="auto_generate" class="text-sm font-medium text-gray-700">
                                    Generate Voucher Otomatis
                                </label>
                            </div>

                            @if($auto_generate_voucher)
                                <div class="grid gap-4 md:grid-cols-3">
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-gray-600">Jumlah Voucher</label>
                                        <input type="number" wire:model="voucher_quantity" min="1"
                                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-gray-600">Max Klaim/User</label>
                                        <input type="number" wire:model="max_claim" min="1"
                                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-gray-600">Budget Limit</label>
                                        <input type="number" wire:model="budget_limit" min="0" step="1000"
                                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Rules Section -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-sm font-medium text-gray-700">Aturan Kampanye</label>
                                <button type="button" wire:click="addRule"
                                        class="text-xs text-emerald-600 hover:text-emerald-700">
                                    + Tambah Aturan
                                </button>
                            </div>

                            @foreach($rules as $index => $rule)
                                <div class="mb-2 flex items-center gap-2">
                                    <select wire:model="rules.{{ $index }}.rule_type"
                                            class="flex-1 rounded-lg border border-gray-300 px-2 py-1.5 text-xs">
                                        @foreach($ruleTypes as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <select wire:model="rules.{{ $index }}.operator"
                                            class="flex-1 rounded-lg border border-gray-300 px-2 py-1.5 text-xs">
                                        @foreach($operators as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" wire:model="rules.{{ $index }}.value" placeholder="Nilai"
                                           class="flex-1 rounded-lg border border-gray-300 px-2 py-1.5 text-xs">
                                    <button type="button" wire:click="removeRule({{ $index }})"
                                            class="rounded-lg p-1.5 text-red-500 hover:bg-red-50">
                                        <i class="fa-solid fa-trash text-xs"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>

                        <!-- Submit -->
                        <div class="flex justify-end gap-3 pt-4">
                            <button type="button" wire:click="closeModal"
                                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">
                                Batal
                            </button>
                            <button type="submit"
                                    class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                                {{ $showEditModal ? 'Simpan Perubahan' : 'Buat Kampanye' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Detail Modal -->
    @if($showDetailModal && $selectedCampaign)
        <div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ show: true }" x-show="show"
             x-on:keydown.escape.window="show = false; $wire.closeModal()">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/50 transition-opacity" x-on:click="show = false; $wire.closeModal()"></div>

                <div class="relative w-full max-w-3xl rounded-2xl bg-white p-6 shadow-xl"
                     x-show="show"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100">

                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-gray-900">Detail Kampanye</h2>
                        <button wire:click="closeModal" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>

                    <div class="space-y-6">
                        <!-- Campaign Info -->
                        <div class="rounded-lg bg-gray-50 p-4">
                            <div class="flex items-center gap-3 mb-4">
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium
                                    {{ $selectedCampaign->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-700' }}">
                                    {{ $selectedCampaign->status_label }}
                                </span>
                                <span class="text-sm text-gray-500">{{ $selectedCampaign->type_label }}</span>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900">{{ $selectedCampaign->name }}</h3>
                            <p class="mt-1 text-sm text-gray-500">{{ $selectedCampaign->description ?? 'Tidak ada deskripsi' }}</p>
                        </div>

                        <!-- Analytics -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="rounded-lg bg-white p-4 border border-gray-200 text-center">
                                <div class="text-2xl font-bold text-gray-900">{{ $selectedCampaign->total_vouchers_generated }}</div>
                                <div class="text-xs text-gray-500">Di Generate</div>
                            </div>
                            <div class="rounded-lg bg-white p-4 border border-gray-200 text-center">
                                <div class="text-2xl font-bold text-emerald-600">{{ $selectedCampaign->total_vouchers_claimed }}</div>
                                <div class="text-xs text-gray-500">Diklaim</div>
                            </div>
                            <div class="rounded-lg bg-white p-4 border border-gray-200 text-center">
                                <div class="text-2xl font-bold text-blue-600">{{ $selectedCampaign->total_vouchers_used }}</div>
                                <div class="text-xs text-gray-500">Dipakai</div>
                            </div>
                            <div class="rounded-lg bg-white p-4 border border-gray-200 text-center">
                                <div class="text-2xl font-bold text-purple-600">{{ $selectedCampaign->conversion_rate }}%</div>
                                <div class="text-xs text-gray-500">Conversion</div>
                            </div>
                        </div>

                        <!-- Budget -->
                        @if($selectedCampaign->budget_limit)
                            <div>
                                <div class="mb-2 flex items-center justify-between text-sm">
                                    <span class="text-gray-500">Budget</span>
                                    <span class="font-medium">Rp {{ number_format($selectedCampaign->budget_used, 0, ',', '.') }} / Rp {{ number_format($selectedCampaign->budget_limit, 0, ',', '.') }}</span>
                                </div>
                                <div class="h-2 w-full rounded-full bg-gray-200">
                                    <div class="h-2 rounded-full bg-emerald-500"
                                         style="width: {{ min(100, ($selectedCampaign->budget_used / $selectedCampaign->budget_limit) * 100) }}%"></div>
                                </div>
                            </div>
                        @endif

                        <!-- Dates -->
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500">Tanggal Mulai</span>
                                <p class="font-medium">{{ $selectedCampaign->start_date->format('d M Y, H:i') }}</p>
                            </div>
                            <div>
                                <span class="text-gray-500">Tanggal Berakhir</span>
                                <p class="font-medium">{{ $selectedCampaign->end_date->format('d M Y, H:i') }}</p>
                            </div>
                        </div>

                        <!-- Rules -->
                        @if($selectedCampaign->rules->isNotEmpty())
                            <div>
                                <h4 class="mb-2 text-sm font-medium text-gray-700">Aturan Kampanye</h4>
                                <ul class="space-y-1">
                                    @foreach($selectedCampaign->rules as $rule)
                                        <li class="flex items-center gap-2 text-sm text-gray-600">
                                            <i class="fa-solid fa-check text-emerald-500"></i>
                                            {{ $ruleTypes[$rule->rule_type] ?? $rule->rule_type }}
                                            {{ $operators[$rule->operator] ?? $rule->operator }}
                                            {{ is_array($rule->value) ? implode(', ', $rule->value) : $rule->value }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    <!-- Close Button -->
                    <div class="mt-6 flex justify-end">
                        <button wire:click="closeModal"
                                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
