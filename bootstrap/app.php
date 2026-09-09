<?php

use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\EnsureValidSessionTimeout;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\ViteManifestNotFoundException;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'session.timeout' => EnsureValidSessionTimeout::class,
        ]);

        $middleware->web(append: [
            EnsureValidSessionTimeout::class,
            EnsureUserIsActive::class,
        ]);

        $middleware->redirectGuestsTo('/login');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return null;
            }

            for ($current = $e; $current !== null; $current = $current->getPrevious()) {
                if ($current instanceof ViteManifestNotFoundException) {
                    return response()->view('errors.vite-manifest-missing', status: 500);
                }
            }

            return null;
        });
    })->create();
