<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'locale' => \App\Http\Middleware\SetLocale::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'vendor.user' => \App\Http\Middleware\VendorUserMiddleware::class,
            'admin.access' => \App\Http\Middleware\EnsureAdminAccess::class,
        ]);

        // Global API rate limiting (60 requests per minute for all API routes)
        $middleware->api(prepend: [
            // \Illuminate\Routing\Middleware\ThrottleRequests::class.':60,1',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle ModelNotFoundException (any model) for API requests
        $exceptions->render(function (
            \Illuminate\Database\Eloquent\ModelNotFoundException $e,
            \Illuminate\Http\Request $request
        ) {
            if ($request->expectsJson() || $request->is('api/*') || $request->wantsJson() || str_starts_with($request->path(), 'api/')) {
                $model = class_basename($e->getModel());
                $message = $model.' not found.';

                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 404);
            }

            return null;
        });

        // Handle NotFoundHttpException that wraps ModelNotFoundException (any model) for API requests
        $exceptions->render(function (
            \Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e,
            \Illuminate\Http\Request $request
        ) {
            $previous = $e->getPrevious();

            if ($previous instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                if ($request->expectsJson() || $request->is('api/*') || $request->wantsJson() || str_starts_with($request->path(), 'api/')) {
                    $model = class_basename($previous->getModel());
                    $message = $model.' not found.';

                    return response()->json([
                        'success' => false,
                        'message' => $message,
                    ], 404);
                }
            }

            return null;
        });
    })
    ->create();
