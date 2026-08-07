<section class="relative overflow-hidden">
    <div class="pointer-events-none absolute inset-0">
        <div class="absolute -left-24 top-24 size-72 rounded-full bg-emerald-300/30 blur-3xl"></div>
        <div class="absolute -right-24 bottom-0 size-80 rounded-full bg-sky-300/40 blur-3xl"></div>
    </div>

    <div x-data="{
        activeSlide: 0,
        slides: @js($heroSlides),
        timer: null,
        get currentSlideImage() {
            return this.slides[this.activeSlide]?.image || '';
        },
        init() {
            this.timer = setInterval(() => {
                this.nextSlide()
            }, 4500)
        },
        nextSlide() {
            this.activeSlide = (this.activeSlide + 1) % this.slides.length
        },
        goToSlide(index) {
            this.activeSlide = index
        }
    }"
        class="relative mx-auto grid min-h-[78vh] max-w-7xl items-center gap-8 px-4 py-14 sm:px-6 lg:grid-cols-2 lg:py-20 lg:pl-8 lg:pr-12">
        <div class="space-y-6">
            <p
                class="inline-flex rounded-full border border-amber-200 bg-amber-50 px-4 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-amber-800">
                Organic • Traceable • Delivered Fresh
            </p>

            <h1 class="text-4xl font-semibold leading-tight text-slate-900 sm:text-5xl"
                x-transition:enter="transition ease-out duration-500"
                x-transition:enter-start="opacity-0 translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-4"
                x-text="slides[activeSlide].title"></h1>

            <p class="max-w-xl text-base text-slate-600 sm:text-lg"
                x-transition:enter="transition ease-out duration-500 delay-100"
                x-transition:enter-start="opacity-0 translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-4"
                x-text="slides[activeSlide].description"></p>

            <div class="flex flex-wrap items-center gap-3"
                x-transition:enter="transition ease-out duration-500 delay-200"
                x-transition:enter-start="opacity-0 translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-4">
                <a href="#produk"
                    class="rounded-xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-xl shadow-emerald-300/70 transition hover:-translate-y-0.5 hover:bg-emerald-700">Belanja
                    Ternak</a>
                <a href="#keunggulan"
                    class="rounded-xl border border-emerald-200 bg-white px-5 py-3 text-sm font-semibold transition hover:bg-emerald-50">Lihat
                    Traceability</a>
            </div>

            <div class="flex gap-2 pt-2">
                <template x-for="(slide, index) in slides" :key="index">
                    <button @click="goToSlide(index)" class="h-2.5 rounded-full transition-all duration-300"
                        :class="activeSlide === index ? 'w-10 bg-emerald-600' : 'w-2.5 bg-emerald-200'"></button>
                </template>
            </div>
        </div>

        <div class="relative">
            <div class="absolute -inset-4 -z-10 rounded-4xl bg-linear-to-br from-sky-400/20 to-emerald-300/20 blur-2xl">
            </div>
            <div
                x-transition:enter="transition ease-out duration-700"
                x-transition:enter-start="opacity-0 scale-105"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-500"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
            >
                <x-image
                    x-bind:src="currentSlideImage"
                    alt="Hero Banner"
                    type="hero"
                    width="800"
                    height="600"
                    loading="eager"
                    decoding="async"
                    class="h-96 w-full rounded-4xl object-cover shadow-2xl shadow-slate-300/70 sm:h-115"
                />
            </div>
        </div>
    </div>
</section>
