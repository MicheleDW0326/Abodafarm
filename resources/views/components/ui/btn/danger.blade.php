{{--
    Danger Button Alias

    Usage:
    <x-ui.btn.danger wire:click="delete">
        Hapus
    </x-ui.btn.danger>
--}}

<x-ui.btn.button {{ $attributes->merge(['variant' => 'danger']) }}>
    {{ $slot }}
</x-ui.btn.button>
