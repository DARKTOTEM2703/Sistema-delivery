<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, $role, $restaurantId = null)
    {
        if (!$request->user() || !$request->user()->hasRole($role, $restaurantId)) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        return $next($request);
    }
}