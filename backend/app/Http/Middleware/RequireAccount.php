<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireAccount
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Para órdenes, siempre requerir autenticación
        if ($request->routeIs('orders.*')) {
            if (!auth()->check()) {
                return response()->json([
                    'error' => 'Debes tener una cuenta para realizar pedidos',
                    'message' => 'Por seguridad, es necesario crear una cuenta antes de ordenar',
                    'require_registration' => true
                ], 401);
            }

            // Verificar que la cuenta esté verificada
            if (!auth()->user()->email_verified_at) {
                return response()->json([
                    'error' => 'Debes verificar tu email antes de ordenar',
                    'require_verification' => true
                ], 403);
            }
        }

        return $next($request);
    }
}