{{--
    Data Table Component for AbodaFarm

    Usage:
    <x-ui.table.data-table
        :columns="['Nama', 'Email', 'Aksi']"
        :data="$users"
        :sortable="[true, true, false]"
        :sortField="$sortField"
        :sortDirection="$sortDirection"
    >
        <x-slot:row :loop="$users as $user">
            <td class="px-4 py-3">{{ $user->name }}</td>
            <td class="px-4 py-3">{{ $user->email }}</td>
            <td class="px-4 py-3">
                <button>Edit</button>
            </td>
        </x-slot:row>
    </x-ui.table.data-table>
--}}

@props([
    'columns' => [],
    'sortable' => [],
    'sortField' => '',
    'sortDirection' => 'asc',
    'emptyTitle' => 'Tidak ada data',
    'emptyDescription' => 'Data akan muncul di sini',
    'emptyIcon' => 'fa-inbox',
    'striped' => true,
    'hover' => true,
    'compact' => false,
])

@php
    $rowClass = $striped ? 'odd:bg-slate-50' : '';
    $rowClass .= $hover ? ' hover:bg-slate-50' : '';
    $cellPadding = $compact ? 'px-3 py-2' : 'px-4 py-3';
@endphp

<div class="rounded-xl border border-slate-200 bg-white overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50">
                <tr>
                    @foreach($columns as $index => $column)
                        @php
                            $isSortable = isset($sortable[$index]) ? $sortable[$index] : false;
                            $isSorted = $sortField === $column;
                        @endphp
                        <th class="{{ $cellPadding }} text-left font-semibold text-slate-900
                            {{ $isSortable ? 'cursor-pointer hover:bg-slate-100 select-none' : '' }}">
                            @if($isSortable)
                                <button
                                    type="button"
                                    wire:click="$emit('sort', '{{ $column }}')"
                                    class="flex items-center gap-1.5 hover:text-emerald-600 transition-colors"
                                >
                                    {{ $column }}
                                    @if($isSorted)
                                        <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-emerald-600"></i>
                                    @else
                                        <i class="fa-solid fa-sort text-slate-300"></i>
                                    @endif
                                </button>
                            @else
                                {{ $column }}
                            @endif
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                {{ $row ?? '' }}
            </tbody>
        </table>
    </div>

    {{-- Pagination slot --}}
    @if(isset($pagination))
        {{ $pagination }}
    @endif
</div>
