<?php

namespace App\Livewire\Pelanggan;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Illuminate\Validation\Rules\Password;

#[Layout('layouts.main')]
#[Title('Profil Saya - Akun Saya')]
class MyProfile extends Component
{
    public string $name = '';
    public string $email = '';
    public string $phone = '';

    // Password change fields
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public bool $showPasswordForm = false;
    public bool $isUpdating = false;
    public bool $isUpdatingPassword = false;

    protected array $messages = [
        'name.required' => 'Nama lengkap harus diisi.',
        'email.required' => 'Email harus diisi.',
        'email.email' => 'Format email tidak valid.',
        'email.unique' => 'Email sudah digunakan akun lain.',
        'password.required' => 'Password baru harus diisi.',
        'password.min' => 'Password minimal 8 karakter.',
        'password.confirmed' => 'Konfirmasi password tidak cocok.',
    ];

    public function mount(): void
    {
        $user = auth()->user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
    }

    public function render()
    {
        return view('livewire.pelanggan.my-profile');
    }

    public function updateProfile(): void
    {
        $this->isUpdating = true;

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . auth()->id()],
        ]);

        $user = auth()->user();
        $user->update([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
        ]);

        $this->isUpdating = false;

        $this->dispatch('notify', [
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => 'Profil berhasil diperbarui.',
        ]);
    }

    public function updatePassword(): void
    {
        $this->isUpdatingPassword = true;

        $this->validate([
            'current_password' => ['required'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = auth()->user();

        if (!Hash::check($this->current_password, $user->password)) {
            $this->isUpdatingPassword = false;
            $this->addError('current_password', 'Password saat ini tidak正确.');
            return;
        }

        $user->update([
            'password' => Hash::make($this->password),
        ]);

        $this->isUpdatingPassword = false;
        $this->showPasswordForm = false;
        $this->reset(['current_password', 'password', 'password_confirmation']);

        $this->dispatch('notify', [
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => 'Password berhasil diperbarui.',
        ]);
    }

    public function togglePasswordForm(): void
    {
        $this->showPasswordForm = !$this->showPasswordForm;
        if (!$this->showPasswordForm) {
            $this->reset(['current_password', 'password', 'password_confirmation']);
            $this->resetValidation();
        }
    }
}
