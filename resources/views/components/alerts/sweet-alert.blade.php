@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        // Map type to icon for consistent styling
        const iconMap = {
            'success': { icon: 'success', color: '#10b981' },  // green
            'error': { icon: 'error', color: '#ef4444' },       // red
            'warning': { icon: 'warning', color: '#f59e0b' },  // yellow/amber
            'info': { icon: 'info', color: '#3b82f6' },        // blue
            'validation': { icon: 'warning', color: '#f59e0b' }, // yellow for validation errors
        };

        // Default titles per type
        const defaultTitles = {
            'success': 'Berhasil!',
            'error': 'Gagal!',
            'warning': 'Perhatian!',
            'info': 'Informasi',
            'validation': 'Validasi Gagal',
        };

        // Listen untuk notification events dari semua komponen Livewire
        Livewire.on('showAlert', (eventData) => {
            // Handle both array and direct object format
            const data = Array.isArray(eventData) ? eventData[0] : eventData;
            const { title, message, type = 'success', icon, confirmText = 'OK' } = data || {};

            // Determine icon based on type or explicit icon parameter
            const iconConfig = iconMap[type] || iconMap.success;
            const finalIcon = icon || iconConfig.icon;
            const finalTitle = title || defaultTitles[type] || defaultTitles.success;

            Swal.fire({
                title: finalTitle,
                text: message,
                icon: finalIcon,
                confirmButtonColor: iconConfig.color,
                confirmButtonText: confirmText,
                allowOutsideClick: false,
                allowEscapeKey: false,
                timer: type === 'success' ? 3000 : null,
                timerProgressBar: type === 'success'
            });
        });

        // Listen untuk confirm dialog - call server method via wire:id
        Livewire.on('showConfirm', (eventData) => {
            // Handle both array and direct object format
            const data = Array.isArray(eventData) ? eventData[0] : eventData;
            const { title, message, confirmText = 'Ya', cancelText = 'Tidak', confirmAction, componentId, id, type = 'warning' } = data || {};

            const iconConfig = iconMap[type] || iconMap.warning;

            Swal.fire({
                title: title || 'Konfirmasi',
                text: message,
                icon: iconConfig.icon,
                showCancelButton: true,
                confirmButtonColor: iconConfig.color,
                cancelButtonColor: '#6b7280',
                confirmButtonText: confirmText,
                cancelButtonText: cancelText
            }).then((result) => {
                if (result.isConfirmed && confirmAction) {
                    // Find the specific component that dispatched the event
                    const component = componentId ? Livewire.find(componentId) : Livewire.first();

                    if (component && typeof component.call === 'function') {
                        // Pass id as parameter if available
                        if (id !== undefined) {
                            component.call(confirmAction, id);
                        } else {
                            component.call(confirmAction);
                        }
                    } else {
                        console.error('Livewire component not found for action:', confirmAction);
                    }
                }
            });
        });
    });
</script>
@endpush
