<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ModeratorMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->isModerator()) {
            return redirect()->route('moderator.login')
                ->with('error', 'You are not authorized to access the moderator portal.');
        }

        return $next($request);
    }
}
