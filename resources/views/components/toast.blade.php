<div x-show="showToast" x-transition
    :class="toastType === 'error' ? 'bg-red-600' : 'bg-emerald-600'"
    class="fixed bottom-5 right-5 z-[60] flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold text-white shadow-xl">
    <template x-if="toastType === 'success'">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
    </template>
    <template x-if="toastType === 'error'">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </template>
    <span x-text="toastText"></span>
</div>
