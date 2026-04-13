<?php

use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component {
    #[Locked]
    public bool $requiresConfirmation;

    #[Locked]
    public string $qrCodeSvg = '';

    #[Locked]
    public string $manualSetupKey = '';

    public bool $showVerificationStep = false;

    public bool $setupComplete = false;

    #[Validate('required|string|size:6', onUpdate: false)]
    public string $code = '';

    /**
     * Mount the component.
     */
    public function mount(bool $requiresConfirmation): void
    {
        $this->requiresConfirmation = $requiresConfirmation;
    }

    #[On('start-two-factor-setup')]
    public function startTwoFactorSetup(): void
    {
        $enableTwoFactorAuthentication = app(EnableTwoFactorAuthentication::class);
        $enableTwoFactorAuthentication(auth()->user());

        $this->loadSetupData();
    }

    /**
     * Load the two-factor authentication setup data for the user.
     */
    private function loadSetupData(): void
    {
        $user = auth()->user()?->fresh();

        try {
            if (! $user || ! $user->two_factor_secret) {
                throw new Exception('Two-factor setup secret is not available.');
            }

            $this->qrCodeSvg = $user->twoFactorQrCodeSvg();
            $this->manualSetupKey = decrypt($user->two_factor_secret);
        } catch (Exception) {
            $this->addError('setupData', 'Failed to fetch setup data.');

            $this->reset('qrCodeSvg', 'manualSetupKey');
        }
    }

    /**
     * Show the two-factor verification step if necessary.
     */
    public function showVerificationIfNecessary(): void
    {
        if ($this->requiresConfirmation) {
            $this->showVerificationStep = true;

            $this->resetErrorBag();

            return;
        }

        $this->closeModal();
        $this->dispatch('two-factor-enabled');
    }

    /**
     * Confirm two-factor authentication for the user.
     */
    public function confirmTwoFactor(ConfirmTwoFactorAuthentication $confirmTwoFactorAuthentication): void
    {
        $this->validate();

        $confirmTwoFactorAuthentication(auth()->user(), $this->code);

        $this->setupComplete = true;

        $this->closeModal();

        $this->dispatch('two-factor-enabled');
    }

    /**
     * Reset two-factor verification state.
     */
    public function resetVerification(): void
    {
        $this->reset('code', 'showVerificationStep');

        $this->resetErrorBag();
    }

    /**
     * Close the two-factor authentication modal.
     */
    public function closeModal(): void
    {
        $this->reset(
            'code',
            'manualSetupKey',
            'qrCodeSvg',
            'showVerificationStep',
            'setupComplete',
        );

        $this->resetErrorBag();
    }

    /**
     * Get the current modal configuration state.
     */
    public function getModalConfigProperty(): array
    {
        if ($this->setupComplete) {
            return [
                'title' => __('Two-factor authentication enabled'),
                'description' => __('Two-factor authentication is now enabled. Scan the QR code or enter the setup key in your authenticator app.'),
                'buttonText' => __('Close'),
            ];
        }

        if ($this->showVerificationStep) {
            return [
                'title' => __('Verify authentication code'),
                'description' => __('Enter the 6-digit code from your authenticator app.'),
                'buttonText' => __('Continue'),
            ];
        }

        return [
            'title' => __('Enable two-factor authentication'),
            'description' => __('To finish enabling two-factor authentication, scan the QR code or enter the setup key in your authenticator app.'),
            'buttonText' => __('Continue'),
        ];
    }
}; ?>

<div
    x-data="{ open: false }"
    x-on:open-two-factor-setup-modal.window="open = true"
    x-on:close-two-factor-setup-modal.window="open = false"
    x-cloak
