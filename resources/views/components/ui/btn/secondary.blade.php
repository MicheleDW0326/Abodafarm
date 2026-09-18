{{--
    Secondary Button Alias

    Usage:
    <x-ui.btn.secondary wire:click="cancel">
        Batal
    </x-ui.btn.secondary>
--}}

<x-ui.btn.button {{ $attributes->merge(['variant' => 'secondary']) }}>
    {{ $slot }}
</x-ui.btn.button>
