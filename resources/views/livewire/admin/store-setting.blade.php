{{-- Single root wrapper for Livewire --}}
<div>
    <div wire:ignore x-data="{ activeTab: @entangle('activeTab') }" class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500">Kelola konten website: testimonials, FAQs, hero slides, statistik, dan area pengiriman. Untuk kategori produk, lihat di Manajemen Produk.</p>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="flex flex-wrap gap-1 rounded-xl bg-slate-100 p-1">
            <button @click="activeTab = 'testimonials'" :class="activeTab === 'testimonials' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-600 hover:text-slate-900'" class="rounded-lg px-4 py-2.5 text-sm font-medium transition-all">
                <i class="fa-solid fa-quote-left mr-2"></i>Testimonials
            </button>
            <button @click="activeTab = 'faqs'" :class="activeTab === 'faqs' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-600 hover:text-slate-900'" class="rounded-lg px-4 py-2.5 text-sm font-medium transition-all">
                <i class="fa-solid fa-circle-question mr-2"></i>FAQs
            </button>
            <button @click="activeTab = 'hero'" :class="activeTab === 'hero' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-600 hover:text-slate-900'" class="rounded-lg px-4 py-2.5 text-sm font-medium transition-all">
                <i class="fa-solid fa-images mr-2"></i>Hero Slides
            </button>
            <button @click="activeTab = 'stats'" :class="activeTab === 'stats' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-600 hover:text-slate-900'" class="rounded-lg px-4 py-2.5 text-sm font-medium transition-all">
                <i class="fa-solid fa-chart-line mr-2"></i>Statistik
            </button>
            <button @click="activeTab = 'delivery'" :class="activeTab === 'delivery' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-600 hover:text-slate-900'" class="rounded-lg px-4 py-2.5 text-sm font-medium transition-all">
                <i class="fa-solid fa-truck mr-2"></i>Area Pengiriman
            </button>
        </div>

        {{-- ============ TESTIMONIALS ============ --}}
        <div x-show="activeTab === 'testimonials'" x-transition class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">Testimonials</h3>
                    <p class="text-sm text-slate-500">Kelola testimonial dari pelanggan</p>
                </div>
                <button wire:click="openTestimonialModal()" class="flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition-all hover:bg-emerald-700">
                    <i class="fa-solid fa-plus"></i>Tambah
                </button>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @forelse($testimonials as $t)
                    <div class="rounded-xl border border-slate-200 bg-white p-5">
                        <div class="mb-3 flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                @if($t->image)
                                    <img src="{{ asset('storage/' . $t->image) }}" alt="{{ $t->name }}" class="size-12 rounded-full object-cover" />
                                @else
                                    <div class="flex size-12 items-center justify-center rounded-full bg-emerald-100 text-lg font-bold text-emerald-700">{{ substr($t->name, 0, 2) }}</div>
                                @endif
                                <div>
                                    <h4 class="font-semibold text-slate-900">{{ $t->name }}</h4>
                                    <p class="text-xs text-slate-500">{{ $t->role }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fa-solid fa-star text-xs {{ $i <= $t->rating ? 'text-amber-400' : 'text-slate-200' }}"></i>
                                @endfor
                            </div>
                        </div>
                        <p class="text-sm text-slate-600 line-clamp-3">{{ $t->content }}</p>
                        <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3">
                            @if($t->is_active)
                                <span class="rounded bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">Aktif</span>
                            @else
                                <span class="rounded bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">Nonaktif</span>
                            @endif
                            <div class="flex gap-1">
                                <button wire:click="openTestimonialModal({{ $t->id }})" class="rounded p-1.5 text-slate-400 hover:bg-slate-100 hover:text-blue-600">
                                    <i class="fa-solid fa-edit text-sm"></i>
                                </button>
                                <button wire:click="confirmDelete('testimonial', {{ $t->id }})" class="rounded p-1.5 text-slate-400 hover:bg-red-100 hover:text-red-600">
                                    <i class="fa-solid fa-trash text-sm"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 rounded-xl border border-dashed border-slate-300 py-12 text-center">
                        <i class="fa-solid fa-quote-left text-4xl text-slate-300 mb-3"></i>
                        <p class="text-slate-500">Belum ada testimonial</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ============ FAQs ============ --}}
        <div x-show="activeTab === 'faqs'" x-transition class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">FAQ</h3>
                    <p class="text-sm text-slate-500">Kelola pertanyaan yang sering ditanyakan</p>
                </div>
                <button wire:click="openFaqModal()" class="flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition-all hover:bg-emerald-700">
                    <i class="fa-solid fa-plus"></i>Tambah
                </button>
            </div>

            <div class="space-y-3">
                @forelse($faqs as $faq)
                    <div class="rounded-xl border border-slate-200 bg-white p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <div class="mb-2 flex items-center gap-2">
                                    <span class="rounded bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">{{ $faq->category_label }}</span>
                                    @if($faq->is_active)
                                        <span class="rounded bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">Aktif</span>
                                    @endif
                                </div>
                                <h4 class="font-semibold text-slate-900">{{ $faq->question }}</h4>
                                <p class="mt-2 text-sm text-slate-600">{{ Str::limit($faq->answer, 150) }}</p>
                            </div>
                            <div class="flex gap-1">
                                <button wire:click="openFaqModal({{ $faq->id }})" class="rounded p-2 text-slate-400 hover:bg-slate-100 hover:text-blue-600">
                                    <i class="fa-solid fa-edit"></i>
                                </button>
                                <button wire:click="confirmDelete('faq', {{ $faq->id }})" class="rounded p-2 text-slate-400 hover:bg-red-100 hover:text-red-600">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-300 py-12 text-center">
                        <i class="fa-solid fa-circle-question text-4xl text-slate-300 mb-3"></i>
                        <p class="text-slate-500">Belum ada FAQ</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ============ HERO SLIDES ============ --}}
        <div x-show="activeTab === 'hero'" x-transition class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">Hero Slides</h3>
                    <p class="text-sm text-slate-500">Banner utama di halaman depan</p>
                </div>
                <button wire:click="openHeroSlideModal()" class="flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition-all hover:bg-emerald-700">
                    <i class="fa-solid fa-plus"></i>Tambah
                </button>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @forelse($heroSlides as $slide)
                    <div class="group relative overflow-hidden rounded-xl border border-slate-200 bg-white">
                        <img src="{{ asset('storage/' . $slide->image) }}" alt="{{ $slide->title }}" class="h-48 w-full object-cover transition-transform group-hover:scale-105" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
                        <div class="absolute bottom-0 left-0 right-0 p-4 text-white">
                            <h4 class="font-semibold">{{ $slide->title }}</h4>
                            <p class="text-xs text-white/80">{{ Str::limit($slide->subtitle, 50) }}</p>
                            <div class="mt-2 flex items-center justify-between">
                                @if($slide->is_active)
                                    <span class="rounded bg-green-500/80 px-2 py-0.5 text-xs">Aktif</span>
                                @else
                                    <span class="rounded bg-slate-500/80 px-2 py-0.5 text-xs">Nonaktif</span>
                                @endif
                                <div class="flex gap-1">
                                    <button wire:click="openHeroSlideModal({{ $slide->id }})" class="rounded bg-white/20 p-1.5 hover:bg-white/40">
                                        <i class="fa-solid fa-edit text-sm"></i>
                                    </button>
                                    <button wire:click="confirmDelete('heroSlide', {{ $slide->id }})" class="rounded bg-red-500/80 p-1.5 hover:bg-red-600">
                                        <i class="fa-solid fa-trash text-sm"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 rounded-xl border border-dashed border-slate-300 py-12 text-center">
                        <i class="fa-solid fa-images text-4xl text-slate-300 mb-3"></i>
                        <p class="text-slate-500">Belum ada hero slide</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ============ STATISTICS ============ --}}
        <div x-show="activeTab === 'stats'" x-transition class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">Statistik Farm</h3>
                    <p class="text-sm text-slate-500">Statistik yang ditampilkan di halaman depan</p>
                </div>
                <button wire:click="openStatisticModal()" class="flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition-all hover:bg-emerald-700">
                    <i class="fa-solid fa-plus"></i>Tambah
                </button>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                @forelse($statistics as $stat)
                    <div class="rounded-xl border border-slate-200 bg-white p-5">
                        <div class="mb-3 flex items-center justify-between">
                            @if($stat->icon)
                                <div class="rounded-lg bg-emerald-100 p-2 text-emerald-600">
                                    <i class="{{ $stat->icon }}"></i>
                                </div>
                            @endif
                            <div class="flex gap-1">
                                <button wire:click="openStatisticModal({{ $stat->id }})" class="rounded p-1 text-slate-400 hover:bg-slate-100 hover:text-blue-600">
                                    <i class="fa-solid fa-edit text-sm"></i>
                                </button>
                                <button wire:click="confirmDelete('statistic', {{ $stat->id }})" class="rounded p-1 text-slate-400 hover:bg-red-100 hover:text-red-600">
                                    <i class="fa-solid fa-trash text-sm"></i>
                                </button>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-slate-900">{{ $stat->value }}</p>
                        <p class="text-sm text-slate-500">{{ $stat->label }}</p>
                        <p class="mt-2 text-xs text-slate-400">key: {{ $stat->key }}</p>
                    </div>
                @empty
                    <div class="col-span-4 rounded-xl border border-dashed border-slate-300 py-12 text-center">
                        <i class="fa-solid fa-chart-line text-4xl text-slate-300 mb-3"></i>
                        <p class="text-slate-500">Belum ada statistik</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ============ DELIVERY AREAS ============ --}}
        <div x-show="activeTab === 'delivery'" x-transition class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">Area Pengiriman</h3>
                    <p class="text-sm text-slate-500">Kelola wilayah dan ongkir pengiriman</p>
                </div>
                <button wire:click="openDeliveryAreaModal()" class="flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition-all hover:bg-emerald-700">
                    <i class="fa-solid fa-plus"></i>Tambah
                </button>
            </div>

            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-900">Wilayah</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-900">Kota</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-900">Ongkir</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-900">Estimasi</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-900">Status</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-900">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($deliveryAreas as $area)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-medium text-slate-900">{{ $area->region_name }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $area->city ?? '-' }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-slate-900">{{ $area->formatted_fee }}</td>
                                <td class="px-4 py-3 text-center text-slate-600">{{ $area->estimated_delivery_label }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if($area->is_active)
                                        <span class="rounded bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">Aktif</span>
                                    @else
                                        <span class="rounded bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button wire:click="openDeliveryAreaModal({{ $area->id }})" class="rounded p-1.5 text-slate-400 hover:bg-slate-100 hover:text-blue-600">
                                            <i class="fa-solid fa-edit text-sm"></i>
                                        </button>
                                        <button wire:click="confirmDelete('deliveryArea', {{ $area->id }})" class="rounded p-1.5 text-slate-400 hover:bg-red-100 hover:text-red-600">
                                            <i class="fa-solid fa-trash text-sm"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <i class="fa-solid fa-truck text-4xl text-slate-300 mb-3"></i>
                                    <p class="text-slate-500">Belum ada area pengiriman</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ==================== MODALS ==================== --}}

    {{-- Testimonial Modal --}}
    <template x-teleport="body">
    <div x-show="$wire.showTestimonialModal" x-transition.opacity class="fixed inset-0 z-[99999]">
        <div class="absolute inset-0 bg-black/50" @click="$wire.closeTestimonialModal()"></div>
        <div class="absolute left-1/2 top-1/2 w-full max-w-lg -translate-x-1/2 -translate-y-1/2 rounded-xl bg-white shadow-xl overflow-hidden" @click.stop>
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <h3 class="text-lg font-semibold">{{ $editingTestimonialId ? 'Edit' : 'Tambah' }} Testimonial</h3>
                <button @click="$wire.closeTestimonialModal()" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
            <form wire:submit.prevent="saveTestimonial" class="space-y-4 px-6 py-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-900">Nama *</label>
                        <input type="text" wire:model.live="testimonialName" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-900">Role *</label>
                        <input type="text" wire:model.live="testimonialRole" placeholder="Contoh: Owner Restoran" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" />
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-900">Konten *</label>
                    <textarea rows="3" wire:model.live="testimonialContent" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"></textarea>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-900">Rating</label>
                    <div class="flex gap-1">
                        @for($i = 1; $i <= 5; $i++)
                            <button type="button" wire:click="$set('testimonialRating', {{ $i }})">
                                <i class="fa-solid fa-star text-2xl {{ $i <= $testimonialRating ? 'text-amber-400' : 'text-slate-200' }} hover:text-amber-400"></i>
                            </button>
                        @endfor
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-900">Foto</label>
                    <input type="file" wire:model.live="testimonialImage" accept="image/*" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm file:mr-3 file:rounded file:border-0 file:bg-emerald-100 file:text-emerald-700 file:cursor-pointer" />
                    @if($testimonialImage?->temporaryUrl())
                        <img src="{{ $testimonialImage->temporaryUrl() }}" class="mt-2 h-16 w-16 rounded-full object-cover" />
                    @elseif($testimonialImagePath)
                        <img src="{{ asset('storage/' . $testimonialImagePath) }}" class="mt-2 h-16 w-16 rounded-full object-cover" />
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" wire:model.live="testimonialIsActive" id="tIsActive" class="size-4 rounded border-slate-300" />
                    <label for="tIsActive" class="text-sm text-slate-900">Aktif</label>
                </div>
                <div class="flex gap-3 border-t pt-4">
                    <button type="button" @click="$wire.closeTestimonialModal()" class="flex-1 rounded-lg border border-slate-200 py-2.5 text-sm font-semibold">Batal</button>
                    <button type="submit" class="flex-1 rounded-lg bg-emerald-600 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    </template>

    {{-- FAQ Modal --}}
    <template x-teleport="body">
    <div x-show="$wire.showFaqModal" x-transition.opacity class="fixed inset-0 z-[99999]">
        <div class="absolute inset-0 bg-black/50" @click="$wire.closeFaqModal()"></div>
        <div class="absolute left-1/2 top-1/2 w-full max-w-lg -translate-x-1/2 -translate-y-1/2 rounded-xl bg-white shadow-xl overflow-hidden" @click.stop>
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <h3 class="text-lg font-semibold">{{ $editingFaqId ? 'Edit' : 'Tambah' }} FAQ</h3>
                <button @click="$wire.closeFaqModal()" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
            <form wire:submit.prevent="saveFaq" class="space-y-4 px-6 py-4">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-900">Kategori</label>
                    <select wire:model.live="faqCategory" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        <option value="product">Produk</option>
                        <option value="order">Pemesanan</option>
                        <option value="delivery">Pengiriman</option>
                        <option value="payment">Pembayaran</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-900">Pertanyaan *</label>
                    <input type="text" wire:model.live="faqQuestion" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-900">Jawaban *</label>
                    <textarea rows="4" wire:model.live="faqAnswer" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"></textarea>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" wire:model.live="faqIsActive" id="faqIsActive" class="size-4 rounded border-slate-300" />
                    <label for="faqIsActive" class="text-sm text-slate-900">Aktif</label>
                </div>
                <div class="flex gap-3 border-t pt-4">
                    <button type="button" @click="$wire.closeFaqModal()" class="flex-1 rounded-lg border border-slate-200 py-2.5 text-sm font-semibold">Batal</button>
                    <button type="submit" class="flex-1 rounded-lg bg-emerald-600 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    </template>

    {{-- Hero Slide Modal --}}
    <template x-teleport="body">
    <div x-show="$wire.showHeroSlideModal" x-transition.opacity class="fixed inset-0 z-[99999]">
        <div class="absolute inset-0 bg-black/50" @click="$wire.closeHeroSlideModal()"></div>
        <div class="absolute left-1/2 top-1/2 w-full max-w-lg -translate-x-1/2 -translate-y-1/2 rounded-xl bg-white shadow-xl overflow-hidden" @click.stop>
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <h3 class="text-lg font-semibold">{{ $editingHeroSlideId ? 'Edit' : 'Tambah' }} Hero Slide</h3>
                <button @click="$wire.closeHeroSlideModal()" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
            <form wire:submit.prevent="saveHeroSlide" class="space-y-4 px-6 py-4">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-900">Judul *</label>
                    <input type="text" wire:model.live="heroSlideTitle" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-900">Subtitle</label>
                    <textarea rows="2" wire:model.live="heroSlideSubtitle" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"></textarea>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-900">Gambar {{ $editingHeroSlideId ? '' : '*' }}</label>
                    <input type="file" wire:model.live="heroSlideImage" accept="image/*" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm file:mr-3 file:rounded file:border-0 file:bg-emerald-100 file:text-emerald-700 file:cursor-pointer" />
                    @if($heroSlideImage?->temporaryUrl())
                        <img src="{{ $heroSlideImage->temporaryUrl() }}" class="mt-2 h-32 w-full rounded-lg object-cover" />
                    @elseif($heroSlideImagePath)
                        <img src="{{ asset('storage/' . $heroSlideImagePath) }}" class="mt-2 h-32 w-full rounded-lg object-cover" />
                    @endif
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-900">Button Text</label>
                        <input type="text" wire:model.live="heroSlideButtonText" placeholder="Lihat Koleksi" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-900">Button URL</label>
                        <input type="text" wire:model.live="heroSlideButtonUrl" placeholder="#products" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" />
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" wire:model.live="heroSlideIsActive" id="hIsActive" class="size-4 rounded border-slate-300" />
                    <label for="hIsActive" class="text-sm text-slate-900">Aktif</label>
                </div>
                <div class="flex gap-3 border-t pt-4">
                    <button type="button" @click="$wire.closeHeroSlideModal()" class="flex-1 rounded-lg border border-slate-200 py-2.5 text-sm font-semibold">Batal</button>
                    <button type="submit" class="flex-1 rounded-lg bg-emerald-600 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    </template>

    {{-- Statistic Modal --}}
    <template x-teleport="body">
    <div x-show="$wire.showStatisticModal" x-transition.opacity class="fixed inset-0 z-[99999]">
        <div class="absolute inset-0 bg-black/50" @click="$wire.closeStatisticModal()"></div>
        <div class="absolute left-1/2 top-1/2 w-full max-w-md -translate-x-1/2 -translate-y-1/2 rounded-xl bg-white shadow-xl overflow-hidden" @click.stop>
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <h3 class="text-lg font-semibold">{{ $editingStatisticId ? 'Edit' : 'Tambah' }} Statistik</h3>
                <button @click="$wire.closeStatisticModal()" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
            <form wire:submit.prevent="saveStatistic" class="space-y-4 px-6 py-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-900">Key *</label>
                        <input type="text" wire:model.live="statisticKey" placeholder="ternak_terjual" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-900">Value *</label>
                        <input type="text" wire:model.live="statisticValue" placeholder="500+" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" />
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-900">Label *</label>
                    <input type="text" wire:model.live="statisticLabel" placeholder="Ternak Terjual" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-900">Icon (FontAwesome)</label>
                    <input type="text" wire:model.live="statisticIcon" placeholder="fa-solid fa-cow" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" />
                </div>
                <div class="flex gap-3 border-t pt-4">
                    <button type="button" @click="$wire.closeStatisticModal()" class="flex-1 rounded-lg border border-slate-200 py-2.5 text-sm font-semibold">Batal</button>
                    <button type="submit" class="flex-1 rounded-lg bg-emerald-600 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    </template>

    {{-- Delivery Area Modal --}}
    <template x-teleport="body">
    <div x-show="$wire.showDeliveryAreaModal" x-transition.opacity class="fixed inset-0 z-[99999]">
        <div class="absolute inset-0 bg-black/50" @click="$wire.closeDeliveryAreaModal()"></div>
        <div class="absolute left-1/2 top-1/2 w-full max-w-md -translate-x-1/2 -translate-y-1/2 rounded-xl bg-white shadow-xl overflow-hidden" @click.stop>
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <h3 class="text-lg font-semibold">{{ $editingDeliveryAreaId ? 'Edit' : 'Tambah' }} Area Pengiriman</h3>
                <button @click="$wire.closeDeliveryAreaModal()" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
            <form wire:submit.prevent="saveDeliveryArea" class="space-y-4 px-6 py-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-900">Nama Wilayah *</label>
                        <input type="text" wire:model.live="deliveryAreaRegion" placeholder="Kota Manado" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-900">Kota</label>
                        <input type="text" wire:model.live="deliveryAreaCity" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" />
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-900">Provinsi</label>
                    <input type="text" wire:model.live="deliveryAreaProvince" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-900">Ongkir (Rp) *</label>
                        <input type="number" wire:model.live="deliveryAreaFee" min="0" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-900">Estimasi (Hari)</label>
                        <input type="number" wire:model.live="deliveryAreaEstimatedDays" min="1" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" />
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" wire:model.live="deliveryAreaIsActive" id="dIsActive" class="size-4 rounded border-slate-300" />
                    <label for="dIsActive" class="text-sm text-slate-900">Aktif</label>
                </div>
                <div class="flex gap-3 border-t pt-4">
                    <button type="button" @click="$wire.closeDeliveryAreaModal()" class="flex-1 rounded-lg border border-slate-200 py-2.5 text-sm font-semibold">Batal</button>
                    <button type="submit" class="flex-1 rounded-lg bg-emerald-600 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    </template>
</div>
