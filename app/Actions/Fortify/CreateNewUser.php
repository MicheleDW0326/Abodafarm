<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => ['required', 'string', Password::min(8)->mixedCase()->numbers()->symbols(), 'confirmed'],
        ], $this->customMessages())->validate();

        $user = User::create([
            'name' => trim($input['name']),
            'email' => strtolower(trim($input['email'])),
            'password' => $input['password'],
        ]);

        $user->assignRole('pelanggan');

        return $user;
    }

    /**
     * Get custom validation error messages in Indonesian.
     *
     * @return array<string, string>
     */
    protected function customMessages(): array
    {
        return [
            'name.required' => 'Nama lengkap harus diisi.',
            'name.regex' => 'Nama hanya boleh mengandung huruf dan spasi.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'email.required' => 'Alamat email harus diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Email sudah terdaftar. Gunakan email lain.',
            'email.exclude_if' => 'Domain email tidak valid. Gunakan email aktif.',
            'password.required' => 'Kata sandi harus diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.mixed' => 'Kata sandi harus mengandung huruf besar dan huruf kecil.',
            'password.numbers' => 'Kata sandi harus mengandung angka.',
            'password.symbols' => 'Kata sandi harus mengandung karakter khusus (!@#$%^&*)',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ];
    }
}
