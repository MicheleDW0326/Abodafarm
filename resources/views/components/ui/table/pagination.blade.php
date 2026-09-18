{{--
    Table Pagination Component for AbodaFarm

    Usage:
    <x-ui.table.pagination
        :paginator="$orders"
        :showInfo="true"
    />

    Or with slots:
    <x-ui.table.pagination>
        <x-slot:info>
            Showing {{ $orders->firstItem() }} - {{ $orders->lastItem() }}
        </x-slot:info>
    </x-ui.table.pagination>
--}}

@props([
    'paginator' => null,
    'showInfo' => true,
    'showLinks' => true,
])

@php
    $hasPages = $paginator && $paginator->hasPages();
@endphp

@if($hasPages || $showInfo)
    <div class="flex items-center justify-between border-t border-slate-200 px-4 py-3">
        {{-- Info --}}
        @if($showInfo && $paginator)
            <p class="text-sm text-slate-500">
                Menampilkan {{ $paginator->firstItem() ?? 0 }} - {{ $paginator->lastItem() ?? 0 }}
                dari {{ $paginator->total() }}
            </p>
        @else
            <div></div>
        @endif

        {{-- Links --}}
        @if($showLinks && $paginator)
            <div>
                {{ $paginator->links() }}
            </div>
        @endif

        {{-- Custom slot for extra info --}}
        {{ $slot }}
    </div>
@endif
