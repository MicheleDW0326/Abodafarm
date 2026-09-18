{{--
    Stat Card Component for Dashboard

    Usage:
    <x-ui.card.stat
        icon="fa-chart"
        label="Total Sales"
        :value="123456"
        :change="15"
        trend="up"
    />

    Atau dengan formatted value:
    <x-ui.card.stat
        icon="fa-users"
        label="Total Customers"
        :value="1500"
        sub="+50 bulan ini"
    />
--}}

@props([
    'icon' => null,           // FontAwesome icon class
    'label' => '',
    'value' => 0,
    'sub' => null,            // Subtitle text
    'change' => null,         // Percentage change (e.g., "+15%", "-5%")
    'trend' => null,         // 'up', 'down', or null
    'color' => 'emerald',    // emerald, blue, violet, amber, rose, slate
    'clickable' => false,
    'href' => null,
    'badge' => null,         // Small badge text
])

@php
    $colorConfig = [
        'emerald' => [
            'bg' => 'bg-emerald-100',
            'icon' => 'text-emerald-600',
            'border' => 'border-emerald-500',
            'change_up' => 'text-emerald-600',
            'change_down' => 'text-red-600',
        ],
        'blue' => [
            'bg' => 'bg-blue-100',
            'icon' => 'text-blue-600',
            'border' => 'border-blue-500',
            'change_up' => 'text-emerald-600',
            'change_down' => 'text-red-600',
        ],
        'violet' => [
            'bg' => 'bg-violet-100',
            'icon' => 'text-violet-600',
            'border' => 'border-violet-500',
            'change_up' => 'text-emerald-600',
            'change_down' => 'text-red-600',
        ],
        'amber' => [
            'bg' => 'bg-amber-100',
            'icon' => 'text-amber-600',
            'border' => 'border-amber-500',
            'change_up' => 'text-emerald-600',
            'change_down' => 'text-red-600',
        ],
        'rose' => [
            'bg' => 'bg-rose-100',
            'icon' => 'text-rose-600',
            'border' => 'border-rose-500',
            'change_up' => 'text-emerald-600',
            'change_down' => 'text-red-600',
        ],
        'slate' => [
            'bg' => 'bg-slate-100',
            'icon' => 'text-slate-600',
            'border' => 'border-slate-500',
            'change_up' => 'text-emerald-600',
            'change_down' => 'text-red-600',
        ],
        'purple' => [
            'bg' => 'bg-purple-100',
            'icon' => 'text-purple-600',
            'border' => 'border-purple-500',
            'change_up' => 'text-emerald-600',
            'change_down' => 'text-red-600',
        ],
    ];

    $colors = $colorConfig[$color] ?? $colorConfig['emerald'];

    // Determine trend color
    $trendColor = '';
    if ($trend === 'up' || ($change !== null && str_starts_with($change, '+'))) {
        $trendColor = $colors['change_up'];
    } elseif ($trend === 'down' || ($change !== null && str_starts_with($change, '-'))) {
        $trendColor = $colors['change_down'];
    }
@endphp

@if($clickable && $href)
    <a href="{{ $href }}" class="block">
@endif

<div
    class="relative rounded-xl border bg-white p-4 transition-all duration-200
        {{ $clickable ? 'hover:shadow-md hover:border-emerald-300 cursor-pointer' : 'shadow-sm' }}
        {{ isset($colors['border']) && $clickable ? 'border-transparent hover:border-' . $color . '-300' : '' }}"
>
    <div class="flex items-start justify-between">
        <div class="flex-1">
            {{-- Label --}}
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">
                {{ $label }}
            </p>

            {{-- Value --}}
            <div class="mt-1 flex items-baseline gap-2">
                <p class="text-2xl font-bold text-slate-900">
                    {{ is_numeric($value) ? number_format($value, 0, ',', '.') : $value }}
                </p>
            </div>

            {{-- Subtitle or Change --}}
            @if($change !== null)
                <div class="mt-1 flex items-center gap-2">
                    @if($trend)
                        <span class="inline-flex items-center gap-1 text-xs font-medium {{ $trendColor }}">
                            @if($trend === 'up')
                                <i class="fa-solid fa-arrow-up text-[10px]"></i>
                            @elseif($trend === 'down')
                                <i class="fa-solid fa-arrow-down text-[10px]"></i>
                            @endif
                            {{ $change }}%
                        </span>
                    @else
                        <span class="text-xs font-medium {{ $trendColor }}">
                            {{ $change }}%
                    @endif
                </span>
            @endif

            @if($sub)
                <span class="text-xs text-slate-500">{{ $sub }}</span>
            @endif
        </div>

        {{-- Icon --}}
        @if($icon)
            <div class="flex-shrink-0 rounded-lg p-2 {{ $colors['bg'] }}">
                <i class="fa-solid {{ $icon }} {{ $colors['icon'] }} text-lg"></i>
            </div>
        @endif
    </div>

    {{-- Badge --}}
    @if($badge)
        <div class="mt-3 pt-3 border-t border-slate-100">
            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium bg-slate-100 text-slate-600">
                {{ $badge }}
            </span>
        </div>
    @endif
</div>

@if($clickable && $href)
    </a>
@endif
