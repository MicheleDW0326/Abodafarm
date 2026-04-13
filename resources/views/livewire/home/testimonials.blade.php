<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="mb-8 text-center">
        <h2 class="text-3xl font-semibold">Ulasan Pelanggan</h2>
        <p class="mt-2 text-slate-600">Cerita singkat dari pelanggan yang sudah rutin berbelanja.</p>
    </div>

    <div class="grid gap-5 md:grid-cols-3">
        @foreach ($testimonials as $testimonial)
            <article class="rounded-2xl border border-slate-100 bg-white p-6 shadow-md">
                <p class="text-slate-700">"{{ $testimonial['quote'] }}"</p>
                <div class="mt-4 border-t border-slate-100 pt-4">
                    <p class="font-semibold">{{ $testimonial['name'] }}</p>
                    <p class="text-sm text-slate-500">{{ $testimonial['role'] }}</p>
                </div>
            </article>
        @endforeach
    </div>
</section>
