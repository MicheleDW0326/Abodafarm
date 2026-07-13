<div
    x-data="{
        loading: true,
        showForNavigation: false,
        init() {
            // Initial page load - always show
            window.addEventListener('load', () => {
                setTimeout(() => {
                    this.loading = false;
                }, 500);
            });

            // Fallback: hide after 3 seconds max
            setTimeout(() => {
                this.loading = false;
            }, 3000);

            // Listen for navigation loading events
            window.addEventListener('livewire:navigating', (event) => {
                const importantRoutes = [
                    'checkout', 'payment', 'login', 'logout', 'register',
                    'order.confirmation', 'booking.confirmation'
                ];
                const url = event.detail?.url || window.location.pathname;
                const isImportant = importantRoutes.some(route => url.includes(route));

                if (isImportant) {
                    this.showForNavigation = true;
                    this.loading = true;
                }
            });

            window.addEventListener('livewire:navigated', () => {
                this.loading = false;
                this.showForNavigation = false;
            });
        }
    }"
    x-show="loading"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-[9999] flex items-center justify-center bg-emerald-900"
>
    <div class="text-center">
        <!-- Animated Logo/Spinner -->
        <div class="relative w-24 h-24 mx-auto mb-6">
            <!-- Outer ring -->
            <div class="absolute inset-0 border-4 border-emerald-700 rounded-full"></div>
            <!-- Spinning ring -->
            <div class="absolute inset-0 border-4 border-transparent border-t-emerald-400 rounded-full animate-spin"></div>
            <!-- Inner ring -->
            <div class="absolute inset-3 border-4 border-transparent border-b-emerald-300 rounded-full animate-spin" style="animation-direction: reverse; animation-duration: 1.5s;"></div>
            <!-- Center icon -->
            <div class="absolute inset-0 flex items-center justify-center">
                <svg class="w-8 h-8 text-emerald-300" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
            </div>
        </div>

        <!-- Brand Name -->
        <h1 class="text-2xl font-bold text-white mb-2 tracking-wide">
            AbodaFarm
        </h1>
        <p class="text-emerald-300 text-sm tracking-widest uppercase">
            Ternak Organik Premium
        </p>

        <!-- Loading dots -->
        <div class="flex justify-center gap-1 mt-6">
            <div class="w-2 h-2 bg-emerald-400 rounded-full animate-bounce" style="animation-delay: 0ms;"></div>
            <div class="w-2 h-2 bg-emerald-400 rounded-full animate-bounce" style="animation-delay: 150ms;"></div>
            <div class="w-2 h-2 bg-emerald-400 rounded-full animate-bounce" style="animation-delay: 300ms;"></div>
        </div>
    </div>

    <!-- Background pattern -->
    <div class="absolute inset-0 opacity-10 pointer-events-none">
        <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
            <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
                <circle cx="1" cy="1" r="1" fill="currentColor" class="text-emerald-300"/>
            </pattern>
            <rect width="100%" height="100%" fill="url(#grid)"/>
        </svg>
    </div>
</div>
