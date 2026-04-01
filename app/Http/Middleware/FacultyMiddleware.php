<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FacultyMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->isFaculty()) {
            return redirect()->route('faculty.login')
                ->with('error', 'You are not authorized to access the faculty portal.');
        }

        return $next($request);
    }
}
