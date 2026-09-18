{{--
    Form Toggle/Switch Component

    Usage:
    <x-ui.form.toggle
        label="Aktifkan fitur ini"
        wire:model.live="isActive"
    />

    Or with description:
    <x-ui.form.toggle
        label="Notifikasi Email"
        description="Kirim notifikasi via email"
        wire:model.live="notifyEmail"
    />
--}}

@props([
    'label' => null,
    'description' => null,
    'checked' => false,
    'disabled' => false,
    'color' => 'emerald', // emerald, blue, violet, amber
])

@php
    $disabledClasses = $disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer';

    $trackColors = match($color) {
        'emerald' => 'peer-checked:bg-emerald-600',
        'blue' => 'peer-checked:bg-blue-600',
        'violet' => 'peer-checked:bg-violet-600',
        'amber' => 'peer-checked:bg-amber-500',
        default => 'peer-checked:bg-emerald-600',
    };

    $thumbColors = match($color) {
        'emerald' => 'peer-checked:bg-white',
        'blue' => 'peer-checked:bg-white',
        'violet' => 'peer-checked:bg-white',
        'amber' => 'peer-checked:bg-white',
        default => 'peer-checked:bg-white',
    };
@endphp

<label class="relative inline-flex items-center gap-3 {{ $disabledClasses }}">
    {{-- Toggle --}}
    <input
        type="checkbox"
        @checked($checked)
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes->merge(['class' => 'peer sr-only']) }}
    >

    {{-- Track --}}
    <div class="peer h-6 w-11 flex-shrink-0 rounded-full border border-slate-200 bg-slate-200 transition-colors duration-200
        peer-checked:bg-emerald-600
        peer-checked:border-transparent
        peer-focus:ring-2 peer-focus:ring-offset-2
        peer-focus:ring-{{ $color }}-500/50
        {{ $trackColors }}">
    </div>

    {{-- Thumb --}}
    <div class="pointer-events-none inline-flex h-5 w-5 transform items-center justify-center rounded-full bg-white shadow transition duration-200
        translate-x-0
        peer-checked:translate-x-5
        {{ $thumbColors }}">
    </div>

    {{-- Label & Description --}}
    @if($label || $description)
        <div class="flex flex-col">
            @if($label)
                <span class="text-sm font-medium text-slate-700">{{ $label }}</span>
            @endif
            @if($description)
                <span class="text-xs text-slate-500">{{ $description }}</span>
            @endif
        </div>
    @endif
</label>
