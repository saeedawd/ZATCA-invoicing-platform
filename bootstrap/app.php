<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'tenant' => \App\Domains\Tenancy\Middleware\SetTenantFromAuth::class,
        ]);

        // Livewire requests hit /livewire/update outside page route middleware,
        // so tenant context must be available on the whole web stack.
        $middleware->appendToGroup('web', \App\Domains\Tenancy\Middleware\SetTenantFromAuth::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
