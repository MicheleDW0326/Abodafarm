{{--
    Base Button Component for AbodaFarm

    Usage:
    <x-ui.button variant="primary" size="md" wire:click="save">
        Simpan
    </x-ui.button>

    Variants: primary, secondary, danger, success, warning, ghost
    Sizes: sm, md, lg
--}}

@props([
    'variant' => 'primary', // primary, secondary, danger, success, warning, ghost
    'size' => 'md', // sm, md, lg
    'disabled' => false,
    'loading' => false,
    'icon' => null, // FontAwesome icon class
    'iconPosition' => 'left', // left, right
    'type' => 'button',
    'fullWidth' => false,
])

@php
    $baseClasses =
        'inline-flex items-center justify-center font-medium rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

    $variantClasses = match ($variant) {
        'primary'
            => 'bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500 shadow-sm hover:shadow-md hover:scale-[1.02]',
        'secondary' => 'bg-slate-100 text-slate-700 hover:bg-slate-200 focus:ring-slate-400',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500 shadow-sm hover:shadow-md',
        'success' => 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500 shadow-sm hover:shadow-md',
        'warning' => 'bg-amber-500 text-white hover:bg-amber-600 focus:ring-amber-400 shadow-sm hover:shadow-md',
        'ghost' => 'bg-transparent text-slate-600 hover:bg-slate-100 focus:ring-slate-400',
        'outline' => 'border-2 border-slate-300 text-slate-700 hover:bg-slate-50 focus:ring-slate-400',
        default => 'bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500',
    };

    $sizeClasses = match ($size) {
        'sm' => 'px-3 py-1.5 text-xs gap-1',
        'md' => 'px-4 py-2.5 text-sm gap-2',
        'lg' => 'px-6 py-3 text-base gap-2',
        'xl' => 'px-8 py-4 text-lg gap-3',
        default => 'px-4 py-2.5 text-sm gap-2',
    };

    $widthClass = $fullWidth ? 'w-full' : '';
@endphp

<button type="{{ $type }}"
    {{ $attributes->class([$baseClasses, $variantClasses, $sizeClasses, $widthClass])->merge([
        'disabled' => $disabled || $loading,
    ]) }}>
    @if ($loading)
        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
            </path>
        </svg>
    @elseif($icon && $iconPosition === 'left')
        <i class="{{ $icon }}"></i>
    @endif

    {{ $slot }}

    @if ($icon && $iconPosition === 'right' && !$loading)
        <i class="{{ $icon }}"></i>
    @endif
</button>
