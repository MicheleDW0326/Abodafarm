{{--
    Form Input Component

    Usage:
    <x-ui.form.input
        label="Nama Produk"
        wire:model.live="name"
        placeholder="Masukkan nama..."
        :error="$errors->first('name')"
    />

    Or with icon:
    <x-ui.form.input
        label="Search"
        wire:model.live="search"
        placeholder="Cari..."
        icon="fa-search"
    />
--}}

@props([
    'label' => null,
    'placeholder' => '',
    'type' => 'text',
    'error' => null,
    'hint' => null,
    'icon' => null,
    'iconPosition' => 'left',
    'disabled' => false,
    'required' => false,
    'wire' => null,
])

@php
    $hasError = $error || ($errors && $errors->has($attributes->get('wire:model')));
    $baseClasses = 'w-full rounded-lg border px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 transition-colors duration-200 focus:outline-none focus:ring-1';

    $borderClasses = $hasError
        ? 'border-red-300 focus:border-red-500 focus:ring-red-500/20 bg-red-50'
        : 'border-slate-200 focus:border-emerald-500 focus:ring-emerald-500/20 bg-white';

    $disabledClasses = $disabled ? 'bg-slate-100 cursor-not-allowed opacity-60' : '';
@endphp

<div class="space-y-1.5">
    {{-- Label --}}
    @if($label)
        <label class="block text-sm font-medium text-slate-700">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    {{-- Input Container --}}
    <div class="relative">
        {{-- Icon Left --}}
        @if($icon && $iconPosition === 'left')
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <i class="fa-solid {{ $icon }} text-slate-400"></i>
            </div>
        @endif

        {{-- Input --}}
        <input
            @if($icon && $iconPosition === 'left') pl-10 @endif
            @if($icon && $iconPosition === 'right') pr-10 @endif
            type="{{ $type }}"
            placeholder="{{ $placeholder }}"
            {{ $disabled ? 'disabled' : '' }}
            {{ $attributes->class([
                $baseClasses,
                $borderClasses,
                $disabledClasses,
            ])->merge([
                'class' => '',
            ]) }}
        />

        {{-- Icon Right --}}
        @if($icon && $iconPosition === 'right')
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                <i class="fa-solid {{ $icon }} text-slate-400"></i>
            </div>
        @endif
    </div>

    {{-- Error Message --}}
    @if($error)
        <p class="text-xs text-red-600">{{ $error }}</p>
    @endif

    {{-- Hint --}}
    @if($hint && !$error)
        <p class="text-xs text-slate-500">{{ $hint }}</p>
    @endif
</div>
