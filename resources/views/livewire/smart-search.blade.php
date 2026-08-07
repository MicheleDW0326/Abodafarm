<div class="relative" x-data="{ open: false }" @click.away="open = false" @keydown.escape="open = false">
    <!-- Search Input -->
    <div class="relative">
        <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
            <x-icon name="svg-search" class="w-4 h-4" />
        </span>
        <input type="text"
               wire:model.live.debounce.250ms="query"
               @focus="open = $wire.query.length >= 2 || true"
               @input="open = $wire.query.length >= 2"
               placeholder="Cari produk, kategori, peternak..."
               class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-12 text-sm text-slate-700 outline-none transition-all placeholder:text-slate-400 focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100/50 lg:h-11">

        <!-- Search Button -->
        <button wire:click="searchAll"
                @if(mb_strlen($query) < 2) disabled @endif
                class="absolute right-1 top-1/2 -translate-y-1/2 rounded-lg bg-emerald-600 p-1.5 text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50">
            <x-icon name="svg-search" class="w-4 h-4" />
        </button>
    </div>

    <!-- Dropdown Results -->
    @if($isOpen)
        <div x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-2"
             class="absolute left-0 right-0 top-full z-50 mt-2 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">

            <div class="max-h-[70vh] overflow-y-auto overscroll-contain">
                @if(count($categories) > 0 || count($products) > 0 || count($farms) > 0)
                    <!-- Categories Section -->
                    @if(count($categories) > 0)
                        <div class="border-b border-slate-100">
                            <div class="flex items-center gap-2 px-4 py-2.5 bg-slate-50">
                                <i class="fa-solid fa-tags text-xs text-slate-400"></i>
                                <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">Kategori</span>
                            </div>
                            @foreach($categories as $item)
                                <button wire:click="selectCategory('{{ $item['slug'] }}')"
                                        class="flex w-full items-center gap-3 px-4 py-3 text-left transition hover:bg-emerald-50">
                                    <div class="flex size-9 items-center justify-center rounded-lg bg-purple-100 text-purple-600">
                                        <i class="fa-solid fa-folder text-sm"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-slate-900 truncate">
                                            {!! $this->highlight($item['name']) !!}
                                        </p>
                                        @if($item['description'])
                                            <p class="text-xs text-slate-500 truncate">{!! $this->highlight($item['description']) !!}</p>
                                        @endif
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-xs text-slate-300"></i>
                                </button>
                            @endforeach
                        </div>
                    @endif

                    <!-- Products Section -->
                    @if(count($products) > 0)
                        <div class="border-b border-slate-100">
                            <div class="flex items-center gap-2 px-4 py-2.5 bg-slate-50">
                                <i class="fa-solid fa-box text-xs text-slate-400"></i>
                                <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">Produk</span>
                            </div>
                            @foreach($products as $item)
                                <button wire:click="selectProduct('{{ $item['slug'] }}')"
                                        class="flex w-full items-center gap-3 px-4 py-3 text-left transition hover:bg-emerald-50">
                                    @if($item['image'])
                                        <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}"
                                             class="size-10 rounded-lg object-cover bg-slate-100">
                                    @else
                                        <div class="flex size-10 items-center justify-center rounded-lg bg-slate-100 text-slate-400">
                                            <i class="fa-solid fa-image text-sm"></i>
                                        </div>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-slate-900 truncate">
                                            {!! $this->highlight($item['name']) !!}
                                        </p>
                                        <p class="text-xs text-slate-500">
                                            {{ $item['category'] ?? '' }}
                                            @if($item['category'])
                                                <span class="mx-1">·</span>
                                            @endif
                                            <span class="font-semibold text-emerald-600">{{ $item['price'] }}</span>
                                        </p>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-xs text-slate-300"></i>
                                </button>
                            @endforeach
                        </div>
                    @endif

                    <!-- Farms Section -->
                    @if(count($farms) > 0)
                        <div>
                            <div class="flex items-center gap-2 px-4 py-2.5 bg-slate-50">
                                <i class="fa-solid fa-tractor text-xs text-slate-400"></i>
                                <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">Peternak</span>
                            </div>
                            @foreach($farms as $item)
                                <button wire:click="selectFarm({{ $item['id'] }})"
                                        class="flex w-full items-center gap-3 px-4 py-3 text-left transition hover:bg-emerald-50">
                                    @if($item['avatar'])
                                        <img src="{{ $item['avatar'] }}" alt="{{ $item['name'] }}"
                                             class="size-10 rounded-full object-cover bg-slate-100">
                                    @else
                                        <div class="flex size-10 items-center justify-center rounded-full bg-green-100 text-green-600 font-bold">
                                            {{ substr($item['name'], 0, 1) }}
                                        </div>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-slate-900 truncate">
                                            {!! $this->highlight($item['name']) !!}
                                        </p>
                                        @if($item['location'])
                                            <p class="text-xs text-slate-500 truncate">
                                                <i class="fa-solid fa-location-dot mr-1"></i>{!! $this->highlight($item['location']) !!}
                                            </p>
                                        @endif
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-xs text-slate-300"></i>
                                </button>
                            @endforeach
                        </div>
                    @endif
                @else
                    <!-- No Results -->
                    <div class="flex flex-col items-center justify-center py-12 px-4">
                        <div class="flex size-14 items-center justify-center rounded-full bg-slate-100 text-slate-400 mb-4">
                            <i class="fa-solid fa-search text-2xl"></i>
                        </div>
                        <p class="text-sm font-medium text-slate-700">Tidak ditemukan</p>
                        <p class="mt-1 text-xs text-slate-500">Coba kata kunci lain</p>
                    </div>
                @endif
            </div>

            <!-- Footer - View All -->
            @if(count($categories) > 0 || count($products) > 0 || count($farms) > 0)
                <div class="border-t border-slate-100 bg-slate-50 px-4 py-3">
                    <button wire:click="searchAll"
                            class="flex w-full items-center justify-center gap-2 text-sm font-medium text-emerald-600 hover:text-emerald-700">
                        <x-icon name="svg-search" class="w-3.5 h-3.5" />
                        Lihat semua hasil untuk "{{ $query }}"
                    </button>
                </div>
            @endif
        </div>
    @endif
</div>
