<div
    x-data="{
        show: true,
        init() {
            setTimeout(() => {
                this.show = false;
                setTimeout(() => {
                    document.getElementById('splash-screen')?.remove();
                }, 800);
            }, 2500);
        }
    }"
    x-show="show"
    x-transition:leave="transition ease-in duration-700"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-110"
    id="splash-screen"
    class="fixed inset-0 z-[99999] flex flex-col items-center justify-center bg-white"
>
    {{-- Decorative Circles --}}
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -top-1/4 -left-1/4 size-[150%] animate-pulse rounded-full bg-emerald-100/50"></div>
        <div class="absolute -bottom-1/4 -right-1/4 size-[150%] animate-pulse rounded-full bg-emerald-50/80" style="animation-delay: 1s"></div>
    </div>

    {{-- Logo Container --}}
    <div class="relative">
        {{-- Glow Effect --}}
        <div class="absolute inset-0 rounded-full bg-emerald-200/40 blur-2xl animate-ping" style="animation-duration: 2s"></div>

        {{-- Main Logo --}}
        <div class="relative flex items-center justify-center">
            <img
                src="/icons/MainLogo.png"
                alt="AbodaFarm"
                class="size-48 drop-shadow-xl"
                x-data="{
                    bounce: false,
                    init() {
                        setTimeout(() => this.bounce = true, 500);
                    }
                }"
                :class="bounce ? 'animate-[bounce_1s_ease-out]' : 'opacity-0 scale-50'"
                x-transition:enter="transition ease-out duration-700"
                x-transition:enter-start="opacity-0 scale-50"
                x-transition:enter-end="opacity-100 scale-100"
            >
        </div>
    </div>

    {{-- Brand Name --}}
    <div class="mt-10 text-center">
        <h1
            class="text-5xl font-black tracking-tight text-emerald-700 drop-shadow-sm"
            x-data="{ fadeIn: false }"
            x-init="setTimeout(() => fadeIn = true, 800)"
            x-show="fadeIn"
            x-transition:enter="transition ease-out duration-700 delay-300"
            x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
        >
            Aboda<span class="text-emerald-500">Farm</span>
        </h1>
        <p
            class="mt-3 text-sm font-medium uppercase tracking-[0.3em] text-emerald-600/70"
            x-data="{ fadeIn: false }"
            x-init="setTimeout(() => fadeIn = true, 1000)"
            x-show="fadeIn"
            x-transition:enter="transition ease-out duration-700 delay-300"
            x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
        >
            Fresh from the Farm
        </p>
    </div>

    {{-- Loading Indicator --}}
    <div class="absolute bottom-12 flex flex-col items-center gap-3">
        <div class="flex gap-1.5">
            <span class="size-2 animate-bounce rounded-full bg-emerald-500" style="animation-delay: 0ms"></span>
            <span class="size-2 animate-bounce rounded-full bg-emerald-500" style="animation-delay: 150ms"></span>
            <span class="size-2 animate-bounce rounded-full bg-emerald-500" style="animation-delay: 300ms"></span>
        </div>
        <span class="text-xs font-medium text-emerald-600/60">Memuat...</span>
    </div>
</div>
