<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckCaja
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login');
        }
        // Verifica si el usuario tiene una caja abierta
        if (!$user->cajas()->where('estado', 'abierto')->exists()) {
            return redirect()->route('caja.apertura')->with('error', 'Debes abrir una caja antes de acceder a ventas.');
        }
        return $next($request);
    }
}
