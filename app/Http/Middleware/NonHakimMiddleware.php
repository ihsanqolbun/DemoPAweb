<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NonHakimMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->user()?->role === 'hakim') {
            abort(403);
        }

        return $next($request);
    }
}