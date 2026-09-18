{{--
    Form Select Component

    Usage:
    <x-ui.form.select
        label="Kategori"
        wire:model.live="category"
        :options="[
            ['value' => 1, 'label' => 'Kategori 1'],
            ['value' => 2, 'label' => 'Kategori 2'],
        ]"
        placeholder="Pilih kategori..."
    />

    Or with options as array:
    <x-ui.form.select
        label="Status"
        wire:model.live="status"
        :options="$statusOptions"
        error="Status wajib dipilih"
    />
--}}

@props([
    'label' => null,
    'placeholder' => 'Pilih...',
    'options' => [],         // Array of ['value' => x, 'label' => y] or simple array
    'error' => null,
    'hint' => null,
    'disabled' => false,
    'required' => false,
])

@php
    $hasError = $error || ($errors && $errors->has($attributes->get('wire:model')));
    $baseClasses = 'w-full rounded-lg border px-4 py-2.5 text-sm text-slate-900 transition-colors duration-200 focus:outline-none focus:ring-1 appearance-none bg-no-repeat bg-[right_0.75rem_center] pr-10';

    $borderClasses = $hasError
        ? 'border-red-300 focus:border-red-500 focus:ring-red-500/20 bg-red-50'
        : 'border-slate-200 focus:border-emerald-500 focus:ring-emerald-500/20 bg-white';

    $disabledClasses = $disabled ? 'bg-slate-100 cursor-not-allowed opacity-60' : '';

    // Build options
    $formattedOptions = collect($options)->map(function ($option) {
        if (is_array($option)) {
            return $option;
        }
        return ['value' => $option, 'label' => $option];
    });
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

    {{-- Select --}}
    <div class="relative">
        <select
            {{ $disabled ? 'disabled' : '' }}
            {{ $attributes->class([
                $baseClasses,
                $borderClasses,
                $disabledClasses,
            ])->merge(['class' => '']) }}
            style="background-image: url(\"data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e\"); background-size: 1.25rem 1.25rem;"
        >
            @if($placeholder)
                <option value="">{{ $placeholder }}</option>
            @endif
            @foreach($formattedOptions as $option)
                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
            @endforeach
        </select>
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
