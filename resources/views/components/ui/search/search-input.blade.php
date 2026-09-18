{{--
    Search Input Component with icon, clear button, and debounce

    Usage:
    <x-ui.search.input
        wire:model.live.debounce.300ms="search"
        placeholder="Cari produk..."
    />

    With custom size:
    <x-ui.search.input
        wire:model.live="search"
        placeholder="Search..."
        size="lg"
    />
--}}

@props([
    'placeholder' => 'Cari...',
    'icon' => 'fa-search',
    'clearable' => true,
    'size' => 'md', // sm, md, lg
    'fullWidth' => true,
])

@php
    $sizeClasses = match($size) {
        'sm' => 'py-1.5 pl-8 pr-8 text-xs',
        'md' => 'py-2 pl-10 pr-10 text-sm',
        'lg' => 'py-3 pl-12 pr-12 text-base',
        default => 'py-2 pl-10 pr-10 text-sm',
    };

    $iconSizes = match($size) {
        'sm' => 'left-2.5 top-1/2 -translate-y-1/2 size-3.5',
        'md' => 'left-3 top-1/2 -translate-y-1/2 size-4',
        'lg' => 'left-4 top-1/2 -translate-y-1/2 size-5',
        default => 'left-3 top-1/2 -translate-y-1/2 size-4',
    };

    $clearSizes = match($size) {
        'sm' => 'right-2 p-1',
        'md' => 'right-2.5 p-1.5',
        'lg' => 'right-3 p-2',
        default => 'right-2.5 p-1.5',
    };
@endphp

<div class="{{ $fullWidth ? 'w-full' : 'w-full max-w-md' }} relative">
    <input
        type="text"
        placeholder="{{ $placeholder }}"
        {{ $attributes->class([
            'w-full rounded-lg border border-slate-200 bg-slate-50 text-slate-700',
            'placeholder:text-slate-400',
            'focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-emerald-500',
            $sizeClasses,
        ])->merge(['class' => '']) }}
    >

    {{-- Search Icon --}}
    <div class="pointer-events-none absolute inset-y-0 {{ $size === 'sm' ? 'left-2.5' : ($size === 'lg' ? 'left-4' : 'left-3') }} flex items-center text-slate-400">
        <i class="fa-solid {{ $icon ?? 'fa-search' }}"></i>
    </div>

    {{-- Clear Button --}}
    @if($clearable)
        <button
            type="button"
            x-on:click="const prev = $el.previousElementSibling; if (prev && prev.value !== undefined) { prev.value = ''; prev.dispatchEvent(new Event('input', { bubbles: true })); }"
            x-show="$el.previousElementSibling && $el.previousElementSibling.value !== undefined && $el.previousElementSibling.value.length > 0"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 scale-90"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-90"
            class="absolute inset-y-0 my-auto {{ $clearSizes }} rounded-full text-slate-400 hover:text-slate-600 hover:bg-slate-200 transition-colors"
        >
            <i class="fa-solid fa-xmark text-xs"></i>
        </button>
    @endif
</div>
