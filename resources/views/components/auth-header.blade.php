@props([
    'title',
    'description',
])

<div class="flex w-full flex-col text-center">
    <h1 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white">{{ $title }}</h1>
    <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $description }}</p>
</div>
