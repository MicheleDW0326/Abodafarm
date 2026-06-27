<div x-data="{ show: false }" class="relative">
    <button
        type="button"
        wire:click="sendTestNotification"
        wire:disabled="isLoading"
        @click="show = true"
        class="flex items-center gap-2 rounded-lg bg-gradient-to-r from-emerald-500 to-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-emerald-500/30 transition-all hover:from-emerald-600 hover:to-emerald-700 hover:shadow-emerald-500/50 disabled:opacity-50 disabled:cursor-not-allowed"
    >
        @if($isLoading)
            <svg class="size-4 animate-spin" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Mengirim...</span>
        @else
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M9.5 19a2.5 2.5 0 0 0 5 0" stroke-linecap="round"/>
            </svg>
            <span>Test Notifikasi</span>
        @endif
    </button>

    {{-- Status Toast --}}
    <div
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        @click.away="show = false"
        class="absolute right-0 top-full z-50 mt-2 w-80 rounded-xl border p-4 shadow-xl"
        :class="{
            'bg-emerald-50 border-emerald-200 text-emerald-800': '{{ $status }}' !== '',
            'bg-red-50 border-red-200 text-red-800': '{{ $error }}' !== ''
        }"
        x-init="setTimeout(() => show = false, 5000)"
    >
        @if($status)
            <div class="flex items-start gap-3">
                <div class="rounded-full bg-emerald-100 p-1.5">
                    <svg class="size-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 6L9 17l-5-5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium">Berhasil!</p>
                    <p class="mt-1 text-xs opacity-80">{{ $status }}</p>
                </div>
            </div>
        @elseif($error)
            <div class="flex items-start gap-3">
                <div class="rounded-full bg-red-100 p-1.5">
                    <svg class="size-4 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 8v4M12 16h.01" stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium">Gagal</p>
                    <p class="mt-1 text-xs opacity-80">{{ $error }}</p>
                </div>
            </div>
        @endif
    </div>
</div>
