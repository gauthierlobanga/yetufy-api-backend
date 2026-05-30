<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;

class InitializeTenancyForTenantDomains
{
    public function handle(Request $request, Closure $next)
    {
        if (in_array($request->getHost(), config('tenancy.central_domains', []), true)) {
            return $next($request);
        }

        if (function_exists('tenancy') && tenancy()->initialized) {
            return $next($request);
        }

        return app(InitializeTenancyByDomain::class)->handle($request, $next);
    }
}
