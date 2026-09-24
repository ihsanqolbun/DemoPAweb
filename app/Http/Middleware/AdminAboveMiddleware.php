<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAboveMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!in_array(auth()->user()?->role, ['super_admin', 'admin'])) {
            abort(403);
        }

        return $next($request);
    }
}