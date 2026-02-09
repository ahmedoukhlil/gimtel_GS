<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CanManageStock
{
    /**
     * Handle an incoming request.
     * 
     * Vérifie que l'utilisateur est authentifié et peut gérer le stock (admin ou admin_stock).
     * Redirige avec un message d'erreur si l'accès est refusé.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier que l'utilisateur est authentifié
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Vous devez être connecté pour accéder à cette page.');
        }

        $user = Auth::user();

        // Vérifier que l'utilisateur peut gérer le stock (admin, admin_stock ou direction_production)
        if (!$user->canManageStock()) {
            if ($request->expectsJson() || $request->header('X-Livewire')) {
                abort(403, 'Accès non autorisé.');
            }
            return redirect()->route('dashboard')
                ->with('error', 'Accès non autorisé.');
        }

        return $next($request);
    }
}
