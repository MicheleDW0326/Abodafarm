<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    /**
     * Redirect authenticated users to their role dashboard.
     */
    public function toResponse($request)
    {
        $user = $request->user();

        if ($user?->hasRole('admin')) {
            return to_route('admin.dashboard');
        }

        if ($user?->hasRole('staf')) {
            return to_route('staff.dashboard');
        }

        if ($user?->hasRole('peternak')) {
            return to_route('peternak.dashboard');
        }

        // Redirect pelanggan ke home page, bukan dashboard
        return to_route('home');
    }
}
