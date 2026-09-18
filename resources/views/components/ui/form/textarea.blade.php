{{--
    Form Textarea Component

    Usage:
    <x-ui.form.textarea
        label="Deskripsi"
        wire:model.live="description"
        placeholder="Masukkan deskripsi..."
        rows="4"
    />
--}}

@props([
    'label' => null,
    'placeholder' => '',
    'rows' => 3,
    'error' => null,
    'hint' => null,
    'disabled' => false,
    'required' => false,
])

@php
    $hasError = $error || ($errors && $errors->has($attributes->get('wire:model')));
    $baseClasses = 'w-full rounded-lg border px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 transition-colors duration-200 focus:outline-none focus:ring-1 resize-none';

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

    {{-- Textarea --}}
    <textarea
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes->class([
            $baseClasses,
            $borderClasses,
            $disabledClasses,
        ])->merge(['class' => '']) }}
    >{{ $attributes->get('value') ?? '' }}</textarea>

    {{-- Error Message --}}
    @if($error)
        <p class="text-xs text-red-600">{{ $error }}</p>
    @endif

    {{-- Hint --}}
    @if($hint && !$error)
        <p class="text-xs text-slate-500">{{ $hint }}</p>
    @endif
</div>
