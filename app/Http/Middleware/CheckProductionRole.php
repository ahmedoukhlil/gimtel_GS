<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckProductionRole
{
    /**
     * Vérifie que l'utilisateur authentifié a le rôle "direction_production".
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Vous devez être connecté pour accéder à cette page.');
        }

        if (!Auth::user()->isDirectionProduction()) {
            if (request()->expectsJson() || request()->header('X-Livewire')) {
                abort(403, 'Accès réservé à la direction production.');
            }
            return redirect()->route('dashboard')
                ->with('error', 'Accès réservé à la direction production.');
        }

        return $next($request);
    }
}
