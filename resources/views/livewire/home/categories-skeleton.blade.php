<section id="ternak" class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="mb-8">
        <div class="h-10 w-64 animate-pulse rounded bg-slate-200"></div>
        <div class="mt-3 h-5 w-80 animate-pulse rounded bg-slate-100"></div>
    </div>

    <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
        @for($i = 0; $i < 4; $i++)
            <div class="overflow-hidden rounded-3xl border border-emerald-100 bg-white shadow-md">
                <div class="h-56 w-full animate-pulse bg-slate-200"></div>
                <div class="space-y-3 p-6">
                    <div class="flex items-center justify-between">
                        <div class="h-6 w-24 animate-pulse rounded bg-slate-200"></div>
                        <div class="h-6 w-16 animate-pulse rounded-full bg-emerald-100"></div>
                    </div>
                    <div class="h-4 w-full animate-pulse rounded bg-slate-100"></div>
                </div>
            </div>
        @endfor
    </div>
</section>
