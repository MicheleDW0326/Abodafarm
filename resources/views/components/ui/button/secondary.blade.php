{{--
    Secondary Button Alias

    Usage:
    <x-ui.btn.secondary wire:click="cancel">
        Batal
    </x-ui.btn.secondary>
--}}

<x-ui.button.button {{ $attributes->merge(['variant' => 'secondary']) }}>
    {{ $slot }}
</x-ui.button.button>
