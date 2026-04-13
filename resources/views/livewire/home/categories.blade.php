<section id="kategori" class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h2 class="text-3xl font-semibold">Kategori Utama</h2>
        <p class="mt-2 text-slate-600">Pilihan produk segar untuk kebutuhan rumah dan bisnis kuliner.</p>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        @foreach ($categories as $category)
            <article class="group overflow-hidden rounded-3xl border border-sky-100 bg-white shadow-md transition hover:-translate-y-1 hover:shadow-xl">
                <img src="{{ $category['image'] }}" alt="{{ $category['name'] }}" class="h-56 w-full object-cover">
                <div class="space-y-2 p-6">
                    <h3 class="text-xl font-semibold">{{ $category['name'] }}</h3>
                    <p class="text-sm text-slate-600">{{ $category['description'] }}</p>
                </div>
            </article>
        @endforeach
    </div>
</section>
