<?php

use Livewire\Component;
use Livewire\Attributes\Title;

new #[Title('Appearance settings')] class extends Component {
    //
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <h2 class="sr-only">{{ __('Appearance settings') }}</h2>

    <x-pages::settings.layout :heading="__('Appearance')" :subheading="__('Update the appearance settings for your account')">
        <div
            x-data="{
                theme: localStorage.getItem('freshgo-theme') || 'system',
                setTheme(value) {
                    this.theme = value;
                    localStorage.setItem('freshgo-theme', value);
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    document.documentElement.classList.toggle('dark', value === 'dark' || (value === 'system' && prefersDark));
                },
                init() {
                    this.setTheme(this.theme);
                }
            }"
            x-init="init()"
            class="grid gap-3 sm:grid-cols-3"
        >
            <button type="button" x-on:click="setTheme('light')" class="rounded-3xl border p-4 text-left transition" :class="theme === 'light' ? 'border-sky-400/40 bg-sky-500/10 text-white' : 'border-white/10 bg-white/5 text-zinc-300 hover:bg-white/10'">
                <div class="text-sm font-semibold">{{ __('Light') }}</div>
                <div class="mt-1 text-xs leading-5 text-current/70">{{ __('Bright, clean view for daytime work.') }}</div>
            </button>

            <button type="button" x-on:click="setTheme('dark')" class="rounded-3xl border p-4 text-left transition" :class="theme === 'dark' ? 'border-sky-400/40 bg-sky-500/10 text-white' : 'border-white/10 bg-white/5 text-zinc-300 hover:bg-white/10'">
                <div class="text-sm font-semibold">{{ __('Dark') }}</div>
                <div class="mt-1 text-xs leading-5 text-current/70">{{ __('Low-light friendly interface with higher contrast.') }}</div>
            </button>

            <button type="button" x-on:click="setTheme('system')" class="rounded-3xl border p-4 text-left transition" :class="theme === 'system' ? 'border-sky-400/40 bg-sky-500/10 text-white' : 'border-white/10 bg-white/5 text-zinc-300 hover:bg-white/10'">
                <div class="text-sm font-semibold">{{ __('System') }}</div>
                <div class="mt-1 text-xs leading-5 text-current/70">{{ __('Follow the operating system preference.') }}</div>
            </button>
        </div>
    </x-pages::settings.layout>
</section>
