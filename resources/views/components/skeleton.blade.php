@props([
    'variant' => 'text',
    'count' => 1,
    'class' => ''
])

@for($i = 0; $i < $count; $i++)
    @switch($variant)
        @case('text')
            <div class="h-4 w-full animate-pulse rounded bg-slate-200 {{ $class }}"></div>
            @break

        @case('title')
            <div class="h-8 w-3/4 animate-pulse rounded bg-slate-200 {{ $class }}"></div>
            @break

        @case('subtitle')
            <div class="h-5 w-2/3 animate-pulse rounded bg-slate-200 {{ $class }}"></div>
            @break

        @case('image')
            <div class="aspect-video w-full animate-pulse rounded-xl bg-slate-200 {{ $class }}"></div>
            @break

        @case('image-square')
            <div class="aspect-square w-full animate-pulse rounded-xl bg-slate-200 {{ $class }}"></div>
            @break

        @case('card')
            <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <div class="h-40 w-full animate-pulse rounded-xl bg-slate-200"></div>
                <div class="mt-4 space-y-2">
                    <div class="h-5 w-3/4 animate-pulse rounded bg-slate-200"></div>
                    <div class="h-4 w-full animate-pulse rounded bg-slate-100"></div>
                </div>
            </div>
            @break

        @case('avatar')
            <div class="size-12 animate-pulse rounded-full bg-slate-200 {{ $class }}"></div>
            @break

        @case('avatar-lg')
            <div class="size-20 animate-pulse rounded-full bg-slate-200 {{ $class }}"></div>
            @break

        @case('button')
            <div class="h-10 w-24 animate-pulse rounded-lg bg-slate-200 {{ $class }}"></div>
            @break

        @case('badge')
            <div class="h-6 w-20 animate-pulse rounded-full bg-slate-200 {{ $class }}"></div>
            @break

        @case('line')
            <div class="h-4 animate-pulse rounded bg-slate-200 {{ $class }}"></div>
            @break

        @case('lines')
            <div class="space-y-2">
                <div class="h-4 w-full animate-pulse rounded bg-slate-200"></div>
                <div class="h-4 w-5/6 animate-pulse rounded bg-slate-100"></div>
                <div class="h-4 w-4/6 animate-pulse rounded bg-slate-100"></div>
            </div>
            @break

        @case('circle')
            <div class="animate-pulse rounded-full bg-slate-200 {{ $class }}"></div>
            @break

        @case('grid-2')
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="h-32 animate-pulse rounded-xl bg-slate-200"></div>
                <div class="h-32 animate-pulse rounded-xl bg-slate-200"></div>
            </div>
            @break

        @case('grid-3')
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div class="h-32 animate-pulse rounded-xl bg-slate-200"></div>
                <div class="h-32 animate-pulse rounded-xl bg-slate-200"></div>
                <div class="h-32 animate-pulse rounded-xl bg-slate-200"></div>
            </div>
            @break

        @case('grid-4')
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @for($j = 0; $j < 4; $j++)
                    <div class="h-32 animate-pulse rounded-xl bg-slate-200"></div>
                @endfor
            </div>
            @break
    @endswitch
@endfor
