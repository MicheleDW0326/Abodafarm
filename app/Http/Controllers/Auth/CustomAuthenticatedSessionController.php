<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Http\Responses\LoginResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\LoginViewResponse;
use Laravel\Fortify\Contracts\VerifyPathResponse;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;

class CustomAuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(Request $request): LoginViewResponse|VerifyPathResponse
    {
        return app(LoginViewResponse::class);
    }

    /**
     * Attempt to authenticate a new session.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse|LoginResponseContract
    {
        $this->validateLogin($request);

        // If rate limiting is enabled, check before authentication
        if (config('fortify.limiters.login')) {
            $this->checkTooManyFailedAttempts($request);
        }

        // Attempt to authenticate
        if (!$this->attemptLogin($request)) {
            // Authentication failed - determine the specific error
            $this->handleFailedAuthentication($request);
        }

        // Clear rate limiter on successful login
        RateLimiter::clear($this->throttleKey($request));

        $request->session()->regenerate();

        return app(LoginResponse::class);
    }

    /**
     * Validate the user login request.
     *
     * @throws ValidationException
     */
    protected function validateLogin(Request $request): void
    {
        $request->validate([
            Fortify::username() => 'required|string|email',
            'password' => 'required|string',
        ], [
            Fortify::username() . '.required' => 'Alamat email harus diisi.',
            Fortify::username() . '.email' => 'Format alamat email tidak valid.',
            'password.required' => 'Kata sandi harus diisi.',
        ]);
    }

    /**
     * Attempt to log the user in.
     */
    protected function attemptLogin(Request $request): bool
    {
        return Auth::attempt(
            $request->only(Fortify::username(), 'password'),
            $request->boolean('remember')
        );
    }

    /**
     * Handle failed authentication with specific error messages.
     *
     * @throws ValidationException
     */
    protected function handleFailedAuthentication(Request $request): void
    {
        $email = $request->input(Fortify::username());

        // Check if email exists in database
        $userExists = \App\Models\User::where('email', strtolower($email))->exists();

        if (!$userExists) {
            // Email not registered
            throw ValidationException::withMessages([
                Fortify::username() => ['Email tidak terdaftar, gunakan email yang terdaftar.'],
            ]);
        }

        // Email exists but wrong password
        throw ValidationException::withMessages([
            'password' => ['Kata sandi yang Anda masukkan salah.'],
        ]);
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    protected function checkTooManyFailedAttempts(Request $request): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            Fortify::username() => [trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ])],
        ]);
    }

    /**
     * Get the rate limiting throttle key for the given request.
     */
    protected function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}