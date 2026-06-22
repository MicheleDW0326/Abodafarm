<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return to_route('login');
        }

        $user = Auth::user();

        if ($user->hasRole('pelanggan')) {
            return to_route('pelanggan.dashboard');
        }

        if ($user->hasRole('peternak')) {
            return to_route('peternak.dashboard');
        }

        if ($user->hasRole('staf')) {
            return to_route('staff.dashboard');
        }

        if (! $user->hasRole('admin')) {
            abort(403);
        }

        return $next($request);
    }
}
