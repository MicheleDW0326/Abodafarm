{{--
    Status Badge Component for AbodaFarm

    Usage:
    <x-ui.badge.status :status="$order->status" />

    Supports:
    - Order statuses: pending, processing, shipped, delivered, completed, cancelled, rejected
    - Payment statuses: pending, paid, failed, expired
    - Booking statuses: pending, approved, completed
    - General: active, inactive, draft, archived
--}}

@props([
    'status' => '',
    'size' => 'md', // sm, md, lg
    'showIcon' => true,
    'customLabel' => null,
])

@php
    $config = [
        // Order statuses
        'pending' => [
            'label' => 'Menunggu',
            'class' => 'bg-amber-100 text-amber-700 border-amber-200',
            'icon' => 'fa-clock',
        ],
        'processing' => [
            'label' => 'Diproses',
            'class' => 'bg-blue-100 text-blue-700 border-blue-200',
            'icon' => 'fa-gear',
        ],
        'approved' => [
            'label' => 'Disetujui',
            'class' => 'bg-teal-100 text-teal-700 border-teal-200',
            'icon' => 'fa-check-circle',
        ],
        'shipped' => [
            'label' => 'Dikirim',
            'class' => 'bg-violet-100 text-violet-700 border-violet-200',
            'icon' => 'fa-truck',
        ],
        'delivered' => [
            'label' => 'Diterima',
            'class' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
            'icon' => 'fa-hand-holding-box',
        ],
        'completed' => [
            'label' => 'Selesai',
            'class' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
            'icon' => 'fa-circle-check',
        ],
        'cancelled' => [
            'label' => 'Dibatalkan',
            'class' => 'bg-red-100 text-red-700 border-red-200',
            'icon' => 'fa-ban',
        ],
        'rejected' => [
            'label' => 'Ditolak',
            'class' => 'bg-red-100 text-red-700 border-red-200',
            'icon' => 'fa-times-circle',
        ],

        // Payment statuses
        'paid' => [
            'label' => 'Lunas',
            'class' => 'bg-green-100 text-green-700 border-green-200',
            'icon' => 'fa-credit-card',
        ],
        'failed' => [
            'label' => 'Gagal',
            'class' => 'bg-red-100 text-red-700 border-red-200',
            'icon' => 'fa-exclamation-circle',
        ],
        'expired' => [
            'label' => 'Kedaluwarsa',
            'class' => 'bg-slate-100 text-slate-600 border-slate-200',
            'icon' => 'fa-clock',
        ],

        // General statuses
        'active' => [
            'label' => 'Aktif',
            'class' => 'bg-green-100 text-green-700 border-green-200',
            'icon' => 'fa-check',
        ],
        'inactive' => [
            'label' => 'Nonaktif',
            'class' => 'bg-slate-100 text-slate-600 border-slate-200',
            'icon' => 'fa-times',
        ],
        'draft' => [
            'label' => 'Draft',
            'class' => 'bg-slate-100 text-slate-600 border-slate-200',
            'icon' => 'fa-file',
        ],
        'archived' => [
            'label' => 'Arsip',
            'class' => 'bg-gray-100 text-gray-600 border-gray-200',
            'icon' => 'fa-archive',
        ],

        // Stock statuses
        'in_stock' => [
            'label' => 'Tersedia',
            'class' => 'bg-green-100 text-green-700 border-green-200',
            'icon' => 'fa-box',
        ],
        'low_stock' => [
            'label' => 'Stok Terbatas',
            'class' => 'bg-amber-100 text-amber-700 border-amber-200',
            'icon' => 'fa-triangle-exclamation',
        ],
        'out_of_stock' => [
            'label' => 'Stok Habis',
            'class' => 'bg-red-100 text-red-700 border-red-200',
            'icon' => 'fa-times-circle',
        ],

        // Availability
        'available' => [
            'label' => 'Siap Jual',
            'class' => 'bg-green-100 text-green-700 border-green-200',
            'icon' => 'fa-tag',
        ],
        'pre_order' => [
            'label' => 'Pre Order',
            'class' => 'bg-violet-100 text-violet-700 border-violet-200',
            'icon' => 'fa-calendar-plus',
        ],
        'in_care' => [
            'label' => 'Dalam Perawatan',
            'class' => 'bg-blue-100 text-blue-700 border-blue-200',
            'icon' => 'fa-heart-pulse',
        ],
        'sold' => [
            'label' => 'Terjual',
            'class' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
            'icon' => 'fa-handshake',
        ],
    ];

    $statusKey = strtolower($status);
    $data = $config[$statusKey] ?? [
        'label' => $customLabel ?? ucfirst($status),
        'class' => 'bg-slate-100 text-slate-600 border-slate-200',
        'icon' => 'fa-circle',
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
    {{ $customLabel ?? $data['label'] }}
</span>
