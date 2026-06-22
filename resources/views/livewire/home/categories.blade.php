<section id="ternak" class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h2 class="text-3xl font-semibold text-slate-900">Kategori Ternak</h2>
        <p class="mt-2 text-slate-600">Klik kategori untuk memfilter katalog ternak unggulan.</p>
    </div>

    <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($categories as $category)
            <button type="button" wire:click="selectCategory('{{ $category['search'] }}')"
                class="group overflow-hidden rounded-3xl border border-emerald-100 bg-white text-left shadow-md transition hover:-translate-y-1 hover:shadow-xl">
                <img src="{{ $category['image'] }}" alt="{{ $category['name'] }}" class="h-56 w-full object-cover">
                <div class="space-y-2 p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-semibold text-slate-900">{{ $category['icon'] }} {{ $category['name'] }}
                        </h3>
                        <span
                            class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Filter</span>
                    </div>
                    <p class="text-sm text-slate-600">{{ $category['description'] }}</p>
                </div>
            </button>
        @endforeach
    </div>
</section>
