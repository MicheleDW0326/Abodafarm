{{--
    Table Row Component for AbodaFarm

    Usage:
    <x-ui.table.row>
        <td>Cell 1</td>
        <td>Cell 2</td>
    </x-ui.table.row>
--}}

@props([
    'hover' => true,
    'clickable' => false,
])

<tr class="{{ $hover ? 'hover:bg-slate-50' : '' }} {{ $clickable ? 'cursor-pointer' : '' }} transition-colors">
    {{ $slot }}
</tr>
