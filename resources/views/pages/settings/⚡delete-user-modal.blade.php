<?php

use App\Concerns\PasswordValidationRules;
use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component {
    use PasswordValidationRules;

    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => $this->currentPasswordRules(),
        ]);

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div
    x-data="{ open: @js($errors->isNotEmpty()) }"
    x-on:open-delete-user-modal.window="open = true"
    x-on:close-delete-user-modal.window="open = false"
    x-cloak
>
    <div x-show="open" x-transition class="fixed inset-0 z-50 flex items-center justify-center px-4 py-8">
        <div class="absolute inset-0 bg-black/70" x-on:click="$dispatch('close-delete-user-modal')"></div>

        <div class="relative w-full max-w-lg rounded-3xl border border-white/10 bg-zinc-950 p-6 shadow-2xl shadow-black/40">
            <form method="POST" wire:submit="deleteUser" class="space-y-6">
                <div>
                    <h3 class="text-2xl font-black tracking-tight text-white">{{ __('Are you sure you want to delete your account?') }}</h3>
                    <p class="mt-2 text-sm leading-6 text-zinc-400">{{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}</p>
                </div>

                <div>
                    <label for="delete-password" class="mb-2 block text-sm font-medium text-white">{{ __('Password') }}</label>
                    <input id="delete-password" wire:model="password" type="password" required class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-white outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-400/20" />
                    @error('password')
                        <p class="mt-2 text-sm text-red-300">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" x-on:click="$dispatch('close-delete-user-modal')" class="rounded-2xl border border-white/10 bg-white/5 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/10">
                        {{ __('Cancel') }}
                    </button>

                    <button type="submit" data-test="confirm-delete-user-button" class="rounded-2xl border border-red-400/20 bg-red-500/10 px-5 py-3 text-sm font-semibold text-red-200 transition hover:bg-red-500/20">
                        {{ __('Delete account') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
