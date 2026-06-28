<?php

namespace App\Concerns;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait ProfileValidationRules
{
    /**
     * Get the validation rules used to validate user profiles.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function profileRules(?int $userId = null): array
    {
        return [
            'name' => $this->nameRules(),
            'email' => $this->emailRules($userId),
        ];
    }

    /**
     * Get the validation rules used to validate user names.
     * Only allows letters (including Indonesian diacritics) and spaces.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function nameRules(): array
    {
        return [
            'required',
            'string',
            'max:255',
            'regex:/^[\pL\s]+$/u', // Only letters and spaces (including Indonesian diacritics)
        ];
    }

    /**
     * Get the validation rules used to validate user emails.
     * Includes blacklist for disposable/temporary email domains.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function emailRules(?int $userId = null): array
    {
        $disposableDomains = [
            'mailinator.com', 'tempmail.com', 'throwaway.email', 'guerrillamail.com',
            'guerrillamail.org', 'guerrillamail.net', 'sharklasers.com', 'grr.la',
            'trashmail.com', 'trashmail.net', 'fakeinbox.com', 'mailnesia.com',
            'tempmailaddress.com', 'getnada.com', 'getairmail.com', 'yopmail.com',
            '10minutemail.com', '10minutemail.net', 'temp-mail.org', 'tempail.com',
            'moakt.com', 'fake.com', 'sss.com', 'ssss.com', 'test.com', 'aaaa.com',
            'abc.com', 'test123.com', 'asdf.com', 'qwerty.com',
        ];

        return [
            'required',
            'string',
            'email:rfc,dns', // RFC validation + DNS check for MX records
            'max:255',
            Rule::excludeIf(function ($attribute, $value) use ($disposableDomains) {
                $domain = strtolower(explode('@', $value)[1] ?? '');
                return in_array($domain, $disposableDomains);
            }),
            $userId === null
                ? Rule::unique(User::class)
                : Rule::unique(User::class)->ignore($userId),
        ];
    }
}
