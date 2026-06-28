@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        // Listen untuk notification events dari semua komponen Livewire
        Livewire.on('showAlert', (data) => {
            const { title, message, type = 'success', icon = 'success', confirmText = 'OK' } = data[0];

            Swal.fire({
                title: title || (type === 'success' ? 'Berhasil!' : 'Error!'),
                text: message,
                icon: icon || (type === 'success' ? 'success' : 'error'),
                confirmButtonColor: '#1A7FA8',
                confirmButtonText: confirmText,
                allowOutsideClick: false,
                allowEscapeKey: false,
                timer: type === 'success' ? 2000 : null,
                timerProgressBar: type === 'success'
            });
        });

        // Listen untuk confirm dialog - call server method via wire:id
        // Data[1] contains the component id from Livewire event system
        Livewire.on('showConfirm', (data, componentId) => {
            const { title, message, confirmText = 'Ya', cancelText = 'Tidak', confirmAction } = data[0];
            const sourceComponentId = componentId || data[1];

            Swal.fire({
                title: title || 'Konfirmasi',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: confirmText,
                cancelButtonText: cancelText
            }).then((result) => {
                if (result.isConfirmed && confirmAction) {
                    // Use the component that dispatched the event
                    const component = sourceComponentId ? Livewire.find(sourceComponentId) : Livewire.first();

                    if (component && typeof component.call === 'function') {
                        component.call(confirmAction);
                    } else {
                        console.error('Livewire component not found for action:', confirmAction);
                    }
                }
            });
        });
    });
</script>
@endpush
