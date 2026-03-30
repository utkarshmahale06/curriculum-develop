<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DepartmentMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->isDepartment()) {
            return redirect()->route('department.login')
                ->with('error', 'You are not authorized to access the department portal.');
        }

        return $next($request);
    }
}
