<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPelanggan
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return to_route('login');
        }

        $user = Auth::user();

        if ($user->hasRole('admin')) {
            return to_route('admin.dashboard');
        }

        if ($user->hasRole('peternak')) {
            return to_route('peternak.dashboard');
        }

        if ($user->hasRole('staf')) {
            return to_route('staff.dashboard');
        }

        if (! $user->hasRole('pelanggan')) {
            abort(403);
        }

        return $next($request);
    }
}
