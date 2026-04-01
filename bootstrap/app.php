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

            if ($user instanceof User && $user->isHod()) {
                return route('hod.dashboard');
            }

            if ($user instanceof User && $user->isFaculty()) {
                return route('faculty.dashboard');
            }

            if ($user instanceof User && $user->isCdc()) {
                return route('cdc.dashboard');
            }

            return route('login');
        });

        Authenticate::redirectUsing(function ($request) {
            if ($request->is('department') || $request->is('department/*')) {
                return route('department.login');
            }

            if ($request->is('hod') || $request->is('hod/*')) {
                return route('hod.login');
            }

            if ($request->is('faculty') || $request->is('faculty/*')) {
                return route('faculty.login');
            }

            return route('login');
        });

        $middleware->alias([
            'cdc' => \App\Http\Middleware\CdcMiddleware::class,
            'department' => \App\Http\Middleware\DepartmentMiddleware::class,
            'hod' => \App\Http\Middleware\HodMiddleware::class,
            'faculty' => \App\Http\Middleware\FacultyMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
