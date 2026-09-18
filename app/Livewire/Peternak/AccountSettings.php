<?php

namespace App\Livewire\Peternak;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.main')]
#[Title('Pengaturan Akun')]
class AccountSettings extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public string $currentPassword = '';
    public string $newPassword = '';
    public string $confirmPassword = '';

    public function mount()
    {
        $user = auth()->user();
        $this->name = $user->name;
        $this->email = $user->email;
    }

    public function updateProfile()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . auth()->id(),
        ]);

        try {
            auth()->user()->update([
                'name' => $this->name,
                'email' => $this->email,
            ]);

            session()->flash('success', 'Profil berhasil diperbarui!');
        } catch (\Exception $e) {
            \Log::error('Peternak profile update error: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan saat memperbarui profil. Silakan coba lagi.');
        }
    }

    public function updatePassword()
    {
        $this->validate([
            'currentPassword' => 'required|current_password',
            'newPassword' => 'required|min:8',
            'confirmPassword' => 'required|same:newPassword',
        ]);

        try {
            auth()->user()->update([
                'password' => bcrypt($this->newPassword),
            ]);

            $this->currentPassword = '';
            $this->newPassword = '';
            $this->confirmPassword = '';

            session()->flash('success', 'Password berhasil diubah!');
        } catch (\Exception $e) {
            \Log::error('Peternak password update error: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan saat mengubah password. Silakan coba lagi.');
        }
    }

    public function render()
    {
        return view('livewire.peternak.account-settings');
    }
}