>
    <div x-show="open" x-transition class="fixed inset-0 z-50 flex items-center justify-center px-4 py-8">
        <div class="absolute inset-0 bg-black/70" x-on:click="open = false; $dispatch('close-two-factor-setup-modal')"></div>

        <div class="relative w-full max-w-md rounded-3xl border border-white/10 bg-zinc-950 p-6 shadow-2xl shadow-black/40">
            <div class="space-y-6">
                <div class="flex flex-col items-center space-y-4">
                    <div class="rounded-full border border-white/10 bg-white/5 p-4">
                        <div class="flex size-16 items-center justify-center rounded-full border border-white/10 bg-white/5">
                            <svg class="size-8 text-sky-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M3 7h18M3 12h18M3 17h18"></path>
                            </svg>
                        </div>
                    </div>

                    <div class="space-y-2 text-center">
                        <h3 class="text-2xl font-black tracking-tight text-white">{{ $this->modalConfig['title'] }}</h3>
                        <p class="text-sm leading-6 text-zinc-400">{{ $this->modalConfig['description'] }}</p>
                    </div>
                </div>

                @if ($showVerificationStep)
                    <div class="space-y-6">
                        <div class="flex flex-col items-center justify-center space-y-3">
                            <input
                                name="code"
                                wire:model="code"
                                type="text"
                                inputmode="numeric"
                                maxlength="6"
                                autocomplete="one-time-code"
                                placeholder="000000"
                                class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-center tracking-[0.4em] text-white outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-400/20"
                            />
                        </div>

                        <div class="flex items-center gap-3">
                            <button type="button" wire:click="resetVerification" class="flex-1 rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm font-semibold text-white transition hover:bg-white/10">
                                {{ __('Back') }}
                            </button>

                            <button type="button" wire:click="confirmTwoFactor" class="flex-1 rounded-2xl bg-sky-500 px-4 py-3 text-sm font-semibold text-white transition hover:bg-sky-400 disabled:cursor-not-allowed disabled:opacity-50" x-bind:disabled="$wire.code.length < 6">
                                {{ __('Confirm') }}
                            </button>
                        </div>
                    </div>
                @else
                    @error('setupData')
                        <div class="rounded-2xl border border-red-400/20 bg-red-500/10 px-4 py-3 text-sm text-red-200">{{ $message }}</div>
                    @enderror

                    <div class="flex justify-center">
                        <div class="relative aspect-square w-64 overflow-hidden rounded-3xl border border-white/10 bg-white p-4">
                            @empty($qrCodeSvg)
                                <div class="absolute inset-0 flex items-center justify-center bg-zinc-100 animate-pulse">
                                    <div class="size-6 rounded-full border-2 border-sky-500/40 border-t-sky-500"></div>
                                </div>
                            @else
                                <div class="flex h-full items-center justify-center">
                                    <div class="rounded-2xl bg-white p-3">
                                        {!! $qrCodeSvg !!}
                                    </div>
                                </div>
                            @endempty
                        </div>
                    </div>

                    <div>
                        <button
                            type="button"
                            wire:click="showVerificationIfNecessary"
                            @disabled($errors->has('setupData'))
                            class="w-full rounded-2xl bg-sky-500 px-5 py-3 text-sm font-semibold text-white transition hover:bg-sky-400 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {{ $this->modalConfig['buttonText'] }}
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div class="relative flex items-center justify-center w-full">
                            <div class="absolute inset-0 top-1/2 h-px bg-white/10"></div>
                            <span class="relative bg-zinc-950 px-2 text-sm text-zinc-500">
                                {{ __('or, enter the code manually') }}
                            </span>
                        </div>

                        <div
                            class="flex items-center gap-2"
                            x-data="{
                                copied: false,
                                async copy() {
                                    try {
                                        await navigator.clipboard.writeText('{{ $manualSetupKey }}');
                                        this.copied = true;
                                        setTimeout(() => this.copied = false, 1500);
                                    } catch (e) {
                                        console.warn('Could not copy to clipboard');
                                    }
                                }
                            }"
                        >
                            <div class="flex w-full items-stretch overflow-hidden rounded-2xl border border-white/10 bg-white/5">
                                @empty($manualSetupKey)
                                    <div class="flex w-full items-center justify-center bg-white/5 p-3 text-sm text-zinc-400">
                                        {{ __('Loading...') }}
                                    </div>
                                @else
                                    <input type="text" readonly value="{{ $manualSetupKey }}" class="w-full bg-transparent px-4 py-3 text-white outline-none" />

                                    <button type="button" x-on:click="copy()" class="border-l border-white/10 px-4 py-3 text-sm font-semibold text-zinc-200 transition hover:bg-white/5">
                                        <span x-show="!copied">{{ __('Copy') }}</span>
                                        <span x-show="copied">{{ __('Copied') }}</span>
                                    </button>
                                @endempty
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
