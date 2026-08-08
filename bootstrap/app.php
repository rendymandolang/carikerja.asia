<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [\App\Http\Middleware\ApplySeoIndexingPolicy::class]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
            'candidate' => \App\Http\Middleware\EnsureCandidate::class,
            'recruiter' => \App\Http\Middleware\EnsureRecruiter::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
