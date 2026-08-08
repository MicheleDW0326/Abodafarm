<section class="py-12">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <!-- Section Header Skeleton -->
        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-slate-200 animate-pulse"></div>
                <div>
                    <div class="h-5 w-32 rounded bg-slate-200 animate-pulse"></div>
                    <div class="mt-1 h-4 w-24 rounded bg-slate-200 animate-pulse"></div>
                </div>
            </div>
        </div>

        <!-- Product Cards Skeleton -->
        <div class="flex gap-4 overflow-x-auto pb-4 scrollbar-hide">
            @for($i = 0; $i < 6; $i++)
                <div class="shrink-0 w-48 overflow-hidden rounded-2xl border border-slate-200 bg-white">
                    <div class="aspect-square bg-slate-200 animate-pulse"></div>
                    <div class="p-3 space-y-2">
                        <div class="h-3 w-16 rounded bg-slate-200 animate-pulse"></div>
                        <div class="h-4 w-full rounded bg-slate-200 animate-pulse"></div>
                        <div class="h-4 w-20 rounded bg-slate-200 animate-pulse"></div>
                    </div>
                </div>
            @endfor
        </div>
    </div>
</section>
