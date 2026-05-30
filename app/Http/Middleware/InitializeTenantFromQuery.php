<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;

class InitializeTenantFromQuery
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->has('tenant_id') && ! tenancy()->initialized) {
            $tenant = Tenant::find($request->query('tenant_id'));
            if ($tenant) {
                tenancy()->initialize($tenant);
            }
        }

        return $next($request);
    }
}
