<?php

namespace App\Providers;

use App\Http\Responses\LoginResponse;
use App\Services\FcmService;
use App\Services\MidtransService;
use App\Services\NotificationService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);

        // Register MidtransService as singleton
        $this->app->singleton(MidtransService::class, function ($app) {
            return new MidtransService();
        });

        // Register FcmService as singleton
        $this->app->singleton(FcmService::class, function ($app) {
            return new FcmService();
        });

        // Register NotificationService as singleton
        $this->app->singleton(NotificationService::class, function ($app) {
            return new NotificationService($app->make(FcmService::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
