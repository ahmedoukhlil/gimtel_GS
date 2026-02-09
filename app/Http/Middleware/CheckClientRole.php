<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckClientRole
{
    /**
     * Vérifie que l'utilisateur authentifié a le rôle "client".
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Vous devez être connecté pour accéder à cette page.');
        }

        if (!Auth::user()->isClient()) {
            if ($request->expectsJson() || $request->header('X-Livewire')) {
                abort(403, 'Accès réservé aux clients.');
            }
            return redirect()->route('dashboard')
                ->with('error', 'Accès réservé aux clients.');
        }

        return $next($request);
    }
}
