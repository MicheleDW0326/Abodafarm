<section id="produk" class="bg-white py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <div class="h-10 w-64 animate-pulse rounded bg-slate-200"></div>
                <div class="mt-3 h-5 w-96 animate-pulse rounded bg-slate-100"></div>
            </div>
            <div class="h-10 w-48 animate-pulse rounded-xl bg-slate-100"></div>
        </div>

        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            @for($i = 0; $i < 6; $i++)
                <div class="overflow-hidden rounded-3xl border border-emerald-100 bg-white shadow-md">
                    <div class="relative">
                        <div class="h-56 w-full animate-pulse bg-slate-200"></div>
                        <div class="absolute left-4 top-4 flex gap-2">
                            <div class="h-6 w-16 animate-pulse rounded-full bg-emerald-600"></div>
                            <div class="h-6 w-20 animate-pulse rounded-full bg-white/95"></div>
                        </div>
                    </div>
                    <div class="space-y-4 p-5">
                        <div>
                            <div class="h-6 w-3/4 animate-pulse rounded bg-slate-200"></div>
                            <div class="mt-2 h-4 w-full animate-pulse rounded bg-slate-100"></div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-2xl bg-slate-50 p-3">
                                <div class="h-3 w-12 animate-pulse rounded bg-slate-200"></div>
                                <div class="mt-2 h-5 w-16 animate-pulse rounded bg-slate-200"></div>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-3">
                                <div class="h-3 w-12 animate-pulse rounded bg-slate-200"></div>
                                <div class="mt-2 h-5 w-16 animate-pulse rounded bg-slate-200"></div>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-3">
                                <div class="h-3 w-20 animate-pulse rounded bg-slate-200"></div>
                                <div class="mt-2 h-5 w-16 animate-pulse rounded bg-slate-200"></div>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-3">
                                <div class="h-3 w-12 animate-pulse rounded bg-slate-200"></div>
                                <div class="mt-2 h-5 w-16 animate-pulse rounded bg-slate-200"></div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between border-t border-slate-100 pt-4">
                            <div class="h-8 w-32 animate-pulse rounded bg-slate-200"></div>
                            <div class="flex gap-2">
                                <div class="h-8 w-16 animate-pulse rounded-lg bg-slate-100"></div>
                                <div class="h-8 w-16 animate-pulse rounded-lg bg-amber-100"></div>
                            </div>
                        </div>
                    </div>
                </div>
            @endfor
        </div>
    </div>
</section>
