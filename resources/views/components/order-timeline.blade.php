{{--
    Order Timeline Component
    Displays chronological history of order/booking status changes

    Usage:
    <x-order-timeline :order="$order" />

    Props:
    - order: Order model instance (must have timeline_data relation loaded)
    - title: Optional title (default: 'Riwayat Pesanan')
    - showIcon: Show status icons (default: true)
    - compact: Compact view (default: false)
--}}

@props([
    'order' => null,
    'title' => 'Riwayat Pesanan',
    'showIcon' => true,
    'compact' => false,
])

@php
    $timelineItems = $order?->timeline_data ?? [];
    $isEmpty = empty($timelineItems);
@endphp

<div class="{{ $compact ? 'space-y-3' : 'rounded-xl border border-slate-200 bg-white p-5' }}">
    {{-- Header --}}
    @if(!$compact)
        <div class="mb-4 flex items-center gap-2">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <h3 class="font-semibold text-slate-900">{{ $title }}</h3>
            @if(!$isEmpty)
                <span class="ml-auto rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">
                    {{ count($timelineItems) }} langkah
                </span>
            @endif
        </div>
    @endif

    @if($isEmpty)
        {{-- Empty State --}}
        <div class="flex flex-col items-center justify-center py-6 text-center">
            <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                <i class="fa-solid fa-clock text-xl"></i>
            </div>
            <p class="text-sm text-slate-500">Belum ada riwayat perubahan status</p>
        </div>
    @else
        {{-- Timeline --}}
        <div class="relative">
            {{-- Vertical Line --}}
            <div class="absolute left-4 top-0 h-full w-0.5 bg-slate-200"></div>

            {{-- Timeline Items --}}
            <div class="space-y-0">
                @foreach($timelineItems as $index => $item)
                    @php
                        $isLast = $loop->last;
                        $isCurrent = $item['is_current'] ?? false;
                        $isTerminal = $item['is_terminal'] ?? false;
                        $status = $item['to_status'] ?? 'unknown';
                        $statusLabel = $item['status_label'] ?? ucfirst($status);
                        $triggeredBy = $item['triggered_by_name'] ?? 'Sistem';
                        $triggeredByType = $item['triggered_by_type'] ?? 'system';
                        $notes = $item['notes'] ?? null;
                        $date = $item['formatted_date'] ?? '';
                        $time = $item['formatted_time'] ?? '';
                        $iconColor = match(true) {
                            $isTerminal && in_array($status, ['cancelled', 'rejected']) => 'bg-red-100 text-red-600',
                            $isTerminal => 'bg-emerald-100 text-emerald-600',
                            $isCurrent => 'bg-emerald-500 text-white',
                            default => 'bg-slate-100 text-slate-600',
                        };
                        $icon = match($status) {
                            'pending' => 'fa-clock',
                            'paid', 'approved' => 'fa-check-circle',
                            'payment_failed', 'rejected' => 'fa-times-circle',
                            'payment_refunded' => 'fa-undo',
                            'processing' => 'fa-gear',
                            'shipped' => 'fa-truck',
                            'delivered' => 'fa-box-open',
                            'completed' => 'fa-check-double',
                            'cancelled' => 'fa-ban',
                            default => 'fa-circle',
                        };
                    @endphp

                    <div class="relative flex gap-4 pb-{{ $isLast ? '0' : '5' }}">
                        {{-- Icon Node --}}
                        <div class="relative z-10 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full {{ $iconColor }} {{ $isCurrent ? 'ring-4 ring-emerald-100' : '' }}">
                            <i class="fa-solid {{ $icon }} text-sm"></i>
                        </div>

                        {{-- Content --}}
                        <div class="min-w-0 flex-1 pt-0.5">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-slate-900">
                                        {{ $statusLabel }}
                                        @if($isCurrent && !$isTerminal)
                                            <span class="ml-1.5 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">
                                                Saat ini
                                            </span>
                                        @endif
                                        @if($isTerminal)
                                            <span class="ml-1.5 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">
                                                Final
                                            </span>
                                        @endif
                                    </p>

                                    {{-- Notes --}}
                                    @if($notes)
                                        <p class="mt-1 text-sm text-slate-600">{{ $notes }}</p>
                                    @endif

                                    {{-- Actor --}}
                                    <div class="mt-1.5 flex items-center gap-1.5 text-xs text-slate-500">
                                        <i class="fa-solid fa-user"></i>
                                        <span>{{ $triggeredBy }}</span>
                                        <span class="text-slate-300">•</span>
                                        <i class="fa-solid fa-calendar"></i>
                                        <span>{{ $date }}</span>
                                        <span class="text-slate-300">•</span>
                                        <i class="fa-solid fa-clock"></i>
                                        <span>{{ $time }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
