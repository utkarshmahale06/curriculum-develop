<?php

use App\Models\User;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
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
        RedirectIfAuthenticated::redirectUsing(function ($request) {
            $user = $request->user();

            if ($user instanceof User && $user->isDepartment()) {
                return route('department.dashboard');
            }

            if ($user instanceof User && $user->isCdc()) {
                return route('cdc.dashboard');
            }

            return route('login');
        });

        Authenticate::redirectUsing(function ($request) {
            return $request->is('department') || $request->is('department/*')
                ? route('department.login')
                : route('login');
        });

        $middleware->alias([
            'cdc' => \App\Http\Middleware\CdcMiddleware::class,
            'department' => \App\Http\Middleware\DepartmentMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
