{{--
    Icon-only Button Component

    Usage:
    <x-ui.button.icon variant="primary" wire:click="save">
        <i class="fa-solid fa-save"></i>
    </x-ui.button.icon>
--}}

@props([
    'variant' => 'primary',
    'size' => 'md',
    'disabled' => false,
    'loading' => false,
    'tooltip' => null,
    'type' => 'button',
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-medium rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

    $variantClasses = match($variant) {
        'primary' => 'bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500',
        'secondary' => 'bg-slate-100 text-slate-700 hover:bg-slate-200 focus:ring-slate-400',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
        'success' => 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500',
        'warning' => 'bg-amber-500 text-white hover:bg-amber-600 focus:ring-amber-400',
        'ghost' => 'bg-transparent text-slate-500 hover:bg-slate-100 hover:text-slate-700 focus:ring-slate-400',
        'white' => 'bg-white text-slate-700 hover:bg-slate-50 focus:ring-slate-300 shadow-sm',
        default => 'bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500',
    };

    $sizeClasses = match($size) {
        'sm' => 'p-1.5',
        'md' => 'p-2',
        'lg' => 'p-3',
        default => 'p-2',
    };
@endphp

<button
    type="{{ $type }}"
    @if($tooltip) title="{{ $tooltip }}" @endif
    {{ $attributes->class([
        $baseClasses,
        $variantClasses,
        $sizeClasses,
    ])->merge([
        'disabled' => $disabled || $loading,
    ]) }}
>
    @if($loading)
        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    @else
        {{ $slot }}
    @endif
</button>
