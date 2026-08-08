<section id="{{ $sectionId }}" class="py-12 {{ $sectionId ? '' : '' }}">
    @if(count($products) > 0)
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="mb-6 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl {{ $iconColor }}">
                        <i class="{{ $icon }}"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">{{ $title }}</h2>
                        @if($subtitle)
                            <p class="text-sm text-slate-500">{{ $subtitle }}</p>
                        @endif
                    </div>
                </div>
                @if($showViewAll)
                    <a href="{{ $viewAllUrl }}"
                       class="inline-flex items-center gap-1 text-sm font-semibold text-emerald-600 transition hover:text-emerald-700">
                        Lihat Semua
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </a>
                @endif
            </div>

            <!-- Product Carousel -->
            <div class="relative"
                 x-data="{
                     isDragging: false,
                     startX: 0,
                     scrollLeft: 0,
                     carousel: null,

                     init() {
                         this.carousel = this.$refs.carousel;
                         this.setupDragScroll();
                     },

                     setupDragScroll() {
                         const carousel = this.$refs.carousel;

                         carousel.addEventListener('mousedown', (e) => {
                             this.isDragging = true;
                             this.startX = e.pageX - carousel.offsetLeft;
                             this.scrollLeft = carousel.scrollLeft;
                             carousel.style.cursor = 'grabbing';
                         });

                         carousel.addEventListener('mouseleave', () => {
                             this.isDragging = false;
                             carousel.style.cursor = 'grab';
                         });

                         carousel.addEventListener('mouseup', () => {
                             this.isDragging = false;
                             carousel.style.cursor = 'grab';
                         });

                         carousel.addEventListener('mousemove', (e) => {
                             if (!this.isDragging) return;
                             e.preventDefault();
                             const x = e.pageX - carousel.offsetLeft;
                             const walk = (x - this.startX) * 1.5;
                             carousel.scrollLeft = this.scrollLeft - walk;
                         });
                     },

                     scrollLeft() {
                         this.$refs.carousel.scrollBy({ left: -300, behavior: 'smooth' });
                     },

                     scrollRight() {
                         this.$refs.carousel.scrollBy({ left: 300, behavior: 'smooth' });
                     }
                 }">

                <!-- Scroll Buttons -->
                <button @click="scrollLeft()"
                        class="absolute left-0 top-1/2 z-10 hidden -translate-y-1/2 rounded-full bg-white p-2 shadow-lg transition hover:bg-slate-50 lg:block">
                    <i class="fa-solid fa-chevron-left text-slate-600"></i>
                </button>
                <button @click="scrollRight()"
                        class="absolute right-0 top-1/2 z-10 hidden -translate-y-1/2 rounded-full bg-white p-2 shadow-lg transition hover:bg-slate-50 lg:block">
                    <i class="fa-solid fa-chevron-right text-slate-600"></i>
                </button>

                <!-- Products Grid (Horizontal Scroll) -->
                <div x-ref="carousel"
                     class="flex gap-4 overflow-x-auto pb-4 scrollbar-hide"
                     style="cursor: grab;">
                    @foreach($products as $product)
                        <article class="group shrink-0 w-48 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-all duration-300 hover:shadow-lg hover:-translate-y-1"
                                 wire:key="product-{{ $product['id'] }}">
                            <a href="{{ route('shop.product', $product['slug']) }}" class="block">
                                <div class="aspect-square overflow-hidden bg-slate-100">
                                    @if($product['image'])
                                        <img src="{{ $product['image'] }}"
                                             alt="{{ $product['name'] }}"
                                             class="size-full object-cover transition-transform duration-500 group-hover:scale-105"
                                             loading="lazy">
                                    @else
                                        <div class="flex size-full items-center justify-center">
                                            <i class="fa-solid fa-box text-3xl text-slate-300"></i>
                                        </div>
                                    @endif

                                    <!-- Discount Badge -->
                                    @if($product['has_discount'])
                                        <span class="absolute left-2 top-2 rounded-lg bg-red-500 px-2 py-1 text-[10px] font-bold text-white">
                                            -{{ $product['discount_percentage'] }}%
                                        </span>
                                    @endif

                                    <!-- Stock Status -->
                                    @if($product['stock_status'] === 'out_of_stock')
                                        <div class="absolute inset-0 flex items-center justify-center bg-black/40">
                                            <span class="rounded-full bg-red-500 px-3 py-1 text-xs font-bold text-white">Stok Habis</span>
                                        </div>
                                    @endif
                                </div>
                            </a>
                            <div class="p-3">
                                <!-- Category -->
                                @if($product['category'])
                                    <p class="text-[10px] font-medium text-emerald-600 uppercase tracking-wide">{{ $product['category'] }}</p>
                                @endif

                                <!-- Name -->
                                <a href="{{ route('shop.product', $product['slug']) }}">
                                    <h3 class="mt-1 line-clamp-2 text-sm font-semibold text-slate-900 transition group-hover:text-emerald-600">
                                        {{ $product['name'] }}
                                    </h3>
                                </a>

                                <!-- Price -->
                                <div class="mt-2 flex items-center gap-2">
                                    <span class="text-sm font-bold text-emerald-600">{{ $product['price'] }}</span>
                                    @if($product['has_discount'])
                                        <span class="text-xs text-slate-400 line-through">{{ $product['original_price'] }}</span>
                                    @endif
                                </div>

                                <!-- Rating -->
                                @if($product['rating'] && $product['rating'] > 0)
                                    <div class="mt-2 flex items-center gap-1">
                                        <i class="fa-solid fa-star text-xs text-amber-400"></i>
                                        <span class="text-xs font-medium text-slate-600">{{ number_format($product['rating'], 1) }}</span>
                                        <span class="text-xs text-slate-400">({{ $product['total_reviews'] }})</span>
                                    </div>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</section>
