<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;

class IdentifyTenantForApi
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Sous-domaine
        $host = $request->getHost();
        $centralDomains = config('tenancy.central_domains', []);
        if (! in_array($host, $centralDomains)) {
            $slug = explode('.', $host)[0];
            $tenant = Tenant::where('slug', $slug)->firstOrFail();
            tenancy()->initialize($tenant);
        }
        // 2. Header X-Tenant (dev local)
        elseif ($slug = $request->header('X-Tenant')) {
            $tenant = Tenant::where('slug', $slug)->firstOrFail();
            tenancy()->initialize($tenant);
        } else {
            abort(404, 'Tenant not found');
        }

        return $next($request);
    }
}
