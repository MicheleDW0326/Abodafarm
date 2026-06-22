<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="mb-8 text-center">
        <div class="mx-auto h-10 w-64 animate-pulse rounded bg-slate-200"></div>
        <div class="mx-auto mt-3 h-5 w-80 animate-pulse rounded bg-slate-100"></div>
    </div>

    <div class="grid gap-5 md:grid-cols-3">
        @for($i = 0; $i < 3; $i++)
            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-md">
                <div class="space-y-2">
                    <div class="h-4 w-full animate-pulse rounded bg-slate-200"></div>
                    <div class="h-4 w-5/6 animate-pulse rounded bg-slate-200"></div>
                    <div class="h-4 w-4/6 animate-pulse rounded bg-slate-100"></div>
                </div>
                <div class="mt-4 border-t border-slate-100 pt-4">
                    <div class="flex items-center gap-3">
                        <div class="size-10 animate-pulse rounded-full bg-slate-200"></div>
                        <div>
                            <div class="h-5 w-24 animate-pulse rounded bg-slate-200"></div>
                            <div class="mt-1 h-4 w-20 animate-pulse rounded bg-slate-100"></div>
                        </div>
                    </div>
                </div>
            </div>
        @endfor
    </div>
</section>
