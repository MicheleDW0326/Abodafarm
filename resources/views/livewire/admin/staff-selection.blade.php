<div>
    {{-- Modal --}}
    @if($showModal)
        <template x-teleport="body">
            <div x-show="$wire.showModal" x-transition.opacity.duration.300ms class="fixed inset-0 z-[99999]">
                <div class="absolute inset-0 bg-black/50" @click="$wire.closeModal()" @keydown.escape.window="$wire.closeModal()"></div>

                <div class="absolute left-1/2 top-1/2 w-full max-w-lg -translate-x-1/2 -translate-y-1/2 rounded-xl bg-white shadow-xl"
                     @click.stop>
                    {{-- Header --}}
                    <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">
                                <i class="fa-solid fa-user-plus mr-2 text-emerald-600"></i>
                                Tugaskan Staff
                            </h3>
                            @if($order)
                                <p class="mt-1 text-sm text-slate-500">
                                    Pesanan: <span class="font-medium text-slate-700">{{ $order->order_number }}</span>
                                </p>
                            @endif
                        </div>
                        <button
                            @click="$wire.closeModal()"
                            class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors"
                        >
                            <i class="fa-solid fa-times text-lg"></i>
                        </button>
                    </div>

                    {{-- Search --}}
                    <div class="border-b border-slate-200 px-6 py-4">
                        <div class="relative">
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="searchQuery"
                                placeholder="Cari staff berdasarkan nama atau telepon..."
                                class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                            />
                            <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            @if($searchQuery)
                                <button
                                    wire:click="$set('searchQuery', '')"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                                >
                                    <i class="fa-solid fa-times-circle"></i>
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- Staff List --}}
                    <div class="max-h-80 overflow-y-auto p-4">
                        @if($availableStaff->isEmpty())
                            <div class="py-8 text-center">
                                <div class="rounded-full bg-slate-100 p-4 text-slate-300 mb-3 inline-block">
                                    <i class="fa-solid fa-users-slash text-3xl"></i>
                                </div>
                                <p class="text-sm text-slate-500">
                                    @if($searchQuery)
                                        Tidak ada staff yang cocok dengan pencarian.
                                    @else
                                        Tidak ada staff yang tersedia.
                                    @endif
                                </p>
                            </div>
                        @else
                            <div class="space-y-2">
                                @foreach($availableStaff as $staff)
                                    <button
                                        type="button"
                                        wire:click="selectStaff({{ $staff['id'] }})"
                                        class="w-full flex items-center gap-4 rounded-xl border-2 p-4 text-left transition-all
                                               {{ $selectedStaff?->id === $staff['id']
                                                   ? 'border-emerald-500 bg-emerald-50'
                                                   : 'border-slate-200 bg-white hover:border-emerald-200 hover:bg-emerald-50/50' }}"
                                    >
                                        {{-- Avatar --}}
                                        <div class="flex-shrink-0">
                                            <div class="size-12 rounded-full bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center text-white font-bold text-lg">
                                                {{ $staff['initials'] }}
                                            </div>
                                        </div>

                                        {{-- Info --}}
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <p class="font-semibold text-slate-900">{{ $staff['name'] }}</p>
                                                @if($selectedStaff?->id === $staff['id'])
                                                    <i class="fa-solid fa-check-circle text-emerald-500"></i>
                                                @endif
                                            </div>
                                            <p class="text-sm text-slate-500">{{ $staff['phone'] }}</p>
                                            <div class="mt-1 flex items-center gap-3 text-xs">
                                                <span class="inline-flex items-center gap-1 text-slate-500">
                                                    <i class="fa-solid fa-list-check text-slate-400"></i>
                                                    {{ $staff['active_orders'] }} pesanan aktif
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Status Badge --}}
                                        <div class="flex-shrink-0">
                                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold
                                                         {{ $staff['status'] === 'available'
                                                             ? 'bg-emerald-100 text-emerald-700'
                                                             : 'bg-amber-100 text-amber-700' }}">
                                                <i class="fa-solid {{ $staff['status'] === 'available' ? 'fa-check-circle mr-1' : 'fa-clock mr-1' }}"></i>
                                                {{ $staff['status_label'] }}
                                            </span>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Footer --}}
                    <div class="flex items-center justify-end gap-3 border-t border-slate-200 px-6 py-4 bg-slate-50 rounded-b-xl">
                        <button
                            wire:click="closeModal()"
                            class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-100"
                        >
                            Batal
                        </button>
                        <button
                            wire:click="assignStaff()"
                            @if(!$selectedStaff) disabled @endif
                            class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-emerald-700 disabled:bg-slate-300 disabled:cursor-not-allowed flex items-center gap-2"
                        >
                            <i class="fa-solid fa-user-plus"></i>
                            Tugaskan Staff
                        </button>
                    </div>
                </div>
            </div>
        </template>
    @endif
</div>
