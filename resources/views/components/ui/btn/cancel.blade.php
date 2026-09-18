{{--
    Cancel/Outline Button Alias

    Usage:
    <x-ui.btn.cancel wire:click="cancel">
        Batal
    </x-ui.btn.cancel>
--}}

<x-ui.btn.button {{ $attributes->merge(['variant' => 'outline']) }}>
    {{ $slot }}
</x-ui.btn.button>
