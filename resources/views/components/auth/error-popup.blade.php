{{--
    Error Alert Popup Component for Authentication Pages
    Reusable Alpine.js popup untuk menampilkan error validasi

    Usage:
    1. Parent element harus memiliki x-data="authAlert()"
    2. Include partial ini di dalam form

    authAlert() function:
    function authAlert() {
        return {
            showAlert: false,
            alertType: 'error',
            alertTitle: '',
            alertMessage: '',

            triggerAlert(type, title, message) {
                this.alertType = type;
                this.alertTitle = title;
                this.alertMessage = message;
                this.showAlert = true;
            },

            closeAlert() {
                this.showAlert = false;
            }
        }
    }

    Trigger dari JavaScript:
    window.dispatchEvent(new CustomEvent('alert', {
        detail: { type: 'error', title: 'Judul', message: 'Pesan error' }
    }));
--}}

<div x-show="showAlert"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click="closeAlert()"
     @keydown.escape.window="closeAlert()"
     class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">

    <div x-show="showAlert"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-4"
         @click.stop
         class="w-full max-w-sm overflow-hidden rounded-2xl bg-white shadow-2xl">

        {{-- Header with Icon --}}
        <div class="relative px-6 pt-6 pb-0 bg-gradient-to-br from-rose-500 to-rose-600">
            <button type="button"
                    @click="closeAlert()"
                    class="absolute right-3 top-3 rounded-full p-1.5 text-white/80 transition-all hover:bg-white/20 hover:text-white">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            <div class="flex justify-center pb-5">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-white/20">
                    <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Content --}}
        <div class="px-6 pb-6 pt-4 text-center">
            <h3 class="text-lg font-bold text-slate-900" x-text="alertTitle"></h3>
            <p class="mt-2 text-sm text-slate-600 leading-relaxed" x-text="alertMessage"></p>
        </div>

        {{-- Action Button --}}
        <div class="px-6 pb-6">
            <button type="button"
                    @click="closeAlert()"
                    class="w-full rounded-xl bg-rose-500 py-3 text-sm font-semibold text-white transition-all hover:bg-rose-600">
                Coba Lagi
            </button>
        </div>
    </div>
</div>