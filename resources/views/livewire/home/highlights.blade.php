<section id="keunggulan" class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="mb-10 text-center">
        <h2 class="text-3xl font-semibold">Kenapa Pilih AbodaFarm</h2>
        <p class="mt-2 text-slate-600">Transparansi peternakan, kualitas terjaga, dan traceability farm-to-table.</p>

    </div>

    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($highlights as $index => $item)
            <article
                class="rounded-2xl border border-emerald-100 bg-linear-to-br from-white to-emerald-50 p-5 shadow-sm"
                data-aos="{{ $index % 2 === 0 ? 'fade-right' : 'fade-left' }}"
                data-aos-delay="{{ ($index % 4) * 100 }}">
                <p class="mb-2 text-sm font-semibold uppercase tracking-wider text-emerald-700">Standar AbodaFarm</p>

                <h3 class="text-lg font-semibold">{{ $item['title'] }}</h3>
                <p class="mt-2 text-sm text-slate-600">{{ $item['description'] }}</p>
            </article>
        @endforeach
    </div>
</section>
