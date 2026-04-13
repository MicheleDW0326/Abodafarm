<?php

use Livewire\Component;

new class extends Component {}; ?>

<section class="mt-10 space-y-6">
    <div class="relative mb-5">
        <h3 class="text-2xl font-black tracking-tight text-white">{{ __('Delete account') }}</h3>
        <p class="mt-2 text-sm leading-6 text-zinc-400">{{ __('Delete your account and all of its resources') }}</p>
    </div>

    <button type="button" x-on:click="$dispatch('open-delete-user-modal')" class="rounded-2xl border border-red-400/20 bg-red-500/10 px-5 py-3 text-sm font-semibold text-red-200 transition hover:bg-red-500/20" data-test="delete-user-button">
            {{ __('Delete account') }}
    </button>

    <livewire:pages::settings.delete-user-modal />
</section>
