{{--
    Primary Button Alias

    Usage:
    <x-ui.btn.primary wire:click="save">
        Simpan
    </x-ui.btn.primary>
--}}

<x-ui.btn.button {{ $attributes->merge(['variant' => 'primary']) }}>
    {{ $slot }}
</x-ui.btn.button>
