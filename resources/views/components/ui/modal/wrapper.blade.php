{{--
    Reusable Modal Component for AbodaFarm

    Usage:
    <x-ui.modal.wrapper
        :show="$showModal"
        title="Judul Modal"
        size="lg"
        wire:close="closeModal"
    >
        <x-slot name="icon">fa-solid fa-plus</x-slot>
        <x-slot name="body">
            <!-- Content here -->
        </x-slot>
        <x-slot name="footer">
            <x-ui.button.cancel wire:click="closeModal" />
            <x-ui.button.primary wire:click="save" />
        </x-slot>
    </x-ui.modal.wrapper>
--}}

@props([
    'show' => false,           // boolean - controls visibility
    'title' => '',              // string - modal title
    'size' => 'md',            // sm|md|lg|xl|2xl|full
    'closeable' => true,        // boolean - show close button
    'closeOnBackdrop' => true,  // boolean - close on backdrop click
    'closeOnEscape' => true,    // boolean - close on ESC key
    'centered' => true,        // boolean - center vertically
    'icon' => null,            // string|null - FontAwesome icon class
    'footerClass' => '',       // string - additional footer classes
])

@php
    $sizeClasses = match($size) {
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        '3xl' => 'max-w-3xl',
        'full' => 'max-w-full',
        default => 'max-w-lg',
    };
@endphp

<div
    x-data="{
        show: @entangle($attributes->get('wire:close') ? 'show' : 'show'),
        init() {
            this.$watch('show', value => {
                document.body.style.overflow = value ? 'hidden' : '';
            });
        }
    }"
    x-on:keydown.escape.window="{{ $closeOnEscape ? 'show = false; ' . ($attributes->get('wire:close') ?? '') : '' }}"
    x-show="show"
    x-transition:enter="ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-[99999] overflow-y-auto"
    role="dialog"
    aria-modal="true"
>
    <!-- Backdrop -->
    <div
        @if($closeOnBackdrop) x-on:click="show = false" @endif
        class="fixed inset-0 bg-black/50 transition-opacity"
        x-show="show"
    ></div>

    <!-- Modal Container -->
    <div
        class="flex min-h-full items-center justify-center p-4"
        x-show="show"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
    >
        <!-- Modal Panel -->
        <div
            @if(!$centered) style="margin-top: 2rem; margin-bottom: 2rem;" @endif
            class="w-full {{ $sizeClasses }} rounded-xl bg-white shadow-2xl overflow-hidden"
            @click.stop
        >
            <!-- Header -->
            @if($title || $closeable)
                <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                    <div class="flex items-center gap-3">
                        @if($icon)
                            <div class="flex-shrink-0">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100">
                                    <i class="{{ $icon }} text-emerald-600"></i>
                                </div>
                            </div>
                        @endif
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">
                                {{ $title }}
                            </h3>
                        </div>
                    </div>

                    @if($closeable)
                        <button
                            x-on:click="show = false"
                            type="button"
                            class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    @endif
                </div>
            @endif

            <!-- Body -->
            <div class="max-h-[calc(90vh-160px)] overflow-y-auto px-6 py-4">
                {{ $body ?? '' }}
            </div>

            <!-- Footer -->
            @if(isset($footer) || trim($footer ?? ''))
                <div class="flex items-center justify-end gap-3 border-t border-slate-200 px-6 py-4 bg-slate-50 {{ $footerClass }}">
                    {{ $footer ?? '' }}
                </div>
            @endif
        </div>
    </div>
</div>
