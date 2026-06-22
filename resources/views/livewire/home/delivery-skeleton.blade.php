<section class="bg-sky-900 py-16 text-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="h-10 w-64 animate-pulse rounded bg-white/20"></div>
        <div class="mt-8 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            @for($i = 0; $i < 4; $i++)
                <div class="rounded-2xl border border-white/20 bg-white/10 p-5 backdrop-blur">
                    <div class="h-3 w-16 animate-pulse rounded bg-white/20"></div>
                    <div class="mt-2 h-6 w-full animate-pulse rounded bg-white/20"></div>
                    <div class="mt-2 h-4 w-full animate-pulse rounded bg-white/10"></div>
                </div>
            @endfor
        </div>
    </div>
</section>
