<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Canvas Module — Schema module baru (mobile-first, generative UI)
            // Route ini terisolasi di /canvas/* dan tidak mengganggu route lama
            \Illuminate\Support\Facades\Route::middleware('web')
                ->group(base_path('routes/canvas.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\IdentifyTenant::class,
        ]);

        $middleware->alias([
            'role'          => \App\Http\Middleware\RoleMiddleware::class,
            'canvas.tenant' => \App\Http\Middleware\CanvasTenantMiddleware::class,
        ]);

        $middleware->redirectTo(
            fn ($request) => '/login'
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
