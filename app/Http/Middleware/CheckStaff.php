<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckStaff
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

        // Prevent admin from accessing staff pages
        if ($user->hasRole('admin')) {
            return to_route('admin.dashboard');
        }

        // Prevent farmer from accessing staff pages
        if ($user->hasRole('peternak')) {
            return to_route('peternak.dashboard');
        }

        // Prevent customer from accessing staff pages
        if ($user->hasRole('pelanggan')) {
            return to_route('pelanggan.dashboard');
        }

        if (! $user->hasRole('staf')) {
            abort(403);
        }

        return $next($request);
    }
}
