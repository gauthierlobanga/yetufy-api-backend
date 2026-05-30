<?php

namespace App\Http\Middleware;

use App\Events\UserLoggedIn;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogUserLastSeen
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Mettre à jour la dernière activité toutes les 5 minutes
            if (! $user->dernier_connexion || $user->dernier_connexion->lt(now()->subMinutes(5))) {
                event(new UserLoggedIn($user));
            }
        }

        return $next($request);
    }
}
