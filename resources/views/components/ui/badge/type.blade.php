{{--
    Type Badge Component for AbodaFarm Products

    Usage:
    <x-ui.badge.type :type="$product->type" />

    Supports:
    - Product types: live, fresh, supply
--}}

@props([
    'type' => '',
    'size' => 'md', // sm, md, lg
    'showIcon' => true,
])

@php
    $config = [
        'live' => [
            'label' => 'Ternak Hidup',
            'class' => 'bg-green-100 text-green-700 border-green-200',
            'icon' => 'fa-cow',
        ],
        'fresh' => [
            'label' => 'Daging Segar',
            'class' => 'bg-rose-100 text-rose-700 border-rose-200',
            'icon' => 'fa-drumstick-bite',
        ],
        'supply' => [
            'label' => 'Pakan & Perlengkapan',
            'class' => 'bg-amber-100 text-amber-700 border-amber-200',
            'icon' => 'fa-box-open',
        ],
    ];

    $typeKey = strtolower($type);
    $data = $config[$typeKey] ?? [
        'label' => ucfirst($type),
        'class' => 'bg-slate-100 text-slate-600 border-slate-200',
        'icon' => 'fa-box',
    ];

    $sizeClasses = match($size) {
        'sm' => 'px-2 py-0.5 text-[10px]',
        'md' => 'px-2.5 py-1 text-xs',
        'lg' => 'px-3 py-1.5 text-sm',
        default => 'px-2.5 py-1 text-xs',
    };
@endphp

<span class="inline-flex items-center gap-1.5 rounded-full border font-medium {{ $data['class'] }} {{ $sizeClasses }}">
    @if($showIcon && isset($data['icon']))
        <i class="fa-solid {{ $data['icon'] }}"></i>
    @endif
    {{ $data['label'] }}
</span>
