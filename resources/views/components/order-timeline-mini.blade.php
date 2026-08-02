{{--
    Order Timeline Mini Component
    Compact timeline for order cards/lists

    Usage:
    <x-order-timeline-mini :order="$order" />

    Props:
    - order: Order model instance (must have timeline_data relation loaded)
--}}

@props([
    'order' => null,
])

@php
    $timelineItems = $order?->timeline_data ?? [];
    $totalItems = count($timelineItems);
    $lastItem = $totalItems > 0 ? end($timelineItems) : null;
@endphp

@if($lastItem)
    <div class="flex items-center gap-2 text-xs text-slate-500">
        <i class="fa-solid fa-clock-rotate-left text-slate-400"></i>
        <span class="text-slate-600">
            {{ $lastItem['status_label'] ?? ucfirst($lastItem['to_status'] ?? 'Unknown') }}
        </span>
        <span class="text-slate-300">•</span>
        <span>{{ $lastItem['formatted_date'] ?? '' }}</span>
        <span>{{ $lastItem['formatted_time'] ?? '' }}</span>
    </div>
@else
    <div class="flex items-center gap-2 text-xs text-slate-400">
        <i class="fa-solid fa-clock"></i>
        <span>Belum ada aktivitas</span>
    </div>
@endif
