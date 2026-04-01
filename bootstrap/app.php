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
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('dashboard'));

        $trusted = env('TRUSTED_PROXIES', '*');
        $at = $trusted === '*' ? '*' : array_values(array_filter(array_map('trim', explode(',', (string) $trusted))));
        $middleware->trustProxies(at: $at === [] ? '*' : $at);

        $middleware->alias([
            'guest' => \Illuminate\Auth\Middleware\RedirectIfAuthenticated::class,
            'active' => \App\Http\Middleware\EnsureUserIsActive::class,
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
            'reports' => \App\Http\Middleware\EnsureCanViewReports::class,
            'password.change' => \App\Http\Middleware\RedirectIfMustChangePassword::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontReportDuplicates();
    })->create();
