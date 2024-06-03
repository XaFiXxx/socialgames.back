<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        // Vérifie si l'utilisateur est authentifié et est admin
        if (Auth::check() && Auth::user()->is_admin) {
            return $next($request);
        }

        // Retourne une réponse JSON si l'utilisateur n'est pas admin
        return response()->json(['error' => "Vous n'avez pas accès à cette zone."], 403);
    }
}
