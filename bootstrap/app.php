<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use App\Services\ErrorService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function ($schedule) {
        // Cleanup old notifications every hour
        $schedule->command('notifications:cleanup --hours=24')->hourly();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        ]);

        $middleware->alias([
            'check.admin' => \App\Http\Middleware\CheckAdmin::class,
            'check.staff' => 'App\\Http\\Middleware\CheckStaff',
            'check.pelanggan' => 'App\\Http\\Middleware\CheckPelanggan',
            'check.peternak' => \App\Http\Middleware\CheckPeternak::class,
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Custom 404 handler
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found',
                    'error' => [
                        'code' => 404,
                        'message' => 'Halaman yang Anda cari tidak ditemukan.'
                    ]
                ], 404);
            }

            if ($e->getStatusCode() === 404) {
                $errorService = app(ErrorService::class);
                $data = $errorService->getErrorPageData(404);

                return response()
                    ->view('errors.404', $data, 404)
                    ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
            }
        });

        // Handle other HTTP exceptions
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, Request $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'An error occurred',
                    'error' => [
                        'code' => $e->getStatusCode(),
                    ]
                ], $e->getStatusCode());
            }

            $errorService = app(ErrorService::class);
            $code = $e->getStatusCode();

            // Only handle common error codes
            if (in_array($code, [403, 404, 419, 429, 500, 503])) {
                $data = $errorService->getErrorPageData($code);
                return response()
                    ->view('errors.' . $code, $data, $code)
                    ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
            }
        });
    })->create();
