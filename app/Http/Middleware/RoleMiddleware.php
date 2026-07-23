<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Parameter di middleware bisa berupa satu atau beberapa role dipisah koma.
        // Di Laravel, dipisah koma otomatis akan menjadi array variadic ...$roles
        
        if (! $request->user() || !in_array($request->user()->role, $roles)) {
            abort(403, 'Akses ditolak.');
        }

        return $next($request);
    }
}
