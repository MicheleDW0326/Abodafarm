{{--
    Modal Wrapper Component Alias

    Usage:
    <x-ui.modal
        :show="$showModal"
        title="Judul Modal"
        size="lg"
        wire:close="closeModal"
    >
        <x-slot name="body">
            <!-- Content -->
        </x-slot>
        <x-slot name="footer">
            <x-ui.btn.cancel wire:click="closeModal" />
            <x-ui.btn.primary wire:click="save" />
        </x-slot>
    </x-ui.modal>
--}}

<x-ui.modal.wrapper {{ $attributes }}>
    {{ $slot }}
</x-ui.modal.wrapper>
