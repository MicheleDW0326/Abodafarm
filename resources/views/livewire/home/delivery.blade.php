<section class="bg-sky-900 py-16 text-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-semibold">Alur Pengiriman</h2>
        <div class="mt-8 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            @foreach ($deliverySteps as $index => $step)
                <article class="rounded-2xl border border-white/20 bg-white/10 p-5 backdrop-blur">
                    <span class="text-xs font-semibold tracking-[0.25em] text-sky-200">TAHAP {{ $index + 1 }}</span>
                    <h3 class="mt-2 text-lg font-semibold">{{ $step['title'] }}</h3>
                    <p class="mt-2 text-sm text-sky-100">{{ $step['description'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
