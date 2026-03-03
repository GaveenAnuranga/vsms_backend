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
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Always return JSON for API routes
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
                $message = app()->hasDebugModeEnabled()
                    ? $e->getMessage()
                    : ($status === 500 ? 'Server Error' : $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'exception' => app()->hasDebugModeEnabled() ? get_class($e) : null,
                ], $status);
            }
        });
    })->create();
