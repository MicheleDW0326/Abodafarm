<section id="keunggulan" class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="mb-10 text-center">
        <div class="mx-auto h-10 w-64 animate-pulse rounded bg-slate-200"></div>
        <div class="mx-auto mt-3 h-5 w-96 animate-pulse rounded bg-slate-100"></div>
    </div>

    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        @for($i = 0; $i < 4; $i++)
            <div class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm">
                <div class="mb-3 h-4 w-24 animate-pulse rounded bg-emerald-100"></div>
                <div class="h-6 w-full animate-pulse rounded bg-slate-200"></div>
                <div class="mt-3 h-4 w-full animate-pulse rounded bg-slate-100"></div>
            </div>
        @endfor
    </div>
</section>
