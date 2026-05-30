<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCentralDomain
{
    public function handle(Request $request, Closure $next): Response
    {
        // Si le domaine de la requête n'est pas un domaine central, on redirige
        if (! in_array($request->getHost(), config('tenancy.central_domains', []))) {
            return redirect('/');
        }

        return $next($request);
    }
}
