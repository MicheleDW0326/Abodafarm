<section id="faq" class="mx-auto max-w-4xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="mb-8 text-center">
        <div class="mx-auto h-10 w-64 animate-pulse rounded bg-slate-200"></div>
        <div class="mx-auto mt-3 h-5 w-80 animate-pulse rounded bg-slate-100"></div>
    </div>

    <div class="space-y-3">
        @for($i = 0; $i < 5; $i++)
            <div class="rounded-xl border border-slate-200 bg-white px-5 py-4">
                <div class="flex items-center justify-between">
                    <div class="h-5 w-full animate-pulse rounded bg-slate-200"></div>
                    <div class="ml-4 h-6 w-6 animate-pulse rounded bg-slate-100"></div>
                </div>
            </div>
        @endfor
    </div>
</section>
