<?php

namespace App\Http\Responses;

use Illuminate\Support\Facades\Session;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    /**
     * Redirect authenticated users to their role dashboard.
     */
    public function toResponse($request)
    {
        $user = $request->user();

        // Check for intended URL (e.g., checkout redirect)
        $intended = Session::pull('url.intended');
        if ($intended === '/checkout') {
            return to_route('checkout');
        }

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
