<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HodMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->isHod()) {
            return redirect()->route('hod.login')
                ->with('error', 'You are not authorized to access the HOD portal.');
        }

        return $next($request);
    }
}
