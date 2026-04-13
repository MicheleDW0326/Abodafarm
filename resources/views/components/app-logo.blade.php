@props([
    'sidebar' => false,
])

<a {{ $attributes->merge(['class' => $sidebar ? 'flex items-center gap-3 rounded-2xl px-3 py-2 text-white transition hover:bg-white/5' : 'inline-flex items-center gap-3 rounded-2xl px-3 py-2 text-white']) }}>
    <span class="grid size-10 shrink-0 place-content-center rounded-2xl bg-linear-to-br from-sky-500 to-emerald-400 text-sm font-black text-white shadow-lg shadow-sky-500/20">
        <x-app-logo-icon class="size-5 fill-current text-white" />
    </span>

    <span class="leading-tight">
        <span class="block text-base font-black tracking-tight">Fresh<span class="text-sky-300">Go</span></span>
        <span class="block text-xs uppercase tracking-[0.24em] text-white/55">Fresh market daily</span>
    </span>
</a>
